<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use Throwable;

/**
 * Branded layout for messages staff send to clients from Communication -> "Send a message to clients":
 * green header with the white SIMDA logo, the message, the signature, and a footer like the website's.
 * One place for the email (HTML + plain text) and the WhatsApp text, so both carry the same signature and footer.
 *
 * No links anywhere: links push emails into spam.
 */
final class BroadcastTemplate
{
    /** Who signs the messages. */
    public const SIGNER_NAME = 'Abdiwahid Anas';

    /** Embedded images (Content-ID => file). Missing files are skipped, never linked from outside. */
    public const CID_LOGO = 'simda_logo_reverse';
    public const CID_SIGNATURE = 'simda_signature';

    public static function logoPath(): string
    {
        return BASE_PATH . '/public/assets/images/email-logo-reverse.png';
    }

    /** Kept in storage/ (blocked from the web) and out of git. */
    public static function signaturePath(): string
    {
        return BASE_PATH . '/storage/email/signature.png';
    }

    public static function whatsappBannerPath(): string
    {
        return BASE_PATH . '/public/assets/images/whatsapp-banner.png';
    }

    /** Contact details, from Administration -> Settings when set. */
    public static function contact(): array
    {
        $c = [
            'phone' => '+252 616256534 · +252 613496943',
            'email' => 'anasabdiwahidhussein@gmail.com',
            'address' => 'KM4, Degmada Hodan, Muqdisho',
        ];
        try {
            $rows = Database::connection()->query(
                "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('institution_phone', 'institution_email')"
            )->fetchAll();
            foreach ($rows as $r) {
                $v = trim((string) $r['setting_value']);
                if ($v === '') {
                    continue;
                }
                if ($r['setting_key'] === 'institution_phone') {
                    $c['phone'] = str_replace(' / ', ' · ', $v);
                } else {
                    $c['email'] = $v;
                }
            }
        } catch (Throwable) {
            // Settings table unavailable: keep the defaults.
        }
        return $c;
    }

    private static function words(string $locale): array
    {
        $so = [
            'greeting' => 'Salaamu Calaykum',
            'thanks' => 'Mahadsanid,',
            'role' => 'Maamulka SIMDA',
            'company' => 'SIMDA Microfinance',
            'full' => 'Ururka Horumarinta Maalgelinta Yaryar ee Islaamiga ah ee Soomaaliyeed',
            'office' => 'Xafiiska Guud',
            'reg' => "Diiwaanka Hay'adda: SIMDA-SO-001",
            'p1' => '0% Ribo', 'p2' => 'Jimco → Sabti', 'p3' => 'Sanduuqa Sadaqada',
            'p1s' => 'Raasumaalka keliya', 'p2s' => 'Ganaax la’aan', 'p3s' => '100% gooni ah',
            'rights' => 'SIMDA Microfinance. Xuquuqda oo dhan way dhowran tahay.',
            'why' => 'Fariintan waxaa laguu soo diray adigoo ah macmiil SIMDA.',
        ];
        $en = [
            'greeting' => 'Hello',
            'thanks' => 'Kind regards,',
            'role' => 'SIMDA Management',
            'company' => 'SIMDA Microfinance',
            'full' => 'Somali Islamic Microfinance Development Association',
            'office' => 'Head office',
            'reg' => 'Institution Reg: SIMDA-SO-001',
            'p1' => '0% Riba', 'p2' => 'Friday → Saturday', 'p3' => 'Charity Fund',
            'p1s' => 'Principal only', 'p2s' => 'No penalty', 'p3s' => '100% separate',
            'rights' => 'SIMDA Microfinance. All rights reserved.',
            'why' => 'You received this message as a SIMDA client.',
        ];
        $ar = [
            'greeting' => 'السلام عليكم',
            'thanks' => 'مع خالص التحية،',
            'role' => 'إدارة سيمدا',
            'company' => 'SIMDA Microfinance',
            'full' => 'جمعية تطوير التمويل الأصغر الإسلامي الصومالية',
            'office' => 'المقر الرئيسي',
            'reg' => 'سجل المؤسسة: SIMDA-SO-001',
            'p1' => '0% ربا', 'p2' => 'الجمعة → السبت', 'p3' => 'صندوق الصدقات',
            'p1s' => 'رأس المال فقط', 'p2s' => 'بدون غرامة', 'p3s' => 'منفصل 100%',
            'rights' => 'SIMDA Microfinance. جميع الحقوق محفوظة.',
            'why' => 'وصلتك هذه الرسالة لأنك عميل لدى سيمدا.',
        ];
        return $locale === 'en' ? $en : ($locale === 'ar' ? $ar : $so);
    }

