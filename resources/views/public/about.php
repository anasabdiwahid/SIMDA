<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$isLoggedIn = AuthMiddleware::check();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);

$aboutI18n = [
    'en' => [
        'nav_home' => 'Home',
        'nav_about' => 'About Us',
        'nav_services' => 'Services',
        'nav_process' => 'Our Process',
        'nav_testimonials' => 'Testimonials',
        'nav_faq' => 'FAQ',
        'nav_contact' => 'Contact Us',
        'cta_dashboard' => 'Dashboard',
        'cta_start' => 'Get Started',

        'hero_badge' => 'Pioneering Ethical Finance in Somalia',
        'hero_title' => 'Building Trust. Empowering Growth.',
        'hero_lead' => 'SIMDA is a dedicated Islamic microfinance institution committed to zero-riba asset financing, small retail development, and home construction for plot owners across Somalia.',

        // 3 Floating Cards
        'card1_title' => '100% Zero-Riba Guarantee',
        'card1_desc' => 'Strictly zero usury or compounding interest. Pure Islamic microfinance where clients only pay back agreed terms.',
        'card1_action' => 'Explore 0% Terms',

        'card2_title' => 'Murabaha Asset Financing',
        'card2_desc' => 'We purchase vehicles, motorbikes, bajaj, smartphones, and kiosk supplies directly for you with transparent cost-plus terms. No savings or deposit accounts.',
        'card2_action' => 'View Asset Solutions',

        'card3_title' => 'Community Empowerment',
        'card3_desc' => '100% segregated charity fund, automatic Friday-to-Saturday payment shift, and dedicated financing for kiosk owners and home builders.',
        'card3_action' => 'Our Community Impact',

        // Stats
        'stat1_num' => '100%',
        'stat1_lbl' => 'Zero Riba Compliance',
        'stat2_num' => '$0',
        'stat2_lbl' => 'Compounded Penalties',
        'stat3_num' => '100%',
        'stat3_lbl' => 'Segregated Charity Fund',
        'stat4_num' => '15+',
        'stat4_lbl' => 'Markets & Districts Served',

        // Story
        'story_eyebrow' => 'Our Mission & Story',
        'story_title' => 'We Are Not a Bank. We Are a Community Growth Engine.',
        'story_p1' => 'The Somali Islamic Microfinance Development Association (SIMDA) was founded to fill a crucial gap in our community: small business owners, roadside kiosk operators, and hardworking families need real, ethical financing to grow — not exploitative debt or complex banking bureaucracy.',
        'story_p2' => 'At SIMDA, we do not operate savings accounts or take public deposits. We are a dedicated microfinance enterprise focused entirely on empowering entrepreneurs through direct Murabaha asset acquisition, working capital support for retail shops, and complete construction solutions for landowners.',
        'story_quote' => '"Our pledge is simple: no riba, no hidden compounding fees, and absolute honesty in every asset we finance for Somali families."',
        'story_author' => 'SIMDA Executive Advisory Board',

        // Pillars
        'pillars_eyebrow' => 'Our Core Solutions',
        'pillars_title' => 'What We Do — Real Economic Empowerment',
        'pil1_title' => 'Home & Plot Construction',
        'pil1_desc' => 'For citizens who own plots of land but lack capital, SIMDA finances and manages the construction of modern family homes under transparent installment plans.',
        'pil2_title' => 'Murabaha Vehicles & Equipment',
        'pil2_desc' => 'We buy cars, delivery motorbikes, bajaj, and high-performance smartphones (iPhone 18 Pro Max) directly and sell them on clear, cost-plus monthly payments.',
        'pil3_title' => 'Small Kiosk & Dukaan Support',
        'pil3_desc' => 'Direct inventory and working capital financing for female roadside vendors, grocery stores, and micro-retailers to boost stock and family earnings.',
        'pil4_title' => 'Zero-Riba Qard & Sharia Shifts',
        'pil4_desc' => 'Pure ethical financing with automatic rescheduling of Friday due dates to Saturday, ensuring clients never incur unfair delinquency fees.',

        // CTA
        'cta_box_title' => 'Ready to expand your business with ethical financing?',
        'cta_box_desc' => 'Calculate your exact zero-riba monthly installment schedule in seconds or speak directly with our team in Mogadishu.',
        'cta_btn_calc' => 'Open Schedule Simulator',
        'cta_btn_contact' => 'Contact Our Office',

        // Footer
        'footer_rights' => 'SIMDA Microfinance. All rights reserved.',
        'footer_developed_by' => 'Developed by',
        'footer_col_newsletter' => "SIMDA's Newsletter",
        'footer_placeholder_email' => 'Your email',
        'footer_btn_subscribe' => 'SUBSCRIBE',
        'footer_newsletter_alert' => 'Thank you for subscribing to SIMDA updates!',
        'footer_col_terms' => 'Terms and conditions',
        'footer_term_riba' => '0% Riba — Principal only!',
        'footer_term_shift' => 'Friday to Saturday shift!',
        'footer_term_charity' => '100% Segregated Charity Fund!',
        'footer_follow_simda' => 'Follow SIMDA',
        'footer_col_info' => 'Information',
        'footer_faq' => 'FAQs',
        'footer_about_simda' => 'About us at SIMDA',
        'footer_schedule_engine' => 'SIMDA Schedule Engine',
        'footer_col_contact' => 'Contact',
        'footer_brand_sub' => 'SIMDA Microfinance',
        'footer_reg' => 'Institution Reg: SIMDA-SO-001',
        'footer_head_office' => 'Head office:',
        'footer_country' => 'Mogadishu, Somalia',
        'footer_seal_zero_riba' => 'ZERO RIBA',
        'footer_seal_guaranteed' => 'GUARANTEED',
        'footer_seal_sharia' => 'SHARIA & AUDIT',
        'footer_seal_compliant' => 'COMPLIANT',
        'footer_part_of' => 'Part of SIMDA',
    ],
    'so' => [
        'nav_home' => 'Bogga Hore',
        'nav_about' => 'Nagu Saabsan',
        'nav_services' => 'Adeegyada',
        'nav_process' => 'Habka Shaqada',
        'nav_testimonials' => 'Aragtida Macaamiisha',
        'nav_faq' => 'FAQ',
        'nav_contact' => 'Xiriirka',
        'cta_dashboard' => 'Dashboard',
        'cta_start' => 'Bilow Hadda',

        'hero_badge' => 'Hoggaamiyaha Maalgelinta Xalaasha ah ee Soomaaliya',
        'hero_title' => 'Dhisidda Kalsoonida. Kobcinta Ganacsiga.',
        'hero_lead' => 'SIMDA waa hay\'ad maalgelin yar-yar oo Islaami ah, u taagan dayn xalaal ah oo 0% Ribo ah, muraabaxo qalabka iyo gaadiidka, dhismaha boosaska, iyo taageerada dukaamada yaryar.',

        // 3 Floating Cards
        'card1_title' => '100% Dammaanad 0% Ribo ah',
        'card1_desc' => 'Marnaba ma jirto wax dulsaar ah ama ribo la kordhiyo. Macmiilku wuxuu bixinayaa keliya raasumaalka lagu heshiiyay si daacad ah.',
        'card1_action' => 'Baro Shuruudaha 0%',

        'card2_title' => 'Muraabaxada Gaadiidka & Qalabka',
        'card2_desc' => 'Waxaan kuu soo iibinaynaa baabuur, mooto, bajaj, taleefanno casri ah iyo badeecadaha dukaamada. Bank ma nihin, wax saving ahna ma hayno.',
        'card2_action' => 'Eeg Qalabka & Gaadiidka',

        'card3_title' => 'Taageerada Bulshada & Dukaamada',
        'card3_desc' => '100% Sanduuq Khayraat oo gooni ah, dib-u-dhigista lacag-bixinta Jimcaha oo loo wareejiyo Sabtida, iyo dhismaha boosaska dadka danyarta ah.',
        'card3_action' => 'Saamaynta Bulshada',

        // Stats
        'stat1_num' => '100%',
        'stat1_lbl' => '0% Ribo & Shareeco',
        'stat2_num' => '$0',
        'stat2_lbl' => 'Dulsaar Dheeraad ah',
        'stat3_num' => '100%',
        'stat3_lbl' => 'Sanduuqa Khayraadka',
        'stat4_num' => '15+',
        'stat4_lbl' => 'Suuqyada & Degmooyinka',

        // Story
        'story_eyebrow' => 'Ujeedkeenna & Taariikhda',
        'story_title' => 'Bank Ma Nihin. Waxaan Nahay Garabka Kobaca Ganacsigaaga.',
        'story_p1' => 'Ururka Horumarinta Maalgelinta Yar Yar ee Islaamiga ah ee Soomaaliya (SIMDA) waxaa loo asaasay inuu daboolo baahida dhabta ah ee bulshada: ganacsatada yaryar, hooyooyinka dukaamada ku leh hareeraha waddooyinka, iyo qoysaska u baahan maalgelin toos ah oo aan dulsaar lahayn.',
        'story_p2' => 'Nidaamka SIMDA gabi ahaanba kuma lug laha akoonno lacag dhigasho (saving) ama bangiyo caadi ah. Waxaan nahay shirkad dadka deyn xalaal ah siisa, badeecadaha iyo hantida muraabaxo ahaan ugu soo iibisa (baabuur, mooto, bajaaj, mobile), dhismaha boosaska u fulisa, iyo dukaamada yar-yar oo aan raasumaal siinno.',
        'story_quote' => '"Ballanqaadkeennu waa caddaan: ma jirto ribo, ma jiro dulsaar qarsoon, waana daacadnimo buuxda oo shareecada waafaqsan."',
        'story_author' => 'Guddiga Maamulka & La-talinta Shareecada ee SIMDA',

        // Pillars
        'pillars_eyebrow' => 'Adeegyada Muhiimka ah',
        'pillars_title' => 'Sida Aan Dadka & Ganacsiyada U Caawinno',
        'pil1_title' => 'Dhismaha Boosaska & Guryaha',
        'pil1_desc' => 'Dadka haysta boosas balse aan awoodin dhismaha, SIMDA waxay u dhisaysaa guryo casri ah iyadoo lacagta loogu qaybinayo qunsuliyado mudo dheer ah oo 0% ribo ah.',
        'pil2_title' => 'Muraabaxada Gaadiidka & Qalabka',
        'pil2_desc' => 'Waxaan kuu soo iibinaynaa baabuurta, mootooyinka shaqada, bajaajta, iyo taleefannada casriga ah (iPhone 18 Pro Max) annagoo kugu siinayna qorshe bixin bishiiba ah.',
        'pil3_title' => 'Taageerada Dukaamada Yaryar',
        'pil3_desc' => 'Raasumaal degdeg ah oo lagu kobcinayo dukaamada xaafadaha, miisaska suuqa, iyo ganacsiyada hooyooyinka si kor loogu qaado dakhliga qoysaska.',
        'pil4_title' => 'Wareejinta Maalinta Jimcaha & Qard Xasan',
        'pil4_desc' => 'Haddii xilliga bixintaadu ku beeganto Jimce, nidaamku si toos ah ayuu ugu wareejinayaa Sabtida, si aanay marnaba ganaax kuugu dhicin.',

        // CTA
        'cta_box_title' => 'Diyaar ma u tahay inaad nala bilowdo maalgelin xalaal ah?',
        'cta_box_desc' => 'Xisaabi jadwalkaaga lacag-bixinta ee bishii ama xiriir toos ah la samee xafiiskayaga Muqdisho.',
        'cta_btn_calc' => 'Fur Xisaabiyaha Jadwalka',
        'cta_btn_contact' => 'Nala Soo Xiriir Hadda',

        // Footer
        'footer_rights' => 'SIMDA Microfinance. Xuquuqda oo dhan way dhowran tahay.',
        'footer_developed_by' => 'Waxaa diyaariyay',
        'footer_col_newsletter' => 'Wargeyska SIMDA',
        'footer_placeholder_email' => 'Email-kaaga gali',
        'footer_btn_subscribe' => 'IS DIIWAANGELI',
        'footer_newsletter_alert' => 'Waad ku mahadsan tahay ku biirista wargeyska SIMDA!',
        'footer_col_terms' => 'Shuruudaha & Xeerarka',
        'footer_term_riba' => '0% Ribo — Raasumaalka keliya!',
        'footer_term_shift' => 'Jimcaha oo loo wareejiyo Sabtida!',
        'footer_term_charity' => '100% Sanduuqa Khayraadka oo Gooni ah!',
        'footer_follow_simda' => 'Ku xirnow SIMDA',
        'footer_col_info' => 'Xogta & Faahfaahinta',
        'footer_faq' => 'Su\'aalaha Badan ee La Isweydiiyo',
        'footer_about_simda' => 'Ku saabsan SIMDA',
        'footer_schedule_engine' => 'Jadwalka Xisaabinta SIMDA',
        'footer_col_contact' => 'Xiriirka',
        'footer_brand_sub' => 'SIMDA Microfinance',
        'footer_reg' => 'Diiwaanka Hay\'adda: SIMDA-SO-001',
        'footer_head_office' => 'Xafiiska Guud:',
        'footer_country' => 'Muqdisho, Soomaaliya',
        'footer_seal_zero_riba' => '0% RIBO',
        'footer_seal_guaranteed' => 'LA DAMMAANAD QAADAY',
        'footer_seal_sharia' => 'SHAREECADA & HANTIDHOWRKA',
        'footer_seal_compliant' => 'WAA WAFAQSAN YAHAY',
        'footer_part_of' => 'Qayb ka mid ah SIMDA',
    ],
    'ar' => [
        'nav_home' => 'الرئيسية',
        'nav_about' => 'من نحن',
        'nav_services' => 'خدماتنا',
        'nav_process' => 'آلية العمل',
        'nav_testimonials' => 'آراء العملاء',
        'nav_faq' => 'التثقيف المالي',
        'nav_contact' => 'اتصل بنا',
        'cta_dashboard' => 'لوحة التحكم',
        'cta_start' => 'ابدأ الآن',

        'hero_badge' => 'ريادة التمويل الإسلامي الأصغر في الصومال',
        'hero_title' => 'نبني الثقة. نُمكِّن النمو.',
        'hero_lead' => 'سيمدا هي مؤسسة تمويل أصغر إسلامية مكرسة لتمويل الأصول بصيغة المرابحة بدون أي فوائد ربوية (0%)، ودعم المتاجر الصغرى، وبناء المنازل لأصحاب الأراضي.',

        // 3 Floating Cards
        'card1_title' => 'ضمان 0% ربا بالكامل',
        'card1_desc' => 'لا توجد أي فوائد ربوية أو غرامات مركبة بتاتاً. تمويل إسلامي نقي يسدد فيه العميل أصل المبلغ المتفق عليه بأمانة وشفافية.',
        'card1_action' => 'تعرف على معايير 0%',

        'card2_title' => 'المرابحة لشراء الأصول والبضائع',
        'card2_desc' => 'نشتري لك السيارات والدراجات النارية والتوك توك والهواتف الذكية وبضائع المتاجر بصيغة المرابحة الشفافة. لسنا بنكاً ولا نوفر حسابات توفير.',
        'card2_action' => 'استعرض حلول الأصول',

        'card3_title' => 'تمكين المجتمع والتكافل',
        'card3_desc' => 'صندوق خيرات مستقل بنسبة 100%، وترحيل تلقائي لمواعيد الجمعة إلى السبت، ودعم مستمر لأصحاب الأراضي ومحال التجزئة الصغرى.',
        'card3_action' => 'أثرنا في المجتمع',

        // Stats
        'stat1_num' => '100%',
        'stat1_lbl' => 'خالٍ من الربا 0%',
        'stat2_num' => '$0',
        'stat2_lbl' => 'فوائد أو غرامات مركبة',
        'stat3_num' => '100%',
        'stat3_lbl' => 'صندوق خيرات مخصص',
        'stat4_num' => '15+',
        'stat4_lbl' => 'سوقاً ومنطقة مخدومة',

        // Story
        'story_eyebrow' => 'رؤيتنا ورسالتنا',
        'story_title' => 'لسنا بنكاً تقليدياً. نحن محرك لنمو مجتمعنا.',
        'story_p1' => 'تأسست الجمعية الصومالية لتنمية التمويل الأصغر الإسلامي (سيمدا) لتلبية حاجة حيوية في مجتمعنا: يحتاج أصحاب المشروعات الصغرى، والأمهات المكافحات في المتاجر، وأصحاب الأراضي إلى تمويل إسلامي حقيقي وأخلاقي يدعم نموهم دون الوقوع في شباك الربا أو التعقيدات البنكية.',
        'story_p2' => 'في سيمدا لا ندير حسابات توفير ولا نستقبل ودائع مصرفية؛ فنحن مؤسسة تمويل أصغر متخصصة تماماً في تقديم التمويل المباشر لشراء الأصول عبر المرابحة الشرعية (السيارات، الدراجات، التوك توك، الهواتف)، ودعم المتاجر بالبضائع، وبناء المنازل لأصحاب الأراضي.',
        'story_quote' => '"عهدنا ثابت: لا ربا، لا فوائد مركبة مخفية، وشفافية وأمانة مطلقة في كل أصل نُموّله للعائلات الصومالية."',
        'story_author' => 'الهيئة الاستشارية والشرعية لسيمدا',

        // Pillars
        'pillars_eyebrow' => 'ركائز التمويل لدينا',
        'pillars_title' => 'حلولنا العملية للتمكين الاقتصادي',
        'pil1_title' => 'تمويل بناء الأراضي والمنازل',
        'pil1_desc' => 'للمواطنين الذين يملكون أراضي وبحاجة للتمويل، تقدم سيمدا تمويلاً كاملاً لتشييد منازل عصرية بأقساط ميسرة بدون ربا.',
        'pil2_title' => 'مرابحة السيارات والمعدات',
        'pil2_desc' => 'نشتري السيارات والدراجات النارية والتوك توك وأحدث الهواتف الذكية (iPhone 18 Pro Max) ونبيعها بهامش ربح ثابت ومحدد عند التعاقد.',
        'pil3_title' => 'تمويل المتاجر ومحال التجزئة',
        'pil3_desc' => 'توفير بضائع ورأس مال عامل مباشر للبقالات والمتاجر الصغرى على الطرقات لزيادة الدخل وتمكين الأسر المنتجة.',
        'pil4_title' => 'قرض حسن وترحيل مواعيد الجمعة',
        'pil4_desc' => 'تمويلات متوافقة كلياً مع الشريعة مع ترحيل آلي للمواعيد التي تصادف يوم الجمعة إلى يوم السبت دون أي رسوم تأخير.',

        // CTA
        'cta_box_title' => 'هل أنت مستعد لتطوير مشروعك بتمويل إسلامي ميسر؟',
        'cta_box_desc' => 'احسب جدول أقساطك الشهرية بدون ربا بدقة في ثوانٍ أو تواصل مباشرة مع فريقنا في مقديشو.',
        'cta_btn_calc' => 'فتح حاسبة الأقساط',
        'cta_btn_contact' => 'تواصل مع المقر الرئيسي',

        // Footer
        'footer_rights' => 'سيمدا للتمويل الأصغر. جميع الحقوق محفوظة.',
        'footer_developed_by' => 'تطوير وتصميم',
        'footer_col_newsletter' => 'النشرة الإخبارية لسيمدا',
        'footer_placeholder_email' => 'بريدك الإلكتروني',
        'footer_btn_subscribe' => 'اشترك',
        'footer_newsletter_alert' => 'شكراً لاشتراكك في نشرة مستجدات سيمدا!',
        'footer_col_terms' => 'الشروط والضوابط',
        'footer_term_riba' => '0% ربا — أصل المبلغ فقط!',
        'footer_term_shift' => 'ترحيل مواعيد الجمعة للسبت تلقائياً!',
        'footer_term_charity' => '100% صندوق خيرات منفصل ومخصص!',
        'footer_follow_simda' => 'تابع سيمدا',
        'footer_col_info' => 'معلومات',
        'footer_faq' => 'الأسئلة الشائعة',
        'footer_about_simda' => 'عن سيمدا',
        'footer_schedule_engine' => 'حاسبة جداول سيمدا',
        'footer_col_contact' => 'اتصل بنا',
        'footer_brand_sub' => 'سيمدا للتمويل الأصغر',
        'footer_reg' => 'رقم التسجيل: SIMDA-SO-001',
        'footer_head_office' => 'المقر الرئيسي:',
        'footer_country' => 'مقديشو، الصومال',
        'footer_seal_zero_riba' => 'بدون ربا 0%',
        'footer_seal_guaranteed' => 'مضمون شرعاً',
        'footer_seal_sharia' => 'شريعة وتدقيق',
        'footer_seal_compliant' => 'معتمد ومطابق',
        'footer_part_of' => 'إحدى مبادرات سيمدا',
    ],
];
$t = $aboutI18n[$locale] ?? $aboutI18n['en'];

