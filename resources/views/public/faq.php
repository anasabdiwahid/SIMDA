<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$tAll = [
    'en' => [
        'page_title' => 'FAQ - Frequently Asked Questions | SIMDA',
        'nav_home' => 'Home',
        'nav_about' => 'About Us',
        'nav_services' => 'Services',
        'nav_process' => 'Our Process',
        'nav_testimonials' => 'Testimonials',
        'nav_faq' => 'FAQ',
        'nav_contact' => 'Contact',
        'cta_dashboard' => 'Dashboard',
        'cta_start' => 'Get Started',

        'hero_badge' => 'SIMDA Knowledge & Advisory Base',
        'watermark' => 'Frequently Ask Question',
        'faq_title' => 'Frequently Asked Questions',
        'faq_subtitle' => 'Have questions about Sharia-compliant financing? We are here to guide you with complete clarity.',
        'click_here_lead' => 'Click Here to explore our ethical microfinance answers.',

        // FAQ Items
        'q1' => 'What is Sharia-compliant Murabaha financing?',
        'a1' => 'Murabaha is an Islamic cost-plus sales contract where SIMDA purchases the asset (motorcycle, phone, inventory) on your behalf and sells it to you at cost plus a transparent, fixed profit agreed upfront. There is 0% Riba, no compounding interest, and no hidden penalties.',

        'q2' => 'Does SIMDA accept savings deposits or manage bank accounts?',
        'a2' => 'No. SIMDA is strictly an ethical Islamic microfinance association, not a deposit-taking commercial bank. We provide micro-loans, asset financing, construction financing, and small kiosk working capital without holding customer savings.',

        'q3' => 'How does the Friday-to-Saturday payment shift work?',
        'a3' => 'Our automated schedule engine respects Islamic Friday observance. Any installment originally falling on a Friday is automatically shifted to Saturday with zero late fees, protecting your schedule integrity seamlessly.',

        'q4' => 'What assets and equipment can I finance through SIMDA?',
        'a4' => 'We finance motorcycles, delivery scooters, tuk-tuks (bajaaj), commercial kiosk inventory, smartphones, and building materials for plot owners with clear ownership documentation.',

        'q5' => 'Who is eligible to apply for SIMDA microfinance?',
        'a5' => 'Somali entrepreneurs, kiosk owners, market vendors, delivery drivers, and solidarity groups. Applicants need a verified guarantor, identification, and a viable business or income source.',

        'q6' => 'What is the 100% Segregated Charity Fund?',
        'a6' => 'Any involuntary late payments or charity contributions are kept 100% separate from SIMDA operational revenues and allocated entirely to community welfare, relief, and disadvantaged entrepreneur grants.',

        // Right Card: AI Chatbot (Multilingual)
        'any_q_title' => 'SIMDA Smart AI',
        'any_q_desc' => 'Ask anything about 0% Riba, Murabaha, or loans in English, Somali, or Arabic.',
        'chat_status_online' => 'Online',
        'chat_welcome_msg' => 'Assalamu Alaykum! I am SIMDA\'s Smart AI Assistant. Ask me anything about our 0% Riba Sharia-compliant financing, Murabaha system, requirements, or policies in English, Somali, or Arabic!',
        'chip_1' => '0% Riba ma dhabbaa?',
        'chip_2' => 'Do you offer savings?',
        'chip_3' => 'How can I apply?',
        'chip_4' => 'What is Murabaha?',
        'chat_placeholder' => 'Ask in English, Somali, or Arabic...',
        'chat_clear_title' => 'Reset Chat',
        'let_me_know' => 'Type your question...',
        'input_placeholder' => 'Ask in English, Somali, or Arabic...',
        'btn_send' => 'Send',
        'send_alert' => 'Thank you! Your question has been submitted.',

        // Footer
        'footer_col_newsletter' => "SIMDA's Newsletter",
        'footer_placeholder_email' => 'Your email',
        'footer_btn_subscribe' => 'SUBSCRIBE',
        'footer_newsletter_alert' => 'Thank you for subscribing to SIMDA updates!',
        'footer_col_terms' => 'Terms and conditions',
        'footer_term_riba' => '0% Riba - Principal only!',
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
        'footer_head_office' => 'Head office',
        'footer_country' => 'Mogadishu, Somalia',
        'footer_seal_zero_riba' => 'ZERO RIBA',
        'footer_seal_guaranteed' => 'GUARANTEED',
        'footer_seal_sharia' => 'SHARIA & AUDIT',
        'footer_seal_compliant' => 'COMPLIANT',
        'footer_part_of' => 'Part of SIMDA',
        'footer_rights' => 'SIMDA Microfinance. All rights reserved.',
        'footer_developed_by' => 'Developed by',
    ],
    'so' => [
        'page_title' => 'Su\'aalaha Badan ee La Isweydiiyo (FAQ) | SIMDA',
        'nav_home' => 'Bogga Hore',
        'nav_about' => 'Nagu Saabsan',
        'nav_services' => 'Adeegyada',
        'nav_process' => 'Habka Shaqada',
        'nav_testimonials' => 'Aragtida Macaamiisha',
        'nav_faq' => 'FAQ',
        'nav_contact' => 'Xiriirka',
        'cta_dashboard' => 'Dashboard',
        'cta_start' => 'Bilow Hadda',

        'hero_badge' => 'Xogta & Su\'aalaha Badanaa La Isweydiiyo',
        'watermark' => 'Frequently Ask Question',
        'faq_title' => 'Su\'aalaha Badanaa La Isweydiiyo',
        'faq_subtitle' => 'Su\'aalo ma ka qabtaa maalgelinta xalaasha ah? Waxaan halkan kuugu diyaarinay jawaabo buuxa oo cad.',
        'click_here_lead' => 'Guji Halkan si aad u barato maalgelinta xalaasha ah ee SIMDA.',

        // FAQ Items
        'q1' => 'Waa maxay Maalgelinta Muraabaxada ee Shareecada waafaqsan?',
        'a1' => 'Muraabaxadu waa heshiis iib Islaami ah oo SIMDA ay hantida (mooto, baabuur, badeeco) kuugu soo iibinayso qiimaha dhabta ah, kadibna kuugu iibinayso faa\'iido go\'an oo bilowgaba lagu heshiiyo. Ma jirto wax Ribo ah, dulsaar dulsaar ah, ama lacag qarsoon.',

        'q2' => 'SIMDA ma leedahay lacag dhigasho (savings) mise akoonno bangi?',
        'a2' => 'Maya. SIMDA waa hay\'ad maalgelineed oo Islaami ah oo dadka deyn xalaal ah iyo qalab siisa, ma nihin bangi lacag la dhigto. Ma jiro qof lacag keyd ah noo dhigan kara.',

        'q3' => 'Sidee ayuu u shaqeeyaa nidaamka dib-u-dhigidda Jimcaha ilaa Sabtida?',
        'a3' => 'Nidaamkayaga xisaabtu wuxuu dhowraa maalinta Jimcaha ah oo ah maalin cibaado. Qayb kasta oo lacag bixin ah oo Jimco ku aadda si toos ah ayay Sabtida ugu wareegeysaa iyadoon wax ganaax ah lagugu dallicin.',

        'q4' => 'Qalabkee iyo hantidee ayaan ku qaadan karaa maalgelinta SIMDA?',
        'a4' => 'Waxaad qaadan kartaa mootooyinka shaqada, bajaajta, badeecadaha dukaamada yaryar, taleefannada gacanta, iyo qalabka dhismaha guryaha haddii aad leedahay boos sharci ah.',

        'q5' => 'Yaa xaq u leh inuu codsado maalgelinta SIMDA?',
        'a5' => 'Ganacsatada yaryar, haweenka danta wadaagta ah, wadayaasha gaadiidka, iyo qof kasta oo Soomaali ah oo leh ganacsi socda ama dammaanad lagu kalsoonaan karo.',

        'q6' => 'Waa maxay Sanduuqa Sadaqada ee Gaarka ah (Charity Fund)?',
        'a6' => 'Lacag kasta oo tabaruc ama sadaqo ah waxaa lagu maamulaa sanduuq 100% ka madaxbannaan xisaabaadka SIMDA, waxaana gebi ahaanba loogu adeegaa danta guud ee bulshada danyarta ah.',

        // Right Card: AI Chatbot (Multilingual)
        'any_q_title' => 'Kaaliyaha SIMDA AI',
        'any_q_desc' => '0% Ribo • Weydii su\'aal kasta adoo adeegsanaya Af-Soomaali, English, ama Carabi.',
        'chat_status_online' => 'Khadka ku jira',
        'chat_welcome_msg' => 'Asc! Waxaan ahay Kaaliyaha Caqliga Badan ee SIMDA (Smart Chatbot). Wax kasta oo ku saabsan nidaamka, maalgelinta xalaasha ah (0% Ribo), shuruudaha, ama sida loo codsado ayaad i weydiin kartaa. Af-Soomaali, English, ama Carabi waan kuugu jawaabayaa!',
        'chip_1' => '0% Ribo ma dhabbaa?',
        'chip_2' => 'Keyd ma leedihiin?',
        'chip_3' => 'Sideen u codsadaa?',
        'chip_4' => 'Murabaha waa maxay?',
        'chat_placeholder' => 'Qor su\'aashaada (SO, EN, AR)...',
        'chat_clear_title' => 'Nadiifi Wadahadalka',
        'let_me_know' => 'Ila soo socodsii.',
        'input_placeholder' => 'Qor su\'aashaada halkan...',
        'btn_send' => 'Dir',
        'send_alert' => 'Waad mahadsan tahay! Su\'aashaada waa la gudbiyay.',

        // Footer
        'footer_col_newsletter' => 'Wargelinta SIMDA',
        'footer_placeholder_email' => 'Email-kaaga',
        'footer_btn_subscribe' => 'RUSKEE',
        'footer_newsletter_alert' => 'Waad ku mahadsan tahay ku biirista wargelinta SIMDA!',
        'footer_col_terms' => 'Shuruudaha & Qawaaniinta',
        'footer_term_riba' => '0% Riba - Lacagta asalka ah oo kaliya!',
        'footer_term_shift' => 'Wareejinta Jimcaha ilaa Sabtida!',
        'footer_term_charity' => 'Sanduuqa Sadaqada oo 100% gooni ah!',
        'footer_follow_simda' => 'Ku xirnow SIMDA',
        'footer_col_info' => 'Xogta & Faahfaahinta',
        'footer_faq' => 'Su\'aalaha Badan ee La Isweydiiyo',
        'footer_about_simda' => 'Ku saabsan SIMDA',
        'footer_schedule_engine' => 'Jadwalka Xisaabinta SIMDA',
        'footer_col_contact' => 'Xiriirka',
        'footer_brand_sub' => 'SIMDA Microfinance',
        'footer_reg' => 'Diiwaanka Hay\'adda: SIMDA-SO-001',
        'footer_head_office' => 'Xafiiska Sare',
        'footer_country' => 'Muqdisho, Soomaaliya',
        'footer_seal_zero_riba' => 'RIBO LA\'AAN 0%',
        'footer_seal_guaranteed' => 'LA DAMMAANAD QAADAY',
        'footer_seal_sharia' => 'SHAREECADA & HANTIDHOWRKA',
        'footer_seal_compliant' => 'WAA WAFAQSAN YAHAY',
        'footer_part_of' => 'Qayb ka mid ah SIMDA',
        'footer_rights' => 'SIMDA Microfinance. Dhammaan xuquuqda way dhowran yihiin.',
        'footer_developed_by' => 'Waxaa diyaariyay',
    ],
    'ar' => [
        'page_title' => 'الأسئلة الشائعة | سيمدا للتمويل الأصغر الإسلامي',
        'nav_home' => 'الرئيسية',
        'nav_about' => 'من نحن',
        'nav_services' => 'الخدمات',
        'nav_process' => 'طريقة العمل',
        'nav_testimonials' => 'آراء العملاء',
        'nav_faq' => 'الأسئلة الشائعة',
        'nav_contact' => 'اتصل بنا',
        'cta_dashboard' => 'لوحة التحكم',
        'cta_start' => 'ابدأ الآن',

        'hero_badge' => 'مركز المعرفة والاستشارات بسيمدا',
        'watermark' => 'Frequently Ask Question',
        'faq_title' => 'الأسئلة الشائعة',
        'faq_subtitle' => 'هل لديك استفسارات حول التمويل الإسلامي المتوافق مع الشريعة؟ نحن هنا لمساعدتك بوضوح تام.',
        'click_here_lead' => 'انقر هنا لاستكشاف حلول التمويل الأصغر الأخلاقي لدينا.',

        // FAQ Items
        'q1' => 'ما هو تمويل المرابحة المتوافق مع الشريعة الإسلامية؟',
        'a1' => 'المرابحة هي عقد بيع إسلامي تقوم بموجبه سيمدا بشراء الأصل (دراجة نارية، هاتف، بضاعة) نيابة عنك وبيعه لك برأس المال مضافاً إليه هامش ربح محدد ومتفق عليه مسبقاً، بدون أي ربا أو فوائد مركبة أو غرامات خفية.',

        'q2' => 'هل تقبل سيمدا الودائع الادخارية أو تدير حسابات بنكية؟',
        'a2' => 'لا، سيمدا جمعية تنموية للتمويل الأصغر الإسلامي تقتصر على تقديم التمويل وشراء الأصول وتمويل البناء للمشاريع الصغيرة، ولا تقبل أي ودائع ادخارية إطلاقاً.',

        'q3' => 'كيف تعمل ميزة ترحيل قسط الجمعة تلقائياً إلى السبت؟',
        'a3' => 'يراعي نظامنا الآلي قدسية يوم الجمعة في الإسلام، حيث يتم ترحيل أي قسط يستحق يوم الجمعة تلقائياً إلى يوم السبت دون أي رسوم تأخير أو أعباء مالية إضافية.',

        'q4' => 'ما هي الأصول والمعدات التي يمكنني تمويلها عبر سيمدا؟',
        'a4' => 'نقوم بتمويل الدراجات النارية، دراجات التوصيل، التوك توك (باجاج)، رأس مال الأكشاك والمتاجر، الهواتف الذكية، ومواد البناء لمن يمتلكون أراضي موثقة.',

        'q5' => 'من هم المؤهلون للتقدم بطلب تمويل لدى سيمدا؟',
        'a5' => 'أصحاب المتاجر الصغيرة، الباعة المتجولون، مجموعات التضامن النسائية، وسائقو خدمات التوصيل الذين يمتلكون ضامناً موثوقاً ونشاطاً إنتاجياً حقيقياً.',

        'q6' => 'ما هو صندوق الصدقات الخيري المنفصل بنسبة 100%؟',
        'a6' => 'أي مبالغ تبرعات أو مساهمات خيرية يتم إيداعها في صندوق منفصل تماماً عن ميزانية سيمدا التشغيلية، وتخصص بنسبة 100% للمساعدات المجتمعية والفقراء.',

        // Right Card: AI Chatbot (Multilingual)
        'any_q_title' => 'مساعد سيمدا الذكي',
        'any_q_desc' => '0% ربا • اسأل عن أي شيء بالعربية، الصومالية، أو الإنجليزية وسيجيبك فوراً.',
        'chat_status_online' => 'متصل الآن',
        'chat_welcome_msg' => 'السلام عليكم ورحمة الله وبركاته! أنا مساعد سيمدا الذكي (Chatbot). يسعدني الإجابة عن كافة استفساراتك حول التمويل الإسلامي، خلوه من الربا (0% ربا)، شروط التقديم، أو مواعيد العمل باللغة العربية، الصومالية، أو الإنجليزية.',
        'chip_1' => 'هل التمويل 0% ربا؟',
        'chip_2' => 'هل لديكم حسابات توفير؟',
        'chip_3' => 'كيف أتقدم بطلب تمويل؟',
        'chip_4' => 'ما هو بيع المرابحة؟',
        'chat_placeholder' => 'اكتب سؤالك بأي لغة...',
        'chat_clear_title' => 'إعادة ضبط المحادثة',
        'let_me_know' => 'اكتب سؤالك هنا...',
        'input_placeholder' => 'اكتب سؤالك هنا بأي لغة...',
        'btn_send' => 'إرسال',
        'send_alert' => 'شكراً لك! تم استلام سؤالك.',

        // Footer
        'footer_col_newsletter' => 'نشرة سيمدا البريدية',
        'footer_placeholder_email' => 'بريدك الإلكتروني',
        'footer_btn_subscribe' => 'اشتراك',
        'footer_newsletter_alert' => 'شكراً لاشتراكك في نشرة سيمدا الإخبارية!',
        'footer_col_terms' => 'الشروط والأحكام',
        'footer_term_riba' => 'صفر% ربا - أصل التمويل فقط!',
        'footer_term_shift' => 'ترحيل قسط الجمعة للسبت تلقائياً!',
        'footer_term_charity' => 'صندوق خيرات وتكافل مجتمعي 100%!',
        'footer_follow_simda' => 'تابع سيمدا',
        'footer_col_info' => 'معلومات',
        'footer_faq' => 'الأسئلة الشائعة',
        'footer_about_simda' => 'عن سيمدا',
        'footer_schedule_engine' => 'محرك جدولة سيمدا',
        'footer_col_contact' => 'اتصل بنا',
        'footer_brand_sub' => 'سيمدا للتمويل الأصغر',
        'footer_reg' => 'رقم التسجيل: SIMDA-SO-001',
        'footer_head_office' => 'المقر الرئيسي',
        'footer_country' => 'مقديشو، الصومال',
        'footer_seal_zero_riba' => 'صفر ربا',
        'footer_seal_guaranteed' => 'مضمون 100%',
        'footer_seal_sharia' => 'متوافق شرعياً',
        'footer_seal_compliant' => 'ومعتمد بالكامل',
        'footer_part_of' => 'جزء من سيمدا',
        'footer_rights' => 'سيمدا للتمويل الأصغر. جميع الحقوق محفوظة.',
        'footer_developed_by' => 'تطوير وتصميم',
    ],
];