    /**
     * The branded email. $hasLogo / $hasSignature say whether those images are attached (cid).
     */
    public static function emailHtml(string $name, string $subject, string $message, string $locale, bool $hasLogo, bool $hasSignature): string
    {
        $w = self::words($locale);
        $c = self::contact();
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        $align = $locale === 'ar' ? 'right' : 'left';
        $font = "font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;";
        $year = date('Y');

        // Message paragraphs: blank lines split paragraphs, single line breaks stay.
        $paras = '';
        foreach (preg_split('/\R{2,}/u', trim($message)) ?: [] as $p) {
            $paras .= '<p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#33443D;">' . nl2br($e(trim($p))) . '</p>';
        }

        $logo = $hasLogo
            ? '<img src="cid:' . self::CID_LOGO . '" alt="SIMDA" width="230" style="display:block;margin:0 auto;width:230px;max-width:70%;height:auto;border:0;">'
            : '<div style="' . $font . 'font-size:34px;font-weight:800;letter-spacing:4px;color:#FFFFFF;">SIMDA</div>';
        $footLogo = $hasLogo
            ? '<img src="cid:' . self::CID_LOGO . '" alt="SIMDA" width="130" style="display:block;width:130px;height:auto;border:0;margin:0 0 12px;">'
            : '<div style="font-size:20px;font-weight:800;letter-spacing:3px;color:#FFFFFF;margin:0 0 10px;">SIMDA</div>';
        $signature = $hasSignature
            ? '<img src="cid:' . self::CID_SIGNATURE . '" alt="' . $e(self::SIGNER_NAME) . '" width="200" style="display:block;width:200px;height:auto;border:0;margin:6px 0 2px;">'
            : '';

        $pill = static fn (string $t, string $s): string =>
            '<td width="33%" style="padding:0 4px;" valign="top"><div style="background:#F6F1E4;border:1px solid #EADFC4;border-radius:12px;padding:12px 8px;text-align:center;">'
            . '<div style="font-size:14px;font-weight:800;color:#123B32;">' . $t . '</div>'
            . '<div style="font-size:11.5px;color:#8A6A2A;margin-top:3px;">' . $s . '</div></div></td>';

        return '<!DOCTYPE html><html lang="' . $e($locale) . '" dir="' . $dir . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $e($subject) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#EEF1EE;' . $font . '">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1EE;"><tr><td align="center" style="padding:28px 12px;">'
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:100%;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(18,59,50,0.10);">'

            // Header
            . '<tr><td align="center" style="background:#123B32;background-image:linear-gradient(160deg,#1B5A47 0%,#123B32 55%,#0C2922 100%);padding:34px 24px 26px;">'
            . $logo
            . '<div style="margin-top:14px;font-size:10.5px;font-weight:700;letter-spacing:3px;color:#D6B66C;">SOMALI ISLAMIC MICROFINANCE DEVELOPMENT ASSOCIATION</div>'
            . '</td></tr>'
            . '<tr><td style="height:5px;line-height:5px;font-size:0;background:#D6B66C;background-image:linear-gradient(90deg,#B8862B,#F6DB95,#B8862B);">&nbsp;</td></tr>'

            // Body
            . '<tr><td dir="' . $dir . '" style="padding:36px 40px 10px;text-align:' . $align . ';">'
            . '<h1 style="margin:0 0 18px;font-size:22px;line-height:1.3;color:#123B32;font-weight:800;">' . $e($subject) . '</h1>'
            . '<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#1D2B26;">' . $e($w['greeting']) . ' <strong>' . $e($name) . '</strong>,</p>'
            . $paras
            // Signature
            . '<div style="margin-top:26px;padding-top:18px;border-top:1px solid #ECE7DD;">'
            . '<div style="font-size:14px;color:#52635B;">' . $e($w['thanks']) . '</div>'
            . $signature
            . '<div style="font-size:16px;font-weight:800;color:#123B32;margin-top:4px;">' . $e(self::SIGNER_NAME) . '</div>'
            . '<div style="font-size:12px;font-weight:700;letter-spacing:1.5px;color:#B8862B;text-transform:uppercase;margin-top:2px;">' . $e($w['role']) . '</div>'
            . '</div>'
            . '</td></tr>'

            // Values strip
            . '<tr><td style="padding:22px 36px 30px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
            . $pill($e($w['p1']), $e($w['p1s'])) . $pill($e($w['p2']), $e($w['p2s'])) . $pill($e($w['p3']), $e($w['p3s']))
            . '</tr></table></td></tr>'

            // Footer (like the website footer)
            . '<tr><td dir="' . $dir . '" style="background:#0E2E27;padding:30px 40px 22px;text-align:' . $align . ';">'
            . $footLogo
            . '<div style="font-size:14px;font-weight:800;color:#FFFFFF;">' . $e($w['company']) . '</div>'
            . '<div style="font-size:12px;color:#A9BDB4;margin-top:3px;line-height:1.5;">' . $e($w['full']) . '</div>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;"><tr>'
            . '<td valign="top" style="font-size:12.5px;line-height:1.8;color:#DCE6E1;padding-' . ($dir === 'rtl' ? 'left' : 'right') . ':12px;">'
            . '<div style="color:#D6B66C;font-weight:700;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">' . $e($w['office']) . '</div>'
            . $e($c['address']) . '<br>' . $e($w['reg']) . '</td>'
            . '<td valign="top" style="font-size:12.5px;line-height:1.8;color:#DCE6E1;">'
            . '<div style="color:#D6B66C;font-weight:700;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;">' . ($locale === 'so' ? 'Xiriirka' : ($locale === 'ar' ? 'اتصل بنا' : 'Contact')) . '</div>'
            . '<span style="color:#DCE6E1;">' . $e($c['phone']) . '</span><br><span style="color:#DCE6E1;">' . $e($c['email']) . '</span></td>'
            . '</tr></table>'
            . '<div style="margin-top:20px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.12);font-size:11px;line-height:1.6;color:#8FA69C;">'
            . '© ' . $year . ' ' . $e($w['rights']) . '<br>' . $e($w['why']) . '</div>'
            . '</td></tr>'

            . '</table></td></tr></table></body></html>';
    }

