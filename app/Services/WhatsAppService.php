<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Env;
use Throwable;

/**
 * WhatsApp notifications.
 *
 * Every email SIMDA sends is mirrored to the recipient's WhatsApp when their
 * phone number is on file (users or clients table). Configure in .env:
 *   WHATSAPP_ENABLED=true
 *   WHATSAPP_PROVIDER=local    -> free gateway in whatsapp-gateway/ (scan QR at http://127.0.0.1:3001)
 *     WHATSAPP_GATEWAY_URL, WHATSAPP_GATEWAY_KEY
 *   WHATSAPP_PROVIDER=maytapi  -> Maytapi API
 *     MAYTAPI_PRODUCT_ID, MAYTAPI_PHONE_ID, MAYTAPI_API_TOKEN
 * A WhatsApp failure never blocks the email or the request that triggered it.
 */
final class WhatsAppService
{
    private const MAX_LENGTH = 3500;

    public static function provider(): string
    {
        return strtolower(trim((string) Env::get('WHATSAPP_PROVIDER', 'local'))) === 'maytapi' ? 'maytapi' : 'local';
    }

    public static function isEnabled(): bool
    {
        $flag = strtolower(trim((string) Env::get('WHATSAPP_ENABLED', 'false')));
        if (!in_array($flag, ['1', 'true', 'yes', 'on'], true)) {
            return false;
        }
        if (self::provider() === 'local') {
            return trim((string) Env::get('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3001')) !== '';
        }
        return trim((string) Env::get('MAYTAPI_PRODUCT_ID', '')) !== ''
            && trim((string) Env::get('MAYTAPI_PHONE_ID', '')) !== ''
            && trim((string) Env::get('MAYTAPI_API_TOKEN', '')) !== '';
    }

    /**
     * Send a plain-text WhatsApp message to a phone number.
     */
    public static function send(string $phone, string $message): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp is not configured (WHATSAPP_ENABLED / WHATSAPP_PROVIDER in .env).'];
        }
        $number = self::normalizePhone($phone);
        if ($number === null) {
            return ['success' => false, 'error' => "Invalid phone number: {$phone}"];
        }
        $message = trim($message);
        if ($message === '') {
            return ['success' => false, 'error' => 'Empty message.'];
        }
        if (mb_strlen($message) > self::MAX_LENGTH) {
            $message = mb_substr($message, 0, self::MAX_LENGTH) . '…';
        }

        if (self::provider() === 'local') {
            $url = rtrim(trim((string) Env::get('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3001')), '/') . '/send';
            $headers = ['Content-Type: application/json', 'x-api-key: ' . trim((string) Env::get('WHATSAPP_GATEWAY_KEY', ''))];
            $payload = ['to' => $number, 'message' => $message];
        } else {
            $url = sprintf(
                'https://api.maytapi.com/api/%s/%s/sendMessage',
                rawurlencode(trim((string) Env::get('MAYTAPI_PRODUCT_ID'))),
                rawurlencode(trim((string) Env::get('MAYTAPI_PHONE_ID')))
            );
            $headers = ['Content-Type: application/json', 'x-maytapi-key: ' . trim((string) Env::get('MAYTAPI_API_TOKEN'))];
            $payload = ['to_number' => $number, 'type' => 'text', 'message' => $message];
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
        } catch (Throwable $e) {
            error_log('WhatsApp send failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }

        if ($body === false) {
            error_log("WhatsApp send failed to {$number}: {$curlErr}");
            return ['success' => false, 'error' => $curlErr];
        }
        $json = json_decode((string) $body, true);
        if ($status >= 200 && $status < 300 && is_array($json) && !empty($json['success'])) {
            return ['success' => true, 'to' => $number, 'response' => $json];
        }
        $err = is_array($json) ? (string) ($json['message'] ?? $json['error'] ?? $body) : (string) $body;
        error_log("WhatsApp send failed to {$number} (HTTP {$status}): {$err}");
        return ['success' => false, 'error' => $err, 'http_status' => $status];
    }

    /**
     * Mirror an email to the WhatsApp of whoever owns that email address.
     */
    public static function notifyEmailRecipient(string $email, string $subject, string $plainBody, string $htmlBody = ''): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp disabled.'];
        }
        $phone = self::phoneForEmail($email);
        if ($phone === null) {
            return ['success' => false, 'error' => "No phone on file for {$email}."];
        }

        $text = trim($plainBody);
        if ($text === '' && $htmlBody !== '') {
            $text = self::htmlToText($htmlBody);
        }
        $message = '*' . trim($subject) . "*\n\n" . $text;
        return self::send($phone, $message);
    }

    /**
     * Find the phone number stored for an email (staff users first, then clients).
     */
    public static function phoneForEmail(string $email): ?string
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }
        try {
            $pdo = Database::connection();
            foreach (['users', 'clients'] as $table) {
                $stmt = $pdo->prepare("SELECT phone FROM {$table} WHERE email = :e AND phone IS NOT NULL AND phone <> '' ORDER BY id DESC LIMIT 1");
                $stmt->execute([':e' => $email]);
                $phone = $stmt->fetchColumn();
                if ($phone !== false && self::normalizePhone((string) $phone) !== null) {
                    return (string) $phone;
                }
            }
        } catch (Throwable $e) {
            error_log('WhatsApp phone lookup failed: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Turn "+252 61 625 6534", "0616256534" or "616256534" into "252616256534".
     */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = '252' . substr($digits, 1);
        } elseif (strlen($digits) === 9 && preg_match('/^[67]/', $digits)) {
            $digits = '252' . $digits;
        }
        return (strlen($digits) >= 10 && strlen($digits) <= 15) ? $digits : null;
    }

    private static function htmlToText(string $html): string
    {
        $html = preg_replace('#<(script|style)[^>]*>.*?</\1>#si', '', $html) ?? $html;
        $html = preg_replace('#<br\s*/?>|</(p|div|tr|h[1-6]|li)>#i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n\s*\n+/", "\n\n", $text) ?? $text;
        return trim($text);
    }
}
