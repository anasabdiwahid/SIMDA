/**
 * SIMDA local WhatsApp gateway.
 *
 * Open http://127.0.0.1:3001 and scan the QR code with WhatsApp
 * (Settings -> Linked devices -> Link a device). After that SIMDA's
 * WhatsAppService posts messages to /send and they go out from that phone.
 *
 * Listens on 127.0.0.1 only. The session is stored in ../storage/whatsapp-auth
 * (blocked from the web by the root .htaccess and ignored by git).
 */
'use strict';

const fs = require('fs');
const path = require('path');
const http = require('http');
const pino = require('pino');
const QRCode = require('qrcode');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    Browsers,
    makeCacheableSignalKeyStore,
    BufferJSON,
} = require('@whiskeysockets/baileys');

const ROOT = path.resolve(__dirname, '..');

// Read WHATSAPP_GATEWAY_* settings from SIMDA's .env so both sides share one key.
function readEnv() {
    const env = {};
    try {
        for (const line of fs.readFileSync(path.join(ROOT, '.env'), 'utf8').split(/\r?\n/)) {
            const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/);
            if (m) env[m[1]] = m[2].replace(/^["']|["']$/g, '');
        }
    } catch (_) { /* no .env: use defaults */ }
    return env;
}

const env = readEnv();
const PORT = parseInt(process.env.PORT || env.WHATSAPP_GATEWAY_PORT || '3001', 10);
const API_KEY = process.env.WHATSAPP_GATEWAY_KEY || env.WHATSAPP_GATEWAY_KEY || '';
const AUTH_DIR = path.join(ROOT, 'storage', 'whatsapp-auth');
const logger = pino({ level: 'warn' });

// Messages we sent, by id. When a phone can't decrypt a message it asks us to resend it;
// without this copy Baileys can't, and the phone shows "Waiting for this message" forever.
// Kept on disk so retries still work after a restart. (Images are stored as WhatsApp's
// upload reference, not the picture itself, so the file stays small.)
const SENT_FILE = path.join(ROOT, 'storage', 'whatsapp-sent.json');
const SENT_MAX = 1000;
const sentMessages = new Map();
try {
    for (const [id, msg] of JSON.parse(fs.readFileSync(SENT_FILE, 'utf8'), BufferJSON.reviver)) sentMessages.set(id, msg);
} catch (_) { /* first run or unreadable file: start empty */ }
let saveTimer = null;
function rememberSent(sent) {
    if (!sent || !sent.key || !sent.key.id || !sent.message) return;
    sentMessages.set(sent.key.id, sent.message);
    while (sentMessages.size > SENT_MAX) sentMessages.delete(sentMessages.keys().next().value);
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        fs.writeFile(SENT_FILE, JSON.stringify([...sentMessages], BufferJSON.replacer), () => {});
    }, 500);
}

// How often each message was retried (Baileys stops after a few attempts).
const retryCounts = new Map();
const msgRetryCounterCache = {
    get: (k) => retryCounts.get(k),
    set: (k, v) => { retryCounts.set(k, v); if (retryCounts.size > 5000) retryCounts.delete(retryCounts.keys().next().value); },
    del: (k) => { retryCounts.delete(k); },
    flushAll: () => { retryCounts.clear(); },
};

let sock = null;
let state = { status: 'starting', qr: null, me: null, error: null };
let pairing = { code: null, error: null };