$t = $tAll[$locale] ?? $tAll['en'];
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);
$isRtl = ($locale === 'ar');
$isLoggedIn = !empty($_SESSION['user_id']);

$languagesList = [
    'so' => ['code' => 'SO', 'label' => 'Af-Soomaali', 'flag' => 'assets/images/flags/flag-so.jpg'],
    'en' => ['code' => 'EN', 'label' => 'English', 'flag' => 'assets/images/flags/flag-us.jpg'],
    'ar' => ['code' => 'AR', 'label' => 'العربية', 'flag' => 'assets/images/flags/flag-ar.jpg'],
];
$activeLang = $languagesList[$locale] ?? $languagesList['en'];
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Security::e($t['page_title']) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
    <style>
        .faq-page-wrap {
            position: relative;
            background: #FAFAF7;
            padding: 24px 0 42px;
            overflow: hidden;
        }

        /* 2-Column Grid (Left: Accordions, Right: Any Question card) */
        .faq-main-grid {
            display: grid;
            grid-template-columns: 1.45fr 1fr;
            gap: 24px;
            align-items: start;
            margin-top: 6px;
        }

        /* Left Column FAQ Accordion Items */
        .faq-accordion-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }
        .faq-card {
            background: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.025);
            border: 1px solid rgba(220, 226, 222, 0.65);
            overflow: hidden;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .faq-card:hover {
            box-shadow: 0 6px 16px rgba(18, 59, 50, 0.06);
            border-color: rgba(35, 132, 91, 0.35);
        }
        .faq-card-header {
            width: 100%;
            background: transparent;
            border: none;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        html[dir="rtl"] .faq-card-header {
            text-align: right;
        }
        .faq-card-question {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1F2D2A;
            margin: 0;
            line-height: 1.35;
        }
        .faq-toggle-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #23845B;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.25s ease, background 0.2s ease;
            box-shadow: 0 2px 6px rgba(35, 132, 91, 0.25);
        }
        .faq-toggle-btn svg {
            width: 12px;
            height: 12px;
        }
        .faq-card.is-open .faq-toggle-btn {
            transform: rotate(45deg);
            background: #123B32;
        }
        .faq-card-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease-out, padding 0.3s ease;
            padding: 0 16px;
            background: #FCFCF9;
            border-top: 1px solid transparent;
        }
        .faq-card.is-open .faq-card-body {
            max-height: 240px;
            padding: 8px 16px 14px;
            border-top-color: rgba(0, 0, 0, 0.04);
        }
        .faq-card-answer {
            margin: 0;
            font-size: 0.79rem;
            color: #4A5751;
            line-height: 1.5;
        }

        /* Carousel / Pagination buttons below accordion */
        .faq-pagination-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }
        .faq-page-arrow {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #23845B;
            color: #FFFFFF;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(35, 132, 91, 0.2);
            transition: transform 0.15s ease, background 0.15s ease;
        }
        .faq-page-arrow svg {
            width: 11px;
            height: 11px;
        }
        .faq-page-arrow:hover {
            transform: scale(1.08);
            background: #123B32;
        }

        /* Right Column "Smart AI Chatbot" Card */
        .faq-ask-card {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 14px 14px 12px;
            box-shadow: 0 6px 20px rgba(18, 59, 50, 0.06);
            border: 1px solid rgba(220, 226, 222, 0.85);
            text-align: left;
            position: sticky;
            top: 85px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        html[dir="rtl"] .faq-ask-card {
            text-align: right;
        }
        .faq-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #ECEEEA;
            padding-bottom: 10px;
        }
        .faq-chat-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .faq-chat-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            border: 2px solid #23845B;
            box-shadow: 0 2px 8px rgba(35, 132, 91, 0.2);
        }
        .faq-chat-status-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: #10B981;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            animation: pulseDot 2s infinite ease-in-out;
        }
        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .faq-chat-meta {
            flex: 1;
            min-width: 0;
        }
        .faq-chat-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #123B32;
            margin: 0;
            line-height: 1.25;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .faq-chat-sub {
            font-size: 0.69rem;
            color: #63736D;
            margin: 2px 0 0;
            line-height: 1.3;
        }
        .faq-chat-clear-btn {
            background: transparent;
            border: 1px solid #E1E8E4;
            color: #8C9993;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .faq-chat-clear-btn:hover {
            color: #E11D48;
            border-color: #E11D48;
            background: rgba(225, 29, 72, 0.05);
        }
        .faq-chat-header-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #25D366;
            color: #FFFFFF !important;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 999px;
            text-decoration: none !important;
            transition: all 0.15s ease;
            box-shadow: 0 2px 6px rgba(37, 211, 102, 0.25);
            flex-shrink: 0;
        }
        .faq-chat-header-wa-btn:hover {
            background: #1EBE5D;
            transform: translateY(-1px);
            color: #FFFFFF !important;
        }
        .faq-chat-wa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: #25D366;
            color: #FFFFFF !important;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 999px;
            text-decoration: none !important;
            margin-top: 8px;
            box-shadow: 0 3px 10px rgba(37, 211, 102, 0.35);
            transition: all 0.15s ease;
            width: fit-content;
        }
        .faq-chat-wa-btn:hover {
            background: #1EBE5D;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(37, 211, 102, 0.45);
            color: #FFFFFF !important;
        }
        .faq-chat-wa-btn svg {
            width: 15px;
            height: 15px;
            fill: #FFFFFF;
        }

        /* Messages stream */
        .faq-chat-stream {
            height: 285px;
            overflow-y: auto;
            background: #F9FBFA;
            border: 1px solid #E5EBE8;
            border-radius: 10px;
            padding: 10px 9px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            scroll-behavior: smooth;
        }
        .faq-chat-msg {
            display: flex;
            flex-direction: column;
            max-width: 88%;
            animation: fadeInMsg 0.2s ease-out;
        }
        @keyframes fadeInMsg {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .faq-chat-msg.bot {
            align-self: flex-start;
        }
        .faq-chat-msg.user {
            align-self: flex-end;
        }
        html[dir="rtl"] .faq-chat-msg.bot {
            align-self: flex-start;
        }
        html[dir="rtl"] .faq-chat-msg.user {
            align-self: flex-end;
        }
        .faq-chat-bubble {
            padding: 8px 11px;
            border-radius: 12px;
            font-size: 0.77rem;
            line-height: 1.45;
            word-break: break-word;
            white-space: pre-line;
        }
        .faq-chat-msg.bot .faq-chat-bubble {
            background: #FFFFFF;
            color: #17332B;
            border: 1px solid #DFE8E3;
            border-top-left-radius: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        html[dir="rtl"] .faq-chat-msg.bot .faq-chat-bubble {
            border-top-left-radius: 12px;
            border-top-right-radius: 2px;
        }
        .faq-chat-msg.user .faq-chat-bubble {
            background: #123B32;
            color: #FFFFFF;
            border-top-right-radius: 2px;
            box-shadow: 0 2px 6px rgba(18, 59, 50, 0.18);
        }
        html[dir="rtl"] .faq-chat-msg.user .faq-chat-bubble {
            border-top-right-radius: 12px;
            border-top-left-radius: 2px;
        }
        .faq-chat-time {
            font-size: 0.62rem;
            color: #A0ABA6;
            margin-top: 2px;
            padding: 0 4px;
        }
        .faq-chat-msg.user .faq-chat-time {
            text-align: right;
        }
        html[dir="rtl"] .faq-chat-msg.user .faq-chat-time {
            text-align: left;
        }

        /* Suggestion Chips */
        .faq-chat-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 4px;
        }
        .faq-chat-chip {
            background: #FFFFFF;
            border: 1px solid rgba(35, 132, 91, 0.3);
            color: #123B32;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .faq-chat-chip:hover {
            background: #23845B;
            color: #FFFFFF;
            border-color: #23845B;
            transform: translateY(-1px);
        }

        /* Typing Dots */
        .faq-chat-typing {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: #FFFFFF;
            border: 1px solid #DFE8E3;
            border-radius: 12px;
            border-top-left-radius: 2px;
            width: fit-content;
        }
        .faq-chat-typing span {
            width: 5px;
            height: 5px;
            background: #23845B;
            border-radius: 50%;
            display: inline-block;
            animation: bounceTyping 1.4s infinite ease-in-out both;
        }
        .faq-chat-typing span:nth-child(1) { animation-delay: -0.32s; }
        .faq-chat-typing span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes bounceTyping {
            0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* Input form */
        .faq-chat-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .faq-chat-input {
            flex: 1;
            padding: 8px 11px;
            border-radius: 8px;
            border: 1.2px solid #D6DFDB;
            background: #FDFDFB;
            font-size: 0.77rem;
            font-family: inherit;
            color: #1F2D2A;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            box-sizing: border-box;
        }
        .faq-chat-input:focus {
            outline: none;
            border-color: #23845B;
            box-shadow: 0 0 0 2px rgba(35, 132, 91, 0.14);
        }
        .faq-chat-send-btn {
            background: #23845B;
            color: #FFFFFF;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(35, 132, 91, 0.25);
            transition: background 0.15s ease, transform 0.15s ease;
        }
        .faq-chat-send-btn:hover {
            background: #123B32;
            transform: scale(1.05);
        }
        .faq-chat-send-btn svg {
            width: 14px;
            height: 14px;
        }
        html[dir="rtl"] .faq-chat-send-btn svg {
            transform: scaleX(-1);
        }

        @media (max-width: 900px) {
            .faq-main-grid {
                grid-template-columns: 1fr;
            }
            .faq-ask-card {
                position: static;
            }
        }

        /* ================================================================== */
        /* DARK MODE SUPPORT FOR FAQ PAGE                                     */
        /* ================================================================== */
        [data-theme="dark"] .faq-page-wrap {
            background-color: #0F1B17 !important;
        }

        [data-theme="dark"] .faq-card {
            background: #142822 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3) !important;
        }

        [data-theme="dark"] .faq-card:hover {
            border-color: rgba(214, 182, 108, 0.45) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4) !important;
        }

        [data-theme="dark"] .faq-card-question {
            color: #F0F5F2 !important;
        }

        [data-theme="dark"] .faq-card-body {
            background: #0C1E18 !important;
            border-top-color: rgba(255, 255, 255, 0.06) !important;
        }

        [data-theme="dark"] .faq-card-answer {
            color: #B4C4BC !important;
        }

        [data-theme="dark"] .faq-card.is-open .faq-toggle-btn {
            background: #D6B66C !important;
            color: #0C2D24 !important;
        }

        [data-theme="dark"] .faq-ask-card {
            background: #142822 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.45) !important;
        }

        [data-theme="dark"] .faq-chat-header {
            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-theme="dark"] .faq-chat-title {
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .faq-chat-sub {
            color: #94A8A0 !important;
        }

        [data-theme="dark"] .faq-chat-clear-btn {
            border-color: rgba(255, 255, 255, 0.14) !important;
            color: #B4C4BC !important;
        }

        [data-theme="dark"] .faq-chat-clear-btn:hover {
            color: #FB7185 !important;
            border-color: #FB7185 !important;
            background: rgba(251, 113, 133, 0.1) !important;
        }

        [data-theme="dark"] .faq-chat-stream {
            background: #0B1914 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-theme="dark"] .faq-chat-msg.bot .faq-chat-bubble {
            background: #16332A !important;
            color: #EBF2EE !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-theme="dark"] .faq-chat-msg.user .faq-chat-bubble {
            background: #1B684D !important;
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .faq-chat-time {
            color: #7B8F86 !important;
        }

        [data-theme="dark"] .faq-chat-chip {
            background: #16332A !important;
            border-color: rgba(214, 182, 108, 0.35) !important;
            color: #D6B66C !important;
        }

        [data-theme="dark"] .faq-chat-chip:hover {
            background: #D6B66C !important;
            color: #0C2821 !important;
            border-color: #D6B66C !important;
        }

        [data-theme="dark"] .faq-chat-typing {
            background: #16332A !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-theme="dark"] .faq-chat-input {
            background: #0C1E18 !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: #F0F5F2 !important;
        }

        [data-theme="dark"] .faq-chat-input:focus {
            border-color: #D6B66C !important;
            box-shadow: 0 0 0 2px rgba(214, 182, 108, 0.25) !important;
        }

        [data-theme="dark"] .faq-chat-send-btn {
            background: #23845B !important;
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .faq-chat-send-btn:hover {
            background: #D6B66C !important;
            color: #0C2821 !important;
        }
    </style>
</head>
<body class="fid-page">

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
            <li><a href="?route=faq" class="active"><?= Security::e($t['nav_faq']) ?></a></li>
            <li><a href="?route=contact"><?= Security::e($t['nav_contact']) ?></a></li>
        </ul>

        <div style="display:flex;align-items:center;gap:10px;">
            <!-- Theme Switcher: Light & Dark Mode Icons -->
            <div class="theme-toggle-pill" id="faqThemeToggle" title="Light Mode / Dark Mode">
                <button type="button" class="theme-pill-btn" data-theme-val="light" aria-label="Light Mode" title="<?= $locale === 'so' ? 'Habka Maalintii (Light Mode)' : 'Light Mode' ?>">
                    <?= Icons::svg('sun', 16) ?>
                </button>
                <button type="button" class="theme-pill-btn" data-theme-val="dark" aria-label="Dark Mode" title="<?= $locale === 'so' ? 'Habka Habeenkii (Dark Mode)' : 'Dark Mode' ?>">
                    <?= Icons::svg('moon', 16) ?>
                </button>
            </div>

            <!-- Language Dropdown Selector (Active flag shown, tap opens dropdown menu) -->
            <div class="simda-lang-dropdown" id="faqLangDropdown">
                <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                    <img src="<?= Security::e($activeLang['flag']) ?>" alt="<?= Security::e($activeLang['label']) ?>" class="simda-lang-flag-circle">
                    <span class="simda-lang-code"><?= Security::e($activeLang['code']) ?></span>
                    <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 1 5 5 9 1"></polyline>
                    </svg>
                </button>
                <div class="simda-lang-menu" role="menu">
                    <?php foreach ($languagesList as $code => $info): ?>
                        <a href="?route=faq&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

<!-- 2. EDITORIAL HERO BANNER WITH HIGH-RES BACKGROUND & ORGANIC WAVE -->
<section class="faq-hero-section">
    <div class="fid-container">
        <div class="contact-hero-badge">
            <?= Icons::svg('help-circle', 15) ?>
            <span><?= Security::e($t['hero_badge']) ?></span>
        </div>
        <h1 class="contact-hero-title"><?= Security::e($t['faq_title']) ?></h1>
        <p class="contact-hero-lead"><?= Security::e($t['faq_subtitle']) ?></p>
        <div style="margin-top:16px;">
            <a href="#faqMainArea" style="display:inline-flex;align-items:center;gap:6px;color:#D6B66C;font-size:0.88rem;font-weight:700;text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                <?= Security::e($t['click_here_lead']) ?> <?= Icons::svg('arrow-down', 13) ?>
            </a>
        </div>
    </div>

    <!-- Organic wave divider transitioning into page body -->
    <div class="contact-hero-wave" aria-hidden="true">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C150,90 350,-40 500,45 C650,130 900,10 1200,50 L1200,120 L0,120 Z"></path>
        </svg>
    </div>
</section>

<!-- 3. FAQ PAGE MAIN BODY WITH SPLIT 2-COLUMN DESIGN -->
<main class="faq-page-wrap" id="faqMainArea">
    <div class="fid-container">

        <!-- 2-Column Split Layout -->
        <div class="faq-main-grid" style="margin-top:0;">

            <!-- Left Column: Sleek Accordion Cards -->
            <div>
                <div class="faq-accordion-list" id="faqAccordionList">

                    <!-- Q1 -->
                    <div class="faq-card is-open">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q1']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a1']) ?></p>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="faq-card">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q2']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a2']) ?></p>
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="faq-card">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q3']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a3']) ?></p>
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="faq-card">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q4']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a4']) ?></p>
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="faq-card">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q5']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a5']) ?></p>
                        </div>
                    </div>

                    <!-- Q6 -->
                    <div class="faq-card">
                        <button type="button" class="faq-card-header" onclick="toggleFaq(this)">
                            <h3 class="faq-card-question"><?= Security::e($t['q6']) ?></h3>
                            <span class="faq-toggle-btn" aria-label="Toggle Answer">
                                <?= Icons::svg('plus', 16) ?>
                            </span>
                        </button>
                        <div class="faq-card-body">
                            <p class="faq-card-answer"><?= Security::e($t['a6']) ?></p>
                        </div>
                    </div>

                </div>

                <!-- Circular Pagination Navigation Arrows (Matching Screenshot) -->
                <div class="faq-pagination-bar">
                    <button type="button" class="faq-page-arrow" onclick="expandAllFaq(false)" title="Previous / Collapse" aria-label="Collapse">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7L9 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" class="faq-page-arrow" onclick="expandAllFaq(true)" title="Next / Expand" aria-label="Expand">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3L9 7L5 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>

            <!-- Right Column: Smart AI Multilingual Chatbot -->
            <div>
                <div class="faq-ask-card" id="simdaChatCard" role="region" aria-label="SIMDA AI Assistant">
                    <!-- Chatbot Header -->
                    <div class="faq-chat-header">
                        <div class="faq-chat-avatar-wrap">
                            <img src="assets/images/landing/faq-support-illustration.jpg?v=2" alt="SIMDA AI Assistant" class="faq-chat-avatar">
                            <span class="faq-chat-status-dot" title="<?= Security::e($t['chat_status_online']) ?>"></span>
                        </div>
                        <div class="faq-chat-meta">
                            <div class="faq-chat-title">
                                <span><?= Security::e($t['any_q_title']) ?></span>
                            </div>
                            <div class="faq-chat-sub"><?= Security::e($t['any_q_desc']) ?></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <a href="https://wa.me/252616256534?text=Asc%20SIMDA%2C%20waxaan%20rabaa%20in%20aan%20wax%20ka%20ogaado%20maalgelinta%20xalaasha%20ah" target="_blank" rel="noopener noreferrer" class="faq-chat-header-wa-btn" title="Chat on WhatsApp" aria-label="WhatsApp">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                                    <path d="M12.031 2C6.5 2 2 6.5 2 12.031c0 1.906.531 3.703 1.516 5.25L2.125 22l4.875-1.344A9.957 9.957 0 0 0 12.031 22c5.531 0 10.031-4.5 10.031-10.031C22.062 6.5 17.562 2 12.031 2zm0 18.25c-1.64 0-3.187-.453-4.515-1.25l-.328-.188-3.047.844.828-2.953-.203-.344A8.136 8.136 0 0 1 3.781 12c0-4.547 3.703-8.25 8.25-8.25s8.25 3.703 8.25 8.25-3.703 8.25-8.25 8.25zm4.563-6.172c-.25-.125-1.469-.734-1.688-.812-.234-.094-.406-.14-.578.125-.172.266-.672.812-.828.984-.156.172-.312.188-.562.062-.25-.125-1.062-.39-2.031-1.25-.75-.672-1.266-1.5-1.406-1.75-.156-.25-.016-.39.109-.516.109-.109.25-.281.375-.422.125-.14.172-.25.25-.406.078-.172.047-.328-.016-.453-.062-.125-.578-1.406-.797-1.922-.219-.516-.438-.438-.609-.438h-.516c-.172 0-.453.062-.688.328-.234.25-.906.89-.906 2.156s.922 2.5 1.047 2.672c.14.172 1.813 2.766 4.391 3.875.609.266 1.094.422 1.469.547.625.188 1.188.172 1.64.094.5-.078 1.469-.609 1.688-1.203.203-.594.203-1.094.14-1.203-.062-.109-.234-.172-.484-.297z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                            <button type="button" class="faq-chat-clear-btn" onclick="resetFaqChat()" title="<?= Security::e($t['chat_clear_title']) ?>" aria-label="Reset Chat">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Scroll Stream -->
                    <div class="faq-chat-stream" id="faqChatStream">
                        <!-- Welcome Bot Message -->
                        <div class="faq-chat-msg bot">
                            <div class="faq-chat-bubble"><?= Security::e($t['chat_welcome_msg']) ?></div>
                            <span class="faq-chat-time"><?= date('H:i') ?></span>
                        </div>
                        <!-- Suggestion Quick Chips -->
                        <div class="faq-chat-chips" id="faqChatChips">
                            <button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= Security::e($t['chip_1']) ?></button>
                            <button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= Security::e($t['chip_2']) ?></button>
                            <button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= Security::e($t['chip_3']) ?></button>
                            <button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= Security::e($t['chip_4']) ?></button>
                            <button type="button" class="faq-chat-chip" onclick="askChip(this)" style="background:rgba(37,211,102,0.12);border-color:rgba(37,211,102,0.4);color:#107C3A;">💬 WhatsApp Number</button>
                        </div>
                    </div>

                    <!-- Chat Input Form -->
                    <form class="faq-chat-form" id="faqChatForm" onsubmit="event.preventDefault(); sendFaqChat();">
                        <input type="text" id="faqChatInput" class="faq-chat-input" placeholder="<?= Security::e($t['input_placeholder']) ?>" autocomplete="off" required>
                        <button type="submit" class="faq-chat-send-btn" id="faqChatSendBtn" title="<?= Security::e($t['btn_send']) ?>" aria-label="Send">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- 3. CANONICAL SIGNATURE INDEX FOOTER (EXACTLY AS ON INDEX / HOME.PHP) -->
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
                        <a href="?route=faq&lang=so" title="Af-Soomaali (Somalia)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'so' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'so' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-so.jpg" alt="Somalia" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=faq&lang=en" title="English (USA)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'en' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'en' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-us.jpg" alt="USA" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=faq&lang=ar" title="العربية (Arabic)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'ar' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'ar' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-ar.jpg" alt="Arabic" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                    </div>
                    <div style="font-size:0.74rem;color:rgba(244,245,240,0.68);">&copy; <?= date('Y') ?> <?= Security::e($t['footer_rights'] ?? 'SIMDA Microfinance. All rights reserved.') ?></div>
                    <div style="font-size:0.78rem;color:rgba(244,245,240,0.8);margin-top:4px;"><?= Security::e($t['footer_developed_by'] ?? 'Developed by') ?> <a href="https://anazabdiwahid.netlify.app/" target="_blank" rel="noopener noreferrer" style="color:#D6B66C;font-weight:800;text-decoration:none;border-bottom:1px dashed rgba(214,182,108,0.6);" onmouseover="this.style.color='#E4C782'" onmouseout="this.style.color='#D6B66C'">Anas Abdiwahid</a></div>
                </div>
                <!-- Right: SIMDA Brand Logo -->
                <div class="fid-footer-brand-col">
                    <a href="?route=home" class="fid-footer-logo-link" title="SIMDA — Somali Islamic Microfinance Development Association">
                        <div class="fid-footer-logo-badge">
                            <img src="assets/images/simda-logo.png" alt="SIMDA Microfinance" class="fid-footer-logo-img">
                        </div>
                        <span class="fid-footer-logo-tagline">
                            <span>SOMALI ISLAMIC MICROFINANCE</span>
                            <span>DEVELOPMENT ASSOCIATION</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
