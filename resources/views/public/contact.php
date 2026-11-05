<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$isLoggedIn = AuthMiddleware::check();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);
$csrf = Security::csrfToken();

$contactI18n = [
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

        'hero_badge' => 'Require Consultation? Contact Us Now',
        'hero_title' => 'Contact Us',
        'hero_lead' => 'We are here to support your growth through transparent, ethical, and Sharia-compliant Islamic microfinance services in Mogadishu and across Somalia.',

        // Cards
        'card_phone_title' => '(+252) 61 6256534',
        'card_phone_desc' => 'Direct customer support and WhatsApp consultation with our financing specialists.',
        'card_phone_action' => 'Call Directly',

        'card_mail_title' => 'anasabdiwahidhussein@gmail.com',
        'card_mail_desc' => 'Send documents, official financing inquiries, and partnership requests directly.',
        'card_mail_action' => 'Send Email',

        'card_loc_title' => 'Mogadishu, Somalia',
        'card_loc_desc' => 'Mogadishu Main Boulevard, Hodan District • Visit our office for in-person advisory.',
        'card_loc_action' => 'Open Google Maps',

        // Working time
        'time_title' => 'Our Working Time',
        'time_desc' => 'Our branch teams and officers are ready to assist you during the following business hours:',
        'time_mon_thu' => 'Monday – Thursday: 8:00 AM – 4:30 PM',
        'time_sat' => 'Saturday: 8:30 AM – 1:30 PM',
        'time_fri' => 'Friday: Closed (Weekly Holiday & Worship)',

        // Map
        'map_title' => 'Location / Head Office:',
        'btn_open_maps' => 'Open in Google Maps',

        // Form
        'form_badge' => 'Contact Us',
        'form_title' => 'Get In Touch !',
        'label_name' => 'Full Name',
        'label_email' => 'Email Address',
        'label_phone' => 'Phone / WhatsApp Number',
        'label_subject' => 'Financing Solution of Interest',
        'label_message' => 'Your Message / Inquiry',
        'opt_murabaha' => 'Murabaha Asset Financing (Vehicle, Equipment, Tech)',
        'opt_construction' => 'Home & Plot Construction Financing',
        'opt_retail' => 'Small Retail & Kiosk Working Capital',
        'opt_qard' => 'Zero-Riba Micro-Loan (0% Ribo)',
        'opt_general' => 'General Information & Inquiries',
        'btn_submit' => 'Submit Message',

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

        'hero_badge' => 'Ma u baahan tahay la-tashi? Nala soo xiriir hadda',
        'hero_title' => 'Nala Soo Xiriir',
        'hero_lead' => 'Waxaan diyaar u nahay inaan kugu garab istaagno adeegyo maaliyadeed oo hufan, xalaal ah, oo 100% waafaqsan shareecada Islaamka meel kasta oo aad joogto.',

        // Cards
        'card_phone_title' => '(+252) 61 6256534',
        'card_phone_desc' => 'Khadka tooska ah iyo WhatsApp-ka si aad u hesho talo iyo caawimaad degdeg ah.',
        'card_phone_action' => 'Toos u Soo Wac',

        'card_mail_title' => 'anasabdiwahidhussein@gmail.com',
        'card_mail_desc' => 'Noo soo dir waraaqaha, codsiyada gaarka ah, iyo su\'aalahaaga rasmiga ah.',
        'card_mail_action' => 'Iimayl Soo Dir',

        'card_loc_title' => 'Muqdisho, Soomaaliya',
        'card_loc_desc' => 'Waddada Weyn ee Muqdisho, Degmada Hodan • Booqo xafiiskeena si aan fool-ka-fool u wada hadalno.',
        'card_loc_action' => 'Ka Eeg Google Maps',

        // Working time
        'time_title' => 'Saacadaha Shaqada',
        'time_desc' => 'Laamaha SIMDA iyo saraakiishayada maaliyadeed waxay diyaar kuu yihiin waqtiyadan:',
        'time_mon_thu' => 'Isniin – Khamiis: 8:00 AM – 4:30 PM',
        'time_sat' => 'Sabti: 8:30 AM – 1:30 PM',
        'time_fri' => 'Jimco: Waa Fasax (Maalinta Jimcaha & Cibaadada)',

        // Map
        'map_title' => 'Goobta Xafiiska:',
        'btn_open_maps' => 'Fur Google Maps',

        // Form
        'form_badge' => 'Nala Soo Xiriir',
        'form_title' => 'Farriin Noo Soo Dir !',
        'label_name' => 'Magacaaga oo Buuxa',
        'label_email' => 'Email-kaaga',
        'label_phone' => 'Telefoonkaaga / WhatsApp',
        'label_subject' => 'Adeegga aad u baahan tahay',
        'label_message' => 'Faahfaahinta Farriintaada',
        'opt_murabaha' => 'Muraabaxada Gaadiidka & Qalabka (Baabuur, Mooto, Mobile)',
        'opt_construction' => 'Dhismaha Boosaska & Guryaha',
        'opt_retail' => 'Taageerada Dukaamada Yaryar & Kiosks',
        'opt_qard' => 'Dayn Xalaal ah (0% Ribo)',
        'opt_general' => 'Talo-bixin & Su\'aalo Guud',
        'btn_submit' => 'Dir Farriinta Hadda',

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

        'hero_badge' => 'هل تحتاج إلى استشارة تمويلية؟ اتصل بنا الآن',
        'hero_title' => 'اتصل بنا',
        'hero_lead' => 'نحن هنا لمساعدتك وتقديم حلول التمويل الإسلامي الأخلاقية والميسرة بنسبة 0% ربا في مقديشو وكافة أرجاء الصومال.',

        // Cards
        'card_phone_title' => '(+252) 61 6256534',
        'card_phone_desc' => 'خدمة العملاء المباشرة والاستشارات الفورية عبر الهاتف والواتساب.',
        'card_phone_action' => 'اتصل بنا مباشرة',

        'card_mail_title' => 'anasabdiwahidhussein@gmail.com',
        'card_mail_desc' => 'أرسل المستندات والاستفسارات الرسمية وطلبات الشراكة والتمويل.',
        'card_mail_action' => 'أرسل بريداً إلكترونياً',

        'card_loc_title' => 'مقديشو، الصومال',
        'card_loc_desc' => 'شارع مقديشو الرئيسي، حي هودان • تفضل بزيارة مكتبنا للاستشارة الشخصية.',
        'card_loc_action' => 'فتح في خرائط جوجل',

        // Working time
        'time_title' => 'أوقات العمل الرسمية',
        'time_desc' => 'فريق سيمدا ومستشارونا الماليون في خدمتكم خلال ساعات العمل التالية:',
        'time_mon_thu' => 'الإثنين – الخميس: 8:00 صباحاً – 4:30 مساءً',
        'time_sat' => 'السبت: 8:30 صباحاً – 1:30 ظهراً',
        'time_fri' => 'الجمعة: عطلة أسبوعية رسمية',

        // Map
        'map_title' => 'مقر المكتب الرئيسي:',
        'btn_open_maps' => 'فتح في خرائط جوجل',

        // Form
        'form_badge' => 'تواصل معنا',
        'form_title' => 'أرسل لنا استفسارك !',
        'label_name' => 'الاسم الكامل',
        'label_email' => 'البريد الإلكتروني',
        'label_phone' => 'رقم الهاتف / واتساب',
        'label_subject' => 'نوع التمويل المطلوب',
        'label_message' => 'نص الرسالة أو الاستفسار',
        'opt_murabaha' => 'تمويل المرابحة (سيارات، دراجات، هواتف، أجهزة)',
        'opt_construction' => 'تمويل بناء الأراضي والمنازل',
        'opt_retail' => 'تمويل المتاجر ومنافذ البيع الصغرى',
        'opt_qard' => 'قرض حسن بدون ربا (0%)',
        'opt_general' => 'استفسارات ومعلومات عامة',
        'btn_submit' => 'إرسال الرسالة الآن',

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
$t = $contactI18n[$locale] ?? $contactI18n['en'];

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
    <title><?= Security::e($t['hero_title']) ?> — SIMDA</title>
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
            <li><a href="?route=about"><?= Security::e($t['nav_about']) ?></a></li>
            <li><a href="?route=home#services"><?= Security::e($t['nav_services']) ?></a></li>
            <li><a href="?route=home#our-process"><?= Security::e($t['nav_process']) ?></a></li>
            <li><a href="?route=home#testimonials"><?= Security::e($t['nav_testimonials']) ?></a></li>
            <li><a href="?route=faq"><?= Security::e($t['nav_faq']) ?></a></li>
            <li><a href="?route=contact" class="active"><?= Security::e($t['nav_contact']) ?></a></li>
        </ul>

        <div style="display:flex;align-items:center;gap:10px;">
            <!-- Theme Switcher: Light & Dark Mode Icons -->
            <div class="theme-toggle-pill" id="contactThemeToggle" title="Light Mode / Dark Mode">
                <button type="button" class="theme-pill-btn" data-theme-val="light" aria-label="Light Mode" title="<?= $locale === 'so' ? 'Habka Maalintii (Light Mode)' : 'Light Mode' ?>">
                    <?= Icons::svg('sun', 16) ?>
                </button>
                <button type="button" class="theme-pill-btn" data-theme-val="dark" aria-label="Dark Mode" title="<?= $locale === 'so' ? 'Habka Habeenkii (Dark Mode)' : 'Dark Mode' ?>">
                    <?= Icons::svg('moon', 16) ?>
                </button>
            </div>

            <!-- Language Dropdown Selector (Active flag shown, tap opens dropdown menu) -->
            <div class="simda-lang-dropdown" id="contactLangDropdown">
                <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                    <img src="<?= Security::e($activeLang['flag']) ?>" alt="<?= Security::e($activeLang['label']) ?>" class="simda-lang-flag-circle">
                    <span class="simda-lang-code"><?= Security::e($activeLang['code']) ?></span>
                    <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 1 5 5 9 1"></polyline>
                    </svg>
                </button>
                <div class="simda-lang-menu" role="menu">
                    <?php foreach ($languagesList as $code => $info): ?>
                        <a href="?route=contact&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

<!-- 2. CONTACT HERO HEADER SECTION WITH NATURE/ENTERPRISE PHOTO OVERLAY & WAVE DIVIDER -->
<section class="contact-hero-section">
    <div class="fid-container">
        <div class="contact-hero-badge">
            <?= Icons::svg('message-square', 14) ?>
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

<!-- 3. THREE FLOATING CONTACT INFO CARDS -->
<div class="fid-container contact-cards-wrap">
    <div class="contact-cards-grid">
        <!-- Card 1: Phone & WhatsApp -->
        <div class="contact-info-card">
            <div class="contact-icon-pill">
                <?= Icons::svg('phone', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card_phone_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card_phone_desc']) ?></p>
            <a href="tel:+252616256534" class="contact-card-link">
                <span><?= Security::e($t['card_phone_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>

        <!-- Card 2: Email & Inquiries (Featured Center Deep Green Card) -->
        <div class="contact-info-card featured">
            <div class="contact-icon-pill">
                <?= Icons::svg('send', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card_mail_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card_mail_desc']) ?></p>
            <a href="mailto:anasabdiwahidhussein@gmail.com" class="contact-card-link">
                <span><?= Security::e($t['card_mail_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>

        <!-- Card 3: Location & Office -->
        <div class="contact-info-card">
            <div class="contact-icon-pill">
                <?= Icons::svg('building', 22) ?>
            </div>
            <h3 class="contact-card-title"><?= Security::e($t['card_loc_title']) ?></h3>
            <p class="contact-card-desc"><?= Security::e($t['card_loc_desc']) ?></p>
            <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener noreferrer" class="contact-card-link">
                <span><?= Security::e($t['card_loc_action']) ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>
    </div>
</div>

<!-- 4. MAIN TWO-COLUMN SECTION: WORKING TIME & MAP (LEFT) vs CONTACT FORM (RIGHT) -->
<div class="fid-container">
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom:24px;border-radius:12px;padding:14px 18px;background:rgba(35,132,91,0.12);color:#123B32;border:1px solid #23845B;display:flex;align-items:center;gap:10px;">
            <?= Icons::svg('check-circle', 18) ?>
            <strong><?= Security::e($_SESSION['flash_success']) ?></strong>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" style="margin-bottom:24px;border-radius:12px;padding:14px 18px;background:rgba(201,75,75,0.12);color:#C94B4B;border:1px solid #C94B4B;display:flex;align-items:center;gap:10px;">
            <?= Icons::svg('alert-triangle', 18) ?>
            <strong><?= Security::e($_SESSION['flash_error']) ?></strong>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="contact-main-grid">
        <!-- Left Column: Our Working Time & Interactive Office Map -->
        <div class="contact-time-card">
            <h2 class="contact-time-title"><?= Security::e($t['time_title']) ?></h2>
            <p class="contact-time-desc"><?= Security::e($t['time_desc']) ?></p>

            <ul class="contact-hours-list">
                <li class="contact-hours-item">
                    <span><?= Icons::svg('clock', 16) ?> <strong><?= Security::e($t['time_mon_thu']) ?></strong></span>
                </li>
                <li class="contact-hours-item">
                    <span><?= Icons::svg('clock', 16) ?> <strong><?= Security::e($t['time_sat']) ?></strong></span>
                </li>
                <li class="contact-hours-item holiday">
                    <span><?= Icons::svg('alert-triangle', 16) ?> <?= Security::e($t['time_fri']) ?></span>
                </li>
            </ul>

            <h3 style="font-size:1.1rem;font-weight:800;color:#123B32;margin:10px 0 10px;">
                <?= Security::e($t['map_title']) ?>
            </h3>
            <div class="contact-map-wrap">
                <iframe
                    src="https://maps.google.com/maps?q=Anas%20Abdiwahid%2C%20Mogadishu%2C%20Somalia&z=15&output=embed"
                    title="SIMDA Head Office Location"
                    style="border:0;width:100%;height:100%;min-height:220px;display:block;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
                <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener noreferrer"
                   style="position:absolute;right:12px;bottom:12px;background:#123B32;color:#FFF;font-size:0.75rem;font-weight:700;padding:7px 14px;border-radius:999px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 6px 14px rgba(0,0,0,0.22);text-decoration:none;">
                    <?= Security::e($t['btn_open_maps']) ?> <?= Icons::svg('arrow-right', 12) ?>
                </a>
            </div>
        </div>

        <!-- Right Column: Get In Touch Contact Form -->
        <div class="contact-form-panel">
            <div class="contact-form-badge">
                <?= Icons::svg('message-square', 13) ?>
                <span><?= Security::e($t['form_badge']) ?></span>
            </div>
            <h2 class="contact-form-heading"><?= Security::e($t['form_title']) ?></h2>

            <form action="?route=contact_submit" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

                <div class="contact-field-group">
                    <label class="contact-field-label" for="contact_name"><?= Security::e($t['label_name']) ?> *</label>
                    <input type="text" id="contact_name" name="name" class="contact-input-styled" placeholder="e.g. Anas Abdiwahid" required>
                </div>

                <div class="contact-field-group">
                    <label class="contact-field-label" for="contact_email"><?= Security::e($t['label_email']) ?></label>
                    <input type="email" id="contact_email" name="email" class="contact-input-styled" placeholder="e.g. anas@example.com">
                </div>

                <div class="contact-field-group">
                    <label class="contact-field-label" for="contact_phone"><?= Security::e($t['label_phone']) ?> *</label>
                    <input type="tel" id="contact_phone" name="phone" class="contact-input-styled" placeholder="e.g. +252 61 6256534" required>
                </div>

                <div class="contact-field-group">
                    <label class="contact-field-label" for="contact_subject"><?= Security::e($t['label_subject']) ?></label>
                    <select id="contact_subject" name="subject" class="contact-input-styled">
                        <option value="Murabaha Financing"><?= Security::e($t['opt_murabaha']) ?></option>
                        <option value="Home & Plot Construction"><?= Security::e($t['opt_construction']) ?></option>
                        <option value="Small Retail & Kiosk"><?= Security::e($t['opt_retail']) ?></option>
                        <option value="Zero-Riba Micro-Loan"><?= Security::e($t['opt_qard']) ?></option>
                        <option value="General Inquiry"><?= Security::e($t['opt_general']) ?></option>
                    </select>
                </div>

                <div class="contact-field-group">
                    <label class="contact-field-label" for="contact_message"><?= Security::e($t['label_message']) ?> *</label>
                    <textarea id="contact_message" name="message" class="contact-input-styled" rows="4" placeholder="..." required></textarea>
                </div>

                <button type="submit" class="contact-btn-submit">
                    <span><?= Security::e($t['btn_submit']) ?></span>
                    <?= Icons::svg('arrow-right', 16) ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 5. SCENIC SKYLINE & MONEY ILLUSTRATION FOOTER (EXACTLY AS ON INDEX / HOME.PHP) -->
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

            <!-- LEFT HILL MONEY / CASH STACK & GOLD COINS ILLUSTRATION (Replaces Left Shoe) -->
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

            <!-- RIGHT HILL MONEY WALLET, BANKNOTES & COINS ILLUSTRATION (Replaces Right Shoe) -->
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
                        <a href="?route=contact&lang=so" title="Af-Soomaali (Somalia)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'so' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'so' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-so.jpg" alt="Somalia" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=contact&lang=en" title="English (USA)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'en' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'en' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-us.jpg" alt="USA" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=contact&lang=ar" title="العربية (Arabic)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'ar' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'ar' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
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

