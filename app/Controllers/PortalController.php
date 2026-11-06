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
use App\Services\Par30Service;
use App\Services\RepaymentEngine;
use App\Services\ScheduleEngine;
use App\Services\SmsReminderService;
use App\Services\StatementPdfGenerator;
use PDO;

final class PortalController
{
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
                'client_register_submit' => self::clientRegisterSubmit($pdo),
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
                'murabaha' => self::murabahaView($pdo),
                'murabaha_new' => self::murabahaNew($pdo),
                'financings', 'schedules', 'statements' => self::financingsList($pdo, $route),
                'financing_view' => self::financingView($pdo),
                'statement_pdf' => self::statementPdf($pdo),

                'repayments', 'repayment_record' => self::repaymentsList($pdo),
                'repayment_submit' => self::repaymentSubmit($pdo),

                'charity_charges' => self::charityChargesList($pdo),
                'charity_waive' => self::charityWaive($pdo),
                'credit_scores' => self::creditScoresList($pdo),
                'par30', 'reports' => self::par30View($pdo, $route),
                'sms_outbox' => self::smsOutboxList($pdo),
                'sms_run_dispatcher' => self::smsRunDispatcher($pdo),

                'branches', 'users', 'permissions', 'audit_logs', 'settings' => self::adminSection($pdo, $route),
                'branch_create' => self::branchCreate($pdo),
                'branch_toggle' => self::branchToggle($pdo),
                'user_create' => self::userCreate($pdo),
                'user_update' => self::userUpdate($pdo),
                'user_toggle_status' => self::userToggleStatus($pdo),
                'user_security_reset' => self::userSecurityReset($pdo),
                'user_permissions_save' => self::userPermissionsSave($pdo),
                'settings_save' => self::settingsSave($pdo),