<script>
    // Accordion Toggle
    function toggleFaq(btn) {
        var card = btn.closest('.faq-card');
        var wasOpen = card.classList.contains('is-open');
        card.classList.toggle('is-open', !wasOpen);
    }

    // Expand / Collapse all using pagination arrows
    function expandAllFaq(expand) {
        var cards = document.querySelectorAll('.faq-card');
        cards.forEach(function(c) {
            c.classList.toggle('is-open', expand);
        });
    }

    // Smart Multilingual AI Chatbot Engine
    var currentLocale = '<?= Security::e($locale) ?>';
    var isBotThinking = false;

    function formatTime() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        return h + ':' + m;
    }

    function scrollChatToBottom() {
        var stream = document.getElementById('faqChatStream');
        if (stream) {
            setTimeout(function() {
                stream.scrollTop = stream.scrollHeight;
            }, 60);
        }
    }

    function appendChatMsg(sender, text, actionBtn) {
        var stream = document.getElementById('faqChatStream');
        if (!stream) return;

        var msgDiv = document.createElement('div');
        msgDiv.className = 'faq-chat-msg ' + (sender === 'user' ? 'user' : 'bot');

        var bubble = document.createElement('div');
        bubble.className = 'faq-chat-bubble';
        bubble.textContent = text;

        if (actionBtn && actionBtn.url) {
            var btnWrap = document.createElement('div');
            btnWrap.style.marginTop = '8px';
            var a = document.createElement('a');
            a.className = 'faq-chat-wa-btn';
            a.href = actionBtn.url;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.innerHTML = '<svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M12.031 2C6.5 2 2 6.5 2 12.031c0 1.906.531 3.703 1.516 5.25L2.125 22l4.875-1.344A9.957 9.957 0 0 0 12.031 22c5.531 0 10.031-4.5 10.031-10.031C22.062 6.5 17.562 2 12.031 2zm0 18.25c-1.64 0-3.187-.453-4.515-1.25l-.328-.188-3.047.844.828-2.953-.203-.344A8.136 8.136 0 0 1 3.781 12c0-4.547 3.703-8.25 8.25-8.25s8.25 3.703 8.25 8.25-3.703 8.25-8.25 8.25zm4.563-6.172c-.25-.125-1.469-.734-1.688-.812-.234-.094-.406-.14-.578.125-.172.266-.672.812-.828.984-.156.172-.312.188-.562.062-.25-.125-1.062-.39-2.031-1.25-.75-.672-1.266-1.5-1.406-1.75-.156-.25-.016-.39.109-.516.109-.109.25-.281.375-.422.125-.14.172-.25.25-.406.078-.172.047-.328-.016-.453-.062-.125-.578-1.406-.797-1.922-.219-.516-.438-.438-.609-.438h-.516c-.172 0-.453.062-.688.328-.234.25-.906.89-.906 2.156s.922 2.5 1.047 2.672c.14.172 1.813 2.766 4.391 3.875.609.266 1.094.422 1.469.547.625.188 1.188.172 1.64.094.5-.078 1.469-.609 1.688-1.203.203-.594.203-1.094.14-1.203-.062-.109-.234-.172-.484-.297z"/></svg><span>' + (actionBtn.label || 'WhatsApp') + '</span>';
            btnWrap.appendChild(a);
            bubble.appendChild(btnWrap);
        }

        var timeSpan = document.createElement('span');
        timeSpan.className = 'faq-chat-time';
        timeSpan.textContent = formatTime();

        msgDiv.appendChild(bubble);
        msgDiv.appendChild(timeSpan);

        // Always append in strict chronological order
        stream.appendChild(msgDiv);

        // Keep quick suggestion chips at the bottom of the active conversation
        var chips = document.getElementById('faqChatChips');
        if (chips) {
            stream.appendChild(chips);
        }

        scrollChatToBottom();
    }

    function showTypingIndicator() {
        var stream = document.getElementById('faqChatStream');
        if (!stream || document.getElementById('faqTypingInd')) return;

        var wrap = document.createElement('div');
        wrap.id = 'faqTypingInd';
        wrap.className = 'faq-chat-msg bot';
        wrap.innerHTML = '<div class="faq-chat-typing"><span></span><span></span><span></span></div>';

        stream.appendChild(wrap);
        var chips = document.getElementById('faqChatChips');
        if (chips) {
            stream.appendChild(chips);
        }
        scrollChatToBottom();
    }

    function hideTypingIndicator() {
        var typing = document.getElementById('faqTypingInd');
        if (typing) typing.remove();
    }

    function updateSuggestionChips(suggestions) {
        var chipsContainer = document.getElementById('faqChatChips');
        if (!chipsContainer || !Array.isArray(suggestions) || suggestions.length === 0) return;

        chipsContainer.innerHTML = '';
        suggestions.forEach(function(text) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'faq-chat-chip';
            btn.textContent = text;
            btn.onclick = function() { askChip(btn); };
            chipsContainer.appendChild(btn);
        });
        scrollChatToBottom();
    }

    function askChip(btn) {
        if (isBotThinking) return;
        var text = btn.textContent.trim();
        var input = document.getElementById('faqChatInput');
        if (input) input.value = text;
        sendFaqChat();
    }

    function resetFaqChat() {
        var stream = document.getElementById('faqChatStream');
        if (!stream) return;

        stream.innerHTML = 
            '<div class="faq-chat-msg bot">' +
                '<div class="faq-chat-bubble"><?= addslashes(Security::e($t['chat_welcome_msg'])) ?></div>' +
                '<span class="faq-chat-time">' + formatTime() + '</span>' +
            '</div>' +
            '<div class="faq-chat-chips" id="faqChatChips">' +
                '<button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= addslashes(Security::e($t['chip_1'])) ?></button>' +
                '<button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= addslashes(Security::e($t['chip_2'])) ?></button>' +
                '<button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= addslashes(Security::e($t['chip_3'])) ?></button>' +
                '<button type="button" class="faq-chat-chip" onclick="askChip(this)"><?= addslashes(Security::e($t['chip_4'])) ?></button>' +
                '<button type="button" class="faq-chat-chip" onclick="askChip(this)" style="background:rgba(37,211,102,0.12);border-color:rgba(37,211,102,0.4);color:#107C3A;">💬 WhatsApp Number</button>' +
            '</div>';
        
        var input = document.getElementById('faqChatInput');
        if (input) input.value = '';
    }

    function sendFaqChat() {
        if (isBotThinking) return;
        var input = document.getElementById('faqChatInput');
        if (!input) return;

        var message = input.value.trim();
        if (!message) return;

        // Display user message
        appendChatMsg('user', message);
        input.value = '';
        isBotThinking = true;

        var sendBtn = document.getElementById('faqChatSendBtn');
        if (sendBtn) sendBtn.disabled = true;

        showTypingIndicator();

        // Send to backend endpoint
        fetch('?route=faq_chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8'
            },
            body: JSON.stringify({
                message: message,
                lang: currentLocale
            })
        })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            hideTypingIndicator();
            isBotThinking = false;
            if (sendBtn) sendBtn.disabled = false;

            if (data && data.reply) {
                appendChatMsg('bot', data.reply, data.action_btn);
                if (data.suggestions) {
                    updateSuggestionChips(data.suggestions);
                }
            } else {
                appendChatMsg('bot', 'Waad mahadsan tahay! Su\'aashaada waa la diiwaangeliyay.');
            }
        })
        .catch(function(err) {
            hideTypingIndicator();
            isBotThinking = false;
            if (sendBtn) sendBtn.disabled = false;

            // Intelligent Offline / Network Fallback
            var fallback = '';
            var waAction = null;
            var lower = message.toLowerCase();
            if (/whatsapp|whataap|whatsap/i.test(message)) {
                fallback = "Haa! Waxaad toos noogala soo xiriiri kartaa WhatsApp (+252 61 6256534). Guji batoonka hoose:";
                waAction = {
                    type: 'whatsapp',
                    label: 'La Hadal WhatsApp (+252 61 6256534)',
                    url: 'https://wa.me/252616256534?text=Asc%20SIMDA'
                };
            } else if (/[\u0600-\u06FF]/.test(message)) {
                fallback = "نعم! في سيمدا نعمل بنسبة 0% ربا تماماً، ونقدم تمويل المرابحة الإسلامي للأصول والأعمال الصغيرة. يمكنك أيضاً التواصل معنا هاتفياً: 6256534 61 252+ أو زيارة فرعنا بمقديشو.";
            } else if (/riba|interest|loan|apply|savings|murabaha|where|office|contact/i.test(lower)) {
                fallback = "SIMDA operates strictly on 0% Riba through Murabaha financing for productive assets and kiosks (no savings accounts). Visit KM4 Mogadishu or call +252 61 6256534!";
            } else {
                fallback = "SIMDA waxay u taagan tahay 100% ribo la'aan (0% Riba) iyo maalgelinta xalaasha ah ee ganacsiyada yaryar. Waxaan ku shaqeynaa nidaamka Murabaha. Xafiiskeennu waa KM4, Muqdisho (Tel: +252 61 6256534).";
            }
            appendChatMsg('bot', fallback, waAction);
        });
    }

    // Close language dropdown on outside click
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.simda-lang-dropdown.is-open').forEach(function(d) {
            if (!d.contains(e.target)) d.classList.remove('is-open');
        });
    });
</script>
</body>
</html>