    public static function emailText(string $name, string $subject, string $message, string $locale): string
    {
        $w = self::words($locale);
        $c = self::contact();
        return "{$subject}\n\n{$w['greeting']} {$name},\n\n" . trim($message) . "\n\n"
            . "{$w['thanks']}\n" . self::SIGNER_NAME . "\n{$w['role']}\n\n"
            . "------------------------------\n"
            . "{$w['company']}\n{$w['full']}\n{$w['office']}: {$c['address']}\n{$c['phone']}\n{$c['email']}\n"
            . "{$w['p1']} · {$w['p2']} · {$w['p3']}\n\n© " . date('Y') . " {$w['rights']}";
    }

    /** WhatsApp text (sent as the caption of the banner image when it fits). */
    public static function whatsappText(string $name, string $subject, string $message, string $locale): string
    {
        $w = self::words($locale);
        $c = self::contact();
        $line = '━━━━━━━━━━━━━━━━';
        return "*{$subject}*\n\n{$w['greeting']} *{$name}*,\n\n" . trim($message) . "\n\n"
            . "{$w['thanks']}\n✍️ *" . self::SIGNER_NAME . "*\n_{$w['role']}_\n\n"
            . "{$line}\n🏦 *{$w['company']}*\n_{$w['full']}_\n"
            . "📍 {$c['address']}\n📞 {$c['phone']}\n✉️ {$c['email']}\n"
            . "✅ {$w['p1']} · {$w['p2']} · {$w['p3']}\n{$line}";
    }
}