async function start() {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
    const { state: authState, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
    let version;
    try {
        ({ version } = await fetchLatestBaileysVersion());
    } catch (_) { /* fall back to the library default */ }

    sock = makeWASocket({
        ...(version ? { version } : {}),
        auth: {
            creds: authState.creds,
            // Cache in front of the key files so keys stay in step under load.
            keys: makeCacheableSignalKeyStore(authState.keys, logger),
        },
        logger,
        msgRetryCounterCache,
        // Lets Baileys resend a message a phone could not decrypt.
        getMessage: async (key) => {
            const msg = sentMessages.get(key.id);
            console.log('[gateway] Phone asked to resend ' + key.id + ' (' + (key.remoteJid || '?') + '): ' + (msg ? 'resending' : 'NOT FOUND'));
            return msg;
        },
        browser: Browsers.windows('SIMDA'),
        markOnlineOnConnect: false,
        qrTimeout: 60000,
        // Slow connections: give the WhatsApp handshake and queries more time.
        connectTimeoutMs: 90000,
        defaultQueryTimeoutMs: 90000,
        keepAliveIntervalMs: 25000,
        syncFullHistory: false,
    });

    sock.ev.on('creds.update', saveCreds);

    // Delivery status of what we sent (2 = sent, 3 = delivered, 4 = read), for troubleshooting.
    sock.ev.on('messages.update', (updates) => {
        for (const u of updates) {
            if (!u.key || !u.key.fromMe || !sentMessages.has(u.key.id)) continue;
            const st = u.update && u.update.status;
            if (st) console.log('[gateway] ' + u.key.id + ' status ' + st + ' (' + (u.key.remoteJid || '') + ')');
        }
    });

    sock.ev.on('connection.update', async (u) => {
        const { connection, lastDisconnect, qr } = u;
        if (qr) {
            state = { status: 'waiting_for_scan', qr: await QRCode.toDataURL(qr, { margin: 1, width: 320 }), me: null, error: null };
            console.log('[gateway] New QR code ready: open http://127.0.0.1:' + PORT);
        }
        if (connection === 'open') {
            pairing = { code: null, error: null };
            const id = (sock.user && sock.user.id) || '';
            state = { status: 'connected', qr: null, me: id.split(':')[0].split('@')[0], error: null };
            console.log('[gateway] Connected as +' + state.me);
        }
        if (connection === 'close') {
            const code = lastDisconnect && lastDisconnect.error && lastDisconnect.error.output
                ? lastDisconnect.error.output.statusCode : 0;
            if (code === DisconnectReason.loggedOut) {
                console.log('[gateway] Logged out from the phone. Clearing session; a new QR code will appear.');
                fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            } else {
                console.log('[gateway] Connection closed (' + code + '), reconnecting...');
            }
            state = { status: 'reconnecting', qr: null, me: null, error: String(code || '') };
            if (pairing.code) pairing = { code: null, error: 'Koodhkii wuu dhacay, mid cusub qaado / Code expired, request a new one.' };
            setTimeout(() => start().catch(fail), 3000);
        }
    });
}

function waitForConnection(maxMs) {
    return new Promise((resolve) => {
        const until = Date.now() + maxMs;
        (function check() {
            if (state.status === 'connected' && sock) return resolve(true);
            if (Date.now() >= until) return resolve(false);
            setTimeout(check, 250);
        })();
    });
}

function fail(err) {
    console.error('[gateway] Error:', err);
    state = { status: 'error', qr: null, me: null, error: String(err && err.message || err) };
    setTimeout(() => start().catch(fail), 10000);
}

function normalize(number) {
    let d = String(number || '').replace(/\D+/g, '');
    if (d.startsWith('00')) d = d.slice(2);
    if (d.startsWith('0')) d = '252' + d.slice(1);
    else if (d.length === 9 && /^[67]/.test(d)) d = '252' + d;
    return d.length >= 10 && d.length <= 15 ? d : null;
}

function json(res, code, body) {
    res.writeHead(code, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(JSON.stringify(body));
}

function readBody(req) {
    return new Promise((resolve, reject) => {
        let data = '';
        req.on('data', (c) => { data += c; if (data.length > 100000) req.destroy(); });
        req.on('end', () => { try { resolve(data ? JSON.parse(data) : {}); } catch (e) { reject(e); } });
        req.on('error', reject);
    });
}

function page() {
    const s = state;
    const body = s.status === 'connected'
        ? `<div class="ok">✅ Ku xiran / Connected</div><p>WhatsApp: <b>+${s.me}</b></p><p>SIMDA hadda fariimaha WhatsApp ayay diri kartaa.</p>`
        : s.qr
            ? (pairing.code
                ? `<p>Koodhka isku xirka / Pairing code:</p><div class="code">${pairing.code}</div><ol><li>WhatsApp: <b>Settings → Linked devices → Link a device</b></li><li>Guji <b>Link with phone number instead</b></li><li>Qor koodhkan</li></ol><p><a href="/?reset=1">QR code ku noqo</a></p>`
                : `<img src="${s.qr}" alt="WhatsApp QR code"><ol><li>Fur WhatsApp taleefankaaga</li><li><b>Settings → Linked devices → Link a device</b></li><li>Scan gareey QR-kan</li></ol>
<hr><p><b>QR ma shaqeynayo?</b> Lambarkaaga geli, koodh ayaad heleysaa:</p>
<form method="get" action="/pair"><input name="phone" placeholder="252613496943" required> <button>Hel koodh</button></form>
${pairing.error ? `<p class="err">${pairing.error}</p>` : ''}`)
            : `<p>⏳ ${s.status}… ${s.error ? '(' + s.error + ')' : ''}</p>`;
    return `<!doctype html><html lang="so"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SIMDA WhatsApp</title>${s.status === 'connected' ? '' : '<meta http-equiv="refresh" content="5">'}
<style>body{font-family:system-ui,sans-serif;background:#f4f7f5;color:#163;display:flex;justify-content:center;padding:32px 16px;margin:0}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 20px #0001;padding:28px;max-width:420px;width:100%;text-align:center}
h1{font-size:20px;margin:0 0 16px}.code{font:700 32px monospace;letter-spacing:4px;background:#eef6f0;border-radius:10px;padding:12px;margin:8px 0}
input{padding:8px;border:1px solid #bcc;border-radius:8px;width:160px}button{padding:8px 14px;border:0;border-radius:8px;background:#0a7d3b;color:#fff}.err{color:#b00}img{width:100%;max-width:320px}ol{text-align:left;line-height:1.7}.ok{font-size:22px;font-weight:700;color:#0a7d3b}</style>
</head><body><div class="card"><h1>SIMDA · WhatsApp Gateway</h1>${body}</div></body></html>`;
}

const server = http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://127.0.0.1');
    // Requests that arrive through a Cloudflare tunnel (the live site) may only send, with the key.
    // Linking a phone (/, /pair) stays on this computer, so nobody online can take over the gateway.
    const viaTunnel = Boolean(req.headers['cf-ray'] || req.headers['cf-connecting-ip']);
    if (viaTunnel && !(req.method === 'POST' && url.pathname === '/send')) {
        return json(res, 404, { success: false, error: 'Not found.' });
    }
    if (viaTunnel && !API_KEY) {
        return json(res, 403, { success: false, error: 'Gateway key is not set.' });
    }
    if (req.method === 'GET' && url.pathname === '/pair') {
        const phone = normalize(url.searchParams.get('phone'));
        pairing = { code: null, error: null };
        if (!phone) pairing.error = 'Lambarka sax ma aha / Invalid number.';
        else if (!sock || state.status === 'connected') pairing.error = 'Gateway is not waiting for a link.';
        else {
            try {
                const code = await sock.requestPairingCode(phone);
                pairing.code = String(code).replace(/^(.{4})(.{4})$/, '$1-$2');
                console.log('[gateway] Pairing code issued for +' + phone);
            } catch (e) {
                pairing.error = 'Koodh lama helin: ' + String(e && e.message || e);
            }
        }
        res.writeHead(302, { Location: '/' });
        return res.end();
    }
    if (req.method === 'GET' && url.pathname === '/') {
        if (url.searchParams.get('reset')) pairing = { code: null, error: null };
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' });
        return res.end(page());
    }
    if (req.method === 'GET' && url.pathname === '/status') {
        return json(res, 200, { status: state.status, connected: state.status === 'connected', me: state.me });
    }
    if (req.method === 'POST' && url.pathname === '/send') {
        if (API_KEY && req.headers['x-api-key'] !== API_KEY) return json(res, 401, { success: false, error: 'Invalid API key.' });
        // A dropped connection reconnects by itself within seconds: wait for it instead of failing.
        // (SIMDA waits 20 s for a text message, so stay well under that.)
        if (state.status === 'reconnecting' || state.status === 'starting') {
            await waitForConnection(12000);
        }
        if (state.status !== 'connected' || !sock) {
            const linkNeeded = state.status === 'waiting_for_scan' || state.status === 'error';
            return json(res, 503, {
                success: false,
                error: linkNeeded
                    ? 'WhatsApp is not connected. Scan the QR code at http://127.0.0.1:' + PORT
                    : 'WhatsApp is reconnecting. Please try again in a minute.',
            });
        }
        let body;
        try { body = await readBody(req); } catch (_) { return json(res, 400, { success: false, error: 'Invalid JSON.' }); }
        const to = normalize(body.to);
        const message = String(body.message || '').trim();
        if (!to || !message) return json(res, 422, { success: false, error: 'Fields "to" and "message" are required.' });
        // Optional picture (message becomes its caption). Only PNG/JPG files inside SIMDA's image folders.
        let image = null;
        if (body.image) {
            const file = path.resolve(ROOT, String(body.image));
            const allowed = [path.join(ROOT, 'public', 'assets', 'images'), path.join(ROOT, 'storage', 'email')];
            if (!allowed.some((dir) => file.startsWith(dir + path.sep)) || !/\.(png|jpe?g)$/i.test(file) || !fs.existsSync(file)) {
                return json(res, 422, { success: false, error: 'Image not allowed or not found.' });
            }
            image = fs.readFileSync(file);
        }
        try {
            const jid = to + '@s.whatsapp.net';
            const [check] = await sock.onWhatsApp(jid);
            if (!check || !check.exists) return json(res, 404, { success: false, error: `+${to} is not on WhatsApp.` });
            const sent = await sock.sendMessage(check.jid || jid, image ? { image, caption: message } : { text: message });
            rememberSent(sent);
            return json(res, 200, { success: true, to, id: sent && sent.key ? sent.key.id : null });
        } catch (e) {
            return json(res, 500, { success: false, error: String(e && e.message || e) });
        }
    }
    json(res, 404, { success: false, error: 'Not found.' });
});

server.listen(PORT, '127.0.0.1', () => {
    console.log('[gateway] SIMDA WhatsApp gateway on http://127.0.0.1:' + PORT);
    start().catch(fail);
});
