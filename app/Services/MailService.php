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
        // Credentials come from .env only; never hard-code them here.
        $username = trim((string) Env::get('MAIL_USERNAME', ''));
        $password = str_replace(' ', '', (string) Env::get('MAIL_PASSWORD', ''));
        if ($username === '' || $password === '') {
            return ['success' => false, 'error' => 'Mail is not configured: set MAIL_USERNAME and MAIL_PASSWORD in .env.'];
        }
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

        $msgDomain = (strpos($fromEmail, '@') !== false) ? substr(strrchr($fromEmail, '@'), 1) : 'gmail.com';
        $sendCommand("EHLO {$msgDomain}");
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

        // High-deliverability headers (Inbox priority, zero spam penalty flags)
        $messageId = '<simda.' . bin2hex(random_bytes(10)) . '.' . time() . '@' . $msgDomain . '>';

        $encodeHeader = static function (string $text): string {
            if (preg_match('/^[\x20-\x7E]+$/', $text)) {
                return '"' . addcslashes($text, '"\\') . '"';
            }
            return '=?UTF-8?B?' . base64_encode($text) . '?=';
        };

        $formattedFrom = $encodeHeader($fromName) . ' <' . $fromEmail . '>';
        $formattedSubject = preg_match('/^[\x20-\x7E]+$/', $subject) ? $subject : ('=?UTF-8?B?' . base64_encode($subject) . '?=');

        $baseHeaders = [
            'MIME-Version: 1.0',
            'Date: ' . date('r'),
            'Message-ID: ' . $messageId,
            'From: ' . $formattedFrom,
            'Sender: <' . $fromEmail . '>',
            'Reply-To: ' . $formattedFrom,
            'To: <' . $to . '>',
            'Subject: ' . $formattedSubject,
            'X-Priority: 1 (Highest)',
            'X-MSMail-Priority: High',
            'Importance: High',
            'X-Mailer: SIMDA Banking System v2.0',
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
            'branch_manager' => 'Maamulaha Laanta (Branch Manager)',
            'loan_officer' => 'Header Office (Sarkaalka Maalgelinta)',
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

    /**
     * Send OTP Verification Email for Password Reset
     */
    public static function sendPasswordResetOtpEmail(
        string $email,
        string $fullName,
        string $otpCode,
        string $locale = 'so'
    ): array {
        $subject = ($locale === 'so')
            ? "SIMDA: Koodhka Xaqiijinta ee Furaha Sirta ah (OTP: {$otpCode})"
            : "SIMDA: Password Reset Verification Code (OTP: {$otpCode})";

        $whiteLogoPath = BASE_PATH . '/public/assets/images/simda-logo-white.png';
        if (!file_exists($whiteLogoPath)) {
            $whiteLogoPath = BASE_PATH . '/public/assets/images/simda-logo.png';
        }
        $inlineAttachments = [];
        $logoSrc = 'https://simda.so/assets/images/simda-logo-white.png';
        if (file_exists($whiteLogoPath)) {
            $inlineAttachments[] = [
                'cid' => 'simda_white_logo',
                'path' => $whiteLogoPath,
                'mime' => 'image/png',
                'filename' => 'simda-logo-white.png',
            ];
            $logoSrc = 'cid:simda_white_logo';
        }

        $greeting = ($locale === 'so') ? "Salaamu Calaykum, {$fullName}!" : "Hello, {$fullName}!";
        $subHeading = ($locale === 'so')
            ? "Waxaa lagugu soo diray koodhka xaqiijinta (OTP) si aad u beddesho furahaaga sirta ah ee nidaamka SIMDA."
            : "You have requested a verification code (OTP) to reset your SIMDA portal password.";
        $otpNote = ($locale === 'so')
            ? "Koodhkani wuxuu dhacayaa 10 daqiiqo kadib. Fadlan cidna ha la wadaagin."
            : "This code is valid for 10 minutes. Please never share this code with anyone.";

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$locale}">
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:24px;background:#F4F6F4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1B2E28;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;margin:0 auto;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
        <tr>
            <td style="background:#123B32;padding:34px 28px;text-align:center;">
                <img src="{$logoSrc}" alt="SIMDA" width="190" style="width:190px;max-width:100%;height:auto;display:inline-block;border:0;outline:none;">
                <div style="color:#D6B66C;font-size:11px;font-weight:700;letter-spacing:1.2px;margin-top:10px;text-transform:uppercase;">SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION</div>
            </td>
        </tr>
        <tr>
            <td style="padding:36px 32px;text-align:center;">
                <h2 style="color:#123B32;font-size:22px;font-weight:800;margin:0 0 10px;">{$greeting}</h2>
                <p style="color:#52635B;font-size:14px;line-height:1.6;margin:0 0 24px;">{$subHeading}</p>
                <div style="background:#F7F9F7;border:2px dashed #123B32;border-radius:14px;padding:20px;display:inline-block;margin:0 auto 24px;">
                    <span style="font-size:36px;font-weight:900;letter-spacing:10px;color:#123B32;font-family:monospace;">{$otpCode}</span>
                </div>
                <p style="color:#8A9891;font-size:13px;line-height:1.5;margin:0;">{$otpNote}</p>
            </td>
        </tr>
        <tr>
            <td style="background:#F2F4F2;padding:16px 24px;text-align:center;font-size:12px;color:#788780;">
                © 2026 SIMDA — Somali Islamic Microfinance Development Association. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $plain = "{$greeting}\n\n{$subHeading}\n\nKoodhkaaga OTP: {$otpCode}\n\n{$otpNote}\n\nSIMDA Team";

        return self::send($email, $subject, $html, $plain, $inlineAttachments);
    }

    /**
     * Send 8-character 2FA Security Code to Admin Email
     */
    public static function sendAdmin2FACodeEmail(
        string $email,
        string $fullName,
        string $securityCode,
        string $locale = 'so'
    ): array {
        $subject = ($locale === 'so')
            ? "SIMDA: Koodhka Amniga Sare ee Maamulaha (2FA: {$securityCode})"
            : "SIMDA: Admin Security Verification Code (2FA: {$securityCode})";

        $whiteLogoPath = BASE_PATH . '/public/assets/images/simda-logo-white.png';
        if (!file_exists($whiteLogoPath)) {
            $whiteLogoPath = BASE_PATH . '/public/assets/images/simda-logo.png';
        }
        $inlineAttachments = [];
        $logoSrc = 'https://simda.so/assets/images/simda-logo-white.png';
        if (file_exists($whiteLogoPath)) {
            $inlineAttachments[] = [
                'cid' => 'simda_white_logo',
                'path' => $whiteLogoPath,
                'mime' => 'image/png',
                'filename' => 'simda-logo-white.png',
            ];
            $logoSrc = 'cid:simda_white_logo';
        }

        $greeting = ($locale === 'so') ? "Salaamu Calaykum, Mudane/Marwo {$fullName}!" : "Hello, {$fullName}!";
        $subHeading = ($locale === 'so')
            ? "Waxaa nidaamka maamulka SIMDA laga sameeyay isku-day gelitaan (Admin Login). Si loo adkeeyo amniga sare ee maamulaha, fadlan isticmaal koodhkan gaarka ah ee 8-da xaraf/lambar ah si aad u dhammaystirto gelitaankaaga."
            : "An administrative sign-in attempt was detected on your SIMDA portal account. To protect administrative access, please verify your identity using the 8-character security code below.";
        $expiryNote = ($locale === 'so')
            ? "Koodhkani wuxuu dhacayaa 10 daqiiqo kadib. Ha la wadaagin cid kale. Haddiiba aadan adigu ahayn qofka isku dayay inuu galo akoonkaaga maamulka, fadlan si degdeg ah u beddel furahaaga sirta ah."
            : "This verification code is valid for 10 minutes. Never share this code with anyone. If you did not attempt to sign in, someone may have your credentials — please change your password immediately.";

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$locale}">
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:24px;background:#F4F6F4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1B2E28;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;margin:0 auto;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(0,0,0,0.08);">
        <tr>
            <td style="background:#123B32;padding:34px 28px;text-align:center;">
                <img src="{$logoSrc}" alt="SIMDA" width="190" style="width:190px;max-width:100%;height:auto;display:inline-block;border:0;outline:none;">
                <div style="color:#D6B66C;font-size:11px;font-weight:700;letter-spacing:1.2px;margin-top:10px;text-transform:uppercase;">SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION</div>
            </td>
        </tr>
        <tr>
            <td style="padding:36px 32px;text-align:center;">
                <div style="display:inline-flex;align-items:center;background:rgba(214,182,108,0.18);border:1px solid #D6B66C;color:#856417;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;padding:4px 14px;border-radius:999px;margin-bottom:16px;">
                    🛡️ ADMIN TWO-FACTOR VERIFICATION
                </div>
                <h2 style="color:#123B32;font-size:22px;font-weight:800;margin:0 0 10px;">{$greeting}</h2>
                <p style="color:#52635B;font-size:14px;line-height:1.6;margin:0 0 24px;">{$subHeading}</p>
                <div style="background:#F7FAF8;border:2px dashed #123B32;border-radius:14px;padding:22px;display:inline-block;margin:0 auto 24px;">
                    <div style="font-size:11px;font-weight:700;color:#52635B;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">8-Character Admin Security Code</div>
                    <span style="font-size:34px;font-weight:900;letter-spacing:8px;color:#123B32;font-family:monospace;">{$securityCode}</span>
                </div>
                <p style="color:#8A9891;font-size:12px;line-height:1.55;margin:0;max-width:440px;margin:0 auto;">{$expiryNote}</p>
            </td>
        </tr>
        <tr>
            <td style="background:#F2F4F2;padding:16px 24px;text-align:center;font-size:12px;color:#788780;">
                © 2026 SIMDA — Somali Islamic Microfinance Development Association. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $plain = "{$greeting}\n\n{$subHeading}\n\nKoodhkaaga Amniga Sare ee Maamulaha: {$securityCode}\n\n{$expiryNote}\n\nSIMDA Security Team";

        return self::send($email, $subject, $html, $plain, $inlineAttachments);
    }

    /**
     * OTP that confirms an admin's own email or password change.
     */
    public static function sendProfileOtpEmail(
        string $email,
        string $fullName,
        string $otpCode,
        string $purpose,
        string $locale = 'so'
    ): array {
        $isSo = $locale === 'so';
        $isEmail = $purpose === 'email';
        $subject = $isSo
            ? ($isEmail ? 'SIMDA: Koodhka beddelka email-ka' : 'SIMDA: Koodhka beddelka password-ka')
            : ($isEmail ? 'SIMDA: Email change verification code' : 'SIMDA: Password change verification code');
        $intro = $isSo
            ? ($isEmail ? 'Isticmaal koodhkan si aad u xaqiijiso email-kaaga cusub ee SIMDA.' : 'Isticmaal koodhkan si aad u xaqiijiso beddelka password-kaaga SIMDA.')
            : ($isEmail ? 'Use this code to confirm your new SIMDA email address.' : 'Use this code to confirm your SIMDA password change.');
        $note = $isSo
            ? 'Koodhku wuxuu dhacayaa 10 daqiiqo kadib. Cidna ha la wadaagin. Haddii aadan adigu codsan, password-kaaga beddel.'
            : 'This code expires in 10 minutes. Never share it. If you did not request this, change your password.';
        $greeting = ($isSo ? 'Salaamu Calaykum, ' : 'Hello, ') . $fullName;

        $block = '<div style="background:#F7F9F7;border:2px dashed #123B32;border-radius:14px;padding:18px;text-align:center;margin:20px 0;">'
            . '<span style="font-size:32px;font-weight:800;letter-spacing:8px;color:#123B32;font-family:monospace;">' . htmlspecialchars($otpCode) . '</span></div>';

        return self::send(
            $email,
            $subject,
            self::noticeHtml($subject, $greeting, $intro, $block, $note, $locale),
            "{$greeting}\n\n{$intro}\n\n{$otpCode}\n\n{$note}\n\nSIMDA"
        );
    }

    /**
     * Tells the previous address that the account email was changed.
     */
    public static function sendEmailChangedNotice(string $oldEmail, string $fullName, string $newEmail, string $locale = 'so'): array
    {
        $isSo = $locale === 'so';
        $subject = $isSo ? 'SIMDA: Email-ka akoonkaaga waa la beddelay' : 'SIMDA: Your account email was changed';
        $intro = $isSo
            ? "Email-ka akoonkaaga SIMDA waxaa loo beddelay {$newEmail}."
            : "Your SIMDA account email was changed to {$newEmail}.";
        $note = $isSo
            ? 'Haddii aadan adigu samayn, isla markiiba la xiriir maamulka nidaamka.'
            : 'If you did not do this, contact the system administrators immediately.';
        $greeting = ($isSo ? 'Salaamu Calaykum, ' : 'Hello, ') . $fullName;

        return self::send(
            $oldEmail,
            $subject,
            self::noticeHtml($subject, $greeting, $intro, '', $note, $locale),
            "{$greeting}\n\n{$intro}\n\n{$note}\n\nSIMDA"
        );
    }

    /**
     * Sends an official Payment Receipt email to the client upon repayment.
     */
    public static function sendRepaymentReceiptEmail(array $receiptData, string $locale = 'so'): array
    {
        $isSo = $locale === 'so';
        $clientName = (string) ($receiptData['client_name'] ?? 'Macmiil Sharafle');
        $clientEmail = (string) ($receiptData['client_email'] ?? '');
        $clientCode = (string) ($receiptData['client_code'] ?? '');
        $receiptNumber = (string) ($receiptData['receipt_number'] ?? 'RCP-0000');
        $financingNumber = (string) ($receiptData['financing_number'] ?? '');
        $productType = (string) ($receiptData['product_type'] ?? 'murabaha');
        $amountPaid = number_format((float) ($receiptData['amount_paid'] ?? 0), 2);
        $remainingBalance = number_format((float) ($receiptData['outstanding_balance_after'] ?? 0), 2);
        $paymentMethod = (string) ($receiptData['payment_method'] ?? 'EVC Plus');
        $txRef = (string) ($receiptData['transaction_reference'] ?? '');
        $paymentDate = (string) ($receiptData['payment_date'] ?? date('Y-m-d H:i'));
        $branchName = (string) ($receiptData['branch_name'] ?? 'SIMDA Head Office');
        $recordedBy = (string) ($receiptData['recorded_by'] ?? 'Staff');

        $prodName = match ($productType) {
            'murabaha' => ($isSo ? 'Murabaxo (Asset Financing)' : 'Murabaha Financing'),
            'qard_hassan' => ($isSo ? 'Dayn Qard Hassan (Cash Loan)' : 'Qard Hassan Cash Loan'),
            default => strtoupper($productType),
        };

        $subject = $isSo
            ? "SIMDA: Rasiidka Lacag-Bixinta Rasmi ah — {$receiptNumber} (\${$amountPaid})"
            : "SIMDA Official Payment Receipt — {$receiptNumber} (\${$amountPaid})";

        // Logo embedding
        $logoPath = dirname(__DIR__, 2) . '/public/assets/images/simda-logo.png';
        $inlineAttachments = [];
        $logoSrc = 'https://simda.so/assets/images/simda-logo.png';
        if (is_file($logoPath)) {
            $inlineAttachments[] = [
                'cid' => 'simda_logo',
                'path' => $logoPath,
                'mime' => 'image/png',
                'filename' => 'simda-logo.png',
            ];
            $logoSrc = 'cid:simda_logo';
        }

        $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$locale}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$e($subject)}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#0d2620;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1B2E28;">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 12px 36px rgba(0,0,0,0.3);">
    <!-- BRAND HEADER -->
    <tr>
      <td style="background:#123B32;padding:28px 30px;text-align:center;border-bottom:3px solid #D6B66C;">
        <img src="{$logoSrc}" alt="SIMDA" style="height:48px;max-width:180px;object-fit:contain;margin-bottom:8px;" />
        <div style="color:#D6B66C;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;">
          SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION
        </div>
        <div style="color:#A7C2B8;font-size:12px;margin-top:4px;">
          100% Zero-Riba Sharia-Compliant Financial Platform
        </div>
      </td>
    </tr>

    <!-- RECEIPT STATUS BANNER -->
    <tr>
      <td style="background:#F4F8F6;padding:20px 30px;text-align:center;border-bottom:1px solid #E2EBE6;">
        <div style="display:inline-block;background:#EAF6F0;color:#23845B;padding:6px 16px;border-radius:20px;font-weight:800;font-size:13px;border:1px solid rgba(35,132,91,0.25);">
          ✓ {$e($isSo ? 'Lacag-Bixinta waa la Xaqiijiyay (Payment Verified)' : 'Payment Received & Verified')}
        </div>
        <div style="margin-top:10px;font-size:12px;color:#788780;text-transform:uppercase;letter-spacing:1px;font-weight:700;">
          {$e($isSo ? 'Tirada Rasiidka' : 'Receipt Number')}
        </div>
        <div style="font-family:monospace;font-size:20px;font-weight:800;color:#123B32;letter-spacing:1.5px;margin-top:2px;">
          {$e($receiptNumber)}
        </div>
      </td>
    </tr>

    <!-- MAIN AMOUNT BOX -->
    <tr>
      <td style="padding:28px 30px;text-align:center;">
        <div style="font-size:13px;color:#52635B;margin-bottom:6px;font-weight:600;">
          {$e($isSo ? 'Cadadka Lacagta La Soo Celiyay' : 'Total Amount Paid')}
        </div>
        <div style="font-size:36px;font-weight:900;color:#23845B;letter-spacing:-0.5px;line-height:1;">
          \${$amountPaid} <span style="font-size:16px;font-weight:700;color:#788780;">USD</span>
        </div>
        <div style="font-size:12px;color:#788780;margin-top:8px;">
          {$e($isSo ? 'Baaqiga Harsan Heshiiska:' : 'Remaining Contract Balance:')} <strong style="color:#123B32;font-size:14px;">\${$remainingBalance} USD</strong>
        </div>
      </td>
    </tr>

    <!-- RECEIPT DETAILS TABLE -->
    <tr>
      <td style="padding:0 30px 24px;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;background:#FAFCFB;border-radius:12px;border:1px solid #E8EFEA;overflow:hidden;">
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;width:40%;">{$e($isSo ? 'Macmiilka' : 'Client Name')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-weight:700;color:#1B2E28;">{$e($clientName)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Koodhka Macmiilka' : 'Client Code')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-weight:700;font-family:monospace;color:#123B32;">{$e($clientCode)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Heshiiska / Number' : 'Contract Number')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-weight:700;font-family:monospace;color:#123B32;">{$e($financingNumber)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Nooca Maalgelinta' : 'Product Type')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-weight:600;color:#1B2E28;">{$e($prodName)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Qaabka Bixinta' : 'Payment Method')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-weight:700;color:#1B2E28;">{$e($paymentMethod)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Transaction Ref' : 'Transaction Reference')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;font-family:monospace;font-weight:700;color:#52635B;">{$e($txRef !== '' ? $txRef : 'N/A')}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#788780;">{$e($isSo ? 'Taariikhda & Waqtiga' : 'Date & Time')}:</td>
            <td style="padding:10px 14px;border-bottom:1px solid #E8EFEA;color:#1B2E28;font-weight:600;">{$e($paymentDate)}</td>
          </tr>
          <tr>
            <td style="padding:10px 14px;color:#788780;">{$e($isSo ? 'Laanta & Shaqaalaha' : 'Branch & Staff')}:</td>
            <td style="padding:10px 14px;color:#1B2E28;font-weight:600;">{$e($branchName)} &bull; {$e($recordedBy)}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- SHARIA & TRUST NOTICE -->
    <tr>
      <td style="padding:0 30px 24px;">
        <div style="background:#FFF9EB;border-left:4px solid #D6B66C;padding:12px 16px;border-radius:8px;font-size:12px;color:#856404;line-height:1.5;">
          <strong>{$e($isSo ? 'Fiiro Gaar ah:' : 'Notice:')}</strong>
          {$e($isSo ? 'Rasiidhani waa caddeyn rasmi ah oo muujinaysa lacag-celintaada. Wax ribo ah laguma darin si waafaqsan Shareecada Islaamka. Mahadsanid sida aad waqtiga ugu ilaaliso heshiiskaaga.' : 'This receipt is official confirmation of your repayment. Sharia-compliant and zero interest. Thank you for your prompt repayment.')}
        </div>
      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td style="background:#123B32;padding:20px 30px;text-align:center;color:#A7C2B8;font-size:11px;line-height:1.6;">
        <div style="color:#FFFFFF;font-weight:700;font-size:13px;margin-bottom:4px;">
          SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION (SIMDA)
        </div>
        Mogadishu Head Office, Somalia &bull; info@simda.so &bull; +252 61 500 0000<br>
        &copy; {$e(date('Y'))} SIMDA Microfinance Portal. All rights reserved.
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plain = "SIMDA OFFICIAL PAYMENT RECEIPT\n"
            . "Receipt Number: {$receiptNumber}\n"
            . "Client: {$clientName} ({$clientCode})\n"
            . "Contract: {$financingNumber}\n"
            . "Amount Paid: \${$amountPaid} USD\n"
            . "Remaining Balance: \${$remainingBalance} USD\n"
            . "Payment Method: {$paymentMethod}\n"
            . "Reference: {$txRef}\n"
            . "Date: {$paymentDate}\n"
            . "Branch: {$branchName}\n"
            . "Recorded By: {$recordedBy}\n\n"
            . "Thank you for banking ethically with SIMDA Microfinance.";

        return self::send($clientEmail, $subject, $html, $plain, $inlineAttachments);
    }

    /**
     * Sent to the client when a cash loan or Murabaha is issued: the amount they received, the repayment
     * period and schedule, the reason, and the official disbursement receipt.
     */
    public static function sendFinancingIssuedEmail(array $d, string $locale = 'so'): array
    {
        $isSo = $locale === 'so';
        $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $money = static fn($v): string => number_format((float) $v, 2);

        $isMurabaha = ($d['product_type'] ?? '') === 'murabaha';
        $term = (int) ($d['term'] ?? 0);
        $weekly = ($d['frequency'] ?? 'monthly') === 'weekly';
        $installments = (array) ($d['installments'] ?? []);
        $first = $installments[0] ?? null;
        $last = $installments ? $installments[count($installments) - 1] : null;
        $perInstallment = $first ? (float) $first['total_due'] : ($term > 0 ? (float) $d['total_due'] / $term : 0);
        $fmtDate = static fn(?string $v): string => $v ? date('d/m/Y', strtotime($v)) : '—';

        $periodText = $isSo
            ? $term . ($weekly ? ' toddobaad' : ' bilood')
            : $term . ($weekly ? ' weeks' : ' months');
        $perText = $isSo ? ($weekly ? 'toddobaadkii' : 'bishii') : ($weekly ? 'per week' : 'per month');
        $productName = $isMurabaha
            ? ($isSo ? 'Murabaxo (Iibka Alaabta)' : 'Murabaha Asset Financing')
            : ($isSo ? 'Dayn Lacag Cadaan ah (Qard Xasan, 0% Ribo)' : 'Cash Loan (Qard Hassan, 0% Riba)');
        $receiptNo = (string) ($d['receipt_reference'] ?: $d['financing_number']);

        $subject = $isSo
            ? "SIMDA: Rasiidka Daynta — \${$money($d['amount_given'])} ({$d['financing_number']})"
            : "SIMDA: Financing Receipt — \${$money($d['amount_given'])} ({$d['financing_number']})";

        $logoPath = dirname(__DIR__, 2) . '/public/assets/images/simda-logo.png';
        $inline = [];
        $logoSrc = 'https://simda.so/assets/images/simda-logo.png';
        if (is_file($logoPath)) {
            $inline[] = ['cid' => 'simda_logo', 'path' => $logoPath, 'mime' => 'image/png', 'filename' => 'simda-logo.png'];
            $logoSrc = 'cid:simda_logo';
        }

        $rows = [
            [$isSo ? 'Macmiilka' : 'Client', $d['client_name'] . ' (' . $d['client_code'] . ')'],
            [$isSo ? 'Lambarka Heshiiska' : 'Contract Number', (string) $d['financing_number']],
            [$isSo ? 'Nooca' : 'Product', $productName],
        ];
        if ($isMurabaha && trim((string) $d['goods']) !== '') {
            $rows[] = [$isSo ? 'Alaabta' : 'Goods', (string) $d['goods']];
        }
        $rows[] = [$isSo ? 'Sababta' : 'Purpose', (string) $d['purpose']];
        $rows[] = [$isSo ? 'Lacagta la siiyay' : 'Amount Given', '$' . $money($d['net_disbursed'] ?: $d['amount_given'])];
        $rows[] = [$isSo ? 'Wadarta la celinayo' : 'Total to Repay', '$' . $money($d['total_due'])];
        $rows[] = [$isSo ? 'Muddada bixinta' : 'Repayment Period', $periodText];
        $rows[] = [$isSo ? 'Qaybta ' . $perText : 'Installment ' . $perText, '$' . $money($perInstallment)];
        $rows[] = [$isSo ? 'Bixinta koowaad' : 'First Payment', $fmtDate($first['due_date'] ?? null)];
        $rows[] = [$isSo ? 'Bixinta ugu dambeysa' : 'Final Payment', $fmtDate($last['due_date'] ?? null)];
        if (($d['provider'] ?? '') !== '') {
            $rows[] = [$isSo ? 'Qaabka loo diray' : 'Paid Out Via', (string) $d['provider']];
        }
        $rows[] = [$isSo ? 'Taariikhda' : 'Date', date('d/m/Y H:i', strtotime((string) $d['disbursed_at']) ?: time())];
        $rows[] = [$isSo ? 'Laanta & Shaqaalaha' : 'Branch & Staff', $d['branch_name'] . ' • ' . $d['issued_by']];

        $detailHtml = '';
        $n = count($rows);
        foreach ($rows as $i => [$label, $value]) {
            $border = $i < $n - 1 ? 'border-bottom:1px solid #E8EFEA;' : '';
            $detailHtml .= '<tr><td style="padding:9px 14px;' . $border . 'color:#788780;width:42%;">' . $e($label) . '</td>'
                . '<td style="padding:9px 14px;' . $border . 'font-weight:700;color:#1B2E28;">' . $e((string) $value) . '</td></tr>';
        }

        $scheduleHtml = '';
        foreach ($installments as $row) {
            $scheduleHtml .= '<tr><td style="padding:6px 10px;border-bottom:1px solid #EEF3F0;color:#52635B;">' . (int) $row['installment_number'] . '</td>'
                . '<td style="padding:6px 10px;border-bottom:1px solid #EEF3F0;color:#1B2E28;">' . $e($fmtDate((string) $row['due_date'])) . '</td>'
                . '<td style="padding:6px 10px;border-bottom:1px solid #EEF3F0;text-align:right;font-weight:700;color:#123B32;">$' . $money($row['total_due']) . '</td></tr>';
        }

        $hello = $isSo ? 'Asalaamu calaykum ' . $d['client_name'] . ',' : 'Assalamu alaikum ' . $d['client_name'] . ',';
        $intro = $isSo
            ? 'SIMDA waxay kuu bixisay maalgelinta hoos ku qoran. Fadlan kaydi email-kan; waa rasiidkaaga rasmiga ah.'
            : 'SIMDA has issued the financing below to you. Please keep this email; it is your official receipt.';
        $amountLabel = $isSo ? 'Lacagta Lagu Siiyay' : 'Amount You Received';
        $repayLine = $isSo
            ? 'Waxaad bixinaysaa $' . $money($perInstallment) . ' ' . $perText . ', muddo ' . $periodText . ' ah.'
            : 'You repay $' . $money($perInstallment) . ' ' . $perText . ' for ' . $periodText . '.';
        $receiptLabel = $isSo ? 'Lambarka Rasiidka' : 'Receipt Number';
        $scheduleTitle = $isSo ? 'Jadwalka Bixinta' : 'Repayment Schedule';
        $colDue = $isSo ? 'Taariikhda' : 'Due Date';
        $colAmt = $isSo ? 'Lacagta' : 'Amount';
        $notice = $isSo
            ? 'Wax ribo ah laguma darin, si waafaqsan Shareecada Islaamka. Fadlan ku bixi qaybaha waqtigooda si dhibcahaaga daynta (credit score) u kordhaan.'
            : 'No interest (riba) is charged, in line with Islamic Sharia. Paying each installment on time raises your credit score.';
        $year = date('Y');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$e($locale)}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{$e($subject)}</title></head>
