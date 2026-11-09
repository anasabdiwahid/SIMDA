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
     * The client email, laid out as a simple business letter:
     * logo bar, title, greeting, message, signature, one quiet footer. $hasLogo / $hasSignature say
     * whether those images are attached (cid).
     */
    public static function emailHtml(string $name, string $subject, string $message, string $locale, bool $hasLogo, bool $hasSignature): string
    {
        $w = self::words($locale);
        $c = self::contact();
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        $align = $locale === 'ar' ? 'right' : 'left';
        $font = "font-family:Arial,Helvetica,sans-serif;";

        $paras = '';
        foreach (preg_split('/\R{2,}/u', trim($message)) ?: [] as $p) {
            $paras .= '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#2B3B34;">' . nl2br($e(trim($p))) . '</p>';
        }

        $logo = $hasLogo
            ? '<img src="cid:' . self::CID_LOGO . '" alt="SIMDA" width="170" style="display:block;width:170px;height:auto;border:0;">'
            : '<span style="font-size:24px;font-weight:bold;letter-spacing:3px;color:#FFFFFF;">SIMDA</span>';
        $signature = $hasSignature
            ? '<img src="cid:' . self::CID_SIGNATURE . '" alt="" width="160" style="display:block;width:160px;height:auto;border:0;margin:4px 0 0;">'
            : '';

        return '<!DOCTYPE html><html lang="' . $e($locale) . '" dir="' . $dir . '"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light only"><title>' . $e($subject) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#F2F2F0;' . $font . '">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#F2F2F0"><tr><td align="center" style="padding:24px 12px;">'
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="width:600px;max-width:100%;background:#FFFFFF;border:1px solid #E4E4E0;">'

            // 1. Logo bar
            . '<tr><td bgcolor="#123B32" style="background:#123B32;padding:22px 32px;" align="' . $align . '">' . $logo . '</td></tr>'
            . '<tr><td bgcolor="#D6B66C" style="height:3px;line-height:3px;font-size:0;background:#D6B66C;">&nbsp;</td></tr>'

            // 2. Letter
            . '<tr><td dir="' . $dir . '" bgcolor="#FFFFFF" style="background:#FFFFFF;padding:32px 32px 8px;text-align:' . $align . ';">'
            . '<h1 style="margin:0 0 20px;font-size:20px;line-height:1.35;color:#123B32;font-weight:bold;">' . $e($subject) . '</h1>'
            . '<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#2B3B34;">' . $e($w['greeting']) . ' ' . $e($name) . ',</p>'
            . $paras
            . '</td></tr>'

            // 3. Signature (white behind it so it stays readable in dark mode)
            . '<tr><td dir="' . $dir . '" bgcolor="#FFFFFF" style="background:#FFFFFF;padding:8px 32px 30px;text-align:' . $align . ';">'
            . '<p style="margin:0;font-size:15px;color:#2B3B34;">' . $e($w['thanks']) . '</p>'
            . $signature
            . '<p style="margin:6px 0 0;font-size:15px;font-weight:bold;color:#123B32;">' . $e(self::SIGNER_NAME) . '</p>'
            . '<p style="margin:2px 0 0;font-size:13px;color:#6A756F;">' . $e($w['role']) . ', ' . $e($w['company']) . '</p>'
            . '</td></tr>'

            // 4. Footer: one quiet block
            . '<tr><td dir="' . $dir . '" bgcolor="#F7F6F2" style="background:#F7F6F2;border-top:1px solid #E4E4E0;padding:18px 32px;text-align:' . $align . ';font-size:12px;line-height:1.7;color:#6A756F;">'
            . '<strong style="color:#123B32;">' . $e($w['company']) . '</strong> &middot; ' . $e($w['full']) . '<br>'
            . $e($c['address']) . ' &middot; ' . $e($c['phone']) . '<br>'
            . $e($c['email']) . '<br>'
            . '<span style="color:#9AA39E;">&copy; ' . date('Y') . ' ' . $e($w['rights']) . ' ' . $e($w['why']) . '</span>'
            . '</td></tr>'

            . '</table></td></tr></table></body></html>';
    }

    public static function emailText(string $name, string $subject, string $message, string $locale): string
    {
        $w = self::words($locale);
        $c = self::contact();
        return "{$subject}\n\n{$w['greeting']} {$name},\n\n" . trim($message) . "\n\n"
            . "{$w['thanks']}\n" . self::SIGNER_NAME . "\n{$w['role']}, {$w['company']}\n\n"
            . "--\n{$w['company']} · {$w['full']}\n{$c['address']} · {$c['phone']}\n{$c['email']}";
    }

    /**
     * WhatsApp text, written like a short letter (sent as the caption of the SIMDA logo picture):
     * title, greeting, message, signature, then a two-line footer. No emoji or decorative lines.
     */
    public static function whatsappText(string $name, string $subject, string $message, string $locale): string
    {
        $w = self::words($locale);
        $c = self::contact();
        return "*{$subject}*\n\n"
            . "{$w['greeting']} {$name},\n\n"
            . trim($message) . "\n\n"
            . "{$w['thanks']}\n*" . self::SIGNER_NAME . "*\n{$w['role']}\n\n"
            . "_{$w['company']} · {$c['address']}_\n_{$c['phone']}_";
    }
}