                '404' => self::error404(),
                default => self::error404(),
            };
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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
        if ($role === 'admin') {
            return ['', []];
        }
        if ($role === 'client') {
            return [" AND {$tableAlias}.client_id = :scope_cid ", [':scope_cid' => (int) ($user['client_id'] ?? -1)]];
        }
        return [" AND {$tableAlias}.branch_id = :scope_bid ", [':scope_bid' => (int) ($user['branch_id'] ?? -1)]];
    }

    private static function publicHome(PDO $pdo): void
    {
        $products = $pdo->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
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
        require dirname(__DIR__, 2) . '/resources/views/public/about.php';
    }

    private static function publicContact(PDO $pdo): void
    {
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll();
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
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

            $soCount = 0;
            foreach ($soMarkers as $sm) {
                if (mb_strpos($msgLower, $sm) !== false) $soCount++;
            }
            $enCount = 0;
            foreach ($enMarkers as $em) {
                if (mb_strpos($msgLower, $em) !== false) $enCount++;
            }

            if ($soCount > 0 && $soCount >= $enCount) {
                $lang = 'so';
            } elseif ($enCount > 0 && $enCount > $soCount) {
                $lang = 'en';
            }
        }

        $msgLower = mb_strtolower($message, 'UTF-8');

        // Intent detection
        $reply = '';
        $topic = 'general';
        $suggestions = [];
        $actionBtn = null;

        // 1. WhatsApp, Number, Phone & Direct Contact Intent
        if (
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
                    . "📱 رقم الواتساب والهاتف: +252 61 6256534\n"
                    . "📞 هاتف إضافي: +252 61 5000000\n"
                    . "✉️ البريد الإلكتروني: info@simda.so\n"
                    . "📍 المقر: تقاطع KM4، مديرية هودن، مقديشو\n\n"
                    . "انقر على زر الواتساب الأخضر أدناه لبدء المحادثة المباشرة فوراً:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 تواصل عبر واتساب (+252 61 6256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['أخبرني عن هذا المكان', 'كيف أتقدم بطلب تمويل؟', 'هل التمويل 0% ربا؟'];
            } elseif ($lang === 'en') {
                $reply = "Here are SIMDA's official contact numbers and direct support channels:\n\n"
                    . "📱 WhatsApp & Phone: +252 61 6256534\n"
                    . "📞 Office Line: +252 61 5000000\n"
                    . "✉️ Email: info@simda.so\n"
                    . "📍 Location: KM4, Hodan District, Mogadishu\n\n"
                    . "Click the green WhatsApp button below to chat with our advisors immediately:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 Chat on WhatsApp (+252 61 6256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['Tell me about this place', 'How can I apply?', 'Is it 0% Riba?'];
            } else {
                $reply = "Waa kuwan lambarada rasmiga ah iyo xiriirka tooska ah ee SIMDA:\n\n"
                    . "📱 WhatsApp & Taleefan: +252 61 6256534\n"
                    . "📞 Khadka Xafiiska: +252 61 5000000\n"
                    . "✉️ Email: info@simda.so\n"
                    . "📍 Goobta: KM4, Degmada Hodan, Muqdisho\n\n"
                    . "Guji batoonka cagaaran ee hoose si aad toos noogala hadasho WhatsApp:";
                $actionBtn = [
                    'type' => 'whatsapp',
                    'label' => '💬 La Hadal WhatsApp (+252 61 6256534)',
                    'url' => $waUrl
                ];
                $suggestions = ['Xogta meesha wax iga sheeg', 'Sideen u codsadaa?', '0% Ribo ma dhabbaa?'];
            }
        }
        // 2. Overview / "Xogta meesha wax iga sheeg" / "Tell me about this place / SIMDA"
        elseif (
            preg_match('/(xog|xogta|waran|meesha|mesha|meeshan|meshan|halkan|waxa socda|shirkaddan|hay\'addan|maxaad qabataan|maxaa la qabtaa|wax iga sheeg|iiga waran|i fahamsii|about this place|tell me about|what is this place|overview|what does simda do|عن هذا المكان|أخبرني عن|ما هي هذه المؤسسة|ماذا تفعلون|نبذة)/iu', $message)
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
                    . "المقر الرئيسي: تقاطع KM4، مديرية هودن، مقديشو | هاتف: 6256534 61 252+ | بريد: info@simda.so";
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
                    . "Head Office: KM4, Hodan District, Mogadishu | Tel: +252 61 6256534 | Email: info@simda.so";
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
                    . "Xafiiska Sare: KM4, Degmada Hodan, Muqdisho | Tel: +252 61 6256534 / +252 61 5000000 | Email: info@simda.so";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Sideen u codsadaa?', 'Keyd ma leedihiin?'];
            }
        }
        // 2. Greetings / Salams
        elseif (
            preg_match('/^(asc|salaam|salam|salan|hello|hi|hey|subax|galab|nabad|marhaban|مرحبا|أهلا|السلام)/iu', $message) ||
            preg_match('/(asc|asalaamu|assalamu|salaamu calaykum|salaam)/iu', $message)
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
            preg_match('/(ribo|ribada|dulsaar|riba|interest|usury|ربا|فوائد|فائدة|ربوية)/iu', $message)
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
            preg_match('/(keyd|kayd|lacag dhigasho|dhigo|deposit|saving|savings|توفير|ودائع|إيداع|حساب توفير)/iu', $message)
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
            preg_match('/(murabaha|muraabaxa|cost-plus|مرابحة|المرابحة)/iu', $message)
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
            preg_match('/(alaab|noocyada|mashiin|kiosk|baqaar|dhul|dhismaha|products|finance|equipment|plot|assets|منتجات|خدمات|أكشاك|بناء|معدات|أصول)/iu', $message)
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
        // 5. Requirements & How to Apply
        elseif (
            preg_match('/(codsi|codsadaa|shuruud|shuruudaha|qaabkee|requirements|eligibility|how to apply|apply|process|شروط|كيف أقدم|طلب تمويل|الأوراق)/iu', $message)
        ) {
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
            preg_match('/(dammaanad|garab|wadaag|koox|guarantor|solidarity|group|كفيل|ضمان|تضامني|مجموعات)/iu', $message)
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
            preg_match('/(xafiis|xagee|address|location|phone|email|contact|saacadaha|hours|ciwaan|أين|موقع|عنوان|هاتف|تواصل|ساعات العمل)/iu', $message)
        ) {
            $topic = 'contact';
            $waUrl = 'https://wa.me/252616256534?text=' . rawurlencode(
                $lang === 'ar' ? 'مرحباً سيمدا، أرغب في الاستفسار عن التمويل الإسلامي' :
                ($lang === 'en' ? 'Hello SIMDA, I would like to inquire about Islamic microfinance' :
                'Asc SIMDA, waxaan rabaa in aan wax ka ogaado maalgelinta xalaasha ah')
            );
            $actionBtn = [
                'type' => 'whatsapp',
                'label' => ($lang === 'ar' ? '💬 تواصل عبر واتساب (+252 61 6256534)' : ($lang === 'en' ? '💬 Chat on WhatsApp (+252 61 6256534)' : '💬 La Hadal WhatsApp (+252 61 6256534)')),
                'url' => $waUrl
            ];
            if ($lang === 'ar') {
                $reply = "يقع المقر الرئيسي لسيمدا في تقاطع KM4، مديرية هودن، مقديشو، الصومال. هاتف: 6256534 61 252+ / 5000000 61 252+. البريد: info@simda.so. ساعات العمل: السبت إلى الخميس، من 8:00 صباحاً حتى 4:30 مساءً (الجمعة عطلة).\n\nيمكنك أيضاً التحدث معنا فوراً عبر واتساب بالنقر أدناه:";
                $suggestions = ['كيف أتقدم بطلب؟', 'هل التمويل 0% ربا؟', 'ما هي المنتجات؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA Head Office is located at KM4, Hodan District, Mogadishu, Somalia. Phone: +252 61 6256534 / +252 61 5000000. Email: info@simda.so. Working hours: Saturday to Thursday, 8:00 AM – 4:30 PM (Closed on Friday).\n\nYou can also click below to chat with us directly on WhatsApp:";
                $suggestions = ['How to apply?', 'Is it 0% Riba?', 'What are the products?'];
            } else {
                $reply = "Xafiiska Sare ee SIMDA wuxuu ku yaallaa: KM4, Degmada Hodan, Muqdisho, Soomaaliya. Tel: +252 61 6256534 ama +252 61 5000000. Email: info@simda.so. Saacadaha shaqadu waa Sabti ilaa Khamiis 8:00 subaxnimo ilaa 4:30 galabnimo (Jimcaha waa fasax).\n\nWaxaad sidoo kale toos noogala soo xiriiri kartaa WhatsApp adoo gujinaya batoonka hoose:";
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
                $reply = "سؤال رائع! في سيمدا نحن ملتزمون بتقديم تمويل أصغر إسلامي 100% خالٍ من الربا (0% ربا) لدعم رواد الأعمال، الأكشاك، وتمويل الأصول الإنتاجية بنظام المرابحة. للإجابة الدقيقة على تفاصيل استفسارك، يمكنك زيارة فرعنا بـ KM4 بمقديشو أو التواصل مباشرة عبر الهاتف: 6256534 61 252+ أو البريد info@simda.so.";
                $suggestions = ['هل التمويل خالٍ من الربا؟', 'ما هي شروط التقديم؟', 'أين يقع مكتبكم؟'];
            } elseif ($lang === 'en') {
                $reply = "Thank you for asking! SIMDA is dedicated to 100% Sharia-compliant, 0% Riba microfinance for Somali entrepreneurs, kiosk owners, and asset financing through Murabaha. For tailored assistance on this specific inquiry, our team at KM4 Mogadishu is ready to help at +252 61 6256534 or info@simda.so.";
                $suggestions = ['Is it 0% Riba?', 'How can I apply?', 'Where is your office?'];
            } else {
                $reply = "Waad ku mahadsan tahay su'aashaada! SIMDA waxay u taagan tahay maalgelin xalaal ah oo 100% ribo la'aan ah (0% Ribo) oo lagu taageerayo ganacsatada yaryar, baqaarrada, iyo qalabka ganacsiga iyadoo la adeegsanayo nidaamka Murabaha. Faahfaahin dheeraad ah oo ku saabsan su'aashaada gaarka ah, fadlan nagala soo xiriir xafiiska KM4, Muqdisho ama Tel: +252 61 6256534 / info@simda.so.";
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
            $msg = $isSo ? 'Fadlan geli email-kaaga.' : ($isAr ? 'يرجى إدخال البريد الإلكتروني.' : 'Please enter your email.');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }

        $st = $pdo->prepare('
            SELECT id, full_name, email, username, is_active, lockout_tier, locked_until, security_block_reason
            FROM users
            WHERE (email = :id1 OR username = :id2)
              AND deleted_at IS NULL
            LIMIT 1
        ');
        $st->execute([':id1' => $identifier, ':id2' => $identifier]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $msg = $isSo
                ? 'Fadlan Email-kan lama aqoonsano. Isku day mar kale.'
                : ($isAr
                    ? 'عذراً، هذا البريد الإلكتروني غير مسجل. يرجى المحاولة مرة أخرى.'
                    : 'Please note this email is not recognized. Try again.');
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
            'message' => $isSo ? 'Email-ka waa la aqoonsaday. Fadlan geli furahaaga sirta ah.' : 'Email recognized. Please enter your password.'
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
        $st = $pdo->prepare('
            SELECT u.*, r.slug AS role_slug, r.name_so AS role_name_so, r.name_en AS role_name_en,
                   b.name AS branch_name, b.code AS branch_code,
                   c.id AS client_id, c.client_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            LEFT JOIN clients c ON c.user_id = u.id
            WHERE (u.email = :id1 OR u.username = :id2)
              AND u.deleted_at IS NULL
            LIMIT 1
        ');
        $st->execute([':id1' => $identifier, ':id2' => $identifier]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['flash_error'] = $isSo
                ? 'Fadlan Email-kan lama aqoonsano. Isku day mar kale.'
                : ($isAr ? 'عذراً، هذا البريد الإلكتروني غير مسجل. يرجى المحاولة مرة أخرى.' : 'Please note this email is not recognized. Try again.');
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
            MailService::sendAdmin2FACodeEmail($user['email'], $user['full_name'], $twoFaCode, $locale);

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

            header('Location: ?route=login');
            exit;
        }

        // Non-admin login completes immediately
        try {
            AuthService::completeLoginForUser($user, $pdo);
            unset($_SESSION['login_verified_identifier']);
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

        MailService::sendAdmin2FACodeEmail($user['email'], $user['full_name'], $twoFaCode, $locale);

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

        if (strlen($newPassword) < 6) {
            echo json_encode([
                'success' => false,
                'message' => $isSo ? 'Furaha cusub waa inuu ka koobnaadaa ugu yaraan 6 xaraf.' : 'Password must be at least 6 characters.'
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
        $term = max(1, min(60, (int) ($_GET['term'] ?? 5)));
        $freq = (string) ($_GET['frequency'] ?? 'monthly');

        $schedule = ScheduleEngine::generate($type, $principal, $rate, $term, $freq, date('Y-m-d'));
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

        [$repScope, $repParams] = self::scopeSql($user, 'r');
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
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        $branchSql = ($user['role_slug'] === 'admin') ? '' : ' AND c.branch_id = :bid ';
        $params = ($user['role_slug'] === 'admin') ? [] : [':bid' => (int) $user['branch_id']];

        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q !== '') {
            $branchSql .= ' AND (c.full_name LIKE :q1 OR c.client_code LIKE :q2 OR c.phone LIKE :q3 OR c.id_number LIKE :q4) ';
            $like = '%' . $q . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $stmt = $pdo->prepare("
            SELECT c.*,
                   b.name AS branch_name,
                   cs.current_score,
                   cs.score_category,
                   (SELECT COUNT(*) FROM guarantors g WHERE g.client_id = c.id) AS guarantors_count
            FROM clients c
            INNER JOIN branches b ON b.id = c.branch_id
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            WHERE c.deleted_at IS NULL {$branchSql}
            ORDER BY c.id DESC
        ");
        $stmt->execute($params);
        $clients = $stmt->fetchAll();
        $branches = $pdo->query('SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC')->fetchAll();

        self::renderDashboard('clients/index.php', [
            'pageTitle' => Lang::get('menu_all_clients'),
            'clients' => $clients,
            'branches' => $branches,
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

        $_SESSION['flash_success'] = "Client registered successfully ({$res['client_code']}) with verified Guarantor and baseline 600 Credit Score.";
        header('Location: ?route=clients');
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
        AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
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
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
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

        $cWhere = ($user['role_slug'] === 'admin') ? '' : ' WHERE c.branch_id = :bid ';
        $cParams = ($user['role_slug'] === 'admin') ? [] : [':bid' => (int) $user['branch_id']];
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
            'purpose' => $_POST['purpose'] ?? '',
            'murabaha_goods_description' => $_POST['murabaha_goods_description'] ?? '',
        ], (int) $user['id'], $submitImmediately, date('Y-m-d'), $pdo);

        $_SESSION['flash_success'] = "Application {$res['application_number']} saved as '{$res['status']}' (Required approver: {$res['required_approver_role']}).";
        header('Location: ?route=applications');
        exit;
    }

    private static function applicationSubmitDraft(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        $pdo->prepare("UPDATE financing_applications SET status = 'submitted', submitted_at = NOW() WHERE id = :id AND status = 'draft'")
            ->execute([':id' => $appId]);
        AuditLogger::log('application.submitted', 'financing_applications', $appId, "Submitted draft application #{$appId}.", [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Draft application submitted to the approval queue.';
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
        header('Location: ?route=applications');
        exit;
    }

    private static function approvalQueue(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        [$scopeSql, $params] = self::scopeSql($user, 'fa');

        $stmt = $pdo->prepare("
            SELECT fa.*,
                   c.full_name AS client_name,
                   c.client_code,
                   b.name AS branch_name,
                   p.product_type,
                   u.full_name AS creator_name,
                   cs.current_score,
                   cs.score_category
            FROM financing_applications fa
            INNER JOIN clients c ON c.id = fa.client_id
            INNER JOIN branches b ON b.id = fa.branch_id
            INNER JOIN products p ON p.id = fa.product_id
            INNER JOIN users u ON u.id = fa.created_by
            LEFT JOIN credit_scores cs ON cs.client_id = c.id
            WHERE fa.status = 'submitted' {$scopeSql}
            ORDER BY fa.id ASC
        ");
        $stmt->execute($params);
        $queue = $stmt->fetchAll();

        self::renderDashboard('applications/approval_queue.php', [
            'pageTitle' => Lang::get('menu_approval_queue'),
            'queue' => $queue,
        ]);
    }

    private static function applicationReview(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();
        $appId = (int) ($_POST['application_id'] ?? 0);
        $decision = (string) ($_POST['decision'] ?? '');
        $comments = (string) ($_POST['comments'] ?? '');

        $res = FinancingDomainService::reviewApplication($appId, $user, $decision, $comments, $pdo);
        $_SESSION['flash_success'] = "Application #{$appId} marked as {$res['status']}.";
        header('Location: ?route=approval_queue');
        exit;
    }

    private static function disbursementsList(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
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
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer', 'branch_manager']);
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
            'pageTitle' => 'Daymaha (Cash Loans / Qard Hassan)',
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
            'pageTitle' => 'Maalgelinta Hantida (Murabaxa / Murabaha)',
            'filter' => $filter,
            'summary' => $summary,
            'contracts' => $contracts,
        ]);
    }

    private static function murabahaNew(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin', 'branch_manager', 'loan_officer']);
        $st = $pdo->prepare("SELECT id FROM products WHERE product_type = 'murabaha' AND is_active = 1 LIMIT 1");
        $st->execute();
        $pid = (int) $st->fetchColumn();
        if ($pid > 0) {
            header('Location: ?route=applications&product_id=' . $pid . '#newApp');
        } else {
            header('Location: ?route=applications#newApp');
        }
        exit;
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
        $user = AuthMiddleware::requireRoles(['admin', 'loan_officer']);
        AuthMiddleware::verifyCsrfOrAbort();

        $res = RepaymentEngine::recordRepayment(
            (int) ($_POST['financing_id'] ?? 0),
            (int) $user['id'],
            (string) ($_POST['amount'] ?? '0.00'),
            (string) ($_POST['payment_method'] ?? 'EVC Plus'),
            (string) ($_POST['transaction_reference'] ?? ''),
            (string) ($_POST['payment_date'] ?? date('Y-m-d')),
            (string) ($_POST['notes'] ?? ''),
            !empty($_POST['use_client_credit']),
            (string) ($_POST['ibra_discount'] ?? '0.00'),
            $pdo
        );

        $_SESSION['flash_success'] = "Repayment {$res['receipt_number']} recorded (\${$res['cash_amount']}). Remaining contract balance: \${$res['outstanding_balance_after']}. Client Credit Score: {$res['credit_score']['current_score']}.";
        header('Location: ?route=repayments');
        exit;
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

        self::renderDashboard('risk/charity_charges.php', [
            'pageTitle' => Lang::get('menu_charity_charges'),
            'charges' => $charges,
        ]);
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

        self::renderDashboard('risk/par30.php', [
            'pageTitle' => Lang::get($route === 'reports' ? 'menu_financial_reports' : 'menu_par30'),
            'par30' => $par30,
            'branchBreakdown' => $branchBreakdown,
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

        self::renderDashboard('communication/sms_outbox.php', [
            'pageTitle' => Lang::get('menu_sms_outbox'),
            'messages' => $messages,
        ]);
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

    private static function adminSection(PDO $pdo, string $mode): void
    {
        $user = AuthMiddleware::requireAuth();
        $role = $user['role_slug'] ?? '';

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

        self::renderDashboard('admin/manage.php', [
            'pageTitle' => $titleMap[$mode] ?? 'Administration',
            'sectionTitle' => $titleMap[$mode] ?? 'Administration',
            'mode' => $mode,
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
        // Header Branch -> [loan_officer, client] (cannot create admin, manager, or branch_manager)
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
                        ? 'Fadlan dooro laanta loo qoondeeyey isticmaalahan. Maamulaha Laanta (Header Branch) iyo Shaqaalaha waa inay laan gaar ah leeyihiin.'
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

        header('Location: ?route=users');
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
        if (isset($_POST['role_id']) && ($actorRole === 'admin' || $actorRole === 'manager')) {
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

        $_SESSION['flash_success'] = $locale === 'so'
            ? 'Macluumaadka isticmaalaha (' . $fullName . ') si guul leh ayaa loo cusbooneysiiyay!'
            : 'User (' . $fullName . ') details successfully updated!';

        header('Location: ?route=users');
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

        header('Location: ?route=users');
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

        header('Location: ?route=users');
        exit;
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