$languagesList = [
    'so' => ['label' => 'Af-Soomaali', 'code' => 'SO', 'flag' => 'assets/images/flags/flag-so.jpg'],
    'en' => ['label' => 'English (US)', 'code' => 'EN', 'flag' => 'assets/images/flags/flag-us.jpg'],
    'ar' => ['label' => 'العربية', 'code' => 'AR', 'flag' => 'assets/images/flags/flag-ar.jpg'],
];
$activeLang = $languagesList[$locale] ?? $languagesList['en'];
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $locale === 'ar' ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123B32">
    <title><?= Security::e($t['nav_about']) ?> — SIMDA</title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
</head>
<body class="fid-page">
<div class="offline-banner">
    <?= Security::e(Lang::get('connection_offline')) ?>
</div>

<!-- 1. TOP EDITORIAL NAVIGATION HEADER -->
<header class="fid-header">
    <div class="fid-container fid-nav">
        <a href="?route=home" class="fid-logo-link" title="SIMDA - Somali Islamic Microfinance Development Association">
            <img src="assets/images/simda-logo.png" alt="SIMDA Microfinance">
            <span class="fid-logo-tagline-stacked">
                <span>SOMALI ISLAMIC MICROFINANCE</span>
                <span>DEVELOPMENT ASSOCIATION</span>
            </span>
        </a>

        <ul class="fid-nav-links">
            <li><a href="?route=home#home"><?= Security::e($t['nav_home']) ?></a></li>
            <li><a href="?route=about" style="color:#C59B47;font-weight:700;"><?= Security::e($t['nav_about']) ?></a></li>
            <li><a href="?route=home#services"><?= Security::e($t['nav_services']) ?></a></li>
            <li><a href="?route=home#our-process"><?= Security::e($t['nav_process']) ?></a></li>
            <li><a href="?route=home#testimonials"><?= Security::e($t['nav_testimonials']) ?></a></li>
            <li><a href="?route=faq"><?= Security::e($t['nav_faq']) ?></a></li>
            <li><a href="?route=contact"><?= Security::e($t['nav_contact']) ?></a></li>
        </ul>

        <div style="display:flex;align-items:center;gap:10px;">
            <!-- Theme Switcher: Light & Dark Mode Icons -->
            <div class="theme-toggle-pill" id="aboutThemeToggle" title="Light Mode / Dark Mode">
                <button type="button" class="theme-pill-btn" data-theme-val="light" aria-label="Light Mode" title="<?= $locale === 'so' ? 'Habka Maalintii (Light Mode)' : 'Light Mode' ?>">
                    <?= Icons::svg('sun', 16) ?>
                </button>
                <button type="button" class="theme-pill-btn" data-theme-val="dark" aria-label="Dark Mode" title="<?= $locale === 'so' ? 'Habka Habeenkii (Dark Mode)' : 'Dark Mode' ?>">
                    <?= Icons::svg('moon', 16) ?>
                </button>
            </div>

            <!-- Language Dropdown Selector (Active flag shown, tap opens dropdown menu) -->
            <div class="simda-lang-dropdown" id="aboutLangDropdown">
                <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                    <img src="<?= Security::e($activeLang['flag']) ?>" alt="<?= Security::e($activeLang['label']) ?>" class="simda-lang-flag-circle">
                    <span class="simda-lang-code"><?= Security::e($activeLang['code']) ?></span>
                    <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 1 5 5 9 1"></polyline>
                    </svg>
                </button>
                <div class="simda-lang-menu" role="menu">
                    <?php foreach ($languagesList as $code => $info): ?>
                        <a href="?route=about&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
                            <img src="<?= Security::e($info['flag']) ?>" alt="<?= Security::e($info['label']) ?>" class="simda-lang-flag-circle">
                            <span class="simda-lang-name"><?= Security::e($info['label']) ?></span>
                            <?php if ($locale === $code): ?>
                                <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="?route=<?= $isLoggedIn ? 'dashboard' : 'login' ?>" class="fid-btn-green">
                <span><?= Security::e($isLoggedIn ? $t['cta_dashboard'] : $t['cta_start']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>
    </div>
</header>

<!-- 2. EDITORIAL HERO SECTION WITH HIGH-RES BANNER & ORGANIC WAVE -->
<section class="about-hero-section">
    <div class="fid-container">
        <div class="contact-hero-badge">
            <?= Icons::svg('award', 15) ?>
            <span><?= Security::e($t['hero_badge']) ?></span>
        </div>
        <h1 class="contact-hero-title"><?= Security::e($t['hero_title']) ?></h1>
        <p class="contact-hero-lead"><?= Security::e($t['hero_lead']) ?></p>
    </div>

    <!-- Organic wave divider transitioning into page body -->
    <div class="contact-hero-wave" aria-hidden="true">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C150,90 350,-40 500,45 C650,130 900,10 1200,50 L1200,120 L0,120 Z"></path>
        </svg>
    </div>
</section>

<!-- 3. THREE FLOATING HIGHLIGHT CARDS -->
<div class="fid-container contact-cards-wrap">
    <div class="contact-cards-grid">
        <!-- Card 1: 0% Riba -->
        <div class="contact-info-card">
            <div class="contact-icon-pill">
                <?= Icons::svg('check-circle', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card1_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card1_desc']) ?></p>
            <a href="?route=home#calculator" class="contact-card-link">
                <span><?= Security::e($t['card1_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>

        <!-- Card 2: Murabaha & Asset Empowerment (Featured Center Deep Green) -->
        <div class="contact-info-card featured">
            <div class="contact-icon-pill">
                <?= Icons::svg('layers', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card2_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card2_desc']) ?></p>
            <a href="?route=home#services" class="contact-card-link">
                <span><?= Security::e($t['card2_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>

        <!-- Card 3: Community & Charity Fund -->
        <div class="contact-info-card">
            <div class="contact-icon-pill">
                <?= Icons::svg('users', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card3_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card3_desc']) ?></p>
            <a href="?route=home#education" class="contact-card-link">
                <span><?= Security::e($t['card3_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>
    </div>
</div>

<div class="fid-container">
    <!-- 4. FOUR KEY IMPACT METRICS STRIP -->
    <div class="about-stats-strip">
        <div class="about-stat-item">
            <div class="about-stat-number num-tabular" style="color:#23845B;"><?= Security::e($t['stat1_num']) ?></div>
            <div class="about-stat-label"><?= Security::e($t['stat1_lbl']) ?></div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-number num-tabular" style="color:#123B32;"><?= Security::e($t['stat2_num']) ?></div>
            <div class="about-stat-label"><?= Security::e($t['stat2_lbl']) ?></div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-number num-tabular" style="color:#D6B66C;"><?= Security::e($t['stat3_num']) ?></div>
            <div class="about-stat-label"><?= Security::e($t['stat3_lbl']) ?></div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-number num-tabular" style="color:#123B32;"><?= Security::e($t['stat4_num']) ?></div>
            <div class="about-stat-label"><?= Security::e($t['stat4_lbl']) ?></div>
        </div>
    </div>

    <!-- 5. OUR STORY & VISION PANEL -->
    <div class="about-section-panel">
        <div class="about-story-grid">
            <div>
                <span class="fid-eyebrow" style="color:#23845B;"><?= Security::e($t['story_eyebrow']) ?></span>
                <h2 style="font-size:clamp(1.6rem, 3vw, 2.2rem);font-weight:800;color:#123B32;margin:8px 0 16px;line-height:1.25;">
                    <?= Security::e($t['story_title']) ?>
                </h2>
                <p style="font-size:0.94rem;line-height:1.75;color:#5A6660;margin-bottom:14px;">
                    <?= Security::e($t['story_p1']) ?>
                </p>
                <p style="font-size:0.94rem;line-height:1.75;color:#5A6660;margin-bottom:18px;">
                    <?= Security::e($t['story_p2']) ?>
                </p>

                <div class="about-story-quote">
                    <?= Security::e($t['story_quote']) ?>
                    <div style="font-style:normal;font-weight:700;font-size:0.8rem;color:#D6B66C;margin-top:8px;">
                        — <?= Security::e($t['story_author']) ?>
                    </div>
                </div>
            </div>

            <!-- Right Column Visual Graphic Card -->
            <div style="background:#FAF8F3;border:1px solid #ECE7DD;border-radius:20px;padding:32px 28px;text-align:center;">
                <div style="width:72px;height:72px;border-radius:50%;background:#123B32;color:#D6B66C;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;box-shadow:0 8px 24px rgba(18,59,50,0.22);">
                    <?= Icons::svg('shield', 34) ?>
                </div>
                <h3 style="font-size:1.2rem;font-weight:800;color:#123B32;margin-bottom:8px;">
                    <?= $locale === 'ar' ? 'حوكمة شرعية معتمدة 100%' : ($locale === 'so' ? '100% Shareeco & Hantidhowr' : '100% Sharia & Audit Compliant') ?>
                </h3>
                <p style="font-size:0.84rem;line-height:1.6;color:#6A756F;margin-bottom:20px;">
                    <?= $locale === 'ar' ? 'كل معاملة ومرابحة يتم تدقيقها وفصل رسوم الخيرات تماماً لصندوق التكافل المجتمعي في مقديشو.' : ($locale === 'so' ? 'Dhammaan heshiisyada iyo xisaabaadka waxaa kormeera guddi shareeco oo madax-bannaan iyadoo khidmadaha lagu wareejiyo sanduuqa khayraadka.' : 'Every contract and Murabaha disbursement is audited by an independent Sharia board, with all late-mitigation charges directed to our segregated community charity fund.') ?>
                </p>

                <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                    <span class="badge badge-success" style="padding:6px 14px;font-size:0.78rem;">0% Riba</span>
                    <span class="badge badge-gold" style="padding:6px 14px;font-size:0.78rem;">Sharia Certified</span>
                    <span class="badge badge-success" style="padding:6px 14px;font-size:0.78rem;">Pure Asset Murabaha</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. FOUR CORE SHARIA PILLARS -->
    <div class="about-section-panel">
        <div style="text-align:center;max-width:700px;margin:0 auto 10px;">
            <span class="fid-eyebrow" style="color:#23845B;"><?= Security::e($t['pillars_eyebrow']) ?></span>
            <h2 style="font-size:clamp(1.6rem, 3vw, 2.2rem);font-weight:800;color:#123B32;margin:8px 0 12px;line-height:1.25;">
                <?= Security::e($t['pillars_title']) ?>
            </h2>
        </div>

        <div class="about-pillars-grid">
            <div class="about-pillar-card">
                <div class="about-pillar-header">
                    <div class="about-pillar-icon"><?= Icons::svg('building', 22) ?></div>
                    <h3 class="about-pillar-title"><?= Security::e($t['pil1_title']) ?></h3>
                </div>
                <p class="about-pillar-desc"><?= Security::e($t['pil1_desc']) ?></p>
            </div>

            <div class="about-pillar-card">
                <div class="about-pillar-header">
                    <div class="about-pillar-icon"><?= Icons::svg('truck', 22) ?></div>
                    <h3 class="about-pillar-title"><?= Security::e($t['pil2_title']) ?></h3>
                </div>
                <p class="about-pillar-desc"><?= Security::e($t['pil2_desc']) ?></p>
            </div>

            <div class="about-pillar-card">
                <div class="about-pillar-header">
                    <div class="about-pillar-icon"><?= Icons::svg('briefcase', 22) ?></div>
                    <h3 class="about-pillar-title"><?= Security::e($t['pil3_title']) ?></h3>
                </div>
                <p class="about-pillar-desc"><?= Security::e($t['pil3_desc']) ?></p>
            </div>

            <div class="about-pillar-card">
                <div class="about-pillar-header">
                    <div class="about-pillar-icon"><?= Icons::svg('calendar', 22) ?></div>
                    <h3 class="about-pillar-title"><?= Security::e($t['pil4_title']) ?></h3>
                </div>
                <p class="about-pillar-desc"><?= Security::e($t['pil4_desc']) ?></p>
            </div>
        </div>
    </div>

    <!-- 7. BOTTOM INTERACTIVE CALL TO ACTION -->
    <div class="about-cta-card">
        <div style="max-width:620px;">
            <h2 style="font-size:clamp(1.5rem, 2.5vw, 2rem);font-weight:800;color:#FFFFFF;margin-bottom:10px;line-height:1.25;">
                <?= Security::e($t['cta_box_title']) ?>
            </h2>
            <p style="font-size:0.92rem;color:rgba(244,245,240,0.85);line-height:1.6;margin:0;">
                <?= Security::e($t['cta_box_desc']) ?>
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <a href="?route=home#calculator" class="fid-btn-green" style="background:#D6B66C;color:#123B32;border-color:#D6B66C;font-weight:800;">
                <span><?= Security::e($t['cta_btn_calc']) ?></span>
                <?= Icons::svg('calendar', 14) ?>
            </a>
            <a href="?route=contact" class="fid-btn-green" style="background:transparent;border:2px solid #FFFFFF;color:#FFFFFF;">
                <span><?= Security::e($t['cta_btn_contact']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>
    </div>
</div>

<!-- 8. THE EXACT SIGNATURE INDEX FOOTER (ROLLING HILLS SVG + 4 COLUMNS + SEALS) -->
<div class="fid-footer-wrap">
    <!-- Scenic Rolling Hills + City Skyline + Money / Cash / Coin Illustrations -->
    <div class="fid-footer-scenic" aria-hidden="true">
        <svg viewBox="0 0 1440 210" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Far Left City Buildings -->
            <g fill="#123B32">
                <rect x="0" y="96" width="18" height="75" />
                <rect x="20" y="108" width="22" height="65" />
                <rect x="44" y="116" width="18" height="55" />
                <rect x="65" y="102" width="20" height="70" />
            </g>
            <!-- Left Building Windows -->
            <g fill="#FFFFFF" opacity="0.88">
                <rect x="4" y="102" width="4" height="5" /><rect x="10" y="102" width="4" height="5" />
                <rect x="4" y="110" width="4" height="5" /><rect x="10" y="110" width="4" height="5" />
                <rect x="4" y="118" width="4" height="5" /><rect x="10" y="118" width="4" height="5" />
                <rect x="24" y="114" width="4" height="5" /><rect x="31" y="114" width="4" height="5" />
                <rect x="24" y="122" width="4" height="5" /><rect x="31" y="122" width="4" height="5" />
                <rect x="69" y="108" width="4" height="5" /><rect x="76" y="108" width="4" height="5" />
                <rect x="69" y="116" width="4" height="5" /><rect x="76" y="116" width="4" height="5" />
            </g>

            <!-- LEFT HILL MONEY / CASH STACK & GOLD COINS ILLUSTRATION -->
            <g transform="translate(95, 78)">
                <!-- Isometric Cash Bundle Stack -->
                <path d="M0,48 L95,28 L155,52 L60,72 Z" fill="#1E5C4C" stroke="#D6B66C" stroke-width="2" />
                <path d="M0,48 L0,64 L60,88 L60,72 Z" fill="#123B32" stroke="#D6B66C" stroke-width="1.5" />
                <path d="M60,72 L60,88 L155,68 L155,52 Z" fill="#16493C" stroke="#D6B66C" stroke-width="1.5" />
                <!-- Currency Band Strap -->
                <path d="M52,37 L82,31 L112,61 L82,67 Z" fill="#D6B66C" />
                <!-- Dollar Emblem on Top Bill -->
                <ellipse cx="78" cy="50" rx="16" ry="8" fill="#123B32" stroke="#FFFFFF" stroke-width="1.5" />
                <text x="78" y="54" text-anchor="middle" fill="#D6B66C" font-size="12" font-weight="800" font-family="sans-serif">$</text>
                <!-- Stacked Gold Coins Beside Cash Bundle -->
                <g transform="translate(132, 26)">
                    <ellipse cx="22" cy="36" rx="18" ry="6" fill="#B89242" />
                    <ellipse cx="22" cy="30" rx="18" ry="6" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                    <ellipse cx="22" cy="24" rx="18" ry="6" fill="#E4C782" stroke="#123B32" stroke-width="1.2" />
                    <ellipse cx="22" cy="18" rx="18" ry="6" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                    <text x="22" y="21" text-anchor="middle" fill="#123B32" font-size="9" font-weight="800" font-family="sans-serif">$</text>
                </g>
            </g>

            <!-- Lollipop Trees & Floating Coins Left-Center -->
            <g fill="#123B32">
                <rect x="313" y="102" width="3" height="28" />
                <circle cx="314.5" cy="96" r="11" />
                <circle cx="300" cy="110" r="6" />
                <rect x="299" y="114" width="2" height="16" />
                <circle cx="334" cy="104" r="8" />
                <rect x="333" y="110" width="2.5" height="18" />
                <circle cx="352" cy="96" r="3.5" fill="#D6B66C" />
                <circle cx="678" cy="100" r="9" />
                <rect x="677" y="106" width="2.5" height="22" />
                <circle cx="702" cy="95" r="4" fill="#D6B66C" />
            </g>

            <!-- Center Financial District Skyline & Wheel -->
            <g fill="#123B32">
                <rect x="720" y="92" width="24" height="75" />
                <!-- Ferris / Growth Wheel -->
                <circle cx="788" cy="112" r="32" fill="none" stroke="#123B32" stroke-width="2.5" />
                <circle cx="788" cy="112" r="24" fill="none" stroke="#D6B66C" stroke-width="1.5" stroke-dasharray="4 4" />
                <circle cx="788" cy="112" r="6" fill="#D6B66C" />
                <line x1="788" y1="80" x2="788" y2="144" stroke="#123B32" stroke-width="1.5" />
                <line x1="756" y1="112" x2="820" y2="112" stroke="#123B32" stroke-width="1.5" />
                <line x1="765" y1="89" x2="811" y2="135" stroke="#123B32" stroke-width="1.5" />
                <line x1="811" y1="89" x2="765" y2="135" stroke="#123B32" stroke-width="1.5" />
                <!-- Center Towers -->
                <rect x="832" y="110" width="20" height="60" />
                <rect x="856" y="96" width="24" height="74" />
                <rect x="896" y="102" width="22" height="68" />
                <rect x="922" y="122" width="18" height="48" />
                <rect x="960" y="114" width="20" height="58" />
            </g>
            <!-- Center Building Windows -->
            <g fill="#FFFFFF" opacity="0.88">
                <rect x="725" y="98" width="5" height="5" /><rect x="734" y="98" width="5" height="5" />
                <rect x="725" y="107" width="5" height="5" /><rect x="734" y="107" width="5" height="5" />
                <rect x="725" y="116" width="5" height="5" /><rect x="734" y="116" width="5" height="5" />
                <rect x="861" y="102" width="5" height="5" /><rect x="870" y="102" width="5" height="5" />
                <rect x="861" y="111" width="5" height="5" /><rect x="870" y="111" width="5" height="5" />
                <rect x="901" y="108" width="4" height="5" /><rect x="909" y="108" width="4" height="5" />
                <rect x="901" y="117" width="4" height="5" /><rect x="909" y="117" width="4" height="5" />
                <rect x="964" y="120" width="4" height="5" /><rect x="971" y="120" width="4" height="5" />
            </g>

            <!-- Floating Hot-Air Money Balloon in Sky -->
            <g transform="translate(925, 22)">
                <path d="M24,0 C38,0 48,11 48,25 C48,39 30,54 27,62 L21,62 C18,54 0,39 0,25 C0,11 10,0 24,0 Z" fill="#123B32" />
                <circle cx="24" cy="23" r="11" fill="#D6B66C" />
                <text x="24" y="28" text-anchor="middle" fill="#123B32" font-size="14" font-weight="800" font-family="sans-serif">$</text>
                <rect x="21" y="65" width="6" height="6" rx="1" fill="#123B32" />
            </g>

            <!-- Right-Center Lollipop Trees -->
            <g fill="#123B32">
                <circle cx="1044" cy="112" r="11" />
                <rect x="1042.5" y="120" width="3" height="28" />
                <circle cx="1064" cy="124" r="7" />
                <rect x="1063" y="129" width="2" height="20" />
                <circle cx="1115" cy="110" r="9" />
                <rect x="1114" y="117" width="2.5" height="24" />
                <circle cx="1135" cy="102" r="4" fill="#D6B66C" />
                <circle cx="1408" cy="96" r="11" />
                <rect x="1406.5" y="105" width="3" height="30" />
                <circle cx="1426" cy="112" r="7" />
                <rect x="1425" y="117" width="2" height="22" />
            </g>

            <!-- RIGHT HILL MONEY WALLET, BANKNOTES & COINS ILLUSTRATION -->
            <g transform="translate(1178, 56)">
                <!-- Fanning Dollar Bills Emerging from Wallet -->
                <g transform="rotate(-12, 60, 35)">
                    <rect x="22" y="6" width="96" height="44" rx="5" fill="#23845B" stroke="#D6B66C" stroke-width="1.8" />
                    <circle cx="70" cy="28" r="11" fill="#123B32" />
                    <text x="70" y="33" text-anchor="middle" fill="#D6B66C" font-size="13" font-weight="800" font-family="sans-serif">$</text>
                </g>
                <g transform="rotate(-3, 75, 35)">
                    <rect x="34" y="12" width="96" height="44" rx="5" fill="#1B5246" stroke="#D6B66C" stroke-width="1.8" />
                </g>
                <!-- Rounded Microfinance Wallet / Vault Body -->
                <rect x="10" y="28" width="142" height="54" rx="14" fill="#123B32" stroke="#D6B66C" stroke-width="2" />
                <path d="M18,68 Q80,82 144,66" fill="none" stroke="#FFFFFF" stroke-width="2.5" opacity="0.85" />
                <!-- Wallet Gold Clasp -->
                <rect x="116" y="45" width="36" height="20" rx="10" fill="#D6B66C" />
                <circle cx="127" cy="55" r="5" fill="#123B32" />
                <!-- Gold Coin Stack Beside Wallet -->
                <ellipse cx="170" cy="72" rx="16" ry="5.5" fill="#B89242" />
                <ellipse cx="170" cy="65" rx="16" ry="5.5" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                <ellipse cx="170" cy="58" rx="16" ry="5.5" fill="#E4C782" stroke="#123B32" stroke-width="1.2" />
                <text x="170" y="61" text-anchor="middle" fill="#123B32" font-size="9" font-weight="800" font-family="sans-serif">$</text>
            </g>

            <!-- Main Rolling Hills Silhouette Path Connecting Seamlessly to Footer -->
            <path d="M0,145 C110,162 210,162 320,122 C430,82 515,74 635,116 C755,158 890,165 1035,142 C1165,120 1265,105 1370,118 C1405,123 1425,132 1440,140 L1440,212 L0,212 Z" fill="#123B32" />
        </svg>
    </div>

    <!-- Main 4-Column Footer Body -->
    <footer class="fid-footer">
        <div class="fid-container">
            <div class="fid-footer-grid">
                <!-- Col 1: Newsletter -->
                <div>
                    <h5><?= Security::e($t['footer_col_newsletter']) ?></h5>
                    <form class="fid-footway-newsletter" onsubmit="event.preventDefault(); this.innerHTML = '<div style=\'color:#D6B66C;font-size:0.84rem;font-weight:600;padding:10px 0;line-height:1.4;\'>✓ <?= Security::e($t['footer_newsletter_alert']) ?></div>';">
                        <input type="email" placeholder="<?= Security::e($t['footer_placeholder_email']) ?>" required>
                        <button type="submit" class="fid-footway-sub-btn"><?= Security::e($t['footer_btn_subscribe']) ?></button>
                    </form>
                </div>

                <!-- Col 2: Terms and conditions -->
                <div>
                    <h5><?= Security::e($t['footer_col_terms']) ?></h5>
                    <ul class="fid-footer-links">
                        <li><?= Security::e($t['footer_term_riba']) ?></li>
                        <li><?= Security::e($t['footer_term_shift']) ?></li>
                        <li><?= Security::e($t['footer_term_charity']) ?></li>
                    </ul>
                    <div style="margin-top:24px;display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:700;font-size:0.84rem;color:#FFFFFF;"><?= Security::e($t['footer_follow_simda']) ?></span>
                        <a href="?route=contact" style="color:#FFFFFF;display:inline-flex;" aria-label="Website"><?= Icons::svg('globe', 18) ?></a>
                        <a href="?route=contact" style="color:#FFFFFF;display:inline-flex;" aria-label="Community"><?= Icons::svg('users', 18) ?></a>
                    </div>
                </div>

                <!-- Col 3: Information -->
                <div>
                    <h5><?= Security::e($t['footer_col_info']) ?></h5>
                    <ul class="fid-footer-links">
                        <li><a href="?route=faq"><?= Security::e($t['footer_faq']) ?></a></li>
                        <li><a href="?route=about"><?= Security::e($t['footer_about_simda']) ?></a></li>
                        <li><a href="?route=home#calculator"><?= Security::e($t['footer_schedule_engine']) ?></a></li>
                    </ul>
                    <ul class="fid-footer-links" style="margin-top:20px;">
                        <li class="num-tabular" dir="ltr"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></li>
                        <li dir="ltr"><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?></li>
                    </ul>
                </div>

                <!-- Col 4: Contact -->
                <div>
                    <h5><?= Security::e($t['footer_col_contact']) ?></h5>
                    <ul class="fid-footer-links">
                        <li><strong style="color:#FFFFFF;"><?= Security::e($t['footer_brand_sub']) ?></strong></li>
                        <li><?= Security::e($t['footer_reg']) ?></li>
                    </ul>
                    <ul class="fid-footer-links" style="margin-top:18px;">
                        <li><strong style="color:#FFFFFF;"><?= Security::e($t['footer_head_office']) ?></strong></li>
                        <li><?= Security::e($settings['institution_address'] ?? 'Mogadishu Main Boulevard, Hodan') ?></li>
                        <li><?= Security::e($t['footer_country']) ?></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Row: Trust Seals (Left) + Group Info & Flags (Center) + Copyright -->
            <div class="fid-footer-bottom-bar">
                <!-- Left: Trust & Security Seals -->
                <div class="fid-trust-badges">
                    <div class="fid-trust-seal">
                        <div class="fid-trust-seal-circle">0%</div>
                        <div style="font-size:0.72rem;font-weight:800;line-height:1.25;color:#FFFFFF;">
                            <?= Security::e($t['footer_seal_zero_riba']) ?><br><span style="color:#D6B66C;"><?= Security::e($t['footer_seal_guaranteed']) ?></span>
                        </div>
                    </div>
                    <div class="fid-trust-seal">
                        <div class="fid-trust-seal-circle"><?= Icons::svg('shield', 18) ?></div>
                        <div style="font-size:0.72rem;font-weight:800;line-height:1.25;color:#FFFFFF;">
                            <?= Security::e($t['footer_seal_sharia']) ?><br><span style="color:#23845B;"><?= Security::e($t['footer_seal_compliant']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Center: Group Info, Language Switcher & Copyright -->
                <div style="text-align:center;font-size:0.78rem;color:rgba(244,245,240,0.82);line-height:1.6;">
                    <div><?= Security::e($t['footer_part_of']) ?> (<?= Security::e(Lang::get('app_full_name')) ?>).</div>
                    <div dir="ltr"><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?>, <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></span></div>
                    <div style="display:flex;justify-content:center;align-items:center;gap:12px;margin:12px 0;">
                        <a href="?route=about&lang=so" title="Af-Soomaali (Somalia)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'so' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'so' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-so.jpg" alt="Somalia" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=about&lang=en" title="English (USA)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'en' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'en' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-us.jpg" alt="USA" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=about&lang=ar" title="العربية (Arabic)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'ar' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'ar' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-ar.jpg" alt="Arabic" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                    </div>
                    <div style="font-size:0.74rem;color:rgba(244,245,240,0.68);">&copy; <?= date('Y') ?> <?= Security::e($t['footer_rights'] ?? 'SIMDA Microfinance. All rights reserved.') ?></div>
                    <div style="font-size:0.78rem;color:rgba(244,245,240,0.8);margin-top:4px;"><?= Security::e($t['footer_developed_by'] ?? 'Developed by') ?> <a href="https://anazabdiwahid.netlify.app/" target="_blank" rel="noopener noreferrer" style="color:#D6B66C;font-weight:800;text-decoration:none;border-bottom:1px dashed rgba(214,182,108,0.6);" onmouseover="this.style.color='#E4C782'" onmouseout="this.style.color='#D6B66C'">Anas Abdiwahid</a></div>
                </div>
                <div></div>
            </div>
        </div>
    </footer>
</div>

<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>

