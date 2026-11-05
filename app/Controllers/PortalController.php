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
                'login_submit' => self::loginSubmit($pdo),
                'logout' => self::logout(),
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

                'branches', 'users', 'audit_logs', 'settings' => self::adminSection($pdo, $route),
                'branch_create' => self::branchCreate($pdo),
                'user_create' => self::userCreate($pdo),
                'settings_save' => self::settingsSave($pdo),

                default => self::publicHome($pdo),
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
            $soMarkers = ['waa', 'maxay', 'sidee', 'ma ', 'leedihiin', 'jiraa', 'jirtaa', 'ribo', 'ribada', 'dulsaar', 'keyd', 'kayd', 'lacag', 'codsi', 'codsadaa', 'mashiin', 'kiosk', 'baqaar', 'dhul', 'dhismaha', 'jimco', 'sabti', 'ganaax', 'sadaqo', 'dammaanad', 'garab', 'wadaag', 'koox', 'xafiis', 'xagee', 'lambarka', 'asc', 'nabad', 'mahadsanid', 'subax', 'galab', 'haye', 'haa', 'maya'];
            $enMarkers = ['what', 'how', 'why', 'who', 'is ', 'are ', 'can ', 'do ', 'does ', 'interest', 'riba', 'savings', 'deposit', 'account', 'apply', 'application', 'eligible', 'murabaha', 'loan', 'financing', 'friday', 'saturday', 'shift', 'penalty', 'charity', 'guarantor', 'solidarity', 'office', 'where', 'location', 'contact', 'phone', 'email', 'hello', 'hi', 'thanks', 'thank you', 'yes', 'no'];

            $soCount = 0;
            foreach ($soMarkers as $sm) {
                if (mb_strpos($msgLower, $sm) !== false) $soCount++;
            }
            $enCount = 0;
            foreach ($enMarkers as $em) {
                if (mb_strpos($msgLower, $em) !== false) $enCount++;
            }

            if ($soCount > $enCount && $soCount > 0) {
                $lang = 'so';
            } elseif ($enCount > $soCount && $enCount > 0) {
                $lang = 'en';
            }
        }

        $msgLower = mb_strtolower($message, 'UTF-8');

        // Intent detection
        $reply = '';
        $topic = 'general';
        $suggestions = [];

        // 1. Riba & Interest Check
        if (
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
            if ($lang === 'ar') {
                $reply = "يقع المقر الرئيسي لسيمدا في تقاطع KM4، مديرية هودن، مقديشو، الصومال. هاتف: 6256534 61 252+ / 5000000 61 252+. البريد: info@simda.so. ساعات العمل: السبت إلى الخميس، من 8:00 صباحاً حتى 4:30 مساءً (الجمعة عطلة).";
                $suggestions = ['كيف أتقدم بطلب؟', 'هل التمويل 0% ربا؟', 'ما هي المنتجات؟'];
            } elseif ($lang === 'en') {
                $reply = "SIMDA Head Office is located at KM4, Hodan District, Mogadishu, Somalia. Phone: +252 61 6256534 / +252 61 5000000. Email: info@simda.so. Working hours: Saturday to Thursday, 8:00 AM – 4:30 PM (Closed on Friday).";
                $suggestions = ['How to apply?', 'Is it 0% Riba?', 'What are the products?'];
            } else {
                $reply = "Xafiiska Sare ee SIMDA wuxuu ku yaallaa: KM4, Degmada Hodan, Muqdisho, Soomaaliya. Tel: +252 61 6256534 ama +252 61 5000000. Email: info@simda.so. Saacadaha shaqadu waa Sabti ilaa Khamiis 8:00 subaxnimo ilaa 4:30 galabnimo (Jimcaha waa fasax).";
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
        // 12. Greetings / Salams
        elseif (
            preg_match('/(asc|salaam|salam|hello|hi|subax|galab|nabad|marhaban|مرحبا|أهلا|السلام)/iu', $message)
        ) {
            $topic = 'greeting';
            if ($lang === 'ar') {
                $reply = "وعليكم السلام ورحمة الله وبركاته! أهلاً وسهلاً بك في سيمدا للتمويل الإسلامي. يسعدني مساعدتك في أي استفسار حول خدماتنا، خلوها من الربا 0%، أو كيفية التقديم.";
                $suggestions = ['هل التمويل خالٍ من الربا؟', 'ما هي شروط التقديم؟', 'ما هي المرابحة؟'];
            } elseif ($lang === 'en') {
                $reply = "Hello and welcome to SIMDA! I am glad to assist you. How can I help you today with our 0% Riba ethical microfinance or loan application?";
                $suggestions = ['Is it 0% Riba?', 'How can I apply?', 'What is Murabaha?'];
            } else {
                $reply = "Asc! Ku soo dhowow SIMDA. Waan ku faraxsanahay inaan ku caawiyo. Maxaan maanta kugu caawin karaa oo ku saabsan nidaamka ama maalgelinta xalaasha ah?";
                $suggestions = ['0% Ribo ma dhabbaa?', 'Sideen u codsadaa?', 'Murabaha waa maxay?'];
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
            'suggestions' => $suggestions
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

    private static function loginForm(): void
    {
        if (AuthMiddleware::check()) {
            header('Location: ?route=dashboard');
            exit;
        }
        require dirname(__DIR__, 2) . '/resources/views/auth/login.php';
    }

    private static function loginSubmit(PDO $pdo): void
    {
        AuthMiddleware::verifyCsrfOrAbort();
        $identifier = (string) ($_POST['identifier'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        AuthService::attemptLogin($identifier, $password, $pdo);
        header('Location: ?route=dashboard');
        exit;
    }

    private static function logout(): void
    {
        if (AuthMiddleware::check()) {
            AuditLogger::log('auth.logout', 'users', (int) $_SESSION['user']['id'], 'User signed out.');
        }
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: ?route=login');
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
        $metrics = DashboardAnalyticsService::getOverviewMetrics($user, $pdo);

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
        AuthMiddleware::requireRoles(['admin']);
        $branches = $pdo->query('SELECT * FROM branches ORDER BY id ASC')->fetchAll();
        $roles = $pdo->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();
        $users = $pdo->query('
            SELECT u.*, r.slug AS role_slug, b.name AS branch_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN branches b ON b.id = u.branch_id
            ORDER BY u.id ASC
        ')->fetchAll();
        $settingsList = $pdo->query('SELECT * FROM system_settings ORDER BY setting_key ASC')->fetchAll();
        $auditLogs = $pdo->query('
            SELECT a.*, u.full_name AS user_name
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.id DESC
            LIMIT 100
        ')->fetchAll();

        $titleMap = [
            'branches' => Lang::get('menu_branches'),
            'users' => Lang::get('menu_users'),
            'settings' => Lang::get('menu_settings'),
            'audit_logs' => Lang::get('menu_audit_logs'),
        ];

        self::renderDashboard('admin/manage.php', [
            'pageTitle' => $titleMap[$mode] ?? 'Administration',
            'sectionTitle' => $titleMap[$mode] ?? 'Administration',
            'mode' => $mode,
            'branches' => $branches,
            'roles' => $roles,
            'users' => $users,
            'settingsList' => $settingsList,
            'auditLogs' => $auditLogs,
        ]);
    }

    private static function branchCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $stmt = $pdo->prepare('
            INSERT INTO branches (code, name, city, district, address, phone, is_active)
            VALUES (:code, :name, :city, :dist, :addr, :phone, 1)
        ');
        $stmt->execute([
            ':code' => trim((string) ($_POST['code'] ?? '')),
            ':name' => trim((string) ($_POST['name'] ?? '')),
            ':city' => trim((string) ($_POST['city'] ?? '')),
            ':dist' => trim((string) ($_POST['district'] ?? '')),
            ':addr' => trim((string) ($_POST['address'] ?? '')),
            ':phone' => trim((string) ($_POST['phone'] ?? '')),
        ]);
        AuditLogger::log('branch.created', 'branches', (int) $pdo->lastInsertId(), 'Created branch ' . $_POST['code'], [], (int) $user['id']);
        $_SESSION['flash_success'] = 'New branch created successfully.';
        header('Location: ?route=branches');
        exit;
    }

    private static function userCreate(PDO $pdo): void
    {
        $user = AuthMiddleware::requireRoles(['admin']);
        AuthMiddleware::verifyCsrfOrAbort();
        $stmt = $pdo->prepare('
            INSERT INTO users (role_id, branch_id, full_name, username, email, phone, password_hash, is_active)
            VALUES (:rid, :bid, :name, :uname, :email, :phone, :phash, 1)
        ');
        $stmt->execute([
            ':rid' => (int) ($_POST['role_id'] ?? 3),
            ':bid' => !empty($_POST['branch_id']) ? (int) $_POST['branch_id'] : null,
            ':name' => trim((string) ($_POST['full_name'] ?? '')),
            ':uname' => trim((string) ($_POST['username'] ?? '')),
            ':email' => trim((string) ($_POST['email'] ?? '')),
            ':phone' => trim((string) ($_POST['phone'] ?? '')),
            ':phash' => Security::hashPassword((string) ($_POST['password'] ?? 'Staff@123')),
        ]);
        AuditLogger::log('user.created', 'users', (int) $pdo->lastInsertId(), 'Created user ' . $_POST['username'], [], (int) $user['id']);
        $_SESSION['flash_success'] = 'Staff user account created successfully.';
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

