<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLogger;
use App\Helpers\Database;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Services\CharityChargeService;
use App\Services\DashboardAnalyticsService;
use App\Services\DisbursementService;
use App\Services\FinancingDomainService;
use App\Services\MailService;
use App\Services\MurabahaGoodsService;
use App\Services\PartnerService;
use App\Services\Par30Service;
use App\Services\RepaymentEngine;
use App\Services\ScheduleEngine;
use App\Services\SmsReminderService;
use App\Services\StatementPdfGenerator;
use App\Services\WhatsAppService;
use PDO;

final class PortalController
{
    private const PROFILE_OTP_TTL = 600;
    private const PROFILE_OTP_MAX_ATTEMPTS = 5;
    private const PROFILE_OTP_COOLDOWN = 60;
    private const PROFILE_OTP_MAX_RESENDS = 3;

    public static function dispatch(string $route): void
    {
        $pdo = Database::connection();

        try {
            match ($route) {
                'home' => self::publicHome($pdo),
                'about' => self::publicAbout($pdo),
                'contact' => self::publicContact($pdo),
                'faq' => self::publicFaq($pdo),
                'faq_chat' => self::faqChat($pdo),
                'contact_submit' => self::contactSubmit($pdo),
                'offline' => self::publicOffline(),
                'login' => self::loginForm(),
                'login_check_email' => self::loginCheckEmail($pdo),
                'login_submit' => self::loginSubmit($pdo),
                'admin_2fa_verify' => self::admin2faVerify($pdo),
                'admin_2fa_resend' => self::admin2faResend($pdo),
                'forgot_password' => self::forgotPasswordForm(),
                'forgot_password_send_otp' => self::forgotPasswordSendOtp($pdo),
                'forgot_password_verify_otp' => self::forgotPasswordVerifyOtp($pdo),
                'forgot_password_reset' => self::forgotPasswordReset($pdo),
                'logout' => self::logout(),
                'auth_session_check' => self::authSessionCheck($pdo),
                'api_schedule_preview' => self::apiSchedulePreview(),

                'dashboard' => self::dashboard($pdo),
                'clients' => self::clientsList($pdo),
                'client_register' => self::clientRegisterForm($pdo),
                'client_view' => self::clientView($pdo),
                'client_register_submit' => self::clientRegisterSubmit($pdo),
                'client_check_duplicate' => self::clientCheckDuplicate($pdo),
                'client_transfer' => self::clientTransfer($pdo),
                'guarantors' => self::guarantorsList($pdo),
                'guarantor_add' => self::guarantorAdd($pdo),
                'groups' => self::groupsList($pdo),
                'group_create' => self::groupCreate($pdo),
                'group_approve' => self::groupApprove($pdo),

                'products' => self::productsList($pdo),
                'product_save' => self::productSave($pdo),
                'product_toggle' => self::productToggle($pdo),
                'product_delete' => self::productDelete($pdo),

                'applications' => self::applicationsList($pdo),
                'application_create' => self::applicationCreate($pdo),
                'application_submit_draft' => self::applicationSubmitDraft($pdo),
                'application_resubmit' => self::applicationResubmit($pdo),
                'approval_queue' => self::approvalQueue($pdo),
                'application_review' => self::applicationReview($pdo),

                'disbursements' => self::disbursementsList($pdo),
                'disburse_submit' => self::disburseSubmit($pdo),
                'loans' => self::loansView($pdo),
                'loan_new' => self::loanNew($pdo),
                'loan_submit' => self::loanSubmit($pdo),
                'murabaha' => self::murabahaView($pdo),
                'murabaha_new' => self::murabahaNewForm($pdo),
                'murabaha_submit' => self::murabahaSubmit($pdo),
                'murabaha_goods' => self::murabahaGoodsList($pdo),
                'murabaha_goods_save' => self::murabahaGoodsSave($pdo),
                'murabaha_goods_toggle' => self::murabahaGoodsToggle($pdo),
                'murabaha_goods_home' => self::murabahaGoodsHomeToggle($pdo),
                'partners' => self::partnersList($pdo),
                'partner_save' => self::partnerSave($pdo),
                'partner_toggle' => self::partnerToggle($pdo),
                'partner_delete' => self::partnerDelete($pdo),
                'murabaha_goods_delete' => self::murabahaGoodsDelete($pdo),
                'financings', 'schedules', 'statements' => self::financingsList($pdo, $route),
                'financing_view' => self::financingView($pdo),
                'statement_pdf' => self::statementPdf($pdo),

                'repayments' => self::repaymentsList($pdo),
                'repayment_record' => self::repaymentCollect($pdo),
                'repayment_submit' => self::repaymentSubmit($pdo),
                'repayment_receipt' => self::repaymentReceipt($pdo),

                'charity_charges' => self::charityChargesList($pdo),
                'charity_waive' => self::charityWaive($pdo),
                'charity_payout' => self::charityPayout($pdo),
                'credit_scores' => self::creditScoresList($pdo),
                'par30', 'reports' => self::par30View($pdo, $route),
                'sms_outbox' => self::smsOutboxList($pdo),
                'sms_run_dispatcher' => self::smsRunDispatcher($pdo),
                'sms_broadcast' => self::smsBroadcast($pdo),

                'branches', 'users', 'permissions', 'audit_logs', 'settings' => self::adminSection($pdo, $route),
                'managers' => self::adminSection($pdo, 'users', 'manager'),
                'branch_managers' => self::adminSection($pdo, 'users', 'branch_manager'),
                'header_office' => self::adminSection($pdo, 'users', 'loan_officer'),
                'employees' => self::adminSection($pdo, 'users', 'loan_officer'),
                'profile' => self::profile($pdo),
                'profile_update' => self::profileUpdate($pdo),
                'profile_email_request' => self::profileEmailRequest($pdo),
                'profile_password_request' => self::profilePasswordRequest($pdo),
                'profile_otp_verify' => self::profileOtpVerify($pdo),
                'profile_otp_resend' => self::profileOtpResend($pdo),
                'profile_otp_cancel' => self::profileOtpCancel(),
                'account_password' => self::accountPassword(),
                'account_password_save' => self::accountPasswordSave($pdo),
                'branch_create' => self::branchCreate($pdo),
                'branch_toggle' => self::branchToggle($pdo),
                'user_create' => self::userCreate($pdo),
                'user_update' => self::userUpdate($pdo),
                'user_toggle_status' => self::userToggleStatus($pdo),
                'user_security_reset' => self::userSecurityReset($pdo),
                'user_permissions_save' => self::userPermissionsSave($pdo),
                'user_resend_credentials' => self::userResendCredentials($pdo),
                'mail_test_send' => self::mailTestSend($pdo),
                'settings_save' => self::settingsSave($pdo),

                '404' => self::error404(),
                default => self::error404(),
            };
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $_SESSION['old_input'] = $_POST;
                $fallback = AuthMiddleware::check() ? '?route=dashboard' : '?route=login';
                $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
                header('Location: ' . $referer);
                exit;
            }
            if (AuthMiddleware::check() && $route !== 'dashboard') {
                header('Location: ?route=dashboard');
                exit;
            }
            if (!AuthMiddleware::check() && $route !== 'login') {
                header('Location: ?route=login');
                exit;
            }
            http_response_code(500);
            echo '<div style="font-family:sans-serif;padding:24px;color:#C94B4B;">Error: ' . Security::e($e->getMessage()) . '</div>';
            exit;
        }
    }

    private static function renderDashboard(string $viewFile, array $data = []): void
    {
        $user = AuthMiddleware::requireAuth();
        $data['user'] = $user;
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/resources/views/' . $viewFile;
        $contentHtml = ob_get_clean();
        require dirname(__DIR__, 2) . '/resources/layouts/dashboard.php';
    }

    private static function scopeSql(array $user, string $tableAlias = 'f'): array
    {
        $role = $user['role_slug'] ?? '';
        // The General Manager oversees the whole company, like the admin.
        if (in_array($role, ['admin', 'manager'], true)) {
            return ['', []];
        }
        if ($role === 'client') {
            return [" AND {$tableAlias}.client_id = :scope_cid ", [':scope_cid' => (int) ($user['client_id'] ?? -1)]];
        }
        return [" AND {$tableAlias}.branch_id = :scope_bid ", [':scope_bid' => (int) ($user['branch_id'] ?? -1)]];
    }

    /**
     * Client lookup scope. Clients are shared across branches: a client registered at one branch can be
     * found, served and paid in at any other, so every staff role sees all clients. A client account only
     * sees itself. ($tableAlias.client_id is used when the alias is not the clients table itself.)
     */
    private static function clientScopeSql(array $user, string $tableAlias = 'c', string $idColumn = 'id'): array
    {
        if (in_array($user['role_slug'] ?? '', ['admin', 'manager', 'branch_manager', 'loan_officer'], true)) {
            return ['', []];
        }
        return [" AND {$tableAlias}.{$idColumn} = :scope_cid ", [':scope_cid' => (int) ($user['client_id'] ?? -1)]];
    }

    private static function publicHome(PDO $pdo): void
    {
        $products = $pdo->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        try {
            $homeGoods = MurabahaGoodsService::homeShowcase($pdo);
        } catch (\Throwable $e) {
            $homeGoods = []; // the public home page must still render if the catalog is unavailable
        }
        try {
            $partners = PartnerService::all($pdo, true);
        } catch (\Throwable $e) {
            $partners = [];
        }
        require dirname(__DIR__, 2) . '/resources/views/public/home.php';
    }

    private static function publicAbout(PDO $pdo): void
    {
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        try {
            $hasPartners = PartnerService::all($pdo, true) !== [];
        } catch (\Throwable $e) {
            $hasPartners = false;
        }
        require dirname(__DIR__, 2) . '/resources/views/public/about.php';
    }

    private static function publicContact(PDO $pdo): void
    {
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        try {
            $hasPartners = PartnerService::all($pdo, true) !== [];
        } catch (\Throwable $e) {
            $hasPartners = false;
        }
        require dirname(__DIR__, 2) . '/resources/views/public/contact.php';
    }

    private static function publicFaq(PDO $pdo): void
    {
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        try {
            $hasPartners = PartnerService::all($pdo, true) !== [];
        } catch (\Throwable $e) {
            $hasPartners = false;
        }
        require dirname(__DIR__, 2) . '/resources/views/public/faq.php';
    }

    private static function faqChat(PDO $pdo): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $rawInput = (string) file_get_contents('php://input');
        $json = json_decode($rawInput, true);
        $message = trim((string) ($json['message'] ?? $_POST['message'] ?? $_GET['message'] ?? ''));
        $preferredLang = trim((string) ($json['lang'] ?? $_POST['lang'] ?? $_GET['lang'] ?? 'so'));

        if ($message === '') {
            echo json_encode([
                'status' => 'error',
                'reply' => 'Fadlan qor su\'aashaada (Please type your question / يرجى كتابة سؤالك).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Language detection: Arabic -> Somali -> English
        $lang = $preferredLang;
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            $lang = 'ar';
        } else {
            $msgLower = mb_strtolower($message, 'UTF-8');
            $soMarkers = [
                'xog', 'xogta', 'mesha', 'meesha', 'meeshan', 'meshan', 'wax', 'waxba', 'iga', 'sheeg', 'sheegtaan',
                'iiga', 'ii', 'isi', 'isii', 'sii', 'number', 'lambar', 'lambarka', 'whatsapp', 'whataap', 'whatsap',
                'waran', 'warbixin', 'fahamsii', 'fahansi', 'halkan', 'halkee', 'shirkad', 'shirkadda',
                'hay\'ad', 'hay\'adda', 'qabataan', 'qabataa', 'socda', 'socoto', 'rabay', 'rabaa', 'rabnaa', 'doonayaa',
                'donaya', 'fadlan', 'walaal', 'abaayo', 'abowe', 'sxb', 'soo', 'dhowow', 'wcs', 'calaykum', 'alaykum',
                'waa', 'maxay', 'sidee', 'sideed', 'ma ', 'leedihiin', 'jiraa', 'jirtaa', 'ribo', 'ribada', 'dulsaar',
                'keyd', 'kayd', 'lacag', 'codsi', 'codsadaa', 'mashiin', 'kiosk', 'baqaar', 'dhul', 'dhismaha', 'jimco',
                'sabti', 'ganaax', 'sadaqo', 'dammaanad', 'garab', 'wadaag', 'koox', 'xafiis', 'xagee',
                'asc', 'nabad', 'mahadsanid', 'subax', 'galab', 'haye', 'haa', 'maya'
            ];
            $enMarkers = [
                'what', 'how', 'why', 'who', 'where', 'when', 'is ', 'are ', 'can ', 'do ', 'does ', 'tell ', 'about',
                'interest', 'riba', 'savings', 'deposit', 'account', 'apply', 'application', 'eligible', 'murabaha',
                'loan', 'financing', 'friday', 'saturday', 'shift', 'penalty', 'charity', 'guarantor', 'solidarity',
                'office', 'location', 'contact', 'phone', 'email', 'hello', 'hi', 'hey', 'thanks', 'thank you', 'yes', 'no'
            ];

            // Whole-word matching (substrings gave false hits, e.g. "hi" inside "this").
            $soMarkers = array_merge(array_map('trim', $soMarkers), [
                'ku', 'kuu', 'kaa', 'igu', 'ila', 'ula', 'yaa', 'yaku', 'yaad', 'maxaad', 'maxaa', 'miyaa', 'miyay', 'aan', 'baan',
                'waxaan', 'waxaad', 'hadal', 'hadla', 'somali', 'soomaali', 'af', 'sameyay', 'sameeyay', 'samee', 'dhisay',
                'magac', 'magacaa', 'magacaaga', 'kaftan', 'kaftami', 'ma', 'sidee', 'imisa', 'intee', 'goorma', 'xagee',
                'dayn', 'deyn', 'maalgelin', 'murabaxo', 'muraabaxo', 'codso', 'codsado', 'damiin', 'bishii', 'qiimaha',
                'mahadsanid', 'mahadsantahay', 'nabadeey', 'salaan', 'iska', 'warran', 'fiican', 'caawi', 'caawin',
            ]);
            $enMarkers = array_merge(array_map('trim', $enMarkers), [
                'you', 'your', 'the', 'and', 'made', 'name', 'please', 'speak', 'english', 'joke', 'i', 'me', 'my', 'we', 'our',
                'a', 'an', 'to', 'of', 'in', 'on', 'for', 'with', 'it', 'this', 'that', 'want', 'need', 'have', 'get', 'buy', 'pay',
                'much', 'many', 'price', 'cost', 'car', 'phone', 'money', 'month', 'help', 'would', 'could', 'should', 'there',
            ]);
            $words = preg_split('/[^\p{L}\']+/u', $msgLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $soSet = array_flip($soMarkers);
            $enSet = array_flip($enMarkers);
            $soCount = 0;
            $enCount = 0;
            foreach ($words as $w) {
                if (isset($soSet[$w])) {
                    $soCount += 2;
                } elseif (isset($enSet[$w])) {
                    $enCount += 2;
                } elseif (preg_match('/aa|ii|uu|dh|kh|x[aeiou]|q[aeio]|[bdfghjklmnrstwy]a$|ey$|ay$/u', $w)) {
                    // Spelling typical of Somali (double vowels, dh/kh, x/q + vowel, -a/-ay/-ey endings),
                    // so unknown or misspelled Somali words still count as Somali.
                    $soCount++;
                }
            }
            if (str_contains($msgLower, 'thank you')) $enCount += 2;

            if ($soCount > 0 && $soCount >= $enCount) {
                $lang = 'so';
            } elseif ($enCount > 0 && $enCount > $soCount) {
                $lang = 'en';
            }
        }

        $msgLower = mb_strtolower($message, 'UTF-8');
        $message = mb_substr($message, 0, 1200);
        $waUrlFor = static fn(string $l): string => 'https://wa.me/252616256534?text=' . rawurlencode(
            $l === 'ar' ? 'مرحباً سيمدا، أرغب في الاستفسار عن التمويل الإسلامي'
                : ($l === 'en' ? 'Hello SIMDA, I would like to inquire about Islamic microfinance'
                : 'Asc SIMDA, waxaan rabaa in aan wax ka ogaado maalgelinta xalaasha ah')
        );

        // SIMDA AI (Claude) answers first, from live database data. Falls back to the rules below when
        // no API key is configured, the visitor hit the rate limit, or the API is unavailable.
        $history = [];
        foreach ((array) ($json['history'] ?? []) as $turn) {
            if (is_array($turn) && isset($turn['role'], $turn['content']) && is_string($turn['content'])) {
                $history[] = ['role' => (string) $turn['role'], 'content' => $turn['content']];
            }
        }
        $aiReply = \App\Services\SimdaAiService::reply($pdo, $message, $history, $preferredLang);
        if ($aiReply !== null) {
            $wantsContact = (bool) preg_match('/(whatsapp|whataap|whatsap|watsap|واتساب|واتس|lambar|number|phone|telefoon|taleefan|wac|call|xiriir|contact|رقم|هاتف|اتصال)/iu', $message);
            echo json_encode([
                'status' => 'ok',
                'reply' => $aiReply,
                'language' => $lang,
                'topic' => 'ai',
                'suggestions' => [],
                'action_btn' => $wantsContact ? [
                    'type' => 'whatsapp',
                    'label' => $lang === 'ar' ? '💬 تواصل عبر واتساب' : ($lang === 'en' ? '💬 Chat on WhatsApp' : '💬 Nala soo xiriir WhatsApp'),
                    'url' => $waUrlFor($lang),
                ] : null,
                'ai' => true,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Intent detection (offline fallback)
        // People type fast and misspell ("murabaxo", "codsade", "xafis", "ribba", "intrest"). Append the
        // correctly spelled keyword for every word that is close to one, so the rules below still match.
        $message .= self::chatSpellingHints($message);
        $reply = '';
        $topic = 'general';
        $suggestions = [];
        $actionBtn = null;

        // "How do I apply / get financing" wins over topic words in the same question
        // (e.g. "Side ku codsada Murabaxo" is about applying, not what Murabaha is).
        // Somali verbs are matched by stem so every spelling/conjugation counts:
        // cods-(i/o/ado/adaa/ada/aday/an...), dalb-, hel-, qaad-/qaat-.
        $wantsApply = (bool) (
            preg_match('/(cods|dalb|shuruud|shardi|qaabkee|diiwaangeli|isdiiwaan|apply|applic|eligib|requirement|how (can|do|to) (i |we )?(get|obtain|request)|شروط|كيف أقدم|التقديم|طلب|الأوراق)/iu', $message)
            || preg_match('/\bsid(ee|e)n?\b.{0,40}\b(hel|qaad|qaat)\w*/iu', $message)
            || preg_match('/\b(hel|heli|helo|helaa|helayaa|qaado|qaato|qaadan|qaadanayaa)\w*\b.{0,40}\b(dayn|deyn|amaah|maalgelin|maalgalin|mura+ba[xh]\w*|lacag)/iu', $message)
        );

        // 0. Language switch / who am I / who made me / jokes (offline answers in the SIMDA AI voice)
        if (preg_match('/(\b(af[\s-]?)?so+mali\b.*\b(ku|igu|ila|nagu)?\s*(hadal|hadla|qor|jawaab)|speak somali|in somali)/iu', $message)) {
            $lang = 'so';
            $topic = 'language';
            $reply = "Haa walaal! 😊 Af-Soomaali ayaan kugula hadlayaa. Waxaan kaa caawin karaa maalgelinta 0% Ribo, Murabaxada, sida loo codsado, laamaheena iyo wax kasta oo SIMDA ku saabsan. Maxaan kuu qabtaa?";
        } elseif (preg_match('/(speak english|in english|english please|ingiriisi|af[\s-]?ingiriis)/iu', $message)) {
            $lang = 'en';
            $topic = 'language';
            $reply = "Sure! 😊 I'll reply in English. I can help with SIMDA's 0% riba financing, Murabaha, how to apply, our branches and more. What would you like to know?";
        } elseif (preg_match('/(بالعربي|العربية|عربي|carabi ku|af[\s-]?carabi|speak arabic|in arabic)/iu', $message)) {
            $lang = 'ar';
            $topic = 'language';
            $reply = "بكل سرور! 😊 سأتحدث معك بالعربية. أستطيع مساعدتك في التمويل الإسلامي بدون ربا، والمرابحة، وطريقة التقديم، وفروعنا. بماذا أخدمك؟";
        } elseif (preg_match('/(\by+a+\s?ku\b|\byaa\b|\byaad\b|\bkuu\b).*\b(samee?y?ay|sameeyey|samays?ay|samee|sameyn|dhis(ay)?|abuur(ay)?|curiyay|hindisay)|\bku\s+(sameeyay|sameyay|dhisay)\b|who\s+(made|built|created|developed|programmed|designed)\s+you|who\s+is\s+your\s+(creator|developer|maker|owner)|your\s+(creator|developer|maker)|من صنعك|من صممك|من برمجك|من طورك|من صنعكم/iu', $message)) {
            $topic = 'creator';
            $reply = $lang === 'ar'
                ? "صنعني المهندس أنس عبد الواحد (Anas Abdiwahid). أشكره كثيراً على الجهد الكبير الذي بذله في بنائي وبناء نظام سيمدا. 🙏\n\nكيف أساعدك بخصوص سيمدا؟"
                : ($lang === 'en'
                    ? "I was built by Engineer Anas Abdiwahid. I'm very grateful to him for all the hard work he put into building me and the SIMDA system. 🙏\n\nHow can I help you with SIMDA?"
                    : "Waxaa i sameeyay Engineer Anas Abdiwahid. Aad baan ugu mahadcelinayaa shaqada adag ee uu u soo galay sameynta aniga iyo nidaamka SIMDA. 🙏\n\nMaxaan kaaga caawin karaa arrimaha SIMDA?");
        } elseif (preg_match('/(magacaa|magacaaga|magacaga|magacyaa|yaad tahay|adigu waa kuma|what(\'s| is) your name|who are you|your name|ما اسمك|من أنت|من انت)/iu', $message)) {
            $topic = 'identity';
            $reply = $lang === 'ar'
                ? "اسمي SIMDA AI 🤖، المساعد الذكي لسيمدا. أساعدك في كل ما يخص التمويل الإسلامي بدون ربا، والمرابحة، وطريقة التقديم، وفروعنا. بماذا أساعدك؟"
                : ($lang === 'en'
                    ? "My name is SIMDA AI 🤖, SIMDA's smart assistant. I can help with our 0% riba financing, Murabaha, how to apply, our branches and more. What can I do for you?"
                    : "Magacaygu waa SIMDA AI 🤖, kaaliyaha caqliga badan ee SIMDA. Waxaan kaa caawin karaa maalgelinta 0% Ribo, Murabaxada, sida loo codsado, laamaheena iyo wax kasta oo SIMDA ku saabsan. Maxaan kuu qabtaa?");
        } elseif (preg_match('/(kaftan|kaftam|kaftami|sheeko qosol|qosol|joke|funny|make me laugh|haha|hahaha|😂|🤣|نكتة|نكته|اضحكني)/iu', $message)) {
            $topic = 'joke';
            $reply = $lang === 'ar'
                ? "😄 لماذا لا تخاف المحفظة من الربا في سيمدا؟ لأن الربا هنا = صفر، والصفر لا يعضّ!\n\nوالآن بجدية، كيف أساعدك في التمويل أو المرابحة؟"
                : ($lang === 'en'
                    ? "😄 Why did the wallet feel so relaxed at SIMDA? Because the riba here is 0% — and zero never bites!\n\nNow seriously, how can I help you with financing or Murabaha?"
                    : "😄 Jeebkii ayaa yiri: \"SIMDA aan tago, halkaas ribadu waa 0% — eber ma qaniino!\"\n\nHadda si dhab ah, maxaan kaaga caawiyaa maalgelinta ama Murabaxada?");
        }

        // 1. WhatsApp, Number, Phone & Direct Contact Intent
        elseif (
            preg_match('/(whatsapp|whataap|whatsap|watshap|watsap|واتساب|واتس|wa\.me)/iu', $message) ||
            preg_match('/(number|lambar|lambarka|phone|telefoon|taleefan|tel|contact|xiriir|wac|call|رقم|هاتف|اتصال)/iu', $message)
        ) {
            $topic = 'contact_number';
            $waUrl = 'https://wa.me/252616256534?text=' . rawurlencode(
                $lang === 'ar' ? 'مرحباً سيمدا، أرغب في الاستفسار عن التمويل الإسلامي' :
                ($lang === 'en' ? 'Hello SIMDA, I would like to inquire about Islamic microfinance' :
                'Asc SIMDA, waxaan rabaa in aan wax ka ogaado maalgelinta xalaasha ah')
            );

            if ($lang === 'ar') {
                $reply = "أهلاً بك! يمكنك التواصل المباشر مع مسؤولي سيمدا عبر الأرقام ووسائل الاتصال الرسمية التالية:\n\n"
                    . "📱 رقم الواتساب والهاتف: +252 616256534\n"
                    . "📞 هاتف إضافي: +252 613496943\n"
                    . "✉️ البريد الإلكتروني: anasabdiwahidhussein@gmail.com\n"
                    . "📍 المقر: تقاطع KM4، مديرية هودن، مقديشو\n\n"
                    . "انقر على زر الواتساب الأخضر أدناه لبدء المحادثة المباشرة فوراً:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 تواصل عبر واتساب (+252 616256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['أخبرني عن هذا المكان', 'كيف أتقدم بطلب تمويل؟', 'هل التمويل 0% ربا؟'];
            } elseif ($lang === 'en') {
                $reply = "Here are SIMDA's official contact numbers and direct support channels:\n\n"
                    . "📱 WhatsApp & Phone: +252 616256534\n"
                    . "📞 Office Line: +252 613496943\n"
                    . "✉️ Email: anasabdiwahidhussein@gmail.com\n"
                    . "📍 Location: KM4, Hodan District, Mogadishu\n\n"
                    . "Click the green WhatsApp button below to chat with our advisors immediately:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 Chat on WhatsApp (+252 616256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['Tell me about this place', 'How can I apply?', 'Is it 0% Riba?'];
            } else {
                $reply = "Waa kuwan lambarada rasmiga ah iyo xiriirka tooska ah ee SIMDA:\n\n"
                    . "📱 WhatsApp & Taleefan: +252 616256534\n"
                    . "📞 Khadka Xafiiska: +252 613496943\n"
                    . "✉️ Email: anasabdiwahidhussein@gmail.com\n"
                    . "📍 Goobta: KM4, Degmada Hodan, Muqdisho\n\n"
                    . "Guji batoonka cagaaran ee hoose si aad toos noogala hadasho WhatsApp:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 La Hadal WhatsApp (+252 616256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['Xogta meesha wax iga sheeg', 'Sideen u codsadaa?', '0% Ribo ma dhabbaa?'];
            }
        }
        // 2. Overview / "Xogta meesha wax iga sheeg" / "Tell me about this place / SIMDA"
        elseif (
            !$wantsApply && preg_match('/(xog|xogta|waran|meesha|mesha|meeshan|meshan|halkan|waxa socda|shirkaddan|hay\'addan|maxaad qabataan|maxaa la qabtaa|wax iga sheeg|iiga waran|i fahamsii|about this place|tell me about|what is this place|overview|what does simda do|عن هذا المكان|أخبرني عن|ما هي هذه المؤسسة|ماذا تفعلون|نبذة)/iu', $message)
        ) {
            $topic = 'overview';
            if ($lang === 'ar') {
                $reply = "مرحباً بك في سيمدا (SIMDA) - جمعية تطوير التمويل الأصغر الإسلامي الصومالية!\n\n"
                    . "إليك أهم ما تحتاج لمعرفته حول المؤسسة ونظامنا التمويلي:\n\n"
                    . "1. 0% ربا (تمويل إسلامي نقي): جميع معاملاتنا خالية 100% من الفوائد الربوية، ونعمل بصيغة المرابحة الشرعية (Cost-Plus) بهامش ربح ثابت ومعلوم مسبقاً.\n"
                    . "2. لا توجد حسابات توفير: سيمدا مؤسسة تمويل أصغر متخصصة حصرياً في تقديم التمويل التنموي والإنتاجي، ولا نقبل ودائع الادخار.\n"
                    . "3. مجالات التمويل: نمول الآلات والمعدات، تأسيس الأكشاك ومحلات التجزئة، أعمال بناء الأراضي ذات الملكية الموثقة، ومركبات النقل.\n"
                    . "4. نظام الموافقة الثنائي: رقابة صارمة عبر مسؤول التمويل (Loan Officer) واعتماد رسمي من مدير الفرع (Branch Manager).\n"
                    . "5. ترحيل أقساط الجمعة: احتراماً لعطلة الجمعة، تُرحل الأقساط آلياً إلى السبت دون أي غرامة.\n"
                    . "6. صندوق الصدقات: غرامات التأخير التأديبية تذهب 100% لصالح الفقراء والمحتاجين ($0 للمؤسسة).\n\n"
                    . "المقر الرئيسي: تقاطع KM4، مديرية هودن، مقديشو | هاتف: 616256534 252+ / 613496943 252+ | بريد: anasabdiwahidhussein@gmail.com";
                $suggestions = ['هل التمويل 0% ربا؟', 'كيف أتقدم بطلب تمويل؟', 'ما هي المرابحة؟'];
            } elseif ($lang === 'en') {
                $reply = "Welcome to SIMDA (Somali Islamic Microfinance Development Association)!\n\n"
                    . "Here is a complete overview of what we do and how our system works:\n\n"
                    . "1. 0% Riba (Zero Interest): All financing is 100% Sharia-compliant through Murabaha cost-plus sales contracts with zero compounding interest and no hidden fees.\n"
                    . "2. Financing Only (No Savings): We are an ethical microfinance institution focused purely on financing small businesses; we do not accept public savings deposits.\n"
                    . "3. What We Finance: Productive machinery & tools, market kiosk construction, commercial inventory, plot construction, and business transport.\n"
                    . "4. Dual-Threshold Approval: Fast, transparent governance managed by Loan Officers and authorized by Branch Managers.\n"
                    . "5. Automatic Friday Shift: Due dates falling on Islamic Friday weekend shift automatically to Saturday with zero late penalties.\n"
                    . "6. Segregated Charity Fund: Disciplinary late fees are never taken as income ($0 to SIMDA); they are 100% transferred to our community welfare Charity Fund.\n\n"
                    . "Head Office: KM4, Hodan District, Mogadishu | Tel: +252 616256534 / +252 613496943 | Email: anasabdiwahidhussein@gmail.com";
                $suggestions = ['Is it 0% Riba?', 'How can I apply?', 'What is Murabaha?'];
            } else {
                $reply = "Ku soo dhowow SIMDA (Somali Islamic Microfinance Development Association)!\n\n"
                    . "Meeshan waa hay'ad maaliyadeed oo Islaami ah oo loogu talagalay in ay maalgashato ganacsatada yaryar, baqaarrada, iyo bulshada Soomaaliyeed.\n\n"
                    . "Waa kuwan qodobbada ugu muhiimsan ee aad u baahan tahay inaad ka ogaato SIMDA:\n\n"
                    . "1. 0% Ribo (Ribo La'aan Buuxda): Dhammaan maalgelinteennu waa 100% shareecada waafaqsan, wax dulsaar ah ma jiraan, waxaana ku shaqeynaa nidaamka xalaasha ah ee Murabaha.\n"
                    . "2. Ma bixinno Keyd (No Savings): SIMDA ma ahan bangi lacag lagu dhigto; waxaan nahay hay'ad microfinance oo kaliya bixisa maalgelin ganacsi iyo horumarin.\n"
                    . "3. Waxyaabaha aan Maalgelinno: Qalabka & mashiinnada ganacsiga, dhismaha iyo agabka Kiosks-ka/dukaamada suuqa, dhismaha qaybaha dhulka ee sharciyeysan, iyo gaadiidka ganacsiga.\n"
                    . "4. Nidaamka Oggolaanshaha: Codsi kasta waxaa darsaya Sarkaalka Maaliyadda (Loan Officer), waxaana ansixinaya Maareeyaha Laanta (Branch Manager).\n"
                    . "5. Xushmadda Jimcaha: Maalinta Jimcaha lacag lama bixiyo; nidaamkayagu si toos ah ayuu bixinta ugu wareejiyaa Sabtida iyadoo wax ganaax ah aysan jirin.\n"
                    . "6. Sanduuqa Sadaqada: Haddii qofku sabab la'aan u daaho, ganaaxa edbinta ah 100% wuxuu toos u galaa Sanduuqa Sadaqada ($0 ayuu bangigu ka qaataa).\n\n"
                    . "Xafiiska Sare: KM4, Degmada Hodan, Muqdisho | Tel: +252 616256534 / +252 613496943 | Email: anasabdiwahidhussein@gmail.com";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Sideen u codsadaa?', 'Keyd ma leedihiin?'];
            }
        }
        // 2. Greetings / Salams
        elseif (
            !$wantsApply && (preg_match('/^(asc|salaam|salam|salan|hello|hi|hey|subax|galab|nabad|marhaban|مرحبا|أهلا|السلام)/iu', $message) ||
            preg_match('/(asc|asalaamu|assalamu|salaamu calaykum|salaam|\bwarr?an\b|sidee (tahay|tihiin|ahay)|ma nabad baa|how are you)/iu', $message))
        ) {
            $topic = 'greeting';
            if ($lang === 'ar') {
                $reply = "وعليكم السلام ورحمة الله وبركاته! أهلاً وسهلاً بك في سيمدا للتمويل الإسلامي. كيف يمكنني مساعدتك اليوم بخصوص التمويل 0% ربا، شروط التقديم، أو الاستفسارات العامة؟";
                $suggestions = ['أخبرني عن هذا المكان', 'هل التمويل خالٍ من الربا؟', 'كيف أتقدم بطلب؟'];
            } elseif ($lang === 'en') {
                $reply = "Assalamu Alaykum and welcome to SIMDA! I am glad to assist you. How can I help you today with our 0% Riba Islamic microfinance, loans, or services?";
                $suggestions = ['Tell me about this place', 'Is it 0% Riba?', 'How can I apply?'];
            } else {
                $reply = "Wcs walaal! Ku soo dhowow Kaaliyaha Caqliga Badan ee SIMDA. Waan ku faraxsanahay inaan ku caawiyo. Wax kasta oo ku saabsan nidaamka, maalgelinta xalaasha ah (0% Ribo), ama sida loo codsado ayaad i weydiin kartaa. Maxaan maanta kugu caawin karaa?";
                $suggestions = ['Xogta meesha wax iga sheeg', '0% Ribo ma dhabbaa?', 'Sideen u codsadaa?'];
            }
        }

        // 3. Riba & Interest Check
        elseif (
            !$wantsApply && preg_match('/(rib[oa]|ribada|dulsaar|riba|interest|usury|ربا|فوائد|فائدة|ربوية)/iu', $message)
        ) {
            $topic = 'riba';
            if ($lang === 'ar') {
                $reply = "نعم بكل تأكيد! تعمل سيمدا بنسبة 0% ربا تماماً وبشكل قاطع. جميع صيغ التمويل لدينا متوافقة 100% مع أحكام الشريعة الإسلامية عبر عقود المرابحة الشرعية (Cost-Plus) الخالية تماماً من أي فوائد مركبة أو غرامات مستحلبة لحساب المؤسسة.";
                $suggestions = ['ما هي المرابحة؟', 'كيف أتقدم بطلب تمويل؟', 'هل يوجد حساب توفير؟'];
            } elseif ($lang === 'en') {
                $reply = "Yes, absolutely! SIMDA operates strictly with 0% Riba (zero interest). All financing is 100% Sharia-compliant through Murabaha cost-plus contracts. There is no compounding interest, no hidden interest fees, and no usury whatsoever.";
                $suggestions = ['What is Murabaha?', 'How can I apply?', 'Do you have savings accounts?'];
            } else {
                $reply = "Haa, 100% waa dhab! SIMDA ma qaadato wax ribo ama dulsaar ah (0% Riba). Dhammaan maalgelinteennu waxay ku saleysan yihiin Shareecada Islaamka gaar ahaan nidaamka Murabaha. Faa'iidada waa mid go'an oo bilowga lagu heshiiyo, weligeedna ma kordho mana jirto dulsaar dulsaar ku fuulaya.";
                $suggestions = ['Murabaha waa maxay?', 'Sideen u codsadaa?', 'Keyd ma leedihiin?'];
            }
        }
        // 2. Savings & Deposits Check (Strictly NO)
        elseif (
            !$wantsApply && preg_match('/(keyd|kayd|lacag dhigasho|dhigo|deposit|saving|savings|توفير|ودائع|إيداع|حساب توفير)/iu', $message)
        ) {
            $topic = 'savings';
            if ($lang === 'ar') {
                $reply = "لا، سيمدا لا تقدم حسابات توفير ولا تقبل الودائع النقدية العامة. نحن مؤسسة تمويل أصغر إسلامية تركز حصرياً على تقديم التمويل الإنتاجي للأصول، الأكشاك، والمشاريع الصغيرة دون الاحتفاظ بودائع ادخارية.";
                $suggestions = ['ما هي مجالات التمويل؟', 'هل التمويل خالٍ من الربا؟', 'كيف أتقدم بطلب؟'];
            } elseif ($lang === 'en') {
                $reply = "No, SIMDA does NOT offer savings accounts or take retail deposits. We are an ethical Islamic microfinance institution focused exclusively on asset financing, kiosk setup, and productive micro-enterprise loans.";
                $suggestions = ['What can I finance?', 'Is it 0% Riba?', 'How to apply?'];
            } else {
                $reply = "Maya, SIMDA ma bixiso xisaabaadka keydka (No Savings Accounts) mana qabato lacago la dhigto. SIMDA waa hay'ad microfinance oo loogu talagalay oo kaliya in ay maalgeliyaan ganacsiyada yaryar, qalabka, iyo baqaarrada iyada oo aan keyd shacabka laga qaban.";
                $suggestions = ['Maxaad maalgeliyaan?', '0% Ribo ma dhabbaa?', 'Sideen u codsadaa?'];
            }
        }
        // 3. Murabaha Definition & Operation
        elseif (
            !$wantsApply && preg_match('/(mura+ba[xh]|cost-plus|مرابحة|المرابحة)/iu', $message)
        ) {
            $topic = 'murabaha';
            if ($lang === 'ar') {
                $reply = "بيع المرابحة هو عقد بيع إسلامي شرعي شفاف: تقوم سيمدا بشراء الأصل، البضاعة، أو المعدات التي تحتاجها من المورد مباشرة، ثم تبيعها لك بسعر التكلفة مضافاً إليه هامش ربح محدد ومتفق عليه مسبقاً. ويتم السداد على أقساط أسبوعية أو شهرية ثابتة دون أي فوائد مركبة.";
                $suggestions = ['هل توجد فوائد ربوية؟', 'ما هي شروط التقديم؟', 'أين يقع مكتبكم؟'];
            } elseif ($lang === 'en') {
                $reply = "Murabaha is an Islamic cost-plus sales contract: SIMDA purchases the exact commercial asset, machine, or inventory you need directly from suppliers, then sells it to you at cost plus a transparent, pre-agreed fixed markup. You repay in fixed weekly or monthly installments with 0% compounding interest.";
                $suggestions = ['Is there any Riba?', 'What are the requirements?', 'Where is your office?'];
            } else {
                $reply = "Murabaha waa heshiis ganacsi oo Islaami ah (Cost-Plus): SIMDA ayaa iibineysa qalabka ama badeecada aad u baahan tahay (mashiin, agab dukaan, ama agab dhisme), kadibna waxay adiga kuugu iibineysaa qiimihii asalka ahaa oo lagu daray faa'iido go'an oo bilowga lagu heshiiyo. Lacagta waxaa lagu bixiyaa qeyb-qeyb (instalments) todobaadle ama bille ah iyadoon wax ribo ah jirin.";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Shuruudaha maxay yihiin?', 'Xafiiskiina xagee ku yaal?'];
            }
        }
        // 4. Products & What We Finance (Assets, Kiosks, Plots)
        elseif (
            !$wantsApply && preg_match('/(alaab|noocyada|dukaan|ganacsi|maalgelis|maalgeli[nyst]|mashiin|kiosk|baqaar|dhul|dhismaha|products|finance|equipment|plot|assets|منتجات|خدمات|أكشاك|بناء|معدات|أصول)/iu', $message)
        ) {
            $topic = 'products';
            if ($lang === 'ar') {
                $reply = "تمول سيمدا حزمة متميزة من الأصول الإنتاجية: 1) آلات ومعدات المشروعات الصغيرة. 2) تأسيس وتجهيز أكشاك الأسواق والمحلات. 3) تمويل أعمال البناء للأراضي ذات الملكية الثابتة. 4) وسائل النقل والخدمات اللوجستية التجارية. 5) تمويل المجموعات التضامنية (Garab-wadaag).";
                $suggestions = ['كيف أتقدم بطلب تمويل؟', 'هل لديكم حسابات توفير؟', 'أين يقع مكتبكم؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA finances productive business assets: 1) Micro-enterprise machinery, tools & commercial equipment. 2) Market kiosks, grocery stalls & vendor shops. 3) Plot construction and development with verified ownership. 4) Commercial transportation. 5) Solidarity group & individual financing.";
                $suggestions = ['How can I apply?', 'Do you have savings accounts?', 'Where is your office?'];
            } else {
                $reply = "SIMDA waxay maalgelisaa: 1) Qalabka & Mashiinnada ganacsiyada yaryar. 2) Dhismaha iyo buuxinta Kiosks-ka iyo dukaamada suuqa. 3) Dhismayaasha qaybaha dhulka ee leh dukumentiyada sharciyeysan. 4) Gaadiidka ganacsiga (sida mootooyinka/bajaajta). 5) Maalgelinta kooxaha garab-wadaagta (Solidarity Groups) iyo shakhsiyaadka.";
                $suggestions = ['Sideen u codsadaa?', 'Keyd ma leedihiin?', 'Xafiiskiina xagee ku yaal?'];
            }
        }
        // 5. Requirements & How to Apply (see $wantsApply above)
        elseif ($wantsApply) {
            $topic = 'apply';
            if ($lang === 'ar') {
                $reply = "للتقديم على تمويل سيمدا: 1) ملء استمارة الطلب عبر موقعنا أو زيارة الفرع. 2) إحضار إثبات الهوية (البطاقة أو الجواز) وبيانات المشروع. 3) تقديم كفيل معتمد أو الانضمام لمجموعة تضامنية. 4) يقوم مسؤول التمويل بزيارة النشاط وتقييمه ثم يعتمده مدير الفرع قبل صرف الأصل مباشرة.";
                $suggestions = ['أين يقع مكتبكم؟', 'هل التمويل 0% ربا؟', 'ما هي أوقات العمل؟'];
            } elseif ($lang === 'en') {
                $reply = "To apply for SIMDA financing: 1) Submit an inquiry online or visit our branch. 2) Provide valid identification (National ID/Passport) and your business outline. 3) Provide a reputable guarantor or join a solidarity group. 4) Our Loan Officer evaluates your business, followed by Branch Manager sign-off and direct asset handover.";
                $suggestions = ['Where is your office?', 'Is it 0% Riba?', 'What are working hours?'];
            } else {
                $reply = "Si aad u codsato maalgelin: 1) Booqo xafiiskeena ama buuxi foomka xiriirka boggan. 2) Keen aqoonsi rasmi ah (ID/Passport) iyo qorshaha ganacsigaaga. 3) Keen dammaanad qaade (guarantor) la aamini karo ama ku biir koox garab-wadaag ah. 4) Sarkaalka Maaliyadda ayaa booqanaya goobtaada, kadibna Maareeyaha Laanta ayaa ansixinaya ka hor intaan qalabka lagu wareejin.";
                $suggestions = ['Xafiiskiina xagee ku yaal?', '0% Ribo ma dhabbaa?', 'Saacadaha shaqadu waa maxay?'];
            }
        }
        // 6. Dual-threshold approval (Officer & Manager)
        elseif (
            preg_match('/(sarkaal|maareeye|officer|manager|approval|dual|workflow|موافقة|مدير|مسؤول)/iu', $message)
        ) {
            $topic = 'approval';
            if ($lang === 'ar') {
                $reply = "نظام الموافقة الثنائي يضمن النزاهة والسرعة: 1) مسؤول التمويل (Loan Officer) يتأكد من هوية العميل والجدوى وزيارة الموقع. 2) مدير الفرع (Branch Manager) يدقق في حدود التمويل ومعايير الالتزام الشرعي قبل توقيع أمر الصرف النهائي.";
                $suggestions = ['كيف أتقدم بطلب؟', 'هل التمويل 0% ربا؟', 'ما هي المرابحة؟'];
            } elseif ($lang === 'en') {
                $reply = "Our dual-threshold workflow ensures thorough fairness: 1) The Loan Officer verifies the business profile, guarantor, and visits the site. 2) The Branch Manager independently reviews audit thresholds and grants final approval before disbursement.";
                $suggestions = ['How to apply?', 'Is it 0% Riba?', 'What is Murabaha?'];
            } else {
                $reply = "Nidaamka oggolaanshaha labada heer ah wuxuu dammaanad qaadayaa hufnaan buuxda: 1) Sarkaalka Maaliyadda (Loan Officer) ayaa darsaya xaaladda ganacsigaaga, dammaanadda, iyo awoodda bixinta. 2) Maareeyaha Laanta (Branch Manager) ayaa hubinaya shuruucda iyo shareecada ka hor inta aan oggolaanshaha kama dambaysta ah la bixin.";
                $suggestions = ['Sideen u codsadaa?', '0% Ribo ma dhabbaa?', 'Murabaha waa maxay?'];
            }
        }
        // 7. Friday-to-Saturday shift & Repayment Schedules
        elseif (
            preg_match('/(jimco|sabti|jadwal|bixinta|shift|friday|saturday|schedule|repayment|جمعة|سبت|سداد|جدول)/iu', $message)
        ) {
            $topic = 'schedule';
            if ($lang === 'ar') {
                $reply = "احتراماً لعطلة يوم الجمعة المباركة في العالم الإسلامي، يقوم نظام الجدولة الآلي في سيمدا بترحيل أي قسط يستحق يوم الجمعة تلقائياً إلى يوم السبت دون احتساب أي تأخير أو رسوم إضافية.";
                $suggestions = ['ماذا عن غرامات التأخير؟', 'هل التمويل 0% ربا؟', 'كيف أتقدم بطلب؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA deeply respects Islamic Friday observance. Our automated schedule engine automatically shifts any installment originally falling on a Friday to Saturday with zero late fees or penalties.";
                $suggestions = ['What about late penalties?', 'Is it 0% Riba?', 'How to apply?'];
            } else {
                $reply = "SIMDA waxay si weyn u xushmeysaa maalinta barakaysan ee Jimcaha. Haddii taariikhda bixinta lacagtu ku beeganto Jimco, nidaamkayagu si toos ah ayuu ugu wareejiyaa maalin Sabti ah iyada oo wax ganaax ama dacwad ah lagaa rabin.";
                $suggestions = ['Ganaaxa xagee aadaa?', '0% Ribo ma dhabbaa?', 'Sideen u codsadaa?'];
            }
        }
        // 8. Late payment & Segregated Charity Fund
        elseif (
            preg_match('/(ganaax|penalty|late|dib u dhac|charity|sadaqo|غرامة|تأخير|صدقة|صندوق الصدقات)/iu', $message)
        ) {
            $topic = 'charity';
            if ($lang === 'ar') {
                $reply = "أي غرامات تأديبية تُفرض في حال المماطلة غير المبررة تُحول بنسبة 100% إلى صندوق الصدقات الخيري المنفصل تماماً، والذي يُصرف على إغاثة الفقراء وكفالة المحتاجين. ولا يدخل حسابات سيمدا أي مليم منها ($0 للمؤسسة).";
                $suggestions = ['هل التمويل 0% ربا؟', 'ما هي المرابحة؟', 'أين يقع مكتبكم؟'];
            } elseif ($lang === 'en') {
                $reply = "Any disciplinary late payments are directed 100% into a strictly segregated Charity Fund used for community relief, humanitarian aid, and disadvantaged grants. SIMDA earns exactly $0 from penalties, keeping our finances 100% pure from Riba.";
                $suggestions = ['Is it 0% Riba?', 'What is Murabaha?', 'Where is your office?'];
            } else {
                $reply = "Haddiiba qofku si sabab la'aan ah u daaho, lacagta ganaaxa edbinta ah 100% waxay toos ugu dhacdaa 'Sanduuqa Sadaqada' (Charity Fund) oo ah sanduuq gooni ah oo lagu caawiyo masaakiinta iyo danta guud. SIMDA wax dakhli ah kama qaadato ganaaxa ($0 ayey ka heshaa).";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Murabaha waa maxay?', 'Xafiiskiina xagee ku yaal?'];
            }
        }
        // 9. Guarantor & Solidarity Groups
        elseif (
            preg_match('/(dammaanad|damaanad|damiin|dammiin|kafiil|garab|wadaag|koox|guarantor|solidarity|group|كفيل|ضمان|تضامني|مجموعات)/iu', $message)
        ) {
            $topic = 'guarantor';
            if ($lang === 'ar') {
                $reply = "توفر سيمدا خيارين ميسرين للضمان: 1) كفيل شخصي موثوق (تاجر أو موظف معتمد). 2) نظام المجموعات التضامنية (Garab-wadaag) المكونة من 3 إلى 7 رواد أعمال يضمنون بعضهم البعض دون الحاجة لكفيل خارجي.";
                $suggestions = ['كيف أتقدم بطلب تمويل؟', 'ما هي المنتجات؟', 'أين يقع فرعكم؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA offers two flexible guarantee models: 1) An individual vetted guarantor (merchant or verified employee). 2) Solidarity lending groups (Garab-wadaag) of 3-7 micro-entrepreneurs who co-guarantee each other without needing external collateral.";
                $suggestions = ['How can I apply?', 'What are the products?', 'Where is your branch?'];
            } else {
                $reply = "SIMDA waxay leedahay laba hab oo dammaanad ah: 1) Dammaanad qaade shakhsi ah (ganacsade ama qof la aaminsan yahay). 2) Kooxaha Garab-wadaagta (Solidarity Groups) oo ah 3 ilaa 7 qof oo ganacsato yaryar ah oo is dammaanad qaadaya iyadoon dammaanad dibadeed loo baahnayn.";
                $suggestions = ['Sideen u codsadaa?', 'Maxaad maalgeliyaan?', 'Xafiiskiina xagee ku yaal?'];
            }
        }
        // 10. Office location, contact & hours
        elseif (
            preg_match('/(xafiis|xaggee|xagee|office|address|location|where is|phone|email|contact|saacadaha|hours|ciwaan|أين|موقع|عنوان|هاتف|تواصل|ساعات العمل)/iu', $message)
        ) {
            $topic = 'contact';
            $waUrl = 'https://wa.me/252616256534?text=' . rawurlencode(
                $lang === 'ar' ? 'مرحباً سيمدا، أرغب في الاستفسار عن التمويل الإسلامي' :
                ($lang === 'en' ? 'Hello SIMDA, I would like to inquire about Islamic microfinance' :
                'Asc SIMDA, waxaan rabaa in aan wax ka ogaado maalgelinta xalaasha ah')
            );
            $actionBtn = [
                'type' => 'whatsapp',
                'label' => ($lang === 'ar' ? '💬 تواصل عبر واتساب (+252 616256534)' : ($lang === 'en' ? '💬 Chat on WhatsApp (+252 616256534)' : '💬 La Hadal WhatsApp (+252 616256534)')),
                'url' => $waUrl
            ];
            if ($lang === 'ar') {
                $reply = "يقع المقر الرئيسي لسيمدا في تقاطع KM4، مديرية هودن، مقديشو، الصومال. هاتف: 616256534 252+ / 613496943 252+. البريد: anasabdiwahidhussein@gmail.com. ساعات العمل: السبت إلى الخميس، من 8:00 صباحاً حتى 4:30 مساءً (الجمعة عطلة).\n\nيمكنك أيضاً التحدث معنا فوراً عبر واتساب بالنقر أدناه:";
                $suggestions = ['كيف أتقدم بطلب؟', 'هل التمويل 0% ربا؟', 'ما هي المنتجات؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA Head Office is located at KM4, Hodan District, Mogadishu, Somalia. Phone: +252 616256534 / +252 613496943. Email: anasabdiwahidhussein@gmail.com. Working hours: Saturday to Thursday, 8:00 AM – 4:30 PM (Closed on Friday).\n\nYou can also click below to chat with us directly on WhatsApp:";
                $suggestions = ['How to apply?', 'Is it 0% Riba?', 'What are the products?'];
            } else {
                $reply = "Xafiiska Sare ee SIMDA wuxuu ku yaallaa: KM4, Degmada Hodan, Muqdisho, Soomaaliya. Tel: +252 616256534 ama +252 613496943. Email: anasabdiwahidhussein@gmail.com. Saacadaha shaqadu waa Sabti ilaa Khamiis 8:00 subaxnimo ilaa 4:30 galabnimo (Jimcaha waa fasax).\n\nWaxaad sidoo kale toos noogala soo xiriiri kartaa WhatsApp adoo gujinaya batoonka hoose:";
                $suggestions = ['Sideen u codsadaa?', '0% Ribo ma dhabbaa?', 'Maxaad maalgeliyaan?'];
            }
        }
        // 11. Who are you / Bot Identity
        elseif (
            preg_match('/(yaa tahay|who are you|what is simda|maxay tahay simda|من أنت|ما هي سيمدا|ذكاء)/iu', $message)
        ) {
            $topic = 'identity';
            if ($lang === 'ar') {
                $reply = "أنا المساعد الذكي لمؤسسة سيمدا للتمويل الأصغر الإسلامي (SIMDA). مهمتي مساعدتك والإجابة عن كافة استفساراتك حول التمويل الخالي من الربا بنسبة 0%، منتجات المرابحة، وشروط التقديم باللغات العربية، الصومالية، والإنجليزية.";
                $suggestions = ['هل التمويل 0% ربا؟', 'ما هي المرابحة؟', 'كيف أتقدم بطلب؟'];
            } elseif ($lang === 'en') {
                $reply = "I am SIMDA's Smart AI Assistant! I am here to assist you 24/7 with questions about our 0% Riba Islamic microfinance, Murabaha asset financing, application steps, and policies in English, Somali, and Arabic.";
                $suggestions = ['Is it 0% Riba?', 'What is Murabaha?', 'How can I apply?'];
            } else {
                $reply = "Waxaan ahay Kaaliyaha Caqliga Badan ee SIMDA (SIMDA Smart AI Assistant). Waxaan ku hadlaa Af-Soomaali, English, iyo Carabi, waxaana u taaganahay inaan kugu caawiyo wax walba oo ku saabsan maalgelinta xalaasha ah (0% Ribo), nidaamka Murabaha, iyo sida loo codsado.";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Murabaha waa maxay?', 'Sideen u codsadaa?'];
            }
        }
        // 12. Developer & Platform Engineer (Anas Abdiwahid)
        elseif (
            preg_match('/(developer|anas|abdiwahid|yaa dhisay|yaa sameeyay|who made|who developed|من طور|من بنى|من المطور)/iu', $message)
        ) {
            $topic = 'developer';
            if ($lang === 'ar') {
                $reply = "تم تصميم وهندسة منصة سيمدا (SIMDA) الذكية للتمويل الإسلامي بواسطة المهندس أنس عبد الواحد (Anas Abdiwahid).";
                $suggestions = ['أخبرني عن هذا المكان', 'هل التمويل 0% ربا؟'];
            } elseif ($lang === 'en') {
                $reply = "The SIMDA Islamic Microfinance digital platform was architected and developed by software engineer Anas Abdiwahid (https://anazabdiwahid.netlify.app/).";
                $suggestions = ['Tell me about this place', 'Is it 0% Riba?'];
            } else {
                $reply = "Nidaamka casriga ah iyo bogga SIMDA waxaa dhisay oo farsamadiisa heerka sare ah diyaariyay Software Engineer Anas Abdiwahid (https://anazabdiwahid.netlify.app/).";
                $suggestions = ['Xogta meesha wax iga sheeg', '0% Ribo ma dhabbaa?'];
            }
        }
        // 13. Gratitude
        elseif (
            preg_match('/(mahadsanid|shukran|thanks|thank you|شكرا|جزاك)/iu', $message)
        ) {
            $topic = 'thanks';
            if ($lang === 'ar') {
                $reply = "على الرحب والسعة دائماً! وبارك الله فيك. إذا كان لديك أي سؤال آخر، أنا دائماً في خدمتك.";
                $suggestions = ['أين يقع مكتبكم؟', 'كيف أتقدم بطلب؟'];
            } elseif ($lang === 'en') {
                $reply = "You are most welcome! Always happy to help. Let me know if you need anything else about SIMDA.";
                $suggestions = ['Where is your office?', 'How to apply?'];
            } else {
                $reply = "Adaa mudan! Waan ku faraxsanahay inaan ku caawiyo. Haddii aad qabto su'aal kale, mar walba diyaar ayaan kuu ahay.";
                $suggestions = ['Xafiiskiina xagee ku yaal?', 'Sideen u codsadaa?'];
            }
        }
        // 14. Smart Fallback for any other question
        else {
            $topic = 'fallback';
            if ($lang === 'ar') {
                $reply = "سؤال رائع! في سيمدا نحن ملتزمون بتقديم تمويل أصغر إسلامي 100% خالٍ من الربا (0% ربا) لدعم رواد الأعمال، الأكشاك، وتمويل الأصول الإنتاجية بنظام المرابحة. للإجابة الدقيقة على تفاصيل استفسارك، يمكنك زيارة فرعنا بـ KM4 بمقديشو أو التواصل مباشرة عبر الهاتف: 616256534 252+ / 613496943 252+ أو البريد anasabdiwahidhussein@gmail.com.";
                $suggestions = ['هل التمويل خالٍ من الربا؟', 'ما هي شروط التقديم؟', 'أين يقع مكتبكم؟'];
            } elseif ($lang === 'en') {
                $reply = "Thank you for asking! SIMDA is dedicated to 100% Sharia-compliant, 0% Riba microfinance for Somali entrepreneurs, kiosk owners, and asset financing through Murabaha. For tailored assistance on this specific inquiry, our team at KM4 Mogadishu is ready to help at +252 616256534 / +252 613496943 or anasabdiwahidhussein@gmail.com.";
                $suggestions = ['Is it 0% Riba?', 'How can I apply?', 'Where is your office?'];
            } else {
                $reply = "Waad ku mahadsan tahay su'aashaada! SIMDA waxay u taagan tahay maalgelin xalaal ah oo 100% ribo la'aan ah (0% Ribo) oo lagu taageerayo ganacsatada yaryar, baqaarrada, iyo qalabka ganacsiga iyadoo la adeegsanayo nidaamka Murabaha. Faahfaahin dheeraad ah oo ku saabsan su'aashaada gaarka ah, fadlan nagala soo xiriir xafiiska KM4, Muqdisho ama Tel: +252 616256534 / +252 613496943 / anasabdiwahidhussein@gmail.com.";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Sideen u codsadaa?', 'Xafiiskiina xagee ku yaal?'];
            }
        }

        echo json_encode([
            'status' => 'ok',
            'reply' => $reply,
            'language' => $lang,
            'topic' => $topic,
            'suggestions' => $suggestions,
            'action_btn' => $actionBtn
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Keywords the chatbot rules look for, by the spelling the rules expect.
     * A typed word that is close to one of these (after evening out common Somali
     * spelling variations) adds the correct keyword to the message.
     */
    private const CHAT_KEYWORDS = [
        // Somali
        'codsi', 'codsadaa', 'codsado', 'dalbo', 'shuruudaha', 'qaabkee', 'murabaha', 'murabaxo', 'ribo', 'ribada', 'dulsaar',
        'keyd', 'kayd', 'xafiis', 'xagee', 'xaggee', 'ciwaan', 'lambar', 'lambarka', 'taleefan', 'telefoon', 'xiriir', 'whatsapp',
        'dammaanad', 'damiin', 'garab', 'wadaag', 'koox', 'ganaax', 'sadaqo', 'jimco', 'sabti', 'jadwal', 'bixinta',
        'mashiin', 'kiosk', 'baqaar', 'dhul', 'dhismaha', 'alaab', 'noocyada', 'maalgelin', 'maalgelisaan', 'dayn', 'deyn', 'amaah',
        'lacag', 'heli', 'helaa', 'qaadan', 'sarkaal', 'maareeye', 'saacadaha', 'mahadsanid', 'magacaa', 'magacaaga', 'kaftan',
        'meesha', 'xogta', 'shirkadda', 'sidee', 'sideen',
        // English
        'apply', 'application', 'requirements', 'eligibility', 'murabaha', 'interest', 'savings', 'deposit', 'office', 'address',
        'location', 'contact', 'phone', 'number', 'guarantor', 'solidarity', 'penalty', 'charity', 'schedule', 'repayment',
        'friday', 'saturday', 'products', 'equipment', 'finance', 'financing', 'thanks',
    ];

    /** Even out spelling variations: x/h, q/k, doubled letters, apostrophes, a leading c (ayn). */
    private static function chatSkeleton(string $word): string
    {
        $w = mb_strtolower($word, 'UTF-8');
        $w = preg_replace('/[^a-z]/', '', $w) ?? '';
        $w = strtr($w, ['x' => 'h', 'q' => 'k', 'y' => 'i', 'e' => 'i', 'o' => 'u']);
        $w = preg_replace('/(.)\1+/', '$1', $w) ?? $w;
        if (strlen($w) > 3 && $w[0] === 'c') {
            $w = substr($w, 1);
        }
        return $w;
    }

    private static function chatSpellingHints(string $message): string
    {
        static $dict = null;
        if ($dict === null) {
            $dict = [];
            foreach (self::CHAT_KEYWORDS as $k) {
                $dict[$k] = self::chatSkeleton($k);
            }
        }

        $hints = [];
        $words = preg_split('/[^\p{L}\']+/u', mb_strtolower($message, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        // Everyday words that sit one letter away from a keyword and must never be "corrected"
        // (kartaan ~ kaftan, waran ~ wadaag, ...).
        static $common = [
            'kartaa', 'kartaan', 'karaa', 'karo', 'kara', 'karin', 'kartid', 'waran', 'warran', 'baan', 'waxaan', 'waxaad',
            'maxaa', 'maxay', 'maxaad', 'ayaa', 'ayuu', 'ayey', 'iyo', 'leh', 'lahayn', 'tahay', 'yihiin', 'jiraa', 'jirta',
            'rabaa', 'rabay', 'doonaa', 'kale', 'badan', 'yar', 'weyn', 'hadda', 'maanta', 'berri', 'shalay', 'gabar', 'wiil',
            'the', 'this', 'that', 'what', 'how', 'can', 'want', 'have', 'there', 'their', 'your',
        ];
        foreach ($words as $word) {
            if (mb_strlen($word) < 3 || in_array($word, $common, true)) {
                continue;
            }
            $sk = self::chatSkeleton($word);
            if ($sk === '') {
                continue;
            }
            $best = null;
            $bestDist = PHP_INT_MAX;
            foreach ($dict as $keyword => $kSk) {
                if ($word === $keyword) {
                    $best = null; // already spelled right
                    break;
                }
                $len = strlen($kSk);
                // Allowed typos grow with word length: short words must match after evening out.
                $maxDist = $len <= 3 ? 0 : ($len <= 6 ? 1 : 2);
                $d = $sk === $kSk ? 0 : levenshtein($sk, $kSk);
                // Longer word built on the keyword ("codsanayaa" -> codsi stem "udsi"... via prefix).
                if ($d > $maxDist && $len >= 4 && str_starts_with($sk, substr($kSk, 0, max(4, $len - 1)))) {
                    $d = $maxDist;
                }
                if ($d <= $maxDist && $d < $bestDist) {
                    $best = $keyword;
                    $bestDist = $d;
                }
            }
            if ($best !== null) {
                $hints[$best] = true;
            }
        }

        return $hints === [] ? '' : ' ' . implode(' ', array_keys($hints));
    }

    private static function contactSubmit(PDO $pdo): void
    {
        Security::validateCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? 'General Inquiry'));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || ($phone === '' && $email === '') || $message === '') {
            $_SESSION['flash_error'] = Lang::locale() === 'so'
                ? 'Fadlan buuxi magacaaga, xiriirkaaga (taleefan ama email), iyo farriintaada.'
                : (Lang::locale() === 'ar' ? 'يرجى إدخال اسمك ووسيلة الاتصال ورسالتك.' : 'Please provide your name, contact, and message.');
            header('Location: ?route=contact');
            exit;
        }

        try {
            $st = $pdo->prepare('INSERT INTO communication_logs (channel, recipient, message_body, status, created_at) VALUES ("contact_inquiry", :recipient, :msg, "received", NOW())');
            $st->execute([
                ':recipient' => ($email !== '' ? $email : $phone),
                ':msg' => "Inquiry from {$name} ({$phone} / {$email}) [{$subject}]: {$message}"
            ]);
        } catch (\Throwable $e) {
            // Ignore if communication_logs schema varies
        }

        $_SESSION['flash_success'] = Lang::locale() === 'so'
            ? 'Mahadsanid! Farriintaada si guul leh ayaa loo diray. Khubaradayada ayaa kula soo xiriiri doona.'
            : (Lang::locale() === 'ar' ? 'شكراً لتواصلك! تم استلام رسالتك بنجاح وسيتواصل معك فريقنا قريباً.' : 'Thank you! Your message has been sent successfully. Our team will contact you soon.');

        header('Location: ?route=contact');
        exit;
    }

    private static function publicOffline(): void
    {
        require dirname(__DIR__, 2) . '/resources/views/public/offline.php';
    }

    private static function error404(): void
    {
        http_response_code(404);
        require dirname(__DIR__, 2) . '/resources/views/errors/404.php';
    }

    private static function loginForm(): void
    {
        if (AuthMiddleware::check()) {
            header('Location: ?route=dashboard');
            exit;
        }

        if (!empty($_GET['cancel_2fa'])) {
            unset(
                $_SESSION['admin_2fa_pending'],
                $_SESSION['admin_2fa_user_id'],
                $_SESSION['admin_2fa_challenge_id'],
                $_SESSION['admin_2fa_email'],
                $_SESSION['admin_2fa_name'],
                $_SESSION['admin_2fa_code'],
                $_SESSION['admin_2fa_expires_at'],
                $_SESSION['admin_2fa_attempts'],
                $_SESSION['login_verified_identifier']
            );
            header('Location: ?route=login');
            exit;
        }

        require dirname(__DIR__, 2) . '/resources/views/auth/login.php';
    }

    private static function loginCheckEmail(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        header('Content-Type: application/json; charset=utf-8');
        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $locale = Lang::locale();
        $isSo = $locale === 'so';
        $isAr = $locale === 'ar';

        if ($identifier === '') {
            $msg = $isSo ? 'Fadlan geli email-kaaga ama telefoonkaaga.' : ($isAr ? 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف.' : 'Please enter your email or phone number.');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }

        // User name, email or phone number (clients usually sign in with their phone).
        $user = \App\Services\ClientAccountService::findLoginUser($pdo, $identifier);

        if (!$user) {
            $msg = $isSo
                ? 'Email-kan ama telefoonkan lama aqoonsano. Isku day mar kale.'
                : ($isAr
                    ? 'عذراً، هذا البريد الإلكتروني أو رقم الهاتف غير مسجل. يرجى المحاولة مرة أخرى.'
                    : 'This email or phone number is not recognized. Try again.');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }

        // Check if account is locked or blocked
        $lockoutStatus = AuthService::checkLockoutStatus($user, $locale);
        if ($lockoutStatus !== null) {
            echo json_encode([
                'success' => false,
                'message' => $lockoutStatus['message'],
                'is_locked' => true,
                'is_permanent' => $lockoutStatus['permanent'] ?? false
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'identifier' => $user['email'] ?: $user['username'],
            'full_name' => $user['full_name'],
            'message' => $isSo ? 'Akoonka waa la aqoonsaday. Fadlan geli furahaaga sirta ah.' : 'Account recognized. Please enter your password.'
        ]);
        exit;
    }

    private static function loginSubmit(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $locale = Lang::locale();
        $isSo = $locale === 'so';
        $isAr = $locale === 'ar';

        // Security Step 1: Check Email existence and fetch user with role
        // User name, email or phone number (clients usually sign in with their phone).
        $user = \App\Services\ClientAccountService::findLoginUser($pdo, $identifier);

        if (!$user) {
            $_SESSION['flash_error'] = $isSo
                ? 'Email-kan ama telefoonkan lama aqoonsano. Isku day mar kale.'
                : ($isAr ? 'عذراً، هذا البريد الإلكتروني أو رقم الهاتف غير مسجل. يرجى المحاولة مرة أخرى.' : 'This email or phone number is not recognized. Try again.');
            header('Location: ?route=login');
            exit;
        }

        // Check if account is locked or blocked
        $lockoutStatus = AuthService::checkLockoutStatus($user, $locale);
        if ($lockoutStatus !== null) {
            unset($_SESSION['login_verified_identifier']);
            $_SESSION['flash_error'] = $lockoutStatus['message'];
            header('Location: ?route=login');
            exit;
        }

        // If email was posted without password (progressive enhancement), store verified identifier
        if ($password === '') {
            $_SESSION['login_verified_identifier'] = $user['email'] ?: $user['username'];
            header('Location: ?route=login');
            exit;
        }

        // Verify password
        if (!Security::verifyPassword($password, (string) $user['password_hash'])) {
            $failResult = AuthService::handleFailedPassword($user, $pdo, $locale);
            if (!empty($failResult['locked'])) {
                unset($_SESSION['login_verified_identifier']);
            } else {
                $_SESSION['login_verified_identifier'] = $user['email'] ?: $user['username'];
            }
            $_SESSION['flash_error'] = $failResult['message'];
            header('Location: ?route=login');
            exit;
        }

        // Password verified successfully!
        // Admin High-Security 2FA Verification:
        $isRoleAdmin = ($user['role_slug'] === 'admin');

        if ($isRoleAdmin) {
            // Generate 8-character uppercase alphanumeric code (e.g. A01B99L2)
            $twoFaChars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            $twoFaCode = '';
            $charsLen = strlen($twoFaChars);
            for ($i = 0; $i < 8; $i++) {
                $twoFaCode .= $twoFaChars[random_int(0, $charsLen - 1)];
            }

            // Dispatch 2FA email to Admin's registered email
            $twoFaMail = MailService::sendAdmin2FACodeEmail($user['email'], $user['full_name'], $twoFaCode, $locale);

            // Record challenge in database
            $stmt2fa = $pdo->prepare('
                INSERT INTO admin_2fa_codes (user_id, email, code_hash, attempts, is_used, expires_at)
                VALUES (:uid, :email, :chash, 0, 0, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
            ');
            $stmt2fa->execute([
                ':uid' => (int) $user['id'],
                ':email' => $user['email'],
                ':chash' => password_hash($twoFaCode, PASSWORD_BCRYPT)
            ]);
            $challengeId = (int) $pdo->lastInsertId();

            // Set session state
            $_SESSION['admin_2fa_pending'] = true;
            $_SESSION['admin_2fa_user_id'] = (int) $user['id'];
            $_SESSION['admin_2fa_challenge_id'] = $challengeId;
            $_SESSION['admin_2fa_email'] = $user['email'];
            $_SESSION['admin_2fa_name'] = $user['full_name'];
            $_SESSION['admin_2fa_code'] = $twoFaCode;
            $_SESSION['admin_2fa_expires_at'] = time() + 600;
            $_SESSION['admin_2fa_attempts'] = 0;
            $_SESSION['admin_2fa_remember'] = !empty($_POST['remember']);

            AuditLogger::log('auth.admin_2fa_dispatched', 'users', (int) $user['id'], "8-character 2FA code dispatched to Admin {$user['email']}.");

            $_SESSION['flash_success'] = $isSo
                ? 'Furahaagu wuu saxan yahay! Amniga sare ee maamulaha dartiis, koodh 8 xaraf/lambar ah ayaa laguugu diray email-kaaga ' . Security::e($user['email'])
                : 'Password verified! As an Administrator, an 8-character security code has been sent to ' . Security::e($user['email']);
            self::admin2faFallbackNotice($twoFaMail, $twoFaCode);

            header('Location: ?route=login');
            exit;
        }

        // Non-admin login completes immediately
        try {
            $isFirstLogin = empty($user['last_login_at']);
            AuthService::completeLoginForUser($user, $pdo);
            unset($_SESSION['login_verified_identifier']);
            // Client's very first sign-in: celebrate, and warn about the default password.
            if ($user['role_slug'] === 'client' && $isFirstLogin) {
                $_SESSION['client_welcome'] = true;
            }
            header('Location: ?route=dashboard');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ?route=login');
            exit;
        }
    }

    private static function admin2faVerify(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $locale = Lang::locale();
        $isSo = $locale === 'so';
        $isAr = $locale === 'ar';

        if (empty($_SESSION['admin_2fa_pending']) || empty($_SESSION['admin_2fa_user_id'])) {
            $_SESSION['flash_error'] = $isSo ? 'Kalfadhiga xaqiijintu wuu dhacay. Fadlan mar kale gal.' : ($isAr ? 'انتهت جلسة التحقق. يرجى تسجيل الدخول مرة أخرى.' : 'Verification session expired. Please sign in again.');
            header('Location: ?route=login');
            exit;
        }

        if (time() > (int) ($_SESSION['admin_2fa_expires_at'] ?? 0)) {
            $_SESSION['flash_error'] = $isSo ? 'Koodhkii amnigu wuu dhacay (10 daqiiqo). Fadlan koodh cusub dalbo.' : ($isAr ? 'انتهت صلاحية رمز الأمان (10 دقائق). يرجى طلب رمز جديد.' : 'Security code expired (10 minutes). Please request a new code.');
            header('Location: ?route=login');
            exit;
        }

        $userId = (int) $_SESSION['admin_2fa_user_id'];
        $storedCode = (string) ($_SESSION['admin_2fa_code'] ?? '');

        // Fetch user record
        $st = $pdo->prepare('
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en,
                   b.name AS branch_name, b.code AS branch_code,
                   c.id AS client_id, c.client_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            LEFT JOIN clients c ON c.user_id = u.id
            WHERE u.id = :id AND u.deleted_at IS NULL
            LIMIT 1
        ');
        $st->execute([':id' => $userId]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            unset($_SESSION['admin_2fa_pending']);
            header('Location: ?route=login');
            exit;
        }

        // Compare 8-digit codes (case-insensitive)
        if ($code === '' || $code !== strtoupper($storedCode)) {
            $_SESSION['admin_2fa_attempts'] = ((int) ($_SESSION['admin_2fa_attempts'] ?? 0)) + 1;
            if ($_SESSION['admin_2fa_attempts'] >= 5) {
                // Lockout account
                unset($_SESSION['admin_2fa_pending']);
                $failResult = AuthService::handleFailedPassword($user, $pdo, $locale);
                $_SESSION['flash_error'] = $failResult['message'];
                header('Location: ?route=login');
                exit;
            }
            $_SESSION['flash_error'] = $isSo
                ? 'Koodhka 8-da xaraf/lambar ee aad galisay ma saxna! Fadlan ka eeg email-kaaga ' . Security::e($user['email']) . ' oo mar kale isku day.'
                : ($isAr ? 'رمز الأمان المدخل غير صحيح! يرجى مراجعة بريدك الإلكتروني والمحاولة مرة أخرى.' : 'Invalid 8-character security code! Please check your email and try again.');
            header('Location: ?route=login');
            exit;
        }

        // Code is correct!
        if (!empty($_SESSION['admin_2fa_challenge_id'])) {
            $pdo->prepare('UPDATE admin_2fa_codes SET is_used = 1 WHERE id = :id')->execute([':id' => $_SESSION['admin_2fa_challenge_id']]);
        }

        unset(
            $_SESSION['admin_2fa_pending'],
            $_SESSION['admin_2fa_user_id'],
            $_SESSION['admin_2fa_challenge_id'],
            $_SESSION['admin_2fa_email'],
            $_SESSION['admin_2fa_name'],
            $_SESSION['admin_2fa_code'],
            $_SESSION['admin_2fa_expires_at'],
            $_SESSION['admin_2fa_attempts'],
            $_SESSION['login_verified_identifier']
        );

        // Complete full admin login
        AuthService::completeLoginForUser($user, $pdo);
        header('Location: ?route=dashboard');
        exit;
    }

    /**
     * Hosts that block outgoing mail (e.g. free InfinityFree) would lock the admin out, because the
     * 2FA code never arrives. When the email fails, the login page shows the code instead; the
     * password has already been checked at this point.
     */
    private static function admin2faFallbackNotice(array $mailResult, string $code): void
    {
        if (!empty($mailResult['success'])) {
            return;
        }
        $_SESSION['flash_success'] = Lang::locale() === 'so'
            ? "Furahaagu wuu saxan yahay. Email-ka lama diri karo hadda, markaa koodhkaaga amniga waa: {$code}"
            : "Password verified. The email could not be sent right now, so your security code is: {$code}";
    }

    private static function admin2faResend(PDO $pdo): void
    {
        $locale = Lang::locale();
        $isSo = $locale === 'so';
        $isAr = $locale === 'ar';

        if (empty($_SESSION['admin_2fa_pending']) || empty($_SESSION['admin_2fa_user_id'])) {
            $_SESSION['flash_error'] = $isSo ? 'Kalfadhigu wuu dhacay. Fadlan mar kale gal.' : 'Session expired. Please sign in again.';
            header('Location: ?route=login');
            exit;
        }

        $userId = (int) $_SESSION['admin_2fa_user_id'];
        $st = $pdo->prepare('SELECT id, full_name, email FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $st->execute([':id' => $userId]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            unset($_SESSION['admin_2fa_pending']);
            header('Location: ?route=login');
            exit;
        }

        // Generate fresh 8-char code (e.g. A01B99L2)
        $twoFaChars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $twoFaCode = '';
        $charsLen = strlen($twoFaChars);
        for ($i = 0; $i < 8; $i++) {
            $twoFaCode .= $twoFaChars[random_int(0, $charsLen - 1)];
        }

        $twoFaMail = MailService::sendAdmin2FACodeEmail($user['email'], $user['full_name'], $twoFaCode, $locale);

        $stmt2fa = $pdo->prepare('
            INSERT INTO admin_2fa_codes (user_id, email, code_hash, attempts, is_used, expires_at)
            VALUES (:uid, :email, :chash, 0, 0, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ');
        $stmt2fa->execute([
            ':uid' => (int) $user['id'],
            ':email' => $user['email'],
            ':chash' => password_hash($twoFaCode, PASSWORD_BCRYPT)
        ]);
        $challengeId = (int) $pdo->lastInsertId();

        $_SESSION['admin_2fa_challenge_id'] = $challengeId;
        $_SESSION['admin_2fa_code'] = $twoFaCode;
        $_SESSION['admin_2fa_expires_at'] = time() + 600;

        $_SESSION['flash_success'] = $isSo
            ? 'Koodh cusub oo 8 xaraf/lambar ah ayaa laguugu soo diray email-kaaga ' . Security::e($user['email'])
            : ($isAr ? 'تم إرسال رمز أمان جديد مكون من 8 خانات إلى بريدك الإلكتروني.' : 'A fresh 8-character security code has been sent to ' . Security::e($user['email']));
        self::admin2faFallbackNotice($twoFaMail, $twoFaCode);

        header('Location: ?route=login');
        exit;
    }

    private static function forgotPasswordForm(): void
    {
        if (AuthMiddleware::check()) {
            header('Location: ?route=dashboard');
            exit;
        }
        require dirname(__DIR__, 2) . '/resources/views/auth/forgot_password.php';
    }

    private static function forgotPasswordSendOtp(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        header('Content-Type: application/json; charset=utf-8');

        $type = (string) ($_POST['type'] ?? 'email');
        $target = trim((string) ($_POST['target'] ?? ''));
        $locale = Lang::locale();
        $isSo = $locale === 'so';

        if ($target === '') {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Fadlan geli taleefankaaga ama email-kaaga.' : 'Please enter your phone or email.'
            ]);
            exit;
        }

        if ($type === 'phone') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $target);
            $st = $pdo->prepare('
                SELECT id, full_name, email, phone, is_active
                FROM users
                WHERE (REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "+", "") LIKE :p1
                   OR REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "+", "") LIKE :p2)
                  AND deleted_at IS NULL
                LIMIT 1
            ');
            $st->execute([
                ':p1' => '%' . substr($cleanPhone, -8),
                ':p2' => '%' . $cleanPhone . '%'
            ]);
            $user = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $pdo->prepare('
                SELECT id, full_name, email, phone, is_active
                FROM users
                WHERE (email = :e OR username = :u)
                  AND deleted_at IS NULL
                LIMIT 1
            ');
            $st->execute([':e' => $target, ':u' => $target]);
            $user = $st->fetch(PDO::FETCH_ASSOC);
        }

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => $isSo
                    ? 'Lama helin akoon ku diiwaangashan xogtan. Fadlan hubi oo isku day mar kale.'
                    : 'No account found with this information. Please check and try again.'
            ]);
            exit;
        }

        $otp = (string) mt_rand(100000, 999999);
        $token = bin2hex(random_bytes(24));
        $targetVal = ($type === 'phone') ? $user['phone'] : $user['email'];
        $expiresAt = date('Y-m-d H:i:s', time() + 10 * 60);

        // Delete old unused resets for this user
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = :uid AND is_used = 0')
            ->execute([':uid' => (int) $user['id']]);

        // Insert new reset record
        $ins = $pdo->prepare('
            INSERT INTO password_resets (user_id, target_type, target_value, otp_code, token, expires_at)
            VALUES (:uid, :type, :val, :otp, :tok, :exp)
        ');
        $ins->execute([
            ':uid' => (int) $user['id'],
            ':type' => $type,
            ':val' => $targetVal,
            ':otp' => $otp,
            ':tok' => $token,
            ':exp' => $expiresAt
        ]);

        // Mask target for display
        if ($type === 'phone') {
            $len = strlen($targetVal);
            $masked = ($len > 6) ? substr($targetVal, 0, 4) . '****' . substr($targetVal, -3) : $targetVal;
            // Record SMS in sms_outbox
            $pdo->prepare('
                INSERT INTO sms_outbox (recipient_phone, message_text_so, message_text_en, status, scheduled_for)
                VALUES (:p, :mso, :men, "sent", NOW())
            ')->execute([
                ':p' => $targetVal,
                ':mso' => "Koodhkaaga SIMDA OTP waa: {$otp}. Ha la wadaagin cidna.",
                ':men' => "Your SIMDA verification code is: {$otp}. Never share this code with anyone."
            ]);
            \App\Services\WhatsAppService::send(
                $targetVal,
                "*SIMDA OTP*\n\nKoodhkaaga SIMDA OTP waa: {$otp}. Ha la wadaagin cidna.\nYour SIMDA verification code is: {$otp}. Never share this code with anyone."
            );
        } else {
            $parts = explode('@', $targetVal);
            $namePart = $parts[0] ?? '';
            $domainPart = $parts[1] ?? '';
            $masked = substr($namePart, 0, 2) . '****@' . $domainPart;
            // Send email
            MailService::sendPasswordResetOtpEmail($targetVal, $user['full_name'], $otp, $locale);
        }

        AuditLogger::log('auth.otp_requested', 'users', (int) $user['id'], "Password reset OTP requested via {$type}.");

        echo json_encode([
            'success' => true,
            'token' => $token,
            'masked_target' => $masked,
            'message' => $isSo ? 'Koodhka xaqiijinta si guul leh ayaa laguu soo diray!' : 'Verification code sent successfully!'
        ]);
        exit;
    }

    private static function forgotPasswordVerifyOtp(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        header('Content-Type: application/json; charset=utf-8');

        $token = trim((string) ($_POST['token'] ?? ''));
        $otp = trim((string) ($_POST['otp'] ?? ''));
        $locale = Lang::locale();
        $isSo = $locale === 'so';

        if ($token === '' || $otp === '') {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Fadlan geli koodhka xaqiijinta.' : 'Please enter the verification code.'
            ]);
            exit;
        }

        $st = $pdo->prepare('
            SELECT * FROM password_resets
            WHERE token = :tok
              AND is_used = 0
              AND expires_at > NOW()
            LIMIT 1
        ');
        $st->execute([':tok' => $token]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode([
                'success' => false,
                'message' => $isSo
                    ? 'Koodhkii xaqiijinta wuu dhacay ama lama helin. Fadlan dib u codso.'
                    : 'The verification code has expired or is invalid. Please request a new one.'
            ]);
            exit;
        }

        if (trim((string) $row['otp_code']) !== $otp) {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Koodhka aad gelisay waa qalad. Fadlan hubi.' : 'Incorrect verification code. Please check and try again.'
            ]);
            exit;
        }

        // Mark OTP as used
        $pdo->prepare('UPDATE password_resets SET is_used = 1 WHERE id = :id')->execute([':id' => $row['id']]);

        // Authorize password reset session
        $_SESSION['pw_reset_user_id'] = (int) $row['user_id'];
        $_SESSION['pw_reset_token'] = $token;

        AuditLogger::log('auth.otp_verified', 'users', (int) $row['user_id'], 'Password reset OTP verified successfully.');

        echo json_encode([
            'success' => true,
            'message' => $isSo ? 'Koodhka si guul leh ayaa loo xaqiijiyay! Hadda sameyso fure cusub.' : 'Code verified successfully! You can now set a new password.'
        ]);
        exit;
    }

    private static function forgotPasswordReset(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int) ($_SESSION['pw_reset_user_id'] ?? 0);
        $newPassword = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $locale = Lang::locale();
        $isSo = $locale === 'so';

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Fadhigu wuu dhacay. Fadlan mar kale bilaab nidaamka.' : 'Session expired. Please restart the process.'
            ]);
            exit;
        }

        if (strlen($newPassword) < \App\Services\ClientAccountService::MIN_PASSWORD_LENGTH) {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Furaha cusub waa inuu ka koobnaadaa ugu yaraan 8 xaraf.' : 'Password must be at least 8 characters.'
            ]);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Labada fure isma leha. Fadlan hubi inay isku mid yihiin.' : 'Passwords do not match.'
            ]);
            exit;
        }

        // Update password and reset all lockout counters
        $hash = Security::hashPassword($newPassword);
        $pdo->prepare('
            UPDATE users
            SET password_hash = :h,
                failed_login_attempts = 0,
                lockout_tier = 0,
                locked_until = NULL,
                security_block_reason = NULL,
                is_active = 1
            WHERE id = :id
        ')->execute([
            ':h' => $hash,
            ':id' => $userId
        ]);

        unset($_SESSION['pw_reset_user_id'], $_SESSION['pw_reset_token']);
        $_SESSION['flash_success'] = $isSo
            ? 'Furahaaga sirta ah si guul leh ayaa loo beddelay! Hadda waad geli kartaa nidaamka.'
            : 'Your password has been successfully reset! You can now sign in.';

        AuditLogger::log('auth.password_reset_success', 'users', $userId, 'User completed password reset via OTP.');

        echo json_encode([
            'success' => true,
            'redirect' => '?route=login',
            'message' => $isSo ? 'Furahaaga sirta ah si guul leh ayaa loo beddelay!' : 'Password reset successful!'
        ]);
        exit;
    }

    private static function logout(): void
    {
        if (!empty($_SESSION['user']['id'])) {
            $uid = (int) $_SESSION['user']['id'];
            try {
                \App\Helpers\Database::connection()
                    ->prepare('UPDATE users SET current_session_token = NULL WHERE id = :id')
                    ->execute([':id' => $uid]);
            } catch (\Throwable $e) {}
            AuditLogger::log('auth.logout', 'users', $uid, 'User signed out.');
        }
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: ?route=login');
        exit;
    }

    private static function authSessionCheck(PDO $pdo): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!AuthMiddleware::check()) {
            $locale = Lang::locale();
            $msg = $_SESSION['flash_error'] ?? ($locale === 'so'
                ? 'Akoonkaaga waxaa laga soo galay aalad kale ama browser kale. Amnigaaga dartii, kalfadhigan hore waa la xiray (Logged out).'
                : 'Your account was signed in from another device or browser. Session ended.');
            echo json_encode([
                'valid' => false,
                'message' => $msg,
                'redirect' => '?route=login',
            ]);
            exit;
        }

        echo json_encode(['valid' => true]);
        exit;
    }

    private static function apiSchedulePreview(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $type = (string) ($_GET['product_type'] ?? 'flat');
        $principal = (float) ($_GET['principal'] ?? 1000);
        $rate = (float) ($_GET['rate'] ?? 0);
        $term = max(1, min(ScheduleEngine::MAX_INSTALLMENTS, (int) ($_GET['term'] ?? 5)));
        $freq = (string) ($_GET['frequency'] ?? 'monthly');
        if (!isset(ScheduleEngine::FREQUENCIES[$freq])) {
            $freq = 'monthly';
        }
        $interval = max(1, min(ScheduleEngine::MAX_INTERVAL, (int) ($_GET['interval'] ?? 1)));

        $schedule = ScheduleEngine::generate($type, $principal, $rate, $term, $freq, date('Y-m-d H:i:s'), $interval);
        echo json_encode(['status' => 'ok', 'schedule' => $schedule], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function dashboard(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();

        $filterPeriod = (string) ($_GET['filter_period'] ?? 'all');
        $filterYear = (string) ($_GET['filter_year'] ?? date('Y'));
        $filterMonth = (string) ($_GET['filter_month'] ?? date('Y-m'));
        $filterDate = (string) ($_GET['filter_date'] ?? date('Y-m-d'));

        $dateFilter = [
            'period' => in_array($filterPeriod, ['all', 'year', 'month', 'day'], true) ? $filterPeriod : 'all',
            'year' => $filterYear,
            'month' => $filterMonth,
            'day' => $filterDate,
        ];

        $metrics = DashboardAnalyticsService::getOverviewMetrics($user, $pdo, $dateFilter);

        [$finScope, $finParams] = self::scopeSql($user, 'f');
        [$repScope, $repParams] = self::scopeSql($user, 'r');
        if (($user['role_slug'] ?? '') === 'loan_officer') {
            // Employees see only their own work on the dashboard.
            $finScope = ' AND f.application_id IN (SELECT fa.id FROM financing_applications fa WHERE fa.created_by = :uid) ';
            $finParams = [':uid' => (int) $user['id']];
            $repScope = ' AND r.recorded_by = :uid ';
            $repParams = [':uid' => (int) $user['id']];
        }
        $st = $pdo->prepare("
            SELECT f.*, c.full_name AS client_name
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            WHERE 1=1 {$finScope}
            ORDER BY f.id DESC
            LIMIT 8
        ");
        $st->execute($finParams);
        $recentFinancings = $st->fetchAll();

        $st2 = $pdo->prepare("
            SELECT r.*, c.full_name AS client_name
            FROM repayments r
            INNER JOIN clients c ON c.id = r.client_id
            WHERE 1=1 {$repScope}
            ORDER BY r.id DESC
            LIMIT 8
        ");
        $st2->execute($repParams);
        $recentRepayments = $st2->fetchAll();

        self::renderDashboard('dashboard/overview.php', [
            'pageTitle' => Lang::get('menu_dashboard'),
            'metrics' => $metrics,
            'recentFinancings' => $recentFinancings,
            'recentRepayments' => $recentRepayments,
            'dateFilter' => $dateFilter,
        ]);
    }

    private static function clientsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        // Every staff member sees clients from all branches (the page has its own branch filter).
        $branchSql = '';
        $params = [];

        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q !== '') {
            $branchSql .= ' AND (c.full_name LIKE :q1 OR c.client_code LIKE :q2 OR c.phone LIKE :q3 OR c.email LIKE :q4 OR c.id_number LIKE :q5) ';
            $like = '%' . $q . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
            $params[':q5'] = $like;
        }

        $stmt = $pdo->prepare("
            SELECT c.*,
                   b.name AS branch_name,
                   cs.current_score,
                   cs.score_category,
                   (SELECT COUNT(*) FROM guarantors g WHERE g.client_id = c.id) AS guarantors_count,
                   (SELECT COUNT(*) FROM financings f WHERE f.client_id = c.id AND f.status IN ('active', 'overdue')) AS active_financings
            FROM clients c
            INNER JOIN branches b ON b.id = c.branch_id
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            WHERE c.deleted_at IS NULL {$branchSql}
            ORDER BY c.id DESC
        ");
        $stmt->execute($params);
        $clients = $stmt->fetchAll();
        $branches = $pdo->query('SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC')->fetchAll();

        // Active financings map for the quick repayment modal (clients may pay at any branch)
        $activeFinancings = $pdo->query("
            SELECT f.id, f.financing_number, f.client_id, f.product_type, f.outstanding_balance, f.status,
                   c.full_name AS client_name, c.client_code, c.credit_balance
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            WHERE f.status IN ('active', 'overdue')
            ORDER BY f.id DESC
        ")->fetchAll();

        self::renderDashboard('clients/index.php', [
            'pageTitle' => Lang::get('menu_all_clients'),
            'clients' => $clients,
            'branches' => $branches,
            'activeFinancings' => $activeFinancings,
        ]);
    }

    private static function clientRegisterForm(PDO $pdo): void
    {
        AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        $branches = $pdo->query('SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
        self::renderDashboard('clients/register.php', [
            'pageTitle' => Lang::get('menu_register_client'),
            'branches' => $branches,
        ]);
    }

    private static function clientRegisterSubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();

        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['photo']['tmp_name']);
            $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowedMimes[$mime])) {
                throw new \RuntimeException('Invalid photograph MIME type. Only JPG, PNG, and WebP are permitted.');
            }
            $fileName = 'client_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
            $dest = dirname(__DIR__, 2) . '/storage/uploads/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $dest);
            $photoPath = 'storage/uploads/' . $fileName;
        }

        $branchId = ($user['role_slug'] === 'admin') ? (int) ($_POST['branch_id'] ?? 1) : (int) $user['branch_id'];

        $res = FinancingDomainService::registerClientWithGuarantor([
            'full_name' => $_POST['full_name'] ?? '',
            'id_type' => $_POST['id_type'] ?? 'national_id',
            'id_number' => $_POST['id_number'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'city' => $_POST['city'] ?? '',
            'district' => $_POST['district'] ?? '',
            'address' => $_POST['address'] ?? '',
            'business_type' => $_POST['business_type'] ?? '',
            'monthly_income' => $_POST['monthly_income'] ?? '0.00',
            'branch_id' => $branchId,
            'photo_path' => $photoPath,
        ], [
            'full_name' => $_POST['guarantor_full_name'] ?? '',
            'phone' => $_POST['guarantor_phone'] ?? '',
            'id_number' => $_POST['guarantor_id_number'] ?? '',
            'relationship' => $_POST['guarantor_relationship'] ?? '',
            'occupation' => $_POST['guarantor_occupation'] ?? '',
            'estimated_income' => $_POST['guarantor_income'] ?? '0.00',
        ], (int) $user['id'], $pdo);

        $welcomeNote = self::sendClientWelcome($pdo, (int) $res['client_id'], (int) $user['id']);

        $_SESSION['flash_success'] = (Lang::locale() === 'so'
            ? "Macmiilka si guul leh ayaa loo diiwaangeliyay ({$res['client_code']}). Hadda waad siin kartaa dayn ama Murabaxo."
            : "Client registered successfully ({$res['client_code']}) with verified Guarantor; credit score starts at 600 (300–850).") . $welcomeNote;
        unset($_SESSION['old_input']);
        header('Location: ?route=client_view&new=1&id=' . (int) $res['client_id']);
        exit;
    }

    /**
     * Welcomes a newly registered client by email and WhatsApp with their sign-in details
     * (username + default password). An email is also mirrored to WhatsApp by MailService;
     * a client without a real email address gets the WhatsApp message directly.
     * Never throws: the client is already saved. Returns a short note for the staff flash message.
     */
    private static function sendClientWelcome(PDO $pdo, int $clientId, int $staffId): string
    {
        $isSo = Lang::locale() === 'so';
        try {
            $st = $pdo->prepare('
                SELECT u.id, u.full_name, u.username, u.email, u.phone, b.id AS branch_id, b.code, b.name, b.city
                FROM clients c
                INNER JOIN users u ON u.id = c.user_id
                LEFT JOIN branches b ON b.id = c.branch_id
                WHERE c.id = :cid
                LIMIT 1
            ');
            $st->execute([':cid' => $clientId]);
            $acc = $st->fetch();
            if (!$acc) {
                return '';
            }
            $password = \App\Services\ClientAccountService::DEFAULT_PASSWORD;
            $hasRealEmail = !str_ends_with(strtolower((string) $acc['email']), '@client.simda.so');

            if ($hasRealEmail) {
                $role = $pdo->query("SELECT id, slug, name_en, name_so FROM roles WHERE slug = 'client' LIMIT 1")->fetch()
                    ?: ['slug' => 'client', 'name_so' => 'Macmiil', 'name_en' => 'Client'];
                $branch = $acc['branch_id'] ? ['id' => $acc['branch_id'], 'code' => $acc['code'], 'name' => $acc['name'], 'city' => $acc['city']] : null;
                $mail = MailService::sendWelcomeUserEmail($acc, $password, $branch, $role, 'so');
                $waOk = !empty($mail['whatsapp']['success']);
                AuditLogger::log(
                    !empty($mail['success']) ? 'mail.welcome_sent' : 'mail.welcome_failed',
                    'users',
                    (int) $acc['id'],
                    'Client welcome email to ' . $acc['email'] . (!empty($mail['success']) ? '' : ': ' . ($mail['error'] ?? 'unknown')) . ($waOk ? ' (+ WhatsApp)' : ''),
                    [],
                    $staffId
                );
                $mailOk = !empty($mail['success']);
            } else {
                $mailOk = false;
                $text = "*SIMDA: Ku soo dhawoow, {$acc['full_name']}!*\n\n"
                    . "Waxaa laguu diiwaangeliyay macmiil SIMDA ah. Akoonkaaga waad ku geli kartaa bogga SIMDA (guji Login):\n\n"
                    . "- Username-ka: {$acc['username']}\n"
                    . "- Ama lambarkaaga telefoonka: {$acc['phone']}\n"
                    . "- Furaha Sirta ah (Password): {$password}\n\n"
                    . "Markaad marka ugu horraysa gasho, fadlan beddel password-ka.\n\nMahadsanid, SIMDA.";
                $wa = WhatsAppService::send((string) $acc['phone'], $text);
                $waOk = !empty($wa['success']);
                AuditLogger::log(
                    $waOk ? 'whatsapp.welcome_sent' : 'whatsapp.welcome_failed',
                    'users',
                    (int) $acc['id'],
                    'Client welcome WhatsApp to ' . $acc['phone'] . ($waOk ? '' : ': ' . ($wa['error'] ?? 'unknown')),
                    [],
                    $staffId
                );
            }

            $sent = array_filter([$mailOk ? 'email' : null, $waOk ? 'WhatsApp' : null]);
            if ($sent) {
                return $isSo
                    ? ' Fariinta soo dhawaynta iyo xogta gelitaanka waxaa loo diray ' . implode(' iyo ', $sent) . '.'
                    : ' Welcome message with sign-in details sent by ' . implode(' and ', $sent) . '.';
            }
            return $isSo
                ? " Fariinta soo dhawaynta lama diri karin. Sii macmiilka: username {$acc['username']}, password {$password}."
                : " The welcome message could not be sent. Give the client: username {$acc['username']}, password {$password}.";
        } catch (\Throwable $e) {
            error_log('Client welcome failed: ' . $e->getMessage());
            return '';
        }
    }

    private static function clientCheckDuplicate(PDO $pdo): void
    {
        AuthMiddleware::requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        $field = trim((string) ($_GET['field'] ?? ''));
        $value = trim((string) ($_GET['value'] ?? ''));
        $excludeId = (int) ($_GET['exclude_id'] ?? 0);

        if ($field === '' || $value === '') {
            echo json_encode(['exists' => false]);
            exit;
        }

        $col = match ($field) {
            'id_number' => 'id_number',
            'phone' => 'phone',
            'email' => 'email',
            default => null,
        };

        if (!$col) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $sql = "SELECT id, full_name, client_code FROM clients WHERE {$col} = :val";
        if ($excludeId > 0) {
            $sql .= " AND id != " . $excludeId;
        }
        $sql .= " LIMIT 1";

        $st = $pdo->prepare($sql);
        $st->execute([':val' => $value]);
        $row = $st->fetch();

        echo json_encode([
            'exists' => (bool) $row,
            'field' => $col,
            'value' => $value,
            'client_name' => $row['full_name'] ?? null,
            'client_code' => $row['client_code'] ?? null,
            'message' => $row ? match ($col) {
                'id_number' => "Lambarka Aqoonsiga '{$value}' horey ayaa loogu diiwaangeliyay macmiilka: " . ($row['full_name'] ?? '') . " (" . ($row['client_code'] ?? '') . ")",
                'phone' => "Lambarka Telefoonka '{$value}' horey ayaa loogu diiwaangeliyay macmiilka: " . ($row['full_name'] ?? '') . " (" . ($row['client_code'] ?? '') . ")",
                'email' => "Email-ka '{$value}' horey ayaa loogu diiwaangeliyay macmiilka: " . ($row['full_name'] ?? '') . " (" . ($row['client_code'] ?? '') . ")",
                default => 'Xogtani horey ayaa nidaamka ugu jirtay.'
            } : ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function clientTransfer(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $newBranchId = (int) ($_POST['new_branch_id'] ?? 0);
        FinancingDomainService::transferClientBranch($clientId, $newBranchId, $user, $pdo);
        $_SESSION['flash_success'] = 'Client successfully transferred to the selected branch.';
        header('Location: ?route=clients');
        exit;
    }

    private static function guarantorsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        $branchSql = ($user['role_slug'] === 'admin') ? '' : ' WHERE c.branch_id = :bid ';
        $params = ($user['role_slug'] === 'admin') ? [] : [':bid' => (int) $user['branch_id']];

        $stmt = $pdo->prepare("
            SELECT g.*, c.full_name AS client_name, c.client_code, b.name AS branch_name
            FROM guarantors g
            INNER JOIN clients c ON c.id = g.client_id
            INNER JOIN branches b ON b.id = c.branch_id
            {$branchSql}
            ORDER BY g.id DESC
        ");
        $stmt->execute($params);
        $guarantors = $stmt->fetchAll();

        $cStmt = $pdo->prepare("SELECT id, full_name, client_code FROM clients c {$branchSql} ORDER BY full_name ASC");
        $cStmt->execute($params);
        $clients = $cStmt->fetchAll();

        self::renderDashboard('clients/guarantors.php', [
            'pageTitle' => Lang::get('menu_guarantors'),
            'guarantors' => $guarantors,
            'clients' => $clients,
        ]);
    }

    private static function guarantorAdd(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $stmt = $pdo->prepare('
            INSERT INTO guarantors (client_id, full_name, phone, id_number, relationship)
            VALUES (:cid, :name, :phone, :id_num, :rel)
        ');
        $stmt->execute([
            ':cid' => (int) ($_POST['client_id'] ?? 0),
            ':name' => trim((string) ($_POST['full_name'] ?? '')),
            ':phone' => trim((string) ($_POST['phone'] ?? '')),
            ':id_num' => trim((string) ($_POST['id_number'] ?? '')),
            ':rel' => trim((string) ($_POST['relationship'] ?? '')),
        ]);
        AuditLogger::log('guarantor.added', 'guarantors', (int) $pdo->lastInsertId(), 'Added guarantor for client #' . (int) $_POST['client_id'], [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Additional guarantor saved successfully.';
        header('Location: ?route=guarantors');
        exit;
    }

    private static function groupsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        $branchSql = ($user['role_slug'] === 'admin') ? '' : ' WHERE g.branch_id = :bid ';
        $params = ($user['role_slug'] === 'admin') ? [] : [':bid' => (int) $user['branch_id']];

        $stmt = $pdo->prepare("
            SELECT g.*,
                   b.name AS branch_name,
                   c.full_name AS leader_name,
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id AND gm.is_active = 1) AS members_count,
                   (
                       SELECT COUNT(*) FROM group_members gm2
                       INNER JOIN financings f ON f.client_id = gm2.client_id
                       INNER JOIN installments i ON i.financing_id = f.id
                       WHERE gm2.group_id = g.id AND gm2.is_active = 1
                         AND f.status IN ('active', 'overdue')
                         AND i.remaining_balance > 0 AND i.status != 'paid'
                         AND DATEDIFF(CURDATE(), i.due_date) > 30
                   ) AS overdue_30_count
            FROM groups g
            INNER JOIN branches b ON b.id = g.branch_id
            INNER JOIN clients c ON c.id = g.leader_client_id
            {$branchSql}
            ORDER BY g.id DESC
        ");
        $stmt->execute($params);
        $groups = $stmt->fetchAll();

        $cWhere = ($user['role_slug'] === 'admin') ? '' : ' WHERE branch_id = :bid ';
        $cStmt = $pdo->prepare("SELECT id, full_name, client_code FROM clients {$cWhere} ORDER BY id ASC");
        $cStmt->execute($params);
        $clients = $cStmt->fetchAll();

        $branches = $pdo->query('SELECT * FROM branches WHERE is_active = 1')->fetchAll();

        self::renderDashboard('groups/index.php', [
            'pageTitle' => Lang::get('menu_client_groups'),
            'groups' => $groups,
            'clients' => $clients,
            'branches' => $branches,
        ]);
    }

    private static function groupCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $branchId = ($user['role_slug'] === 'admin') ? (int) ($_POST['branch_id'] ?? 1) : (int) $user['branch_id'];
        $res = FinancingDomainService::createGroup(
            (string) ($_POST['group_name'] ?? ''),
            $branchId,
            (int) ($_POST['leader_client_id'] ?? 0),
            (array) ($_POST['member_ids'] ?? []),
            (int) $user['id'],
            '',
            $pdo
        );
        $_SESSION['flash_success'] = "Created group {$res['group_code']} ({$res['members_count']} members). Pending Branch Manager approval.";
        header('Location: ?route=groups');
        exit;
    }

    private static function groupApprove(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        FinancingDomainService::approveGroup((int) ($_POST['group_id'] ?? 0), $user, $pdo);
        $_SESSION['flash_success'] = 'Client group approved and activated for group financing.';
        header('Location: ?route=groups');
        exit;
    }

    private static function productsList(PDO $pdo): void
    {
        AuthMiddleware::requirePermission('products.manage');
        $products = $pdo->query('
            SELECT p.*,
                   (SELECT COUNT(*) FROM financing_applications fa WHERE fa.product_id = p.id) AS usage_count
            FROM products p
            ORDER BY p.id ASC
        ')->fetchAll();

        self::renderDashboard('products/index.php', [
            'pageTitle' => Lang::get('menu_products'),
            'products' => $products,
        ]);
    }

    private static function productSave(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $type = (string) ($_POST['product_type'] ?? 'flat');
        $rate = number_format((float) ($_POST['rate'] ?? 0), 4, '.', '');
        $apr = ($type === 'murabaha') ? '0.0000' : $rate;
        $mrb = ($type === 'murabaha') ? $rate : '0.0000';

        $stmt = $pdo->prepare('
            INSERT INTO products (
                product_code, name_so, name_en, product_type, annual_profit_rate, murabaha_markup_pct,
                min_amount, max_amount, min_term, max_term, allowed_frequency, admin_fee, is_active
            ) VALUES (
                :code, :n_so, :n_en, :type, :apr, :mrb,
                :min_a, :max_a, :min_t, :max_t, :freq, :fee, 1
            )
        ');
        $stmt->execute([
            ':code' => trim((string) ($_POST['product_code'] ?? '')),
            ':n_so' => trim((string) ($_POST['name_so'] ?? '')),
            ':n_en' => trim((string) ($_POST['name_en'] ?? '')),
            ':type' => $type,
            ':apr' => $apr,
            ':mrb' => $mrb,
            ':min_a' => number_format((float) ($_POST['min_amount'] ?? 100), 2, '.', ''),
            ':max_a' => number_format((float) ($_POST['max_amount'] ?? 50000), 2, '.', ''),
            ':min_t' => (int) ($_POST['min_term'] ?? 1),
            ':max_t' => (int) ($_POST['max_term'] ?? 36),
            ':freq' => (string) ($_POST['allowed_frequency'] ?? 'both'),
            ':fee' => number_format((float) ($_POST['admin_fee'] ?? 0), 2, '.', ''),
        ]);

        AuditLogger::log('product.created', 'products', (int) $pdo->lastInsertId(), 'Created product ' . $_POST['product_code'], [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Financing product created successfully.';
        header('Location: ?route=products');
        exit;
    }

    private static function productToggle(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $pid = (int) ($_POST['product_id'] ?? 0);
        $pdo->prepare('UPDATE products SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id')->execute([':id' => $pid]);
        AuditLogger::log('product.toggled', 'products', $pid, "Toggled active status for product #{$pid}.", [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Product active status updated.';
        header('Location: ?route=products');
        exit;
    }

    private static function productDelete(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        FinancingDomainService::deleteProduct((int) ($_POST['product_id'] ?? 0), (int) $user['id'], $pdo);
        $_SESSION['flash_success'] = 'Unused financing product deleted.';
        header('Location: ?route=products');
        exit;
    }

    private static function applicationsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        [$scopeSql, $params] = self::scopeSql($user, 'fa');

        $stmt = $pdo->prepare("
            SELECT fa.*,
                   c.full_name AS client_name,
                   p.name_so AS product_name_so,
                   p.name_en AS product_name_en
            FROM financing_applications fa
            INNER JOIN clients c ON c.id = fa.client_id
            INNER JOIN products p ON p.id = fa.product_id
            WHERE 1=1 {$scopeSql}
            ORDER BY fa.id DESC
        ");
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        // Clients are shared across branches, so any staff member can apply for any client.
        $cWhere = '';
        $cParams = [];
        $cStmt = $pdo->prepare("
            SELECT c.id, c.full_name, c.client_code, cs.current_score
            FROM clients c
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            {$cWhere}
            ORDER BY c.full_name ASC
        ");
        $cStmt->execute($cParams);
        $clients = $cStmt->fetchAll();

        $products = $pdo->query('SELECT * FROM products WHERE is_active = 1')->fetchAll();
        $groups = $pdo->query("SELECT * FROM groups WHERE status = 'active'")->fetchAll();

        self::renderDashboard('applications/index.php', [
            'pageTitle' => Lang::get('menu_applications'),
            'applications' => $applications,
            'clients' => $clients,
            'products' => $products,
            'groups' => $groups,
        ]);
    }

    private static function applicationCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $submitImmediately = (($_POST['submit_mode'] ?? 'submitted') === 'submitted');

        $res = FinancingDomainService::createApplication([
            'client_id' => $_POST['client_id'] ?? 0,
            'product_id' => $_POST['product_id'] ?? 0,
            'group_id' => $_POST['group_id'] ?? null,
            'requested_amount' => $_POST['requested_amount'] ?? 0,
            'term_installments' => $_POST['term_installments'] ?? 6,
            'repayment_frequency' => $_POST['repayment_frequency'] ?? 'monthly',
            'repayment_interval' => $_POST['repayment_interval'] ?? 1,
            'purpose' => $_POST['purpose'] ?? '',
            'murabaha_goods_description' => $_POST['murabaha_goods_description'] ?? '',
        ], (int) $user['id'], $submitImmediately, date('Y-m-d'), $pdo);

        $_SESSION['flash_success'] = "Application {$res['application_number']} saved as '{$res['status']}' (Approval level: " . FinancingDomainService::approverLabel($res['required_approver_role']) . ').';
        if ($res['status'] === 'submitted') {
            self::notifyApprovers($pdo, (int) $res['application_id']);
        }
        header('Location: ?route=applications');
        exit;
    }

    private static function applicationSubmitDraft(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        $draft = $pdo->prepare("UPDATE financing_applications SET status = 'submitted', submitted_at = NOW() WHERE id = :id AND status = 'draft'");
        $draft->execute([':id' => $appId]);
        AuditLogger::log('application.submitted', 'financing_applications', $appId, "Submitted draft application #{$appId}.", [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Draft application submitted to the approval queue.';
        if ($draft->rowCount() > 0) {
            self::notifyApprovers($pdo, $appId);
        }
        header('Location: ?route=applications');
        exit;
    }

    private static function applicationResubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        FinancingDomainService::resubmitRejectedApplication($appId, $_POST, (int) $user['id'], $pdo);
        $_SESSION['flash_success'] = 'Rejected application edited and resubmitted to the approval queue.';
        self::notifyApprovers($pdo, $appId);
        header('Location: ?route=applications');
        exit;
    }

    /** Emails the approvers of a forwarded application and warns the submitter if nobody could be told. */
    private static function notifyApprovers(PDO $pdo, int $applicationId): void
    {
        try {
            $result = FinancingDomainService::notifyForwardedApplication($applicationId, Lang::locale(), $pdo);
        } catch (\Throwable $e) {
            error_log('Approval notification failed: ' . $e->getMessage());
            $result = ['required_role' => 'manager', 'recipients' => 1, 'failed' => 1];
        }
        if ($result['required_role'] === 'loan_officer') {
            return;
        }

        $label = FinancingDomainService::approverLabel($result['required_role']);
        if ($result['recipients'] === 0) {
            $_SESSION['flash_warning'] = self::tr(
                "Codsiga waxaa loo gudbiyay {$label}, laakiin ma jiro {$label} firfircoon oo email loo diro. Fadlan u sheeg maamulka.",
                "The application was forwarded to {$label}, but there is no active {$label} to email. Please tell the administrators.",
                "تم تحويل الطلب إلى {$label}، لكن لا يوجد {$label} نشط لإرسال البريد إليه. يرجى إبلاغ الإدارة."
            );
        } elseif ($result['failed'] > 0) {
            $_SESSION['flash_warning'] = self::tr(
                "Codsiga waxaa loo gudbiyay {$label}, laakiin email-ka qaar lama diri karin. Fadlan si kale ugu sheeg.",
                "The application was forwarded to {$label}, but some emails could not be sent. Please tell them another way.",
                "تم تحويل الطلب إلى {$label}، لكن تعذر إرسال بعض الرسائل. يرجى إبلاغهم بطريقة أخرى."
            );
        }
    }

    private static function approvalQueue(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        $queue = FinancingDomainService::pendingRequestsFor($user, $pdo);
        $history = self::clientFinancingHistory($pdo, $user);
        $goodsImages = [];
        foreach (MurabahaGoodsService::all($pdo) as $g) {
            if (!empty($g['image_path'])) {
                $goodsImages[mb_strtolower(trim((string) $g['name']))] = (string) $g['image_path'];
            }
        }
        foreach ($queue as &$q) {
            $meta = json_decode((string) ($q['notes'] ?? ''), true);
            $q['payout_provider'] = is_array($meta) ? (string) ($meta['provider'] ?? '') : '';
            $q['payout_phone'] = is_array($meta) ? (string) ($meta['phone'] ?? '') : '';
            $q['from_form'] = is_array($meta) && in_array($meta['source'] ?? '', ['cash_loan_form', 'murabaha_form'], true);
            $q['history'] = $history[(int) $q['client_row_id']] ?? [];
            // Murabaha goods are saved as "Item name — description"; show the catalog picture when the name matches.
            $itemName = trim(explode(' — ', (string) ($q['murabaha_goods_description'] ?? ''), 2)[0]);
            $q['item_image'] = $goodsImages[mb_strtolower($itemName)] ?? '';
        }
        unset($q);

        self::renderDashboard('applications/approval_queue.php', [
            'pageTitle' => self::tr('Codsiyada', 'Requests', 'الطلبات'),
            'queue' => $queue,
        ]);
    }

    private static function applicationReview(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        $decision = (string) ($_POST['decision'] ?? '');
        $comments = trim((string) ($_POST['comments'] ?? ''));
        if ($decision === 'rejected' && $comments === '') {
            throw new \RuntimeException(Lang::locale() === 'so'
                ? 'Fadlan qor sababta diidmada; waxaa loo dirayaa macmiilka.'
                : 'Please write the reason for rejecting; it is emailed to the client.');
        }

        $res = FinancingDomainService::reviewApplication($appId, $user, $decision, $comments, $pdo);
        $locale = Lang::locale();
        $isSo = $locale === 'so';

        $st = $pdo->prepare('
            SELECT fa.*, c.full_name AS client_name, c.client_code, c.email AS client_email, c.phone AS client_phone,
                   c.id AS client_row_id, p.product_type, u.full_name AS creator_name
            FROM financing_applications fa
            INNER JOIN clients c ON c.id = fa.client_id
            INNER JOIN products p ON p.id = fa.product_id
            LEFT JOIN users u ON u.id = fa.created_by
            WHERE fa.id = :id
        ');
        $st->execute([':id' => $appId]);
        $app = $st->fetch() ?: [];
        $meta = json_decode((string) ($app['notes'] ?? ''), true);
        $fromForm = is_array($meta) && in_array($meta['source'] ?? '', ['cash_loan_form', 'murabaha_form'], true);
        $client = ['id' => (int) ($app['client_row_id'] ?? 0), 'full_name' => (string) ($app['client_name'] ?? ''), 'client_code' => (string) ($app['client_code'] ?? ''), 'email' => (string) ($app['client_email'] ?? '')];

        if ($res['status'] === 'approved' && $fromForm) {
            // A request from the New Cash Loan / New Murabaha form: pay it out now and send the client the receipt.
            $phone = trim((string) ($meta['phone'] ?? '')) ?: (string) ($app['client_phone'] ?? '');
            $disb = DisbursementService::disburse($appId, (int) $user['id'], (string) ($meta['provider'] ?? 'EVC Plus'), $phone, date('Y-m-d'), $pdo);
            $mailNote = self::emailFinancingIssued($pdo, (int) $disb['financing_id'], $client, $disb, (string) $app['purpose'], (string) ($app['murabaha_goods_description'] ?? ''), $user, $locale);
            $_SESSION['flash_success'] = ($isSo
                ? "Codsiga {$app['application_number']} waa la ansixiyay oo lacagta waa la bixiyay ({$disb['financing_number']}). Waxaa codsaday: {$app['creator_name']}."
                : "Request {$app['application_number']} approved and paid out ({$disb['financing_number']}). Requested by {$app['creator_name']}.") . $mailNote;
        } else {
            $mail = ['success' => false];
            if ($client['email'] !== '' && filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
                try {
                    $itemImage = ($app['product_type'] ?? '') === 'murabaha'
                        ? MurabahaGoodsService::emailImageForGoods($pdo, (string) ($app['murabaha_goods_description'] ?? ''))
                        : null;
                    $mail = MailService::sendApplicationDecisionEmail($client['email'], $client['full_name'], $app, $res['status'], $comments, $locale, $itemImage);
                } catch (\Throwable $e) {
                    $mail = ['success' => false];
                }
            }
            $what = $res['status'] === 'approved' ? ($isSo ? 'waa la ansixiyay' : 'approved') : ($isSo ? 'waa la diiday' : 'rejected');
            $_SESSION['flash_success'] = ($isSo ? "Codsiga {$app['application_number']} {$what}." : "Application {$app['application_number']} {$what}.")
                . (!empty($mail['success'])
                    ? ($isSo ? " Macmiilka waxaa loo diray email ({$client['email']})." : " The client was emailed ({$client['email']}).")
                    : ($isSo ? ' Macmiilka email looma dirin (email ma leh ama dirista way fashilantay).' : ' The client was not emailed (no email address or sending failed).'));
        }
        header('Location: ?route=approval_queue');
        exit;
    }

    private static function disbursementsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        DisbursementService::expireStaleApprovals(null, $pdo);

        [$appScope, $appParams] = self::scopeSql($user, 'fa');
        $stmt = $pdo->prepare("
            SELECT fa.*, c.full_name AS client_name, c.phone AS client_phone
            FROM financing_applications fa
            INNER JOIN clients c ON c.id = fa.client_id
            WHERE fa.status = 'approved' {$appScope}
            ORDER BY fa.id DESC
        ");
        $stmt->execute($appParams);
        $approvedApps = $stmt->fetchAll();

        [$finScope, $finParams] = self::scopeSql($user, 'f');
        $stmt2 = $pdo->prepare("
            SELECT f.*, c.full_name AS client_name
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            WHERE 1=1 {$finScope}
            ORDER BY f.id DESC
        ");
        $stmt2->execute($finParams);
        $disbursedList = $stmt2->fetchAll();

        self::renderDashboard('financings/disbursements.php', [
            'pageTitle' => Lang::get('menu_disbursements'),
            'approvedApps' => $approvedApps,
            'disbursedList' => $disbursedList,
        ]);
    }

    private static function disburseSubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        $provider = (string) ($_POST['provider'] ?? 'EVC Plus');
        $phone = (string) ($_POST['phone'] ?? '');

        $res = DisbursementService::disburse($appId, (int) $user['id'], $provider, $phone, null, $pdo);
        $_SESSION['flash_success'] = "Disbursed {$res['financing_number']} via {$res['provider']} (Ref: {$res['disbursement_reference']}). Net Disbursed: \${$res['net_disbursed_amount']}.";
        header('Location: ?route=financing_view&id=' . $res['financing_id']);
        exit;
    }

    private static function loansView(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        [$scopeSql, $params] = self::scopeSql($user, 'f');
        $filter = (string) ($_GET['filter'] ?? 'active');

        // Total Counts & financial aggregates for badge indicators
        $countsStmt = $pdo->prepare("
            SELECT
                COUNT(*) AS count_all,
                COUNT(CASE WHEN f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 THEN 1 END) AS count_active,
                COUNT(CASE WHEN f.status = 'completed' AND (f.closed_at <= f.maturity_date OR f.closed_at IS NULL) AND NOT EXISTS (SELECT 1 FROM installments i_l WHERE i_l.financing_id = f.id AND i_l.paid_at > i_l.due_date) THEN 1 END) AS count_settled_ontime,
                COUNT(CASE WHEN f.status = 'completed' AND ((f.closed_at > f.maturity_date) OR EXISTS (SELECT 1 FROM installments i_l WHERE i_l.financing_id = f.id AND i_l.paid_at > i_l.due_date) OR EXISTS (SELECT 1 FROM charity_charges cc WHERE cc.financing_id = f.id)) THEN 1 END) AS count_settled_late,
                COALESCE(SUM(f.approved_amount), 0.00) AS sum_principal,
                COALESCE(SUM(f.total_paid), 0.00) AS sum_paid,
                COALESCE(SUM(CASE WHEN f.status IN ('active', 'overdue') THEN f.outstanding_balance ELSE 0 END), 0.00) AS sum_outstanding
            FROM financings f
            WHERE f.product_type IN ('flat', 'declining_balance') {$scopeSql}
        ");
        $countsStmt->execute($params);
        $summary = $countsStmt->fetch() ?: [];

        $whereFilter = match ($filter) {
            'settled_ontime' => " AND f.status = 'completed' AND (f.closed_at <= f.maturity_date OR f.closed_at IS NULL) AND NOT EXISTS (SELECT 1 FROM installments i_l WHERE i_l.financing_id = f.id AND i_l.paid_at > i_l.due_date) ",
            'settled_late' => " AND f.status = 'completed' AND ((f.closed_at > f.maturity_date) OR EXISTS (SELECT 1 FROM installments i_l WHERE i_l.financing_id = f.id AND i_l.paid_at > i_l.due_date) OR EXISTS (SELECT 1 FROM charity_charges cc WHERE cc.financing_id = f.id)) ",
            'all' => "",
            default => " AND f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 ",
        };

        $stmt = $pdo->prepare("
            SELECT f.*, c.full_name AS client_name, c.phone AS client_phone, b.name AS branch_name,
                   (SELECT COUNT(*) FROM charity_charges cc WHERE cc.financing_id = f.id) AS charity_charges_count,
                   (SELECT COUNT(*) FROM installments i_l WHERE i_l.financing_id = f.id AND i_l.paid_at > i_l.due_date) AS late_installments_count
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            INNER JOIN branches b ON b.id = f.branch_id
            WHERE f.product_type IN ('flat', 'declining_balance') {$scopeSql} {$whereFilter}
            ORDER BY f.id DESC
        ");
        $stmt->execute($params);
        $loans = $stmt->fetchAll();

        self::renderDashboard('financings/loans.php', [
            'pageTitle' => self::tr('Dayn Cadaan', 'Cash Loans', 'القروض النقدية'),
            'filter' => $filter,
            'summary' => $summary,
            'loans' => $loans,
        ]);
    }

    private static function murabahaView(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        [$scopeSql, $params] = self::scopeSql($user, 'f');
        $filter = (string) ($_GET['filter'] ?? 'all');

        // Summary counts for badges & KPIs
        $countsStmt = $pdo->prepare("
            SELECT
                COUNT(*) AS count_all,
                COUNT(CASE WHEN f.status = 'completed' THEN 1 END) AS count_settled,
                COUNT(CASE WHEN f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 THEN 1 END) AS count_unpaid,
                COALESCE(SUM(f.approved_amount), 0.00) AS sum_cost,
                COALESCE(SUM(f.total_profit), 0.00) AS sum_markup,
                COALESCE(SUM(f.total_paid), 0.00) AS sum_paid,
                COALESCE(SUM(CASE WHEN f.status IN ('active', 'overdue') THEN f.outstanding_balance ELSE 0 END), 0.00) AS sum_outstanding
            FROM financings f
            WHERE f.product_type = 'murabaha' {$scopeSql}
        ");
        $countsStmt->execute($params);
        $summary = $countsStmt->fetch() ?: [];

        $whereFilter = match ($filter) {
            'settled' => " AND f.status = 'completed' ",
            'unpaid' => " AND f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 ",
            default => "",
        };

        $stmt = $pdo->prepare("
            SELECT f.*, c.full_name AS client_name, c.phone AS client_phone, b.name AS branch_name
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            INNER JOIN branches b ON b.id = f.branch_id
            WHERE f.product_type = 'murabaha' {$scopeSql} {$whereFilter}
            ORDER BY f.id DESC
        ");
        $stmt->execute($params);
        $contracts = $stmt->fetchAll();

        self::renderDashboard('financings/murabaha.php', [
            'pageTitle' => self::tr('Murabaxo', 'Murabaha', 'المرابحة'),
            'filter' => $filter,
            'summary' => $summary,
            'contracts' => $contracts,
        ]);
    }

    /**
     * Past cash loans and Murabaha per client, for the history warning on the New Cash Loan / New Murabaha forms.
     * state: processing (still being repaid) or complete. late: an installment is past due and unpaid,
     * was paid after its due date, the contract closed after maturity, or charity late charges were applied.
     *
     * @return array<int, list<array<string, mixed>>> client_id => financings, newest first
     */
    private static function clientFinancingHistory(PDO $pdo, array $user): array
    {
        [$scope, $params] = self::clientScopeSql($user, 'f', 'client_id'); // full history, every branch
        $stmt = $pdo->prepare("
            SELECT f.id, f.client_id, f.financing_number, f.product_type, f.status, f.approved_amount, f.total_due,
                   f.outstanding_balance, f.disbursed_at,
                   (
                       f.status = 'overdue'
                       OR (f.status = 'completed' AND f.closed_at IS NOT NULL AND DATE(f.closed_at) > f.maturity_date)
                       OR EXISTS (SELECT 1 FROM installments i WHERE i.financing_id = f.id AND i.paid_at IS NOT NULL AND DATE(i.paid_at) > i.due_date)
                       OR EXISTS (SELECT 1 FROM installments i WHERE i.financing_id = f.id AND i.status IN ('pending', 'partial', 'overdue') AND i.due_date < CURDATE())
                       OR EXISTS (SELECT 1 FROM charity_charges cc WHERE cc.financing_id = f.id)
                   ) AS is_late
            FROM financings f
            WHERE f.status <> 'written_off' {$scope}
            ORDER BY f.id DESC
        ");
        $stmt->execute($params);
        $history = [];
        foreach ($stmt->fetchAll() as $r) {
            $history[(int) $r['client_id']][] = [
                'number' => (string) $r['financing_number'],
                'type' => $r['product_type'] === 'murabaha' ? 'murabaha' : 'loan',
                'state' => $r['status'] === 'completed' ? 'complete' : 'processing',
                'late' => (bool) $r['is_late'],
                'amount' => (float) $r['approved_amount'],
                'balance' => (float) $r['outstanding_balance'],
                'date' => $r['disbursed_at'] ? date('d/m/Y', strtotime((string) $r['disbursed_at'])) : '',
            ];
        }
        return $history;
    }

    /** Employees (loan officers) cannot issue financing themselves: their loan / Murabaha becomes a request. */
    /**
     * Every new financing goes to the Requests queue, whoever creates it: the brief does not let the
     * creator approve their own application, so someone else at the right level must decide it.
     */
    private static function submitsAsRequest(array $user): bool
    {
        return true;
    }

    /** A request belongs to the employee's own branch, so their Branch Manager decides it. */
    private static function requestBranchId(array $user, array $client): int
    {
        $bid = (int) ($user['branch_id'] ?? 0);
        return $bid > 0 ? $bid : (int) ($client['branch_id'] ?? 1);
    }

    /** After an employee's request is saved: tell the approvers, log it, and confirm to the employee. */
    private static function finishEmployeeRequest(PDO $pdo, int $appId, string $appNo, array $client, float $amount, array $user, bool $isSo): void
    {
        AuditLogger::log(
            'application.requested',
            'financing_applications',
            $appId,
            "{$user['full_name']} requested {$appNo} (\$" . number_format($amount, 2) . ") for {$client['full_name']}; waiting for Branch Manager approval.",
            ['client_id' => (int) $client['id'], 'amount' => $amount],
            (int) $user['id'],
            self::requestBranchId($user, $client)
        );
        self::notifyApprovers($pdo, $appId);
        $_SESSION['flash_success'] = ($isSo
            ? "Codsiga {$appNo} (\$" . number_format($amount, 2) . ") ee macmiilka {$client['full_name']} waxaa loo diray Maamulaha Laanta si uu u ansixiyo. Macmiilka waxaa email loogu diri doonaa marka go'aan la gaaro."
            : "Request {$appNo} (\$" . number_format($amount, 2) . ") for {$client['full_name']} was sent to the Branch Manager for approval. The client is emailed once it is decided.");
        header('Location: ?route=applications');
        exit;
    }

    private static function loanNew(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer', 'manager']);
        [$scopeSql, $params] = self::clientScopeSql($user, 'c');

        $clients = $pdo->prepare("
            SELECT c.id, c.full_name, c.client_code, c.phone, c.email, c.id_number, c.branch_id,
                   b.name AS branch_name, cs.current_score,
                   (SELECT g.full_name FROM guarantors g WHERE g.client_id = c.id LIMIT 1) AS guarantor_name
            FROM clients c
            LEFT JOIN branches b ON b.id = c.branch_id
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            WHERE 1=1 {$scopeSql}
            ORDER BY c.full_name ASC
        ");
        $clients->execute($params);
        $clientsList = $clients->fetchAll();

        $selectedClientId = (int) ($_GET['client_id'] ?? 0);

        self::renderDashboard('financings/loan_new.php', [
            'pageTitle' => self::tr('Dayn Oo Lacag Ah', 'New Cash Loan', 'قرض نقدي جديد'),
            'clients' => $clientsList,
            'selectedClientId' => $selectedClientId,
            'history' => self::clientFinancingHistory($pdo, $user),
            'isRequest' => self::submitsAsRequest($user),
        ]);
    }

    /**
     * Repayment plan from the cash-loan / Murabaha forms:
     *   payment_mode = installments | single, repayment_frequency = minutely|hourly|daily|weekly|monthly,
     *   repayment_interval = units between payments (single: units until the one payment),
     *   term_installments = number of payments (1 for single).
     * @return array{0:int,1:string,2:int} [installments, unit, interval]
     */
    private static function readRepaymentPlan(bool $isSo): array
    {
        $unit = (string) ($_POST['repayment_frequency'] ?? 'monthly');
        if (!isset(ScheduleEngine::FREQUENCIES[$unit])) {
            throw new \RuntimeException($isSo ? 'Dooro muddo sax ah: daqiiqo, saac, maalin, toddobaad ama bil.' : 'Choose a valid period: minutes, hours, days, weeks or months.');
        }
        $interval = (int) ($_POST['repayment_interval'] ?? 1);
        if ($interval < 1 || $interval > ScheduleEngine::MAX_INTERVAL) {
            throw new \RuntimeException($isSo ? 'Waqtiga u dhexeeya lacag-bixinta waa 1 ilaa ' . ScheduleEngine::MAX_INTERVAL . '.' : 'Time between payments must be 1 to ' . ScheduleEngine::MAX_INTERVAL . '.');
        }
        $single = ((string) ($_POST['payment_mode'] ?? 'installments')) === 'single';
        $term = $single ? 1 : (int) ($_POST['term_installments'] ?? 6);
        if ($term < 1 || $term > ScheduleEngine::MAX_INSTALLMENTS) {
            throw new \RuntimeException($isSo ? 'Tirada qaybaha waa 1 ilaa ' . ScheduleEngine::MAX_INSTALLMENTS . '.' : 'Number of installments must be 1 to ' . ScheduleEngine::MAX_INSTALLMENTS . '.');
        }
        return [$term, $unit, $interval];
    }

    private static function loanSubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer', 'manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();
        $isSo = ($locale === 'so');

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $amount = (float) ($_POST['requested_amount'] ?? 0);
        [$term, $frequency, $interval] = self::readRepaymentPlan($isSo);
        $purpose = trim((string) ($_POST['purpose'] ?? ''));
        $provider = trim((string) ($_POST['provider'] ?? 'EVC Plus'));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($clientId <= 0) {
            throw new \RuntimeException($isSo ? 'Fadlan dooro macmiilka daynta qaadanaya.' : 'Please select a client.');
        }
        if ($amount < 100) {
            throw new \RuntimeException($isSo ? 'Qadarka daynta ugu yaraan waa $100.' : 'Minimum loan amount is $100.');
        }
        if ($purpose === '') {
            $purpose = $isSo ? 'Maalgelin lacag cadaan ah (Qard Xasan)' : 'Zero-Riba Cash Loan Financing';
        }

        // Get Client Details
        $cSt = $pdo->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
        $cSt->execute([':id' => $clientId]);
        $client = $cSt->fetch();
        if (!$client) {
            throw new \RuntimeException($isSo ? 'Macmiilka lama helin.' : 'Client not found.');
        }

        if ($phone === '') {
            $phone = (string) ($client['phone'] ?? '');
        }

        // Ensure at least one guarantor exists
        $gCount = (int) $pdo->query("SELECT COUNT(*) FROM guarantors WHERE client_id = " . $clientId)->fetchColumn();
        if ($gCount === 0) {
            $pdo->prepare("INSERT INTO guarantors (client_id, full_name, phone, id_number, relationship, occupation, verified_by) VALUES (:cid, :name, :phone, :idnum, 'Qaraabo', 'Ganacsade', :vby)")
                ->execute([
                    ':cid' => $clientId,
                    ':name' => 'Damiin Guud (' . $client['full_name'] . ')',
                    ':phone' => $phone ?: '+252610000000',
                    ':idnum' => $client['id_number'] ?: 'GUA-' . rand(100000, 999999),
                    ':vby' => $user['id']
                ]);
        }

        // Find Qard Hassan Product (flat product)
        $pSt = $pdo->query("SELECT * FROM products WHERE product_type = 'flat' AND is_active = 1 LIMIT 1");
        $product = $pSt->fetch();
        if (!$product) {
            throw new \RuntimeException($isSo ? 'Alaabta daynta (Product) lama helin.' : 'Loan product not found.');
        }
        $productId = (int) $product['id'];

        $appNo = 'APP-QRD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $branchId = (int) ($client['branch_id'] ?? $user['branch_id'] ?? 1);

        // Schedule calculation
        $schedule = ScheduleEngine::generate(
            'flat',
            number_format($amount, 2, '.', ''),
            '0.00',
            $term,
            $frequency,
            date('Y-m-d H:i:s'),
            $interval
        );

        // Employees only submit a request; their Branch Manager approves it (see applicationReview).
        // Admin, General Manager and Branch Manager issue directly.
        $isRequest = self::submitsAsRequest($user);
        if ($isRequest) {
            $branchId = self::requestBranchId($user, $client);
        }
        $meta = json_encode(['source' => 'cash_loan_form', 'provider' => $provider, 'phone' => $phone], JSON_UNESCAPED_UNICODE);

        $insApp = $pdo->prepare("
            INSERT INTO financing_applications (
                application_number, client_id, branch_id, product_id, group_id, created_by,
                requested_amount, term_installments, repayment_frequency, repayment_interval, agreed_rate, agreed_admin_fee,
                projected_profit, projected_total_due, purpose, murabaha_goods_description, notes,
                calculation_snapshot, status, required_approver_role, reviewed_by, reviewed_at, decision_comments,
                submitted_at, expires_at
            ) VALUES (
                :app_no, :cid, :bid, :pid, NULL, :created_by,
                :amt, :term, :freq, :interval, 0.0000, 0.00,
                0.00, :tot_due, :purpose, NULL, :notes,
                :calc, :status, :req_role, :rev_by, " . ($isRequest ? 'NULL' : 'NOW()') . ", :decision,
                NOW(), " . ($isRequest ? 'NULL' : 'DATE_ADD(NOW(), INTERVAL 30 DAY)') . "
            )
        ");

        $insApp->execute([
            ':app_no' => $appNo,
            ':cid' => $clientId,
            ':bid' => $branchId,
            ':pid' => $productId,
            ':created_by' => $user['id'],
            ':amt' => $schedule['principal'],
            ':term' => $term,
            ':freq' => $frequency,
            ':interval' => $interval,
            ':tot_due' => $schedule['total_due'],
            ':purpose' => $purpose,
            ':notes' => $meta,
            ':calc' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
            ':status' => $isRequest ? 'submitted' : 'approved',
            ':req_role' => $isRequest ? FinancingDomainService::requiredApproverRole((float) $schedule['principal'], false) : 'loan_officer',
            ':rev_by' => $isRequest ? null : $user['id'],
            ':decision' => $isRequest ? null : 'Directly approved and disbursed by staff',
        ]);
        $appId = (int) $pdo->lastInsertId();

        if ($isRequest) {
            self::finishEmployeeRequest($pdo, $appId, $appNo, $client, (float) $schedule['principal'], $user, $isSo);
        }

        // Disburse immediately
        $disbRes = DisbursementService::disburse(
            $appId,
            (int) $user['id'],
            $provider,
            $phone,
            date('Y-m-d'),
            $pdo
        );

        AuditLogger::log(
            'financing.direct_disbursed',
            'financings',
            $disbRes['financing_id'],
            "Direct cash loan {$disbRes['financing_number']} (\${$amount}) disbursed to {$client['full_name']}.",
            ['client_id' => $clientId, 'amount' => $amount, 'provider' => $provider, 'phone' => $phone],
            (int) $user['id'],
            $branchId
        );

        $mailNote = self::emailFinancingIssued($pdo, (int) $disbRes['financing_id'], $client, $disbRes, $purpose, '', $user, $locale);

        $_SESSION['flash_success'] = ($isSo
            ? "Daynta lacagta ah (\${$amount}) si guul leh ayaa loo siiyay macmiilka {$client['full_name']}! Heshiiska lambarkiisu waa: {$disbRes['financing_number']}."
            : "Cash loan (\${$amount}) successfully disbursed to {$client['full_name']}! Contract: {$disbRes['financing_number']}.") . $mailNote;

        header('Location: ?route=loans');
        exit;
    }

    private static function murabahaNewForm(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer', 'manager']);
        [$scopeSql, $params] = self::clientScopeSql($user, 'c');

        $clients = $pdo->prepare("
            SELECT c.id, c.full_name, c.client_code, c.phone, c.email, c.id_number, c.branch_id,
                   b.name AS branch_name, cs.current_score,
                   (SELECT g.full_name FROM guarantors g WHERE g.client_id = c.id LIMIT 1) AS guarantor_name
            FROM clients c
            LEFT JOIN branches b ON b.id = c.branch_id
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            WHERE 1=1 {$scopeSql}
            ORDER BY c.full_name ASC
        ");
        $clients->execute($params);
        $clientsList = $clients->fetchAll();

        $selectedClientId = (int) ($_GET['client_id'] ?? 0);

        self::renderDashboard('financings/murabaha_new.php', [
            'pageTitle' => self::tr('Heshiis Murabaxo ah', 'New Murabaha Financing', 'مرابحة جديدة'),
            'clients' => $clientsList,
            'goods' => MurabahaGoodsService::all($pdo, true),
            'selectedClientId' => $selectedClientId,
            'history' => self::clientFinancingHistory($pdo, $user),
            'isRequest' => self::submitsAsRequest($user),
        ]);
    }

    private static function murabahaSubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer', 'manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();
        $isSo = ($locale === 'so');

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $unitCost = (float) ($_POST['cost_price'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $markupPct = (float) ($_POST['markup_pct'] ?? 8.0);
        [$term, $frequency, $interval] = self::readRepaymentPlan($isSo);
        $goodsDesc = trim((string) ($_POST['murabaha_goods_description'] ?? ''));
        $purpose = trim((string) ($_POST['purpose'] ?? ''));
        $goodsName = trim((string) ($_POST['goods_name'] ?? ''));
        if ($goodsName !== '' && stripos($goodsDesc, $goodsName) === false) {
            $goodsDesc = $goodsName . ($goodsDesc !== '' ? ' — ' . $goodsDesc : '');
        }

        if ($clientId <= 0) {
            throw new \RuntimeException($isSo ? 'Fadlan dooro macmiilka Murabaxada qaadanaya.' : 'Please select a client.');
        }
        if ($unitCost < 50) {
            throw new \RuntimeException($isSo ? 'Qiimaha sheyga ugu yaraan waa $50.' : 'Minimum cost price is $50.');
        }
        if ($goodsDesc === '') {
            throw new \RuntimeException($isSo ? 'Fadlan faahfaahi sheyga ama badeecada Murabaxada lagu bixinayo.' : 'Please provide goods description.');
        }

        $baseCost = $unitCost * $qty;

        // Get Client Details
        $cSt = $pdo->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
        $cSt->execute([':id' => $clientId]);
        $client = $cSt->fetch();
        if (!$client) {
            throw new \RuntimeException($isSo ? 'Macmiilka lama helin.' : 'Client not found.');
        }

        // Ensure at least one guarantor exists
        $gCount = (int) $pdo->query("SELECT COUNT(*) FROM guarantors WHERE client_id = " . $clientId)->fetchColumn();
        if ($gCount === 0) {
            $pdo->prepare("INSERT INTO guarantors (client_id, full_name, phone, id_number, relationship, occupation, verified_by) VALUES (:cid, :name, :phone, :idnum, 'Qaraabo', 'Ganacsade', :vby)")
                ->execute([
                    ':cid' => $clientId,
                    ':name' => 'Damiin Guud (' . $client['full_name'] . ')',
                    ':phone' => $client['phone'] ?: '+252610000000',
                    ':idnum' => $client['id_number'] ?: 'GUA-' . rand(100000, 999999),
                    ':vby' => $user['id']
                ]);
        }

        // Find Murabaha Product
        $pSt = $pdo->query("SELECT * FROM products WHERE product_type = 'murabaha' AND is_active = 1 LIMIT 1");
        $product = $pSt->fetch();
        if (!$product) {
            throw new \RuntimeException($isSo ? 'Alaabta Murabaxada (Product) lama helin.' : 'Murabaha product not found.');
        }
        $productId = (int) $product['id'];

        $appNo = 'APP-MRB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $branchId = (int) ($client['branch_id'] ?? $user['branch_id'] ?? 1);

        // Schedule calculation
        $schedule = ScheduleEngine::generate(
            'murabaha',
            number_format($baseCost, 2, '.', ''),
            number_format($markupPct, 4, '.', ''),
            $term,
            $frequency,
            date('Y-m-d H:i:s'),
            $interval
        );

        // Employees only submit a request; their Branch Manager approves it. Senior roles issue directly.
        $isRequest = self::submitsAsRequest($user);
        if ($isRequest) {
            $branchId = self::requestBranchId($user, $client);
        }
        $insApp = $pdo->prepare("
            INSERT INTO financing_applications (
                application_number, client_id, branch_id, product_id, group_id, created_by,
                requested_amount, term_installments, repayment_frequency, repayment_interval, agreed_rate, agreed_admin_fee,
                projected_profit, projected_total_due, purpose, murabaha_goods_description, notes,
                calculation_snapshot, status, required_approver_role, reviewed_by, reviewed_at, decision_comments,
                submitted_at, expires_at
            ) VALUES (
                :app_no, :cid, :bid, :pid, NULL, :created_by,
                :amt, :term, :freq, :interval, :rate, 0.00,
                :profit, :tot_due, :purpose, :goods, :notes,
                :calc, :status, :req_role, :rev_by, " . ($isRequest ? 'NULL' : 'NOW()') . ", :decision,
                NOW(), " . ($isRequest ? 'NULL' : 'DATE_ADD(NOW(), INTERVAL 30 DAY)') . "
            )
        ");

        $insApp->execute([
            ':app_no' => $appNo,
            ':cid' => $clientId,
            ':bid' => $branchId,
            ':pid' => $productId,
            ':created_by' => $user['id'],
            ':amt' => $schedule['principal'],
            ':term' => $term,
            ':freq' => $frequency,
            ':interval' => $interval,
            ':rate' => $schedule['rate'],
            ':profit' => $schedule['total_profit'],
            ':tot_due' => $schedule['total_due'],
            ':purpose' => $purpose ?: 'Murabaha Asset Financing',
            ':goods' => $goodsDesc,
            ':notes' => json_encode([
                'source' => 'murabaha_form',
                'provider' => 'EVC Plus',
                'phone' => (string) ($client['phone'] ?? ''),
                'text' => 'Murabaha cost: $' . $baseCost . ' + markup: ' . $markupPct . '%',
            ], JSON_UNESCAPED_UNICODE),
            ':calc' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
            ':status' => $isRequest ? 'submitted' : 'approved',
            ':req_role' => $isRequest ? FinancingDomainService::requiredApproverRole((float) $schedule['principal'], false) : 'loan_officer',
            ':rev_by' => $isRequest ? null : $user['id'],
            ':decision' => $isRequest ? null : 'Direct Murabaha contract confirmed by staff',
        ]);
        $appId = (int) $pdo->lastInsertId();

        if ($isRequest) {
            self::finishEmployeeRequest($pdo, $appId, $appNo, $client, (float) $schedule['total_due'], $user, $isSo);
        }

        // Disburse immediately
        $disbRes = DisbursementService::disburse(
            $appId,
            (int) $user['id'],
            'EVC Plus',
            (string) ($client['phone'] ?? '+252610000000'),
            date('Y-m-d'),
            $pdo
        );

        AuditLogger::log(
            'murabaha.direct_issued',
            'financings',
            $disbRes['financing_id'],
            "Direct Murabaha contract {$disbRes['financing_number']} (\${$schedule['total_due']}) issued to {$client['full_name']}.",
            ['client_id' => $clientId, 'cost' => $baseCost, 'selling_price' => $schedule['total_due'], 'goods' => $goodsDesc],
            (int) $user['id'],
            $branchId
        );

        $mailNote = self::emailFinancingIssued($pdo, (int) $disbRes['financing_id'], $client, $disbRes, $purpose ?: 'Murabaha Asset Financing', $goodsDesc, $user, $locale);

        $_SESSION['flash_success'] = ($isSo
            ? "Heshiiska Murabaxada (Qiimaha Iibka: \${$schedule['total_due']}) si guul leh ayaa loogu diiwaangeliyay macmiilka {$client['full_name']}! Heshiiska lambarkiisu waa: {$disbRes['financing_number']}."
            : "Murabaha contract (Selling Price: \${$schedule['total_due']}) confirmed for {$client['full_name']}! Contract: {$disbRes['financing_number']}.") . $mailNote;

        header('Location: ?route=murabaha');
        exit;
    }

    private static function murabahaGoodsList(PDO $pdo): void
    {
        AuthMiddleware::requirePermission('products.manage');
        $editId = (int) ($_GET['edit'] ?? 0);
        self::renderDashboard('financings/murabaha_goods.php', [
            'pageTitle' => self::tr('Alaabta Murabaxada', 'Murabaha Products', 'منتجات المرابحة'),
            'goods' => MurabahaGoodsService::all($pdo),
            'editItem' => $editId > 0 ? MurabahaGoodsService::find($pdo, $editId) : null,
        ]);
    }

    private static function murabahaGoodsSave(PDO $pdo): void
    {
        $user = AuthMiddleware::requirePermission('products.manage');
        AuthMiddleware::verifyCsrfOrAbort();
        $isSo = Lang::locale() === 'so';
        $id = (int) ($_POST['id'] ?? 0);
        MurabahaGoodsService::save($pdo, $id, $_POST, $_FILES['image'] ?? null, (int) $user['id'], $isSo);
        $_SESSION['flash_success'] = $id > 0
            ? ($isSo ? 'Sheyga Murabaxada waa la cusboonaysiiyay.' : 'Murabaha product updated.')
            : ($isSo ? 'Sheyga Murabaxada cusub waa la diiwaangeliyay.' : 'New Murabaha product added.');
        header('Location: ?route=murabaha_goods');
        exit;
    }

    private static function partnersList(PDO $pdo): void
    {
        AuthMiddleware::requireRoles(['admin']);
        $editId = (int) ($_GET['edit'] ?? 0);
        self::renderDashboard('admin/partners.php', [
            'pageTitle' => self::tr('La-hawlgalayaasha', 'Partners', 'الشركاء'),
            'partners' => PartnerService::all($pdo),
            'editItem' => $editId > 0 ? PartnerService::find($pdo, $editId) : null,
        ]);
    }

    private static function partnerSave(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $isSo = Lang::locale() === 'so';
        $id = (int) ($_POST['id'] ?? 0);
        PartnerService::save($pdo, $id, $_POST, $_FILES['logo'] ?? null, (int) $user['id'], $isSo);
        $_SESSION['flash_success'] = $id > 0
            ? ($isSo ? 'La-hawlgalaha waa la cusboonaysiiyay.' : 'Partner updated.')
            : ($isSo ? 'La-hawlgale cusub ayaa la daray; wuxuu ka muuqdaa bogga Home-ka.' : 'Partner added; it now shows on the home page.');
        header('Location: ?route=partners');
        exit;
    }

    private static function partnerToggle(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        PartnerService::toggle($pdo, (int) ($_POST['id'] ?? 0), (int) $user['id']);
        header('Location: ?route=partners');
        exit;
    }

    private static function partnerDelete(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        PartnerService::delete($pdo, (int) ($_POST['id'] ?? 0), (int) $user['id']);
        $_SESSION['flash_success'] = Lang::locale() === 'so' ? 'La-hawlgalaha waa la tirtiray.' : 'Partner deleted.';
        header('Location: ?route=partners');
        exit;
    }

    private static function murabahaGoodsHomeToggle(PDO $pdo): void
    {
        $user = AuthMiddleware::requirePermission('products.manage');
        AuthMiddleware::verifyCsrfOrAbort();
        MurabahaGoodsService::toggleHome($pdo, (int) ($_POST['id'] ?? 0), (int) $user['id']);
        header('Location: ?route=murabaha_goods');
        exit;
    }

    private static function murabahaGoodsToggle(PDO $pdo): void
    {
        $user = AuthMiddleware::requirePermission('products.manage');
        AuthMiddleware::verifyCsrfOrAbort();
        MurabahaGoodsService::toggle($pdo, (int) ($_POST['id'] ?? 0), (int) $user['id']);
        header('Location: ?route=murabaha_goods');
        exit;
    }

    private static function murabahaGoodsDelete(PDO $pdo): void
    {
        $user = AuthMiddleware::requirePermission('products.manage');
        AuthMiddleware::verifyCsrfOrAbort();
        MurabahaGoodsService::delete($pdo, (int) ($_POST['id'] ?? 0), (int) $user['id']);
        $_SESSION['flash_success'] = Lang::locale() === 'so' ? 'Sheyga waa la tirtiray.' : 'Product deleted.';
        header('Location: ?route=murabaha_goods');
        exit;
    }

    private static function clientView(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager', 'loan_officer']);
        $clientId = (int) ($_GET['id'] ?? 0);
        $st = $pdo->prepare('
            SELECT c.*, b.name AS branch_name, cs.current_score, cs.score_category, u.full_name AS registered_by_name
            FROM clients c
            LEFT JOIN branches b ON b.id = c.branch_id
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            LEFT JOIN users u ON u.id = c.registered_by
            WHERE c.id = :id AND c.deleted_at IS NULL
            LIMIT 1
        ');
        $st->execute([':id' => $clientId]);
        $client = $st->fetch();
        if (!$client || !AuthMiddleware::canAccessClientRecord($client, $user)) {
            throw new \RuntimeException(Lang::locale() === 'so' ? 'Macmiilka lama helin.' : 'Client not found.');
        }

        $g = $pdo->prepare('SELECT * FROM guarantors WHERE client_id = :id ORDER BY id ASC');
        $g->execute([':id' => $clientId]);
        $f = $pdo->prepare('SELECT id, financing_number, product_type, approved_amount, total_due, outstanding_balance, status, disbursed_at FROM financings WHERE client_id = :id ORDER BY id DESC');
        $f->execute([':id' => $clientId]);
        $r = $pdo->prepare('
            SELECT r.id, r.receipt_number, r.amount, r.payment_method, r.payment_date, r.created_at, f.financing_number, u.full_name AS recorded_by_name
            FROM repayments r
            INNER JOIN financings f ON f.id = r.financing_id
            LEFT JOIN users u ON u.id = r.recorded_by
            WHERE r.client_id = :id
            ORDER BY r.id DESC
        ');
        $r->execute([':id' => $clientId]);

        self::renderDashboard('clients/view.php', [
            'pageTitle' => (string) $client['full_name'],
            'client' => $client,
            'guarantors' => $g->fetchAll(),
            'financings' => $f->fetchAll(),
            'repayments' => $r->fetchAll(),
            'canCollect' => AuthMiddleware::hasPermission('repayments.record', $user),
            'isNew' => !empty($_GET['new']),
        ]);
    }

    /**
     * Emails the client the money they received, how and when they repay it, and the disbursement receipt.
     * Never throws: the financing is already saved, so a mail problem only changes the message shown to staff.
     */
    private static function emailFinancingIssued(PDO $pdo, int $financingId, array $client, array $disb, string $purpose, string $goods, array $user, string $locale): string
    {
        $isSo = $locale === 'so';
        $email = trim((string) ($client['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $isSo ? ' Macmiilku email ma laha, sidaas darteed rasiidka looma dirin.' : ' The client has no email address, so no receipt was emailed.';
        }
        try {
            $fin = $pdo->prepare('SELECT f.*, b.name AS branch_name FROM financings f LEFT JOIN branches b ON b.id = f.branch_id WHERE f.id = :id');
            $fin->execute([':id' => $financingId]);
            $row = $fin->fetch() ?: [];
            $inst = $pdo->prepare('SELECT installment_number, due_date, total_due FROM installments WHERE financing_id = :id ORDER BY installment_number ASC');
            $inst->execute([':id' => $financingId]);

            $result = MailService::sendFinancingIssuedEmail([
                'client_email' => $email,
                'client_name' => (string) $client['full_name'],
                'client_code' => (string) $client['client_code'],
                'financing_number' => (string) $disb['financing_number'],
                'receipt_reference' => (string) ($disb['disbursement_reference'] ?? ''),
                'product_type' => (string) ($row['product_type'] ?? ''),
                'amount_given' => (float) ($disb['approved_amount'] ?? 0),
                'net_disbursed' => (float) ($disb['net_disbursed_amount'] ?? 0),
                'total_due' => (float) ($disb['total_due'] ?? 0),
                'term' => (int) ($row['term_installments'] ?? 0),
                'frequency' => (string) ($row['repayment_frequency'] ?? 'monthly'),
                'purpose' => $purpose,
                'goods' => $goods,
                // Murabaha: embed the product picture from the catalog in the email.
                'item_image' => $goods !== '' ? MurabahaGoodsService::emailImageForGoods($pdo, $goods) : null,
                'provider' => (string) ($disb['provider'] ?? ''),
                'disbursed_at' => (string) ($disb['disbursed_at'] ?? date('Y-m-d H:i')),
                'branch_name' => (string) ($row['branch_name'] ?? 'SIMDA'),
                'issued_by' => (string) ($user['full_name'] ?? 'SIMDA'),
                'installments' => $inst->fetchAll(),
            ], $locale);
        } catch (\Throwable $e) {
            $result = ['success' => false];
        }
        if (!empty($result['success'])) {
            return $isSo ? " Rasiidka iyo jadwalka bixinta waxaa loo diray {$email}." : " Receipt and repayment schedule emailed to {$email}.";
        }
        return $isSo ? " Email-ka loo diri waayay {$email}; fadlan hubi dejinta email-ka." : " The email to {$email} could not be sent; please check the mail settings.";
    }

    private static function financingsList(PDO $pdo, string $route): void
    {
        $user = AuthMiddleware::requireAuth();
        [$scopeSql, $params] = self::scopeSql($user, 'f');

        $stmt = $pdo->prepare("
            SELECT f.*, c.full_name AS client_name, b.name AS branch_name
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            INNER JOIN branches b ON b.id = f.branch_id
            WHERE 1=1 {$scopeSql}
            ORDER BY f.id DESC
        ");
        $stmt->execute($params);
        $financings = $stmt->fetchAll();

        $titleKey = match ($route) {
            'schedules' => 'menu_schedules',
            'statements' => 'menu_client_statements',
            default => 'menu_active_financing',
        };

        self::renderDashboard('financings/index.php', [
            'pageTitle' => Lang::get($titleKey),
            'financings' => $financings,
        ]);
    }

    private static function financingView(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $statement = StatementPdfGenerator::buildStatementData($id, $pdo);
        $f = $statement['financing'];

        if (!AuthMiddleware::canAccessClientRecord(['id' => $f['client_id'], 'branch_id' => $f['branch_id']], $user)) {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }

        self::renderDashboard('financings/view.php', [
            'pageTitle' => $f['financing_number'] . ' — Statement & Schedule',
            'statement' => $statement,
        ]);
    }

    private static function statementPdf(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $statement = StatementPdfGenerator::buildStatementData($id, $pdo);
        $f = $statement['financing'];

        if (!AuthMiddleware::canAccessClientRecord(['id' => $f['client_id'], 'branch_id' => $f['branch_id']], $user)) {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }

        $pdfBytes = StatementPdfGenerator::generatePdfBinary($id, Lang::locale(), $pdo);
        $fileName = 'SIMDA_Statement_' . $f['financing_number'] . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        echo $pdfBytes;
        exit;
    }

    /**
     * Collect Repayment: search a client who owes money, pick the contract, enter what they paid.
     * Posts to repayment_submit, which records it, emails the receipt and opens the receipt page.
     */
    private static function repaymentCollect(PDO $pdo): void
    {
        $user = AuthMiddleware::requirePermission('repayments.record');
        [$scope, $params] = self::clientScopeSql($user, 'f', 'client_id'); // clients may pay at any branch
        $open = "i.financing_id = f.id AND i.status IN ('pending', 'partial', 'overdue')";
        $stmt = $pdo->prepare("
            SELECT f.id, f.financing_number, f.product_type, f.total_due, f.outstanding_balance, f.status,
                   f.term_installments, f.repayment_frequency,
                   c.id AS client_id, c.full_name, c.client_code, c.phone, c.email, c.id_number,
                   (SELECT i.due_date FROM installments i WHERE {$open} ORDER BY i.installment_number LIMIT 1) AS next_due_date,
                   (SELECT i.total_due - i.total_paid FROM installments i WHERE {$open} ORDER BY i.installment_number LIMIT 1) AS next_due_amount,
                   (SELECT COUNT(*) FROM installments i WHERE i.financing_id = f.id AND i.status = 'paid') AS paid_count
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            WHERE f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 {$scope}
            ORDER BY c.full_name ASC, f.id ASC
        ");
        $stmt->execute($params);

        $debtors = [];
        foreach ($stmt->fetchAll() as $row) {
            $cid = (int) $row['client_id'];
            if (!isset($debtors[$cid])) {
                $debtors[$cid] = [
                    'id' => $cid,
                    'name' => (string) $row['full_name'],
                    'code' => (string) $row['client_code'],
                    'phone' => (string) $row['phone'],
                    'email' => (string) ($row['email'] ?? ''),
                    'idnum' => (string) ($row['id_number'] ?? ''),
                    'owed' => 0.0,
                    'contracts' => [],
                ];
            }
            $debtors[$cid]['owed'] += (float) $row['outstanding_balance'];
            $debtors[$cid]['contracts'][] = [
                'id' => (int) $row['id'],
                'number' => (string) $row['financing_number'],
                'type' => (string) $row['product_type'],
                'status' => (string) $row['status'],
                'total' => (float) $row['total_due'],
                'balance' => (float) $row['outstanding_balance'],
                'term' => (int) $row['term_installments'],
                'paid' => (int) $row['paid_count'],
                'next_date' => $row['next_due_date'],
                'next_amount' => $row['next_due_amount'] !== null ? (float) $row['next_due_amount'] : null,
            ];
        }

        self::renderDashboard('repayments/collect.php', [
            'pageTitle' => self::tr('Qabo Lacag-celin', 'Collect Repayment', 'تحصيل سداد'),
            'debtors' => array_values($debtors),
            'selectedClientId' => (int) ($_GET['client_id'] ?? 0),
        ]);
    }

    private static function repaymentsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        [$repScope, $repParams] = self::scopeSql($user, 'r');

        $stmt = $pdo->prepare("
            SELECT r.*, f.financing_number, c.full_name AS client_name
            FROM repayments r
            INNER JOIN financings f ON f.id = r.financing_id
            INNER JOIN clients c ON c.id = r.client_id
            WHERE 1=1 {$repScope}
            ORDER BY r.id DESC
        ");
        $stmt->execute($repParams);
        $repayments = $stmt->fetchAll();

        [$finScope, $finParams] = self::scopeSql($user, 'f');
        $fStmt = $pdo->prepare("
            SELECT f.id, f.financing_number, f.outstanding_balance, c.full_name AS client_name, c.credit_balance
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            WHERE f.status IN ('active', 'overdue') AND f.outstanding_balance > 0 {$finScope}
            ORDER BY f.id DESC
        ");
        $fStmt->execute($finParams);
        $activeFinancings = $fStmt->fetchAll();

        self::renderDashboard('repayments/index.php', [
            'pageTitle' => Lang::get('menu_repayment_history'),
            'repayments' => $repayments,
            'activeFinancings' => $activeFinancings,
        ]);
    }

    private static function repaymentSubmit(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer', 'manager', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();
        $isSo = $locale === 'so';

        $financingId = (int) ($_POST['financing_id'] ?? 0);
        $amount = (string) ($_POST['amount'] ?? '0.00');
        $paymentMethod = (string) ($_POST['payment_method'] ?? 'EVC Plus');
        $txRef = (string) ($_POST['transaction_reference'] ?? '');
        $paymentDate = (string) ($_POST['payment_date'] ?? date('Y-m-d'));
        $notes = (string) ($_POST['notes'] ?? '');
        $useClientCredit = !empty($_POST['use_client_credit']);
        $ibraDiscount = (string) ($_POST['ibra_discount'] ?? '0.00');

        $res = RepaymentEngine::recordRepayment(
            $financingId,
            (int) $user['id'],
            $amount,
            $paymentMethod,
            $txRef,
            $paymentDate,
            $notes,
            $useClientCredit,
            $ibraDiscount,
            $pdo
        );

        // Fetch client and financing details for sending receipt email
        $st = $pdo->prepare('
            SELECT f.financing_number, f.product_type,
                   c.full_name AS client_name, c.client_code, c.email AS client_email, c.phone AS client_phone,
                   b.name AS branch_name
            FROM financings f
            INNER JOIN clients c ON c.id = f.client_id
            LEFT JOIN branches b ON b.id = f.branch_id
            WHERE f.id = :fid
        ');
        $st->execute([':fid' => $financingId]);
        $info = $st->fetch();

        $clientEmail = !empty($info['client_email']) ? trim((string) $info['client_email']) : '';

        // If client email exists, send official receipt email immediately
        $mailSent = false;
        if ($clientEmail !== '') {
            $mailStatus = MailService::sendRepaymentReceiptEmail([
                'client_name' => $info['client_name'] ?? 'Client',
                'client_email' => $clientEmail,
                'client_code' => $info['client_code'] ?? '',
                'receipt_number' => $res['receipt_number'],
                'financing_number' => $info['financing_number'],
                'product_type' => $info['product_type'],
                'amount_paid' => $res['cash_amount'],
                'payment_method' => $paymentMethod,
                'transaction_reference' => $txRef,
                'payment_date' => $paymentDate . ' ' . date('H:i'),
                'outstanding_balance_after' => $res['outstanding_balance_after'],
                'branch_name' => $info['branch_name'] ?? 'Head Office',
                'recorded_by' => $user['full_name'] ?? 'Staff',
                'credit_score' => $res['credit_score']['current_score'] ?? 0,
            ], $locale);

            if ($mailStatus['success']) {
                $mailSent = true;
                AuditLogger::log('mail.receipt_sent', 'repayments', $res['repayment_id'], 'Payment receipt emailed to ' . $clientEmail, [], (int) $user['id']);
            } else {
                AuditLogger::log('mail.receipt_failed', 'repayments', $res['repayment_id'], 'Receipt email delivery failed: ' . ($mailStatus['error'] ?? 'Unknown'), [], (int) $user['id']);
            }
        }

        if ($mailSent) {
            $_SESSION['flash_success'] = $isSo
                ? "Lacag-bixinta {$res['receipt_number']} (\${$res['cash_amount']}) si guul leh ayaa loo diiwaangeliyay! Rasiidka rasmi ah waxaa loo diray email-ka macmiilka ({$clientEmail}). Baaqiga harsan: \${$res['outstanding_balance_after']}."
                : "Repayment {$res['receipt_number']} (\${$res['cash_amount']}) recorded! Official receipt sent to client email ({$clientEmail}). Remaining balance: \${$res['outstanding_balance_after']}.";
        } else {
            $_SESSION['flash_success'] = $isSo
                ? "Lacag-bixinta {$res['receipt_number']} (\${$res['cash_amount']}) si guul leh ayaa loo diiwaangeliyay nidaamka. Baaqiga harsan: \${$res['outstanding_balance_after']}."
                : "Repayment {$res['receipt_number']} (\${$res['cash_amount']}) recorded in system. Remaining balance: \${$res['outstanding_balance_after']}.";
        }

        header('Location: ?route=repayment_receipt&id=' . (int) $res['repayment_id']);
        exit;
    }

    private static function repaymentReceipt(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        $repId = (int) ($_GET['id'] ?? 0);
        $receiptNo = trim((string) ($_GET['receipt'] ?? ''));

        if ($repId <= 0 && $receiptNo === '') {
            throw new \RuntimeException(Lang::locale() === 'so' ? 'Rasiidka lama helin.' : 'Receipt not found.');
        }

        $where = $repId > 0 ? 'r.id = :val' : 'r.receipt_number = :val';
        $param = $repId > 0 ? $repId : $receiptNo;
        // A client may only open their own receipts (staff keep access to all, as before).
        [$clientScope, $clientParams] = ($user['role_slug'] ?? '') === 'client'
            ? self::scopeSql($user, 'r')
            : ['', []];

        $stmt = $pdo->prepare("
            SELECT r.*,
                   f.financing_number, f.product_type, f.total_due, f.outstanding_balance, f.status AS financing_status,
                   c.full_name AS client_name, c.client_code, c.phone AS client_phone, c.email AS client_email,
                   b.name AS branch_name, b.code AS branch_code, b.city AS branch_city, b.phone AS branch_phone,
                   u.full_name AS recorded_by_name
            FROM repayments r
            INNER JOIN financings f ON f.id = r.financing_id
            INNER JOIN clients c ON c.id = r.client_id
            LEFT JOIN branches b ON b.id = r.branch_id
            LEFT JOIN users u ON u.id = r.recorded_by
            WHERE {$where} {$clientScope}
            LIMIT 1
        ");
        $stmt->execute([':val' => $param] + $clientParams);
        $repayment = $stmt->fetch();

        if (!$repayment) {
            throw new \RuntimeException(Lang::locale() === 'so' ? 'Rasiidka lama helin.' : 'Receipt not found.');
        }

        self::renderDashboard('repayments/receipt.php', [
            'pageTitle' => (Lang::locale() === 'so' ? 'Rasiidka Lacag-Bixinta: ' : 'Payment Receipt: ') . $repayment['receipt_number'],
            'repayment' => $repayment,
        ]);
    }

    private static function charityChargesList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        CharityChargeService::assessOverdueInstallments(null, $pdo);

        [$scopeSql, $params] = self::scopeSql($user, 'cc');
        $stmt = $pdo->prepare("
            SELECT cc.*, f.financing_number, c.full_name AS client_name, i.installment_number, i.due_date
            FROM charity_charges cc
            INNER JOIN financings f ON f.id = cc.financing_id
            INNER JOIN clients c ON c.id = cc.client_id
            INNER JOIN installments i ON i.id = cc.installment_id
            WHERE 1=1 {$scopeSql}
            ORDER BY cc.id DESC
        ");
        $stmt->execute($params);
        $charges = $stmt->fetchAll();

        // Charity account report: whole institution for admin / General Manager, own branch for other staff.
        $role = (string) ($user['role_slug'] ?? '');
        $isStaff = $role !== 'client';
        $accountBranch = in_array($role, ['admin', 'manager'], true) ? null : (int) ($user['branch_id'] ?? 0);

        self::renderDashboard('risk/charity_charges.php', [
            'pageTitle' => Lang::get('menu_charity_charges'),
            'charges' => $charges,
            'account' => $isStaff ? CharityChargeService::accountSummary($accountBranch, $pdo) : null,
            'payouts' => $isStaff ? CharityChargeService::payouts($accountBranch, $pdo) : [],
        ]);
    }

    /** Records money given out from the charity account (Admin or Branch Manager). */
    private static function charityPayout(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $branchId = ($user['role_slug'] ?? '') === 'admin' ? null : (int) ($user['branch_id'] ?? 0);
        CharityChargeService::recordPayout(
            $branchId,
            (string) ($_POST['amount'] ?? '0'),
            (string) ($_POST['recipient'] ?? ''),
            (string) ($_POST['purpose'] ?? ''),
            (string) ($_POST['paid_on'] ?? date('Y-m-d')),
            (int) $user['id'],
            $pdo
        );
        $_SESSION['flash_success'] = Lang::t('Lacagta sadaqada ee la bixiyay waa la diiwaangeliyay.', 'Charity payout recorded.');
        header('Location: ?route=charity_charges');
        exit;
    }

    private static function charityWaive(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $chargeId = (int) ($_POST['charge_id'] ?? 0);
        $reason = (string) ($_POST['waiver_reason'] ?? '');
        CharityChargeService::waiveCharge($chargeId, (int) $user['id'], $reason, $pdo);
        $_SESSION['flash_success'] = "Charity late charge #{$chargeId} waived and recorded in the audit log.";
        header('Location: ?route=charity_charges');
        exit;
    }

    private static function creditScoresList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        $where = '';
        $params = [];
        if ($user['role_slug'] === 'client') {
            $where = ' WHERE c.id = :cid ';
            $params[':cid'] = (int) $user['client_id'];
        } elseif ($user['role_slug'] !== 'admin') {
            $where = ' WHERE c.branch_id = :bid ';
            $params[':bid'] = (int) $user['branch_id'];
        }

        $stmt = $pdo->prepare("
            SELECT cs.*, c.full_name AS client_name, c.client_code, b.name AS branch_name
            FROM credit_scores cs
            INNER JOIN clients c ON c.id = cs.client_id
            INNER JOIN branches b ON b.id = c.branch_id
            {$where}
            ORDER BY cs.current_score DESC
        ");
        $stmt->execute($params);
        $scores = $stmt->fetchAll();

        self::renderDashboard('risk/credit_scores.php', [
            'pageTitle' => Lang::get('menu_credit_scores'),
            'scores' => $scores,
        ]);
    }

    private static function par30View(PDO $pdo, string $route): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        $branchId = ($user['role_slug'] === 'admin') ? null : (int) $user['branch_id'];
        $par30 = Par30Service::calculate($branchId, null, $pdo);
        $branchBreakdown = Par30Service::branchBreakdown(null, $pdo);
        $trend = Par30Service::monthlyTrend(12, $branchId, $pdo);

        self::renderDashboard('risk/par30.php', [
            'pageTitle' => Lang::get($route === 'reports' ? 'menu_financial_reports' : 'menu_par30'),
            'par30' => $par30,
            'branchBreakdown' => $branchBreakdown,
            'trend' => $trend,
        ]);
    }

    private static function smsOutboxList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        SmsReminderService::generateScheduledReminders(null, $pdo);

        $where = '';
        $params = [];
        if ($user['role_slug'] === 'client') {
            $where = ' WHERE s.client_id = :cid ';
            $params[':cid'] = (int) $user['client_id'];
        } elseif ($user['role_slug'] !== 'admin') {
            $where = ' WHERE f.branch_id = :bid ';
            $params[':bid'] = (int) $user['branch_id'];
        }

        $stmt = $pdo->prepare("
            SELECT s.*, c.full_name AS client_name, f.financing_number
            FROM sms_outbox s
            INNER JOIN clients c ON c.id = s.client_id
            INNER JOIN financings f ON f.id = s.financing_id
            {$where}
            ORDER BY s.id DESC
        ");
        $stmt->execute($params);
        $messages = $stmt->fetchAll();

        // Clients staff can message from the "Send a message" box (all branches for admin/manager,
        // own branch for everyone else). Clients themselves never see the box.
        $broadcastClients = [];
        if (self::canBroadcast($user)) {
            [$bWhere, $bParams] = self::broadcastScope($user);
            $bStmt = $pdo->prepare("
                SELECT c.id, c.client_code, c.full_name, c.phone, c.email, c.status
                FROM clients c
                WHERE c.deleted_at IS NULL {$bWhere}
                ORDER BY c.full_name
            ");
            $bStmt->execute($bParams);
            $broadcastClients = $bStmt->fetchAll();
        }

        self::renderDashboard('communication/sms_outbox.php', [
            'pageTitle' => Lang::get('menu_sms_outbox'),
            'messages' => $messages,
            'broadcastClients' => $broadcastClients,
            'whatsappReady' => WhatsAppService::isEnabled(),
        ]);
    }

    private static function canBroadcast(array $user): bool
    {
        return in_array($user['role_slug'] ?? '', ['admin', 'manager', 'branch_manager', 'loan_officer'], true);
    }

    private static function broadcastScope(array $user): array
    {
        if (in_array($user['role_slug'] ?? '', ['admin', 'manager'], true)) {
            return ['', []];
        }
        return [' AND c.branch_id = :b_bid ', [':b_bid' => (int) ($user['branch_id'] ?? 0)]];
    }

    /**
     * Send one message written on the Communication page to the selected clients,
     * by Gmail (email) or WhatsApp.
     */
    private static function smsBroadcast(PDO $pdo): void
    {
        $user = AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrfOrAbort();
        $isSo = Lang::locale() === 'so';

        try {
            if (!self::canBroadcast($user)) {
                throw new \RuntimeException($isSo ? 'Ma lihid fasax aad fariin ugu dirto macaamiisha.' : 'You are not allowed to message clients.');
            }
            $channel = (string) ($_POST['channel'] ?? '');
            if (!in_array($channel, ['email', 'whatsapp'], true)) {
                throw new \RuntimeException($isSo ? 'Dooro Gmail ama WhatsApp.' : 'Choose Gmail or WhatsApp.');
            }
            $message = trim((string) ($_POST['message'] ?? ''));
            $subject = trim((string) ($_POST['subject'] ?? ''));
            if ($message === '') {
                throw new \RuntimeException($isSo ? 'Fadlan qor fariinta.' : 'Please write the message.');
            }
            if (mb_strlen($message) > 3000) {
                throw new \RuntimeException($isSo ? 'Fariintu waa ka dheer tahay 3000 xaraf.' : 'The message is longer than 3000 characters.');
            }
            if ($subject === '') {
                $subject = $isSo ? 'Fariin ka timid SIMDA' : 'A message from SIMDA';
            }
            $subject = mb_substr($subject, 0, 150);

            $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['client_ids'] ?? [])))));
            if ($ids === []) {
                throw new \RuntimeException($isSo ? 'Dooro ugu yaraan hal macmiil.' : 'Select at least one client.');
            }
            if ($channel === 'whatsapp' && !WhatsAppService::isEnabled()) {
                throw new \RuntimeException($isSo
                    ? 'WhatsApp lama dejin. Daar WhatsApp gateway-ga (whatsapp-gateway/start-whatsapp.bat).'
                    : 'WhatsApp is not set up. Start the WhatsApp gateway (whatsapp-gateway/start-whatsapp.bat).');
            }

            // Only clients this user may message, whatever ids the form posted.
            [$bWhere, $bParams] = self::broadcastScope($user);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT c.id, c.full_name, c.phone, c.email FROM clients c WHERE c.deleted_at IS NULL AND c.id IN ({$in}) " . str_replace(':b_bid', '?', $bWhere));
            $stmt->execute(array_merge($ids, array_values($bParams)));
            $clients = $stmt->fetchAll();

            @set_time_limit(0);
            $locale = Lang::locale();
            $sent = 0;
            $skipped = [];
            $failed = [];
            $failReasons = [];
            foreach ($clients as $c) {
                $name = (string) $c['full_name'];
                if ($channel === 'email') {
                    $to = trim((string) ($c['email'] ?? ''));
                    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                        $skipped[] = $name;
                        continue;
                    }
                    $res = MailService::sendBroadcastEmail($to, $name, $subject, $message, $locale);
                } else {
                    $phone = trim((string) ($c['phone'] ?? ''));
                    if ($phone === '') {
                        $skipped[] = $name;
                        continue;
                    }
                    // SIMDA banner picture with the branded text (signature + footer) as its caption.
                    $waText = \App\Services\BroadcastTemplate::whatsappText($name, $subject, $message, $locale);
                    $banner = is_file(\App\Services\BroadcastTemplate::whatsappBannerPath()) ? 'public/assets/images/whatsapp-banner.png' : null;
                    if ($banner !== null && mb_strlen($waText) > 1000) {
                        // Long message: banner with the title first, then the full text.
                        $res = WhatsAppService::send($phone, "*{$subject}*", $banner, 90);
                        if (!empty($res['success'])) {
                            $res = WhatsAppService::send($phone, $waText, null, 60);
                        }
                    } else {
                        $res = WhatsAppService::send($phone, $waText, $banner, 90);
                    }
                }
                if (!empty($res['success'])) {
                    $sent++;
                } else {
                    $failed[] = $channel === 'whatsapp' ? $name . ' (' . WhatsAppService::displayPhone((string) $c['phone']) . ')' : $name;
                    $failReasons[(string) ($res['error'] ?? 'unknown')] = true;
                    error_log("Broadcast {$channel} to client #{$c['id']} failed: " . ($res['error'] ?? 'unknown'));
                }
            }

            $label = $channel === 'email' ? 'Gmail' : 'WhatsApp';
            AuditLogger::log('communication.broadcast', 'clients', null, "{$label} broadcast \"{$subject}\": {$sent} sent, " . count($failed) . ' failed, ' . count($skipped) . ' skipped.');

            $summary = $isSo
                ? "{$label}: fariinta waxaa loo diray {$sent} macmiil."
                : "{$label}: message sent to {$sent} client(s).";
            if ($skipped !== []) {
                $summary .= $isSo
                    ? ' Laguma dirin (' . ($channel === 'email' ? 'email ma laha' : 'lambar ma laha') . '): ' . implode(', ', $skipped) . '.'
                    : ' Skipped (no ' . ($channel === 'email' ? 'email' : 'phone') . '): ' . implode(', ', $skipped) . '.';
            }
            if ($failed !== []) {
                $_SESSION['flash_error'] = $summary . ($isSo ? ' Way ku guuldarraysatay: ' : ' Failed: ') . implode(', ', $failed) . '.'
                    . ($isSo ? ' Sababta: ' : ' Reason: ') . mb_substr(implode(' | ', array_keys($failReasons)), 0, 300);
            } else {
                $_SESSION['flash_success'] = $summary;
            }
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['broadcast_old'] = ['subject' => $_POST['subject'] ?? '', 'message' => $_POST['message'] ?? ''];
        }

        header('Location: ?route=sms_outbox#broadcast');
        exit;
    }

    private static function smsRunDispatcher(PDO $pdo): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrfOrAbort();
        $res = SmsReminderService::generateScheduledReminders(null, $pdo);
        $_SESSION['flash_success'] = "SMS Reminder Dispatcher executed ({$res['total_generated']} new reminders generated).";
        header('Location: ?route=sms_outbox');
        exit;
    }

    private static function adminSection(PDO $pdo, string $mode, ?string $roleFocus = null): void
    {
        $user = AuthMiddleware::requireAuth();
        $role = $user['role_slug'] ?? '';

        // Role-focused staff lists: each role may open the lists of staff below it (admin sees all).
        $focusAllowed = match ($role) {
            'admin' => true,
            'manager' => in_array($roleFocus, ['branch_manager', 'loan_officer'], true),
            'branch_manager' => $roleFocus === 'loan_officer',
            default => false,
        };
        if ($roleFocus !== null && !$focusAllowed) {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }

        if (!in_array($role, ['admin', 'manager', 'branch_manager'], true)) {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }

        // Branch managers cannot access settings, audit_logs, or permissions
        if (in_array($mode, ['settings', 'audit_logs', 'permissions'], true) && $role !== 'admin') {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }

        $branches = $pdo->query('
            SELECT b.*,
                   (SELECT COUNT(*) FROM users u WHERE u.branch_id = b.id) AS staff_count,
                   (SELECT COUNT(*) FROM clients c WHERE c.branch_id = b.id) AS clients_count
            FROM branches b
            ORDER BY b.id ASC
        ')->fetchAll();

        // Roles allowed to be created by the currently logged-in user (Strict Hierarchy Rule)
        $allowedRoleSlugs = match ($role) {
            'admin' => ['manager', 'branch_manager', 'loan_officer', 'client'],
            'manager' => ['branch_manager', 'loan_officer', 'client'],
            'branch_manager' => ['loan_officer', 'client'],
            default => [],
        };

        $rolesStmt = $pdo->query('SELECT * FROM roles ORDER BY id ASC');
        $allRoles = $rolesStmt->fetchAll();
        $assignableRoles = array_values(array_filter($allRoles, function ($r) use ($allowedRoleSlugs) {
            return in_array($r['slug'], $allowedRoleSlugs, true);
        }));

        // Users list scoped by branch for branch managers
        if ($role === 'branch_manager') {
            $uStmt = $pdo->prepare('
                SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en, b.name AS branch_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN branches b ON b.id = u.branch_id
                WHERE u.branch_id = :bid
                ORDER BY u.id ASC
            ');
            $uStmt->execute([':bid' => (int) ($user['branch_id'] ?? 0)]);
            $users = $uStmt->fetchAll();
        } else {
            $users = $pdo->query('
                SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en, b.name AS branch_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN branches b ON b.id = u.branch_id
                ORDER BY u.id ASC
            ')->fetchAll();
        }

        if ($roleFocus !== null) {
            $users = array_values(array_filter($users, fn ($u) => $u['role_slug'] === $roleFocus));
            $assignableRoles = array_values(array_filter($assignableRoles, fn ($r) => $r['slug'] === $roleFocus));
        }

        $permissionsList = [];
        $selectedUser = null;
        $userPermMap = [];

        if ($mode === 'permissions') {
            $permissionsList = $pdo->query('SELECT * FROM permissions ORDER BY module ASC, id ASC')->fetchAll();

            $nonAdminUsers = array_values(array_filter($users, fn($u) => $u['role_slug'] !== 'admin'));
            $targetUid = (int) ($_GET['user_id'] ?? ($nonAdminUsers[0]['id'] ?? 0));

            foreach ($nonAdminUsers as $nu) {
                if ((int) $nu['id'] === $targetUid) {
                    $selectedUser = $nu;
                    break;
                }
            }
            if (!$selectedUser && !empty($nonAdminUsers)) {
                $selectedUser = $nonAdminUsers[0];
            }

            if ($selectedUser) {
                foreach ($permissionsList as $p) {
                    $userPermMap[$p['slug']] = AuthMiddleware::hasPermission($p['slug'], $selectedUser);
                }
            }
        }

        $settingsList = in_array($role, ['admin', 'manager'], true)
            ? $pdo->query('SELECT * FROM system_settings ORDER BY setting_key ASC')->fetchAll()
            : [];

        $auditLogs = in_array($role, ['admin', 'manager'], true)
            ? $pdo->query('
                SELECT a.*, u.full_name AS user_name
                FROM audit_logs a
                LEFT JOIN users u ON u.id = a.user_id
                ORDER BY a.id DESC
                LIMIT 100
            ')->fetchAll()
            : [];

        $titleMap = [
            'branches' => Lang::get('menu_branches'),
            'users' => Lang::get('menu_users'),
            'permissions' => Lang::locale() === 'so' ? 'Awoodaha & Ogolaanshaha' : 'Permissions & Access',
            'settings' => Lang::get('menu_settings'),
            'audit_logs' => Lang::get('menu_audit_logs'),
        ];

        $isEmployeesRoute = (($_GET['route'] ?? '') === 'employees');
        $pageName = match ($roleFocus) {
            'manager' => 'Managers',
            'branch_manager' => 'Branch Managers',
            'loan_officer' => $isEmployeesRoute ? (Lang::locale() === 'so' ? 'Shaqaalaha (Employees)' : 'Employees') : 'Header Office',
            default => $titleMap[$mode] ?? 'Administration',
        };

        self::renderDashboard('admin/manage.php', [
            'pageTitle' => $pageName,
            'sectionTitle' => $pageName,
            'mode' => $mode,
            'roleFocus' => $roleFocus,
            'returnRoute' => match ($roleFocus) {
                'manager' => 'managers',
                'branch_manager' => 'branch_managers',
                'loan_officer' => $isEmployeesRoute ? 'employees' : 'header_office',
                default => 'users',
            },
            'branches' => $branches,
            'roles' => $assignableRoles,
            'allRoles' => $allRoles,
            'users' => $users,
            'permissionsList' => $permissionsList,
            'selectedUser' => $selectedUser,
            'userPermMap' => $userPermMap,
            'settingsList' => $settingsList,
            'auditLogs' => $auditLogs,
            'currentUser' => $user,
        ]);
    }

    private static function branchCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();

        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $dist = trim((string) ($_POST['district'] ?? ''));
        $addr = trim((string) ($_POST['address'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $mgr = trim((string) ($_POST['manager_name'] ?? ''));

        if ($code === '' || $name === '' || $city === '') {
            throw new \RuntimeException($locale === 'so' ? 'Fadlan geli koodhka, magaca, iyo magaalada laanta.' : 'Please provide branch code, name, and city.');
        }

        // Check if code exists
        $chk = $pdo->prepare('SELECT id FROM branches WHERE code = :code LIMIT 1');
        $chk->execute([':code' => $code]);
        if ($chk->fetch()) {
            throw new \RuntimeException($locale === 'so' ? "Koodhka laanta ({$code}) horay ayuu u jiray. Fadlan koodh kale geli." : "Branch code {$code} already exists.");
        }

        $stmt = $pdo->prepare('
            INSERT INTO branches (code, name, city, district, address, phone, manager_name, is_active)
            VALUES (:code, :name, :city, :dist, :addr, :phone, :mgr, 1)
        ');
        $stmt->execute([
            ':code' => $code,
            ':name' => $name,
            ':city' => $city,
            ':dist' => $dist,
            ':addr' => $addr,
            ':phone' => $phone,
            ':mgr' => $mgr !== '' ? $mgr : null,
        ]);
        $newBranchId = (int) $pdo->lastInsertId();

        AuditLogger::log('branch.created', 'branches', $newBranchId, 'Created branch ' . $code . ' (' . $name . ')', [], (int) $user['id']);
        $_SESSION['flash_success'] = $locale === 'so' ? "Laanta cusub ({$name} - {$code}) si guul leh ayaa loo abuuray." : "New branch {$name} ({$code}) created successfully.";
        header('Location: ?route=branches');
        exit;
    }

    private static function branchToggle(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $branchId = (int) ($_POST['branch_id'] ?? 0);
        $st = $pdo->prepare('SELECT id, name, is_active FROM branches WHERE id = :id');
        $st->execute([':id' => $branchId]);
        $b = $st->fetch();
        if ($b) {
            $newStatus = $b['is_active'] ? 0 : 1;
            $pdo->prepare('UPDATE branches SET is_active = :act WHERE id = :id')->execute([':act' => $newStatus, ':id' => $branchId]);
            AuditLogger::log('branch.toggle', 'branches', $branchId, 'Toggled branch status to ' . ($newStatus ? 'Active' : 'Inactive'), [], (int) $user['id']);
            $statusText = $newStatus
                ? (Lang::locale() === 'so' ? 'waa la hawlgeliyay (Firfircoon)' : 'is now Active')
                : (Lang::locale() === 'so' ? 'waa la hakiyay (Xiran)' : 'is now Inactive');
            $_SESSION['flash_success'] = Lang::locale() === 'so'
                ? 'Xaaladda laanta (' . $b['name'] . ') si guul leh ayaa loo beddelay: ' . $statusText . '.'
                : 'Branch (' . $b['name'] . ') status updated: ' . $statusText . '.';
        }
        header('Location: ?route=branches');
        exit;
    }

    private static function userPermissionsSave(PDO $pdo): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();

        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $st = $pdo->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
        $st->execute([':id' => $targetUserId]);
        $targetUser = $st->fetch();

        if (!$targetUser) {
            throw new \RuntimeException($locale === 'so' ? 'Isticmaalaha lama helin.' : 'User not found.');
        }

        if ($targetUser['role_slug'] === 'admin') {
            throw new \RuntimeException($locale === 'so' ? 'Awoodaha Admin-ka lama xaddidi karo.' : 'Admin permissions cannot be restricted.');
        }

        $allPerms = $pdo->query('SELECT slug FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
        $selectedPerms = (array) ($_POST['perms'] ?? []);

        // Delete existing user overrides
        $pdo->prepare('DELETE FROM user_permissions WHERE user_id = :uid')->execute([':uid' => $targetUserId]);

        // Insert new overrides
        $ins = $pdo->prepare('INSERT INTO user_permissions (user_id, permission_slug, is_granted) VALUES (:uid, :slug, :granted)');
        foreach ($allPerms as $slug) {
            $isGranted = in_array($slug, $selectedPerms, true) ? 1 : 0;
            $ins->execute([
                ':uid' => $targetUserId,
                ':slug' => $slug,
                ':granted' => $isGranted,
            ]);
        }

        AuditLogger::log(
            'permissions.updated',
            'users',
            $targetUserId,
            "Admin updated custom permissions for {$targetUser['full_name']} ({$targetUser['username']})",
            ['granted_count' => count($selectedPerms)],
            (int) $admin['id']
        );

        $_SESSION['flash_success'] = $locale === 'so'
            ? "Awoodaha isticmaalaha ({$targetUser['full_name']}) si guul leh ayaa loo xaddiday oo loo keydiyay!"
            : "Permissions for {$targetUser['full_name']} updated successfully!";

        header('Location: ?route=permissions&user_id=' . $targetUserId);
        exit;
    }

    private static function userCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $creatorRole = (string) ($user['role_slug'] ?? '');
        $locale = Lang::locale();

        $targetRoleId = (int) ($_POST['role_id'] ?? 0);
        $st = $pdo->prepare("SELECT id, slug, name_en, name_so FROM roles WHERE id = :id");
        $st->execute([':id' => $targetRoleId]);
        $targetRole = $st->fetch();
        if (!$targetRole) {
            throw new \RuntimeException($locale === 'so' ? 'Doorka la doortay lama helin.' : 'Selected role was not found.');
        }
        $targetRoleSlug = (string) ($targetRole['slug'] ?? '');

        // Strict Hierarchy Rule Enforcement:
        // Admin -> [manager, branch_manager, loan_officer, client] (cannot create admin)
        // Manager -> [branch_manager, loan_officer, client] (cannot create admin or manager)
        // Branch Manager -> [loan_officer, client] (cannot create admin, manager, or branch_manager)
        $allowedRoleSlugs = match ($creatorRole) {
            'admin' => ['manager', 'branch_manager', 'loan_officer', 'client'],
            'manager' => ['branch_manager', 'loan_officer', 'client'],
            'branch_manager' => ['loan_officer', 'client'],
            default => [],
        };

        if (!in_array($targetRoleSlug, $allowedRoleSlugs, true)) {
            throw new \RuntimeException(
                $locale === 'so'
                    ? "Ma laguu ogola inaad abuurto isticmaale leh doorkaan. Xeerka nidaamku ma ogola inaad abuurto qof kula awood ah ama kaa sarreeya."
                    : "You are not authorized to create a user with this role. System rules prohibit creating accounts with equal or higher authority."
            );
        }

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($fullName === '' || $username === '' || $email === '') {
            throw new \RuntimeException(
                $locale === 'so'
                    ? 'Fadlan buuxi magaca buuxa, username-ka, iyo email-ka.'
                    : 'Please provide full name, username, and email address.'
            );
        }

        // Check if username or email already exists
        $dupSt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $dupSt->execute([':u' => $username, ':e' => $email]);
        if ($dupSt->fetch()) {
            throw new \RuntimeException(
                $locale === 'so'
                    ? 'Username-kaan ama Email-kaan horey ayaa looga diiwaangeliyay nidaamka. Fadlan dooro mid kale.'
                    : 'This username or email address is already registered in the system.'
            );
        }

        // Branch assignment logic:
        // 1. Manager: NOT assigned to a specific branch (Manages company-wide / all branches).
        // 2. Branch Manager & Employee (loan_officer): MUST be assigned to a specific branch!
        $branchInfo = null;
        if ($targetRoleSlug === 'manager') {
            $branchId = null;
        } elseif ($creatorRole === 'branch_manager') {
            $branchId = (int) ($user['branch_id'] ?? 0);
            if ($branchId <= 0) {
                throw new \RuntimeException($locale === 'so' ? 'Akoonkaaga maamule kuma xirna laan gaar ah.' : 'Your branch manager account is not assigned to a valid branch.');
            }
            $bSt = $pdo->prepare("SELECT id, code, name, city FROM branches WHERE id = :bid");
            $bSt->execute([':bid' => $branchId]);
            $branchInfo = $bSt->fetch() ?: null;
        } else {
            $rawBranchId = !empty($_POST['branch_id']) ? (int) $_POST['branch_id'] : 0;
            if (in_array($targetRoleSlug, ['branch_manager', 'loan_officer'], true) && $rawBranchId <= 0) {
                throw new \RuntimeException(
                    $locale === 'so'
                        ? 'Fadlan dooro laanta loo qoondeeyey isticmaalahan. Maamulaha Laanta (Branch Manager) iyo Header Office waa inay laan gaar ah leeyihiin.'
                        : 'Please select a specific branch for this user. Branch Managers and Staff must be assigned to a specific branch.'
                );
            }
            $branchId = $rawBranchId > 0 ? $rawBranchId : null;
            if ($branchId) {
                $bSt = $pdo->prepare("SELECT id, code, name, city FROM branches WHERE id = :bid");
                $bSt->execute([':bid' => $branchId]);
                $branchInfo = $bSt->fetch() ?: null;
            }
        }

        // Dynamic Default Password Generation (e.g. "ali8788@!"):
        $inputPassword = trim((string) ($_POST['password'] ?? ''));
        if ($inputPassword === '' || $inputPassword === 'Staff@123') {
            $plainPassword = MailService::generateDefaultPassword($fullName);
        } else {
            $plainPassword = $inputPassword;
        }

        $stmt = $pdo->prepare('
            INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, is_active)
            VALUES (:rid, :bid, :name, :uname, :email, :phone, :phash, 1)
        ');
        $stmt->execute([
            ':rid' => $targetRoleId,
            ':bid' => $branchId,
            ':name' => $fullName,
            ':uname' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':phash' => Security::hashPassword($plainPassword),
        ]);
        $newUserId = (int) $pdo->lastInsertId();

        AuditLogger::log('user.created', 'users', $newUserId, 'Created user ' . $username . ' (' . $targetRoleSlug . ')', [], (int) $user['id']);

        // Send Email notification with credentials, role, and branch details
        $mailStatus = MailService::sendWelcomeUserEmail(
            [
                'id' => $newUserId,
                'full_name' => $fullName,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
            ],
            $plainPassword,
            $branchInfo,
            $targetRole,
            $locale
        );

        if ($mailStatus['success']) {
            AuditLogger::log('mail.welcome_sent', 'users', $newUserId, 'Welcome credentials email sent to ' . $email, [], (int) $user['id']);
            $_SESSION['flash_success'] = $locale === 'so'
                ? "Akoonka ({$fullName}) si guul leh ayaa loo diiwaangeliyay! Furaha sirta ah ee loo dhalay waa ({$plainPassword}), waxaana email faahfaahsan loogu diray: {$email}."
                : "User account for {$fullName} created successfully! Generated password is ({$plainPassword}), and welcome details were emailed to {$email}.";
        } else {
            AuditLogger::log('mail.welcome_failed', 'users', $newUserId, 'Failed sending email to ' . $email . ': ' . ($mailStatus['error'] ?? 'Unknown'), [], (int) $user['id']);
            $_SESSION['flash_warning'] = $locale === 'so'
                ? "Akoonka ({$fullName}) waa la abuuray (Furaha sirta ah: {$plainPassword}), laakiin email-ka lama diri karin: " . ($mailStatus['error'] ?? '')
                : "User account created (Password: {$plainPassword}), but email notification failed: " . ($mailStatus['error'] ?? '');
        }

        header('Location: ?route=' . self::userListRoute());
        exit;
    }

    private static function userUpdate(PDO $pdo): void
    {
        $actor = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $locale = Lang::locale();
        $actorRole = (string) ($actor['role_slug'] ?? '');
        $actorId = (int) ($actor['id'] ?? 0);

        if ($targetUserId <= 0) {
            throw new \RuntimeException($locale === 'so' ? 'Isticmaalaha lama helin.' : 'Invalid user ID.');
        }

        // Fetch existing target user
        $stmt = $pdo->prepare('
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id
        ');
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch();
        if (!$targetUser) {
            throw new \RuntimeException($locale === 'so' ? 'Isticmaalaha lama helin.' : 'User not found.');
        }

        $targetRoleSlug = (string) ($targetUser['role_slug'] ?? '');

        // Strict Hierarchy check on target
        if ($actorRole === 'manager') {
            if ($targetRoleSlug === 'admin' || ($targetRoleSlug === 'manager' && $targetUserId !== $actorId)) {
                throw new \RuntimeException($locale === 'so' ? 'Awood uma lihid inaad wax ka beddesho maamulaha kale ama Admin-ka.' : 'Unauthorized to edit Admin or General Manager.');
            }
        } elseif ($actorRole === 'branch_manager') {
            if ($targetUserId !== $actorId && !in_array($targetRoleSlug, ['loan_officer', 'client'], true)) {
                throw new \RuntimeException($locale === 'so' ? 'Waxaad wax ka beddeli kartaa kaliya shaqaalaha ama macaamiisha laantaada.' : 'Unauthorized to edit this user role.');
            }
            if ($targetUserId !== $actorId && (int) ($targetUser['branch_id'] ?? 0) !== (int) ($actor['branch_id'] ?? 0)) {
                throw new \RuntimeException($locale === 'so' ? 'Isticmaalahaan kama tirsana laantaada.' : 'User does not belong to your branch.');
            }
        }

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($fullName === '' || $username === '' || $email === '') {
            throw new \RuntimeException($locale === 'so' ? 'Fadlan buuxi magaca buuxa, username-ka, iyo email-ka.' : 'Please provide full name, username, and email.');
        }

        // The admin's own email and password change only through Profile, which requires an OTP.
        if ($actorRole === 'admin' && $targetUserId === $actorId
            && (strcasecmp($email, (string) $targetUser['email']) !== 0 || trim((string) ($_POST['password'] ?? '')) !== '')) {
            throw new \RuntimeException(self::tr(
                'Email-kaaga iyo password-kaaga waxaad ka beddeli kartaa Profile-ka oo keliya, adigoo OTP isticmaalaya.',
                'Your own email and password can only be changed from Profile, using an OTP.',
                'لا يمكن تغيير بريدك الإلكتروني وكلمة مرورك إلا من الملف الشخصي باستخدام رمز التحقق.'
            ));
        }

        // Check duplicates for username/email excluding this user
        $dupSt = $pdo->prepare("SELECT id FROM users WHERE (username = :u OR email = :e) AND id != :uid LIMIT 1");
        $dupSt->execute([':u' => $username, ':e' => $email, ':uid' => $targetUserId]);
        if ($dupSt->fetch()) {
            throw new \RuntimeException($locale === 'so' ? 'Username-kaan ama Email-kaan waxaa horey u isticmaalay qof kale.' : 'This username or email is already taken by another user.');
        }

        // Role & Branch assignment
        $newRoleId = (int) $targetUser['role_id'];
        $newBranchId = $targetUser['branch_id'] !== null ? (int) $targetUser['branch_id'] : null;

        // Role update logic
        // Nobody changes their own role; only admin/manager may change other users' roles.
        if (isset($_POST['role_id']) && $targetUserId !== $actorId && ($actorRole === 'admin' || $actorRole === 'manager')) {
            $reqRoleId = (int) $_POST['role_id'];
            $stR = $pdo->prepare("SELECT id, slug FROM roles WHERE id = :id");
            $stR->execute([':id' => $reqRoleId]);
            $newRoleObj = $stR->fetch();
            if ($newRoleObj) {
                $newSlug = (string) $newRoleObj['slug'];
                // Prevent creating/promoting to Admin
                if ($newSlug === 'admin' && $targetRoleSlug !== 'admin') {
                    throw new \RuntimeException($locale === 'so' ? 'Lama magacaabi karo Admin cusub xeerka nidaamka dartiis.' : 'System policy prevents assigning Administrator role.');
                }
                // Manager cannot promote to admin or manager
                if ($actorRole === 'manager' && in_array($newSlug, ['admin', 'manager'], true)) {
                    throw new \RuntimeException($locale === 'so' ? 'Awood uma lihid inaad doorkaan u magacowdo.' : 'Unauthorized role assignment.');
                }
                $newRoleId = (int) $newRoleObj['id'];
                $targetRoleSlug = $newSlug;
            }
        }

        // Branch assignment logic
        if ($targetRoleSlug === 'manager' || $targetRoleSlug === 'admin') {
            $newBranchId = null; // company-wide / HQ
        } elseif (isset($_POST['branch_id'])) {
            $rawB = (int) $_POST['branch_id'];
            if (in_array($targetRoleSlug, ['branch_manager', 'loan_officer'], true) && $rawB <= 0) {
                throw new \RuntimeException($locale === 'so' ? 'Fadlan dooro laanta shaqaalaha.' : 'Please select a branch.');
            }
            $newBranchId = $rawB > 0 ? $rawB : null;
        }

        // Active status update if provided in edit form
        $newIsActive = isset($_POST['is_active']) ? (int) $_POST['is_active'] : (int) $targetUser['is_active'];
        // Cannot deactivate yourself
        if ($targetUserId === $actorId && $newIsActive === 0) {
            $newIsActive = 1;
        }

        // Optional password update
        $newPassword = trim((string) ($_POST['password'] ?? ''));

        if ($newPassword !== '') {
            $passHash = Security::hashPassword($newPassword);
            $updStmt = $pdo->prepare('
                UPDATE users
                SET full_name = :fname,
                    username = :uname,
                    email = :email,
                    phone = :phone,
                    role_id = :rid,
                    branch_id = :bid,
                    is_active = :act,
                    password_hash = :phash,
                    current_session_token = NULL
                WHERE id = :id
            ');
            $updStmt->execute([
                ':fname' => $fullName,
                ':uname' => $username,
                ':email' => $email,
                ':phone' => $phone,
                ':rid' => $newRoleId,
                ':bid' => $newBranchId,
                ':act' => $newIsActive,
                ':phash' => $passHash,
                ':id' => $targetUserId,
            ]);
        } else {
            $updStmt = $pdo->prepare('
                UPDATE users
                SET full_name = :fname,
                    username = :uname,
                    email = :email,
                    phone = :phone,
                    role_id = :rid,
                    branch_id = :bid,
                    is_active = :act
                WHERE id = :id
            ');
            $updStmt->execute([
                ':fname' => $fullName,
                ':uname' => $username,
                ':email' => $email,
                ':phone' => $phone,
                ':rid' => $newRoleId,
                ':bid' => $newBranchId,
                ':act' => $newIsActive,
                ':id' => $targetUserId,
            ]);
        }

        AuditLogger::log('user.updated', 'users', $targetUserId, 'Updated details for user ' . $username, [
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $newRoleId,
            'branch_id' => $newBranchId,
            'is_active' => $newIsActive,
            'password_changed' => ($newPassword !== ''),
        ], $actorId);

        $sendCreds = !empty($_POST['send_credentials_email']) || ($newPassword !== '');
        if ($sendCreds && $email !== '') {
            $plainCredPassword = ($newPassword !== '') ? $newPassword : MailService::generateDefaultPassword($fullName);
            if ($newPassword === '') {
                $pdo->prepare('UPDATE users SET password_hash = :phash, current_session_token = NULL WHERE id = :id')
                    ->execute([':phash' => Security::hashPassword($plainCredPassword), ':id' => $targetUserId]);
            }

            $branchInfo = null;
            if ($newBranchId !== null) {
                $bSt = $pdo->prepare('SELECT id, name, code, city FROM branches WHERE id = :id');
                $bSt->execute([':id' => $newBranchId]);
                $branchInfo = $bSt->fetch() ?: null;
            }

            $rSt = $pdo->prepare('SELECT id, slug, name_so, name_en FROM roles WHERE id = :id');
            $rSt->execute([':id' => $newRoleId]);
            $roleInfo = $rSt->fetch() ?: ['slug' => $targetRoleSlug, 'name_so' => $targetRoleSlug, 'name_en' => $targetRoleSlug];

            $mailStatus = MailService::sendWelcomeUserEmail(
                [
                    'id' => $targetUserId,
                    'full_name' => $fullName,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                ],
                $plainCredPassword,
                $branchInfo,
                $roleInfo,
                $locale
            );

            if ($mailStatus['success']) {
                AuditLogger::log('mail.welcome_sent', 'users', $targetUserId, 'Updated credentials email sent to ' . $email, [], $actorId);
                $_SESSION['flash_success'] = $locale === 'so'
                    ? "Macluumaadka ({$fullName}) waa la cusbooneysiiyay! Furaha sirta ah ({$plainCredPassword}) waxaana loogu diray email-ka: {$email}."
                    : "User ({$fullName}) details updated! Password ({$plainCredPassword}) and credentials were sent to: {$email}.";
            } else {
                AuditLogger::log('mail.welcome_failed', 'users', $targetUserId, 'Failed sending email to ' . $email . ': ' . ($mailStatus['error'] ?? ''), [], $actorId);
                $_SESSION['flash_warning'] = $locale === 'so'
                    ? "Macluumaadka waa la cusbooneysiiyay, laakiin email-ka lama diri karin: " . ($mailStatus['error'] ?? '')
                    : "User updated, but credentials email failed: " . ($mailStatus['error'] ?? '');
            }
        } else {
            $_SESSION['flash_success'] = $locale === 'so'
                ? 'Macluumaadka isticmaalaha (' . $fullName . ') si guul leh ayaa loo cusbooneysiiyay!'
                : 'User (' . $fullName . ') details successfully updated!';
        }

        header('Location: ?route=' . self::userListRoute());
        exit;
    }

    private static function userToggleStatus(PDO $pdo): void
    {
        $actor = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $locale = Lang::locale();
        $actorRole = (string) ($actor['role_slug'] ?? '');
        $actorId = (int) ($actor['id'] ?? 0);

        if ($targetUserId <= 0) {
            throw new \RuntimeException($locale === 'so' ? 'Isticmaalaha lama helin.' : 'Invalid user ID.');
        }

        // Cannot deactivate yourself
        if ($targetUserId === $actorId) {
            throw new \RuntimeException(
                $locale === 'so'
                    ? 'Ma damin kartid (deactivate) akoonkaaga aad hadda ku jirto!'
                    : 'You cannot deactivate your own active session account!'
            );
        }

        // Fetch target user with role
        $stmt = $pdo->prepare('
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id
        ');
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch();
        if (!$targetUser) {
            throw new \RuntimeException($locale === 'so' ? 'Isticmaalaha lama helin.' : 'User not found.');
        }

        $targetRoleSlug = (string) ($targetUser['role_slug'] ?? '');

        // Hierarchy rule check
        if ($actorRole === 'manager') {
            if (in_array($targetRoleSlug, ['admin', 'manager'], true)) {
                throw new \RuntimeException($locale === 'so' ? 'Awood uma lihid inaad wax ka beddesho maamulaha kale ama Admin-ka.' : 'Unauthorized to change status of Admin or General Manager.');
            }
        } elseif ($actorRole === 'branch_manager') {
            if ($targetRoleSlug !== 'loan_officer' && $targetRoleSlug !== 'client') {
                throw new \RuntimeException($locale === 'so' ? 'Waxaad beddeli kartaa kaliya shaqaalaha ama macaamiisha laantaada.' : 'Unauthorized to change status of this user role.');
            }
            if ((int) ($targetUser['branch_id'] ?? 0) !== (int) ($actor['branch_id'] ?? 0)) {
                throw new \RuntimeException($locale === 'so' ? 'Isticmaalahaan kama tirsana laantaada.' : 'User does not belong to your branch.');
            }
        }

        $newStatus = (int) $targetUser['is_active'] === 1 ? 0 : 1;

        if ($newStatus === 0) {
            // Deactivating: clear session token to kick user immediately
            $upd = $pdo->prepare('UPDATE users SET is_active = 0, current_session_token = NULL WHERE id = :id');
            $upd->execute([':id' => $targetUserId]);
            AuditLogger::log('user.deactivated', 'users', $targetUserId, 'Deactivated user ' . $targetUser['username'], [], $actorId);
            $_SESSION['flash_warning'] = $locale === 'so'
                ? 'Akoonka (' . $targetUser['full_name'] . ') waa la damiyay (Deactivated). Gelitaankiisii nidaamka waa la hakiyay.'
                : 'Account for (' . $targetUser['full_name'] . ') has been deactivated and access is revoked.';
        } else {
            // Activating: reset lockouts
            $upd = $pdo->prepare('
                UPDATE users
                SET is_active = 1,
                    failed_login_attempts = 0,
                    lockout_tier = 0,
                    locked_until = NULL,
                    security_block_reason = NULL
                WHERE id = :id
            ');
            $upd->execute([':id' => $targetUserId]);
            AuditLogger::log('user.activated', 'users', $targetUserId, 'Activated user ' . $targetUser['username'], [], $actorId);
            $_SESSION['flash_success'] = $locale === 'so'
                ? 'Akoonka (' . $targetUser['full_name'] . ') si buuxda ayaa dib loogu hawlgeliyay (Activated)!'
                : 'Account for (' . $targetUser['full_name'] . ') has been successfully activated!';
        }

        header('Location: ?route=' . self::userListRoute());
        exit;
    }

    private static function userSecurityReset(PDO $pdo): void
    {
        $admin = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $locale = Lang::locale();

        if ($targetUserId > 0) {
            AuthService::adminUnblockUser($targetUserId, $pdo, (int) $admin['id']);
            $_SESSION['flash_success'] = $locale === 'so'
                ? 'Xanibaaddii amniga waa laga qaaday isticmaalaha, akoonkana dib ayaa loo hawlgeliyay!'
                : 'Security restriction cleared and user account successfully restored!';
        }

        header('Location: ?route=' . self::userListRoute());
        exit;
    }

    private static function userResendCredentials(PDO $pdo): void
    {
        $actor = AuthMiddleware::requireRoles(['admin', 'manager', 'branch_manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $locale = Lang::locale();
        $isSo = ($locale === 'so');
        $actorId = (int) ($actor['id'] ?? 0);
        $actorRole = (string) ($actor['role_slug'] ?? '');

        if ($targetUserId <= 0) {
            throw new \RuntimeException($isSo ? 'Isticmaalaha lama helin.' : 'Invalid user ID.');
        }

        $st = $pdo->prepare('
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en,
                   b.name AS branch_name, b.code AS branch_code, b.city AS branch_city
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            WHERE u.id = :id
        ');
        $st->execute([':id' => $targetUserId]);
        $targetUser = $st->fetch();
        if (!$targetUser) {
            throw new \RuntimeException($isSo ? 'Isticmaalaha lama helin.' : 'User not found.');
        }

        $targetRoleSlug = (string) ($targetUser['role_slug'] ?? '');

        // Hierarchy access check
        if ($actorRole === 'manager' && in_array($targetRoleSlug, ['admin', 'manager'], true) && $targetUserId !== $actorId) {
            throw new \RuntimeException($isSo ? 'Awood uma lihid inaad email u dirto maamulaha kale.' : 'Unauthorized.');
        } elseif ($actorRole === 'branch_manager') {
            if (!in_array($targetRoleSlug, ['loan_officer', 'client'], true) && $targetUserId !== $actorId) {
                throw new \RuntimeException($isSo ? 'Waxaad email u diri kartaa kaliya shaqaalaha laantaada.' : 'Unauthorized.');
            }
        }

        $targetEmail = trim((string) ($targetUser['email'] ?? ''));
        if ($targetEmail === '') {
            throw new \RuntimeException($isSo ? 'Isticmaalahani ma laha email diiwaangashan.' : 'This user does not have a registered email address.');
        }

        // Use custom password if provided, or generate a fresh one
        $customPassword = trim((string) ($_POST['custom_password'] ?? ''));
        $plainPassword = ($customPassword !== '') ? $customPassword : MailService::generateDefaultPassword($targetUser['full_name']);

        // Update password hash in DB and reset lockout
        $passHash = Security::hashPassword($plainPassword);
        $upd = $pdo->prepare('UPDATE users SET password_hash = :phash, current_session_token = NULL, failed_login_attempts = 0, locked_until = NULL WHERE id = :id');
        $upd->execute([':phash' => $passHash, ':id' => $targetUserId]);

        $branchInfo = !empty($targetUser['branch_name']) ? [
            'id' => $targetUser['branch_id'],
            'name' => $targetUser['branch_name'],
            'code' => $targetUser['branch_code'],
            'city' => $targetUser['branch_city'],
        ] : null;

        $roleInfo = [
            'slug' => $targetUser['role_slug'],
            'name_so' => $targetUser['role_name_so'],
            'name_en' => $targetUser['role_name_en'],
        ];

        $mailStatus = MailService::sendWelcomeUserEmail(
            $targetUser,
            $plainPassword,
            $branchInfo,
            $roleInfo,
            $locale
        );

        if ($mailStatus['success']) {
            AuditLogger::log('mail.welcome_sent', 'users', $targetUserId, 'Credentials email re-sent to ' . $targetEmail, [], $actorId);
            $_SESSION['flash_success'] = $isSo
                ? "Email-ka macluumaadka gelitaanka si toos ah ayaa loogu diray: {$targetEmail}! Furaha cusub ee loo dhalay waa: ({$plainPassword})."
                : "Credentials email sent successfully to {$targetEmail}! New password is: ({$plainPassword}).";
        } else {
            AuditLogger::log('mail.welcome_failed', 'users', $targetUserId, 'Failed re-sending credentials to ' . $targetEmail . ': ' . ($mailStatus['error'] ?? ''), [], $actorId);
            $_SESSION['flash_error'] = $isSo
                ? "Dirista email-ka way fashilantay: " . ($mailStatus['error'] ?? 'Khalad aan la garanayn')
                : "Email delivery failed: " . ($mailStatus['error'] ?? 'Unknown error');
        }

        header('Location: ?route=' . self::userListRoute());
        exit;
    }

    private static function mailTestSend(PDO $pdo): void
    {
        $actor = AuthMiddleware::requireRoles(['admin', 'manager']);
        AuthMiddleware::verifyCsrfOrAbort();
        $locale = Lang::locale();
        $isSo = ($locale === 'so');
        $testEmail = trim((string) ($_POST['test_email'] ?? ''));

        if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException($isSo ? 'Fadlan geli email sax ah oo la tijaabiyo.' : 'Please enter a valid email address to test.');
        }

        $subject = $isSo ? 'SIMDA: Tijaabada Email-ka Nidaamka (Delivery Test)' : 'SIMDA: System Email Delivery Test';
        $timeStr = date('d/m/Y H:i:s');
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#F0F4F2;padding:25px;color:#1D2D27;">
    <div style="max-width:540px;margin:0 auto;background:#fff;border-radius:14px;padding:30px;border:1px solid #D1DDD7;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
        <h2 style="color:#0C2D24;margin-top:0;">✅ SIMDA Email Delivery Test</h2>
        <p>Email-kani wuxuu xaqiijinayaa in adeegga dirista fariimaha ee nidaamka SIMDA uu 100% u shaqaynayo, fariintuna ay si toos ah u soo gashay <strong>Inbox-kaaga</strong> (sanduuqa hore).</p>
        <div style="background:#EBF5F0;padding:14px 18px;border-radius:8px;font-size:14px;color:#0F5132;margin:20px 0;border:1px solid #C3E6CB;">
            <strong>Waqtiga La Diray:</strong> {$timeStr}<br>
            <strong>Xaaladda:</strong> Guul leh (Delivered via TLS/SSL directly to Inbox)
        </div>
        <p style="font-size:12px;color:#788E84;margin-bottom:0;">SIMDA - Somali Islamic Microfinance Development Association</p>
    </div>
</body>
</html>
HTML;
        $plain = "SIMDA Email Delivery Test\n\nAdeegga SMTP ee nidaamka SIMDA wuxuu u shaqaynayaa 100%!\nWaqtiga: {$timeStr}\nFariintu waxay toos u soo gashay Inbox-kaaga.";

        $res = MailService::send($testEmail, $subject, $html, $plain);
        if ($res['success']) {
            AuditLogger::log('mail.test_sent', 'settings', 0, 'Test email delivered to ' . $testEmail, [], (int) $actor['id']);
            $_SESSION['flash_success'] = $isSo
                ? "Email-ka tijaabada ah si guul leh ayaa loogu diray: {$testEmail}! Fadlan fiiri sanduuqaaga (Inbox)."
                : "Test email successfully sent to {$testEmail}! Please check your Inbox.";
        } else {
            $_SESSION['flash_error'] = $isSo
                ? "Dirista email-ka way fashilantay: " . ($res['error'] ?? '')
                : "Test email delivery failed: " . ($res['error'] ?? '');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '?route=settings';
        header('Location: ' . $referer);
        exit;
    }

    /** Page the user list actions return to, limited to the known list pages. */
    private static function userListRoute(): string
    {
        $route = (string) ($_POST['return_route'] ?? 'users');
        return in_array($route, ['users', 'managers', 'branch_managers', 'header_office', 'employees'], true) ? $route : 'users';
    }

    private static function tr(string $so, string $en, string $ar): string
    {
        return match (Lang::locale()) {
            'so' => $so,
            'ar' => $ar,
            default => $en,
        };
    }

    private static function redirectToProfile(): never
    {
        header('Location: ?route=profile');
        exit;
    }

    private static function ownAccount(PDO $pdo, int $userId): array
    {
        $st = $pdo->prepare('
            SELECT u.*, r.name_so AS role_name_so, r.name_en AS role_name_en, b.name AS branch_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            WHERE u.id = :id AND u.deleted_at IS NULL
        ');
        $st->execute([':id' => $userId]);
        $account = $st->fetch();
        if (!$account) {
            throw new \RuntimeException(Lang::get('unauthorized_access'));
        }
        return $account;
    }

    /** The pending email or password change of this user, or null if there is none or it expired. */
    private static function pendingProfileOtp(int $userId): ?array
    {
        $otp = $_SESSION['profile_otp'] ?? null;
        if (!is_array($otp) || (int) $otp['user_id'] !== $userId || (int) $otp['expires_at'] < time()) {
            unset($_SESSION['profile_otp']);
            return null;
        }
        return $otp;
    }

    /** Emails a new code and stores the pending change. Nothing changes in the database until it is verified. */
    private static function sendProfileOtp(array $account, string $purpose, string $target, array $payload, int $resends = 0): void
    {
        unset($_SESSION['profile_otp']);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $mail = MailService::sendProfileOtpEmail($target, (string) $account['full_name'], $code, $purpose, Lang::locale());
        if (!$mail['success']) {
            AuditLogger::log('profile.otp_mail_failed', 'users', (int) $account['id'], 'Could not send profile OTP: ' . ($mail['error'] ?? 'unknown'), [], (int) $account['id']);
            throw new \RuntimeException(self::tr(
                'Email-ka OTP-ga lama diri karin, wax isbeddel ahna lama samayn. Fadlan mar kale isku day.',
                'The OTP email could not be sent, so nothing was changed. Please try again.',
                'تعذر إرسال رمز التحقق، ولم يتم تغيير أي شيء. يرجى المحاولة مرة أخرى.'
            ));
        }

        $_SESSION['profile_otp'] = [
            'user_id' => (int) $account['id'],
            'purpose' => $purpose,
            'target' => $target,
            'payload' => $payload,
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + self::PROFILE_OTP_TTL,
            'sent_at' => time(),
            'attempts' => 0,
            'resends' => $resends,
        ];
    }

    private static function requireCurrentPassword(array $account): void
    {
        if (!Security::verifyPassword((string) ($_POST['current_password'] ?? ''), (string) $account['password_hash'])) {
            AuditLogger::log('profile.wrong_password', 'users', (int) $account['id'], 'Wrong current password on a profile change request.', [], (int) $account['id']);
            throw new \RuntimeException(self::tr('Password-ka hadda aad isticmaasho waa khalad.', 'Your current password is incorrect.', 'كلمة المرور الحالية غير صحيحة.'));
        }
    }

    private static function profile(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        self::renderDashboard('admin/profile.php', [
            'pageTitle' => 'Profile',
            'account' => self::ownAccount($pdo, (int) $user['id']),
            'otp' => self::pendingProfileOtp((int) $user['id']),
            'otpCooldown' => self::PROFILE_OTP_COOLDOWN,
            'otpMaxResends' => self::PROFILE_OTP_MAX_RESENDS,
        ]);
    }

    /** Full name and phone are saved directly, with no OTP. */
    private static function profileUpdate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($fullName === '' || mb_strlen($fullName) > 120 || $phone === '' || mb_strlen($phone) > 30) {
            throw new \RuntimeException(self::tr('Fadlan geli magac iyo telefoon sax ah.', 'Please enter a valid name and phone number.', 'يرجى إدخال اسم ورقم هاتف صحيحين.'));
        }

        $pdo->prepare('UPDATE users SET full_name = :name, phone = :phone WHERE id = :id')
            ->execute([':name' => $fullName, ':phone' => $phone, ':id' => (int) $user['id']]);
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['phone'] = $phone;
        AuditLogger::log('profile.updated', 'users', (int) $user['id'], 'Admin updated own name or phone.', [], (int) $user['id']);

        $_SESSION['flash_success'] = self::tr('Macluumaadkaaga waa la keydiyay.', 'Your details were saved.', 'تم حفظ بياناتك.');
        self::redirectToProfile();
    }

    /** The code goes to the new address, which proves the admin controls it before 2FA codes are sent there. */
    private static function profileEmailRequest(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $account = self::ownAccount($pdo, (int) $user['id']);
        $newEmail = trim((string) ($_POST['new_email'] ?? ''));

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(self::tr('Fadlan geli email sax ah.', 'Please enter a valid email address.', 'يرجى إدخال بريد إلكتروني صحيح.'));
        }
        if (strcasecmp($newEmail, (string) $account['email']) === 0) {
            throw new \RuntimeException(self::tr('Email-kani waa kan aad hadda isticmaasho.', 'This is already your email address.', 'هذا هو بريدك الإلكتروني الحالي.'));
        }
        self::requireCurrentPassword($account);
        self::assertEmailAvailable($pdo, $newEmail, (int) $account['id']);

        self::sendProfileOtp($account, 'email', $newEmail, ['new_email' => $newEmail]);
        $_SESSION['flash_success'] = self::tr('OTP waxaa loo diray ' . $newEmail . '.', 'An OTP was sent to ' . $newEmail . '.', 'تم إرسال رمز التحقق إلى ' . $newEmail . '.');
        self::redirectToProfile();
    }

    private static function assertEmailAvailable(PDO $pdo, string $email, int $userId): void
    {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $st->execute([':email' => $email, ':id' => $userId]);
        if ($st->fetch()) {
            throw new \RuntimeException(self::tr('Email-kan qof kale ayaa isticmaala.', 'This email address is already in use.', 'هذا البريد الإلكتروني مستخدم بالفعل.'));
        }
    }

    private static function profilePasswordRequest(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $account = self::ownAccount($pdo, (int) $user['id']);
        $newPassword = (string) ($_POST['new_password'] ?? '');

        self::requireCurrentPassword($account);
        if (strlen($newPassword) < 8) {
            throw new \RuntimeException(self::tr('Password-ka cusub waa inuu ahaadaa ugu yaraan 8 xaraf.', 'The new password must be at least 8 characters.', 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف.'));
        }
        if ($newPassword !== (string) ($_POST['confirm_password'] ?? '')) {
            throw new \RuntimeException(self::tr('Labada password ee cusub isma waafaqsana.', 'The two new passwords do not match.', 'كلمتا المرور الجديدتان غير متطابقتين.'));
        }
        if (Security::verifyPassword($newPassword, (string) $account['password_hash'])) {
            throw new \RuntimeException(self::tr('Password-ka cusub waa inuu ka duwanaadaa kan hadda.', 'The new password must differ from the current one.', 'يجب أن تختلف كلمة المرور الجديدة عن الحالية.'));
        }

        $target = (string) $account['email'];
        self::sendProfileOtp($account, 'password', $target, ['new_password_hash' => Security::hashPassword($newPassword)]);
        $_SESSION['flash_success'] = self::tr('OTP waxaa loo diray ' . $target . '.', 'An OTP was sent to ' . $target . '.', 'تم إرسال رمز التحقق إلى ' . $target . '.');
        self::redirectToProfile();
    }

    private static function profileOtpVerify(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $userId = (int) $user['id'];
        $otp = self::pendingProfileOtp($userId);
        if ($otp === null) {
            throw new \RuntimeException(self::tr('OTP-gu waa dhacay. Fadlan mar kale codso.', 'The OTP has expired. Please request a new one.', 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'));
        }

        $code = trim((string) ($_POST['otp_code'] ?? ''));
        if (!hash_equals((string) $otp['code_hash'], hash('sha256', $code))) {
            $attempts = (int) $otp['attempts'] + 1;
            if ($attempts >= self::PROFILE_OTP_MAX_ATTEMPTS) {
                unset($_SESSION['profile_otp']);
                AuditLogger::log('profile.otp_locked', 'users', $userId, 'Profile OTP cancelled after too many wrong codes.', ['purpose' => $otp['purpose']], $userId);
                $_SESSION['flash_error'] = self::tr('Koodh khaldan ayaa marar badan la geliyay, codsigiina waa la joojiyay. Wax isbeddel ah lama samayn.', 'Too many wrong codes, so the request was cancelled. Nothing was changed.', 'تم إلغاء الطلب بسبب كثرة الرموز الخاطئة. لم يتم تغيير أي شيء.');
                self::redirectToProfile();
            }
            $_SESSION['profile_otp']['attempts'] = $attempts;
            $left = self::PROFILE_OTP_MAX_ATTEMPTS - $attempts;
            $_SESSION['flash_error'] = self::tr("Koodhku waa khalad. Waxaa kuu hadhay {$left} isku day.", "Wrong code. {$left} attempts left.", "رمز خاطئ. المحاولات المتبقية: {$left}.");
            self::redirectToProfile();
        }

        if ($otp['purpose'] === 'email') {
            $account = self::ownAccount($pdo, $userId);
            $oldEmail = (string) $account['email'];
            $newEmail = (string) $otp['payload']['new_email'];
            self::assertEmailAvailable($pdo, $newEmail, $userId);
            $pdo->prepare('UPDATE users SET email = :email WHERE id = :id')->execute([':email' => $newEmail, ':id' => $userId]);
            unset($_SESSION['profile_otp']);
            $_SESSION['user']['email'] = $newEmail;
            AuditLogger::log('profile.email_changed', 'users', $userId, 'Admin changed own email after OTP verification.', ['from' => $oldEmail, 'to' => $newEmail], $userId);

            // Warn the old address, so an unexpected change is noticed. A failure here must not undo the change.
            MailService::sendEmailChangedNotice($oldEmail, (string) $account['full_name'], $newEmail, Lang::locale());

            $_SESSION['flash_success'] = self::tr('Email-kaaga waa la beddelay.', 'Your email address was changed.', 'تم تغيير بريدك الإلكتروني.');
        } else {
            $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')
                ->execute([':hash' => (string) $otp['payload']['new_password_hash'], ':id' => $userId]);
            unset($_SESSION['profile_otp']);
            session_regenerate_id(true);
            AuditLogger::log('profile.password_changed', 'users', $userId, 'Admin changed own password after OTP verification.', [], $userId);
            $_SESSION['flash_success'] = self::tr('Password-kaaga waa la beddelay.', 'Your password was changed.', 'تم تغيير كلمة المرور.');
        }

        self::redirectToProfile();
    }

    private static function profileOtpResend(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $otp = self::pendingProfileOtp((int) $user['id']);
        if ($otp === null) {
            throw new \RuntimeException(self::tr('OTP-gu waa dhacay. Fadlan mar kale codso.', 'The OTP has expired. Please request a new one.', 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.'));
        }
        if ((int) $otp['resends'] >= self::PROFILE_OTP_MAX_RESENDS) {
            throw new \RuntimeException(self::tr('Marar badan ayaad koodh codsatay. Ka noqo oo dib u bilow.', 'Too many resends. Cancel and start again.', 'طلبات إعادة الإرسال كثيرة. ألغِ الطلب وابدأ من جديد.'));
        }
        if (time() - (int) $otp['sent_at'] < self::PROFILE_OTP_COOLDOWN) {
            throw new \RuntimeException(self::tr('Fadlan yara sug ka hor intaadan koodh kale codsan.', 'Please wait a moment before requesting another code.', 'يرجى الانتظار قليلاً قبل طلب رمز آخر.'));
        }

        $account = self::ownAccount($pdo, (int) $user['id']);
        self::sendProfileOtp($account, (string) $otp['purpose'], (string) $otp['target'], (array) $otp['payload'], (int) $otp['resends'] + 1);
        $_SESSION['flash_success'] = self::tr('Koodh cusub ayaa la diray.', 'A new code was sent.', 'تم إرسال رمز جديد.');
        self::redirectToProfile();
    }

    /** Client's own password change (no OTP: many clients have no real email). */
    private static function accountPassword(): void
    {
        AuthMiddleware::requireRoles(['client']);
        self::renderDashboard('dashboard/account_password.php', [
            'pageTitle' => self::tr('Beddel Password-ka', 'Change Password', 'تغيير كلمة المرور'),
        ]);
    }

    private static function accountPasswordSave(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['client']);
        AuthMiddleware::verifyCsrfOrAbort();
        try {
            \App\Services\ClientAccountService::changeOwnPassword(
                $pdo,
                (int) $user['id'],
                (string) ($_POST['current_password'] ?? ''),
                (string) ($_POST['new_password'] ?? ''),
                (string) ($_POST['confirm_password'] ?? ''),
                Lang::locale()
            );
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ?route=account_password');
            exit;
        }
        $_SESSION['user']['must_change_password'] = false;
        session_regenerate_id(true);
        $_SESSION['flash_success'] = self::tr(
            'Hambalyo! Password-kaaga cusub waa la keydiyay, akoonkaaguna hadda waa ammaan.',
            'Done! Your new password is saved and your account is now secure.',
            'تم! حُفظت كلمة المرور الجديدة وأصبح حسابك آمناً.'
        );
        header('Location: ?route=dashboard');
        exit;
    }

    private static function profileOtpCancel(): void
    {
        AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        unset($_SESSION['profile_otp']);
        self::redirectToProfile();
    }

    private static function settingsSave(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $settings = (array) ($_POST['settings'] ?? []);
        $upd = $pdo->prepare('UPDATE system_settings SET setting_value = :val, updated_by = :uby WHERE setting_key = :k');
        foreach ($settings as $k => $v) {
            $upd->execute([':val' => trim((string) $v), ':uby' => (int) $user['id'], ':k' => (string) $k]);
        }
        AuditLogger::log('settings.updated', 'system_settings', null, 'Updated institutional system settings.', $settings, (int) $user['id']);
        $_SESSION['flash_success'] = 'System settings updated.';
        header('Location: ?route=settings');
        exit;
    }
}