<body style="margin:0;padding:24px 12px;background:#0d2620;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1B2E28;">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;margin:0 auto;background:#FFFFFF;border-radius:16px;overflow:hidden;">
    <tr><td style="background:#123B32;padding:26px 30px;text-align:center;border-bottom:3px solid #D6B66C;">
      <img src="{$logoSrc}" alt="SIMDA" style="height:46px;max-width:180px;object-fit:contain;margin-bottom:8px;" />
      <div style="color:#D6B66C;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;">Somali Islamic Microfinance Development Association</div>
    </td></tr>
    <tr><td style="padding:24px 30px 6px;font-size:14px;line-height:1.55;color:#1B2E28;">
      <div style="font-weight:700;margin-bottom:6px;">{$e($hello)}</div>{$e($intro)}
    </td></tr>
    <tr><td style="padding:16px 30px;text-align:center;">
      <div style="background:#F4F8F6;border:1px solid #E2EBE6;border-radius:14px;padding:20px;">
        <div style="font-size:12px;color:#788780;text-transform:uppercase;letter-spacing:1px;font-weight:700;">{$e($amountLabel)}</div>
        <div style="font-size:34px;font-weight:900;color:#23845B;line-height:1.1;margin-top:4px;">\${$money($d['net_disbursed'] ?: $d['amount_given'])} <span style="font-size:15px;color:#788780;">USD</span></div>
        <div style="font-size:13px;color:#123B32;margin-top:8px;font-weight:600;">{$e($repayLine)}</div>
        <div style="margin-top:12px;font-size:11px;color:#788780;text-transform:uppercase;letter-spacing:1px;font-weight:700;">{$e($receiptLabel)}</div>
        <div style="font-family:monospace;font-size:17px;font-weight:800;color:#123B32;letter-spacing:1px;">{$e($receiptNo)}</div>
      </div>
    </td></tr>
    <tr><td style="padding:6px 30px 18px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;background:#FAFCFB;border-radius:12px;border:1px solid #E8EFEA;">{$detailHtml}</table>
    </td></tr>
    <tr><td style="padding:0 30px 18px;">
      <div style="font-size:13px;font-weight:800;color:#123B32;margin-bottom:8px;">{$e($scheduleTitle)}</div>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:12px;border:1px solid #E8EFEA;border-radius:10px;">
        <tr style="background:#F4F8F6;"><td style="padding:7px 10px;font-weight:700;color:#52635B;">#</td><td style="padding:7px 10px;font-weight:700;color:#52635B;">{$e($colDue)}</td><td style="padding:7px 10px;font-weight:700;color:#52635B;text-align:right;">{$e($colAmt)}</td></tr>
        {$scheduleHtml}
      </table>
    </td></tr>
    <tr><td style="padding:0 30px 24px;">
      <div style="background:#FFF9EB;border-left:4px solid #D6B66C;padding:12px 16px;border-radius:8px;font-size:12px;color:#856404;line-height:1.5;">{$e($notice)}</div>
    </td></tr>
    <tr><td style="background:#123B32;padding:18px 30px;text-align:center;color:#A7C2B8;font-size:11px;line-height:1.6;">
      <div style="color:#FFFFFF;font-weight:700;font-size:13px;margin-bottom:4px;">SIMDA</div>
      Mogadishu Head Office, Somalia &bull; info@simda.so<br>&copy; {$year} SIMDA Microfinance Portal.
    </td></tr>
  </table>
