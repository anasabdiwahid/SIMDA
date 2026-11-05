<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuditLogger;
use App\Helpers\Env;

final class MailService
{
    /**
     * Generate a unique default password based on the user's first name,
     * 4 random digits, and special characters.
     * Example: "Ali Abdi Ali" -> "ali8788@!"
     */
    public static function generateDefaultPassword(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));
        $firstName = strtolower((string) ($parts[0] ?? 'simda'));
        $cleanFirst = preg_replace('/[^a-z0-9]/', '', $firstName);
        if (strlen((string) $cleanFirst) < 2) {
            $cleanFirst = 'simda';
        }
        $digits = (string) mt_rand(1000, 9999);
        return $cleanFirst . $digits . '@!';
    }

    /**
     * Send an SMTP email using Gmail or configured SMTP provider over SSL/TLS.
     * Supports both plain HTML/alternative and multipart/related with inline CID images.
     */
    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $plainBody = '',
        array $inlineAttachments = []
    ): array {
        $host = (string) Env::get('MAIL_HOST', 'smtp.gmail.com');
        $port = (int) Env::get('MAIL_PORT', 465);
        $username = (string) Env::get('MAIL_USERNAME', 'anasrk85@gmail.com');
        $rawPassword = (string) Env::get('MAIL_PASSWORD', 'pcxgozgqinnputbo');
        $password = str_replace(' ', '', $rawPassword);
        $fromEmail = (string) Env::get('MAIL_FROM_ADDRESS', $username);
        $fromName = (string) Env::get('MAIL_FROM_NAME', 'SIMDA Somali Islamic Microfinance');
        $encryption = (string) Env::get('MAIL_ENCRYPTION', 'ssl');

        $remoteSocket = ($encryption === 'ssl' && !str_starts_with($host, 'ssl://'))
            ? "ssl://{$host}:{$port}"
            : "{$host}:{$port}";

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $socket = @stream_socket_client($remoteSocket, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            $errMsg = "SMTP Connection failed to {$remoteSocket}: {$errstr} ({$errno})";
            error_log($errMsg);
            return ['success' => false, 'error' => $errMsg];
        }

        $readResponse = function () use ($socket): string {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (strlen($str) >= 4 && substr($str, 3, 1) === ' ') {
                    break;
                }
            }
            return $data;
        };

        $sendCommand = function (string $cmd) use ($socket, $readResponse): string {
            fputs($socket, $cmd . "\r\n");
            return $readResponse();
        };

        $banner = $readResponse();
        if (substr($banner, 0, 3) !== '220') {
            fclose($socket);
            return ['success' => false, 'error' => "Unexpected SMTP banner: {$banner}"];
        }

        $sendCommand("EHLO localhost");
        $auth = $sendCommand("AUTH LOGIN");
        if (substr($auth, 0, 3) !== '334') {
            fclose($socket);
            return ['success' => false, 'error' => "AUTH LOGIN rejected: {$auth}"];
        }

        $uRes = $sendCommand(base64_encode($username));
        if (substr($uRes, 0, 3) !== '334') {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP Username rejected: {$uRes}"];
        }

        $pRes = $sendCommand(base64_encode($password));
        if (substr($pRes, 0, 3) !== '235') {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP Authentication failed: {$pRes}"];
        }

        $mFrom = $sendCommand("MAIL FROM:<{$fromEmail}>");
        if (substr($mFrom, 0, 3) !== '250') {
            fclose($socket);
            return ['success' => false, 'error' => "MAIL FROM rejected: {$mFrom}"];
        }

        $rcpt = $sendCommand("RCPT TO:<{$to}>");
        if (substr($rcpt, 0, 3) !== '250') {
            fclose($socket);
            return ['success' => false, 'error' => "RCPT TO rejected: {$rcpt}"];
        }

        $dataCmd = $sendCommand("DATA");
        if (substr($dataCmd, 0, 3) !== '354') {
            fclose($socket);
            return ['success' => false, 'error' => "DATA command rejected: {$dataCmd}"];
        }

        $textPlain = $plainBody !== '' ? $plainBody : strip_tags($htmlBody);

        // Standard high-deliverability email headers (prevents spam classifier flags)
        $msgDomain = (strpos($fromEmail, '@') !== false) ? substr(strrchr($fromEmail, '@'), 1) : 'gmail.com';
        $messageId = '<simda.' . bin2hex(random_bytes(12)) . '.' . time() . '@' . $msgDomain . '>';

        $baseHeaders = [
            'MIME-Version: 1.0',
            'Message-ID: ' . $messageId,
            'Date: ' . date('r'),
            'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
            'Reply-To: <' . $fromEmail . '>',
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'Auto-Submitted: auto-generated',
            'X-Auto-Response-Suppress: All',
        ];

        if (!empty($inlineAttachments)) {
            $relBoundary = 'bnd_rel_' . md5(uniqid((string) mt_rand(), true));
            $altBoundary = 'bnd_alt_' . md5(uniqid((string) mt_rand(), true));

            $headers = array_merge($baseHeaders, [
                'Content-Type: multipart/related; boundary="' . $relBoundary . '"',
            ]);

            $payload = implode("\r\n", $headers) . "\r\n\r\n";
            $payload .= "--{$relBoundary}\r\n";
            $payload .= "Content-Type: multipart/alternative; boundary=\"{$altBoundary}\"\r\n\r\n";

            $payload .= "--{$altBoundary}\r\n";
            $payload .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $payload .= chunk_split(base64_encode($textPlain)) . "\r\n";

            $payload .= "--{$altBoundary}\r\n";
            $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $payload .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $payload .= "--{$altBoundary}--\r\n\r\n";

            foreach ($inlineAttachments as $att) {
                $cid = (string) ($att['cid'] ?? 'img');
                $filePath = (string) ($att['path'] ?? '');
                $mimeType = (string) ($att['mime'] ?? 'image/png');
                $filename = (string) ($att['filename'] ?? basename($filePath));

                if (is_file($filePath)) {
                    $fileBytes = file_get_contents($filePath);
                    $payload .= "--{$relBoundary}\r\n";
                    $payload .= "Content-Type: {$mimeType}; name=\"{$filename}\"\r\n";
                    $payload .= "Content-Transfer-Encoding: base64\r\n";
                    $payload .= "Content-ID: <{$cid}>\r\n";
                    $payload .= "Content-Disposition: inline; filename=\"{$filename}\"\r\n\r\n";
                    $payload .= chunk_split(base64_encode($fileBytes)) . "\r\n";
                }
            }

            $payload .= "--{$relBoundary}--\r\n";
            $payload .= ".\r\n";
        } else {
            $boundary = 'bnd_' . md5(uniqid((string) mt_rand(), true));
            $headers = array_merge($baseHeaders, [
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            ]);

            $payload = implode("\r\n", $headers) . "\r\n\r\n";
            $payload .= "--{$boundary}\r\n";
            $payload .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $payload .= chunk_split(base64_encode($textPlain)) . "\r\n";
            $payload .= "--{$boundary}\r\n";
            $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $payload .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $payload .= "--{$boundary}--\r\n";
            $payload .= ".\r\n";
        }

        fputs($socket, $payload);
        $finalRes = $readResponse();
        $sendCommand("QUIT");
        fclose($socket);

        if (substr($finalRes, 0, 3) === '250') {
            return ['success' => true, 'response' => trim($finalRes)];
        }

        return ['success' => false, 'error' => "Delivery failed: {$finalRes}"];
    }

    /**
     * Send professional branded welcome email when a user account is created.
     */
    public static function sendWelcomeUserEmail(
        array $userData,
        string $plainPassword,
        ?array $branch,
        array $roleInfo,
        string $locale = 'so'
    ): array {
        $fullName = (string) ($userData['full_name'] ?? 'Shaqaale SIMDA');
        $email = (string) ($userData['email'] ?? '');
        $username = (string) ($userData['username'] ?? '');
        $roleSlug = (string) ($roleInfo['slug'] ?? '');
        $roleNameSo = (string) ($roleInfo['name_so'] ?? $roleSlug);
        $roleNameEn = (string) ($roleInfo['name_en'] ?? $roleSlug);

        $appUrl = (string) Env::get('APP_URL', 'http://localhost/SIMDA');
        $loginUrl = rtrim($appUrl, '/') . '/index.php?route=login';

        // Role title formatting
        $roleLabel = match ($roleSlug) {
            'manager' => 'Maamulaha Guud ee Shirkadda (General Operations Manager)',
            'branch_manager' => 'Maamulaha Laanta (Head of Branch)',
            'loan_officer' => 'Sarkaalka Maalgelinta / Shaqaale (Financing Officer)',
            'client' => 'Macmiilka SIMDA (Registered Client)',
            default => $roleNameSo . ' (' . $roleNameEn . ')',
        };

        // Branch description formatting
        if ($roleSlug === 'manager') {
            $branchText = 'Dhammaan Laamaha Shirkadda (Company-Wide - All Branches)';
            $branchSubtext = 'Waxaad maamulaysaa dhammaan xafiisyada iyo laamaha shirkadda SIMDA.';
        } elseif (!empty($branch)) {
            $bName = (string) ($branch['name'] ?? 'Laanta Gaarka ah');
            $bCode = (string) ($branch['code'] ?? '');
            $bCity = (string) ($branch['city'] ?? '');
            $branchText = "{$bName} (" . ($bCode !== '' ? $bCode : 'Branch') . ")" . ($bCity !== '' ? " - {$bCity}" : '');
            $branchSubtext = "Laantaada loo qoondeeyay oo aad ka hawlgali doonto.";
        } else {
            $branchText = 'Xarunta Guud (Head Office)';
            $branchSubtext = 'Xarunta dhexe ee SIMDA.';
        }

        $subject = match ($roleSlug) {
            'manager' => "SIMDA: Akoonkaaga Cusub ee Maamulaha Guud (General Operations Manager)",
            'branch_manager' => "SIMDA: Akoonkaaga Maamulaha Laanta ({$branchText})",
            default => "SIMDA: Macluumaadka Gelitaanka Akoonkaaga Cusub",
        };

        // Prepare inline logo attachment for CID embedding (supported by Gmail, Outlook, Apple Mail)
        $logoPath = dirname(__DIR__, 2) . '/public/assets/images/simda-logo.png';
        $inlineAttachments = [];
        if (is_file($logoPath)) {
            $inlineAttachments[] = [
                'cid' => 'simda_logo',
                'path' => $logoPath,
                'mime' => 'image/png',
                'filename' => 'simda-logo.png',
            ];
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background-color:#F0F4F2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1D2D27;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F0F4F2;padding:30px 10px;">
        <tr>
            <td align="center">
                <!-- MAIN CONTAINER -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(12,45,36,0.12);border:1px solid #E2EBE6;">
                    
                    <!-- HEADER BANNER -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #0C2D24 0%, #164E3F 100%);padding:36px 30px 28px;text-align:center;border-bottom:4px solid #D6B66C;">
                            <!-- BRAND LOGO PILL -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 14px;">
                                <tr>
                                    <td style="background-color:#FFFFFF;border-radius:14px;padding:10px 22px;box-shadow:0 6px 18px rgba(0,0,0,0.22);text-align:center;">
                                        <img src="cid:simda_logo" alt="SIMDA" width="160" style="display:block;margin:0 auto;max-width:160px;height:auto;border:0;outline:none;" />
                                    </td>
                                </tr>
                            </table>
                            <h1 style="margin:0 0 4px;color:#FFFFFF;font-size:24px;font-weight:900;letter-spacing:1px;font-family:Georgia,serif;">SIMDA</h1>
                            <p style="margin:0;color:#D6B66C;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;">Somali Islamic Microfinance Development Association</p>
                            <p style="margin:5px 0 0;color:rgba(255,255,255,0.78);font-size:11px;">Building Trust • Islamic Microfinance</p>
                        </td>
                    </tr>

                    <!-- BODY CONTENT -->
                    <tr>
                        <td style="padding:36px 34px 26px;">
                            <h2 style="margin:0 0 10px;color:#0C2D24;font-size:22px;font-weight:800;">
                                Salaamu Calaykum, {$fullName}! 👋
                            </h2>
                            <p style="margin:0 0 20px;color:#4A5E56;font-size:15px;line-height:1.6;">
                                Waxaa si guul leh lagugu diiwaangeliyay nidaamka maamulka maaliyadda Islaamiga ah ee <strong>SIMDA</strong>. Hoos waxaad ka helaysaa doorkaaga shaqo, laantaada loo qoondeeyay, iyo macluumaadka aad nidaamka kaga geli doonto.
                            </p>

                            <!-- ACCOUNT DETAILS CARD -->
                            <div style="background-color:#F8FAF9;border:1px solid #DFE8E3;border-radius:14px;padding:22px 24px;margin-bottom:26px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;width:38%;color:#6B8076;font-size:13px;font-weight:600;">Magaca Buuxa:</td>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#0C2D24;font-size:14px;font-weight:700;">{$fullName}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#6B8076;font-size:13px;font-weight:600;">Doorka Shaqada:</td>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#0C2D24;font-size:14px;font-weight:700;">
                                            <span style="background-color:#EBF5F0;color:#0F5132;padding:3px 10px;border-radius:20px;font-size:12px;display:inline-block;border:1px solid #C3E6CB;">
                                                {$roleLabel}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#6B8076;font-size:13px;font-weight:600;">Qoondaynta Laanta:</td>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#0C2D24;font-size:14px;font-weight:700;">
                                            {$branchText}
                                            <div style="font-size:11px;color:#788E84;font-weight:400;margin-top:2px;">{$branchSubtext}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#6B8076;font-size:13px;font-weight:600;">Username-ka Gelitaanka:</td>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#0C2D24;font-size:14px;font-weight:700;font-family:monospace;">{$username}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#6B8076;font-size:13px;font-weight:600;">Email-kaaga:</td>
                                        <td style="padding:8px 0;border-bottom:1px solid #EBEFEA;color:#0C2D24;font-size:14px;font-weight:600;">{$email}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 0 6px;color:#6B8076;font-size:13px;font-weight:600;">Furaha Sirta ah (Password):</td>
                                        <td style="padding:12px 0 6px;">
                                            <span style="display:inline-block;background:#E7F4EE;color:#0C2D24;font-family:'Courier New',Courier,monospace;font-size:17px;font-weight:800;letter-spacing:1px;padding:6px 14px;border-radius:8px;border:1.5px dashed #1B7A53;">
                                                {$plainPassword}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- LOGIN BUTTON -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:26px 0 20px;">
                                <tr>
                                    <td align="center">
                                        <a href="{$loginUrl}" target="_blank" style="background:linear-gradient(135deg,#0C2D24 0%,#1B7A53 100%);color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:800;padding:14px 34px;border-radius:30px;display:inline-block;box-shadow:0 6px 18px rgba(12,45,36,0.28);letter-spacing:0.5px;">
                                            🚀 Gal Nidaamka SIMDA (Sign In)
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- SECURITY NOTICE -->
                            <div style="background-color:#FFF9E6;border-left:4px solid #D6B66C;padding:14px 16px;border-radius:6px;margin-top:20px;">
                                <p style="margin:0;color:#7A5E14;font-size:12.5px;line-height:1.5;">
                                    🔒 <strong>Talada Amniga:</strong> Fadlan furahaaga sirta ah ha la wadaagin cidna. Marka ugu horreysa ee aad nidaamka gasho, waxaad ka beddeli kartaa qaybta profile-kaaga ama settings-ka akoonka.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#F8FAF9;padding:24px 30px;text-align:center;border-top:1px solid #E2EBE6;">
                            <p style="margin:0 0 6px;color:#0C2D24;font-size:13px;font-weight:700;">SIMDA - Somali Islamic Microfinance Development Association</p>
                            <p style="margin:0 0 10px;color:#788E84;font-size:11.5px;">Mogadishu Main Boulevard, Hodan District, Mogadishu, Somalia</p>
                            <p style="margin:0;color:#94A8A0;font-size:11px;">
                                Fariintan waxaa si toos ah uga soo dirtay nidaamka SIMDA. Haddii aadan adigu codsan diiwaangelintan, fadlan la xiriir maamulka shirkadda.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $plain = <<<TEXT
SIMDA - Somali Islamic Microfinance Development Association
Ku soo dhowow Nidaamka SIMDA!

Salaamu Calaykum, {$fullName}!
Waxaa si guul leh lagugu diiwaangeliyay nidaamka SIMDA.

Macluumaadka Akoonkaaga:
- Magaca Buuxa: {$fullName}
- Doorka Shaqada: {$roleLabel}
- Qoondaynta Laanta: {$branchText} ({$branchSubtext})
- Username-ka: {$username}
- Email-ka: {$email}
- Furaha Sirta ah (Password): {$plainPassword}

Link-ga Gelitaanka:
{$loginUrl}

Talada Amniga: Fadlan furahaaga sirta ah ha la wadaagin cidna. Marka ugu horreysa ee aad gasho nidaamka waad beddeli kartaa.

Wabilahi Tawfiiq,
Maamulka SIMDA
TEXT;

        return self::send($email, $subject, $html, $plain, $inlineAttachments);
    }
}