</body>
</html>
HTML;

        $plain = ($isSo ? "RASIIDKA DAYNTA SIMDA\n" : "SIMDA FINANCING RECEIPT\n") . "{$receiptLabel}: {$receiptNo}\n\n";
        foreach ($rows as [$label, $value]) {
            $plain .= "{$label}: {$value}\n";
        }
        $plain .= "\n{$scheduleTitle}:\n";
        foreach ($installments as $row) {
            $plain .= '#' . (int) $row['installment_number'] . '  ' . $fmtDate((string) $row['due_date']) . '  $' . $money($row['total_due']) . "\n";
        }
        $plain .= "\n{$notice}\n";

        return self::send((string) $d['client_email'], $subject, $html, $plain, $inline);
    }

    /**
     * Tells a Branch Manager or General Manager that an application was forwarded for their decision.
     */
    public static function sendApplicationForwardedEmail(string $email, string $fullName, array $app, string $locale = 'so'): array
    {
        $isSo = $locale === 'so';
        $amount = '$' . number_format((float) $app['requested_amount'], 2);
        $isMurabaha = ($app['product_type'] ?? '') === 'murabaha';
        $productType = $isSo ? ($isMurabaha ? 'Murabaxo' : 'Dayn Cadaan') : ($isMurabaha ? 'Murabaha' : 'Cash Loan');
        $submittedAt = date('d/m/Y H:i', strtotime((string) ($app['submitted_at'] ?? 'now')));
        $queueUrl = rtrim((string) Env::get('APP_URL', 'http://localhost/SIMDA'), '/') . '/index.php?route=approval_queue';

        $subject = $isSo ? "SIMDA: Codsi {$productType} oo {$amount} ah ayaa laguu soo gudbiyay" : "SIMDA: {$productType} application of {$amount} forwarded to you";
        $intro = $isSo
            ? "Waxaa laguu soo gudbiyay codsi {$productType} ah oo lacagtiisu tahay {$amount}, maadaama uu ku jiro heerka aad adigu go'aaminayso."
            : "A {$productType} application of {$amount} was forwarded to you because it falls within your approval level.";
        $action = $isSo ? 'Fadlan gal system-ka oo natiijada codsiga ka soo saar.' : 'Please log in to the system and give the result of this application.';
        $greeting = ($isSo ? 'Salaamu Calaykum, ' : 'Hello, ') . $fullName;

        $location = trim((string) ($app['client_district'] ?? '') . ', ' . (string) ($app['client_city'] ?? ''), ', ');
        $rows = [
            [$isSo ? 'Lacagta la codsaday' : 'Amount requested', $amount],
            [$isSo ? 'Nooca' : 'Type', $productType],
            [$isSo ? 'Lambarka codsiga' : 'Application no.', (string) $app['application_number']],
            [$isSo ? 'Macmiilka' : 'Client', $app['client_name'] . ' (' . $app['client_code'] . ')'],
            [$isSo ? 'Telefoonka' : 'Phone', (string) $app['client_phone']],
            [$isSo ? 'Aqoonsiga' : 'ID number', (string) $app['client_id_number']],
            [$isSo ? 'Goobta' : 'Location', $location],
            [$isSo ? 'Ganacsiga' : 'Business', (string) $app['business_type']],
            [$isSo ? 'Dakhliga bishii' : 'Monthly income', '$' . number_format((float) $app['monthly_income'], 2)],
            [$isSo ? 'Laanta' : 'Branch', (string) $app['branch_name']],
            [$isSo ? 'Muddada' : 'Term', $app['term_installments'] . ' ' . ($isSo ? 'qaybood' : 'installments')],
            [$isSo ? 'Ujeeddada' : 'Purpose', (string) ($app['purpose'] ?? '')],
            [$isSo ? 'Waxaa gudbiyay' : 'Submitted by', (string) $app['creator_name']],
            [$isSo ? 'Waqtiga' : 'Time', $submittedAt],
        ];

        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $table = '<table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;border-collapse:collapse;font-size:14px;">';
        $plainRows = '';
        foreach ($rows as [$label, $value]) {
            if ($value === '') {
                continue;
            }
            $table .= '<tr><td style="padding:8px 12px 8px 0;border-bottom:1px solid #E6EAE7;color:#788780;vertical-align:top;">' . $e($label) . '</td>'
                . '<td style="padding:8px 0;border-bottom:1px solid #E6EAE7;color:#1B2E28;font-weight:600;">' . $e($value) . '</td></tr>';
            $plainRows .= "{$label}: {$value}\n";
        }
        $table .= '</table><p style="color:#1B2E28;font-size:14px;font-weight:700;margin:0 0 16px;">' . $e($action) . '</p>'
            . '<a href="' . $e($queueUrl) . '" style="display:inline-block;background:#123B32;color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:8px;">'
            . $e($isSo ? 'Gal System-ka' : 'Open the System') . '</a>';

        return self::send(
            $email,
            $subject,
            self::noticeHtml($subject, $greeting, $intro, $table, '', $locale),
            "{$greeting}\n\n{$intro}\n\n{$plainRows}\n{$action}\n{$queueUrl}\n\nSIMDA"
        );
    }

    private static function noticeHtml(string $title, string $greeting, string $intro, string $blockHtml, string $note, string $locale): string
    {
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="' . $e($locale) . '"><head><meta charset="utf-8"><title>' . $e($title) . '</title></head>'
            . '<body style="margin:0;padding:24px;background:#F4F6F4;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;color:#1B2E28;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#FFFFFF;border-radius:14px;overflow:hidden;">'
            . '<tr><td style="background:#123B32;padding:20px 28px;color:#FFFFFF;font-size:18px;font-weight:800;letter-spacing:1px;">SIMDA</td></tr>'
            . '<tr><td style="padding:28px;">'
            . '<h2 style="color:#123B32;font-size:20px;margin:0 0 10px;">' . $e($greeting) . '</h2>'
            . '<p style="color:#52635B;font-size:14px;line-height:1.6;margin:0;">' . $e($intro) . '</p>'
            . $blockHtml
            . '<p style="color:#788780;font-size:13px;line-height:1.5;margin:16px 0 0;">' . $e($note) . '</p>'
            . '</td></tr></table></body></html>';
    }
}
