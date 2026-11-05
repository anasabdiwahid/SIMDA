<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$isSo = ($locale === 'so');
$isLoggedIn = AuthMiddleware::check();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);

$homeI18n = [
    'en' => [
        'nav_home' => 'Home',
        'nav_about' => 'About Us',
        'nav_services' => 'Services',
        'nav_process' => 'Our Process',
        'nav_testimonials' => 'Testimonials',
        'nav_faq' => 'FAQ',
        'nav_contact' => 'Contact',
        'cta_dashboard' => 'Dashboard',
        'cta_start' => 'Get Started',
        'btn_learn' => 'Learn More',
        'btn_calc' => 'Schedule Simulator',

        // Slide 1: Murabaha
        'slide1_tag' => 'Muraabaxo (Murabaha)',
        'slide1_eyebrow' => 'SHARIA-COMPLIANT FINANCING • MURABAHA',
        'slide1_title' => 'Ethical Murabaha<br>Asset Financing',
        'slide1_lead' => 'Transparent cost-plus asset and equipment financing with fixed zero-riba terms, empowering Somali entrepreneurs to scale with certainty.',
        'slide1_badge_val' => '100% Sharia',
        'slide1_badge_sub' => 'Ethical & Audited',

        // Slide 2: Maalgalin / Dukaanada (Somali Mother Kiosk)
        'slide2_tag' => 'Maalgalinta Dukaamada',
        'slide2_eyebrow' => 'MICRO-ENTERPRISE • WORKING CAPITAL',
        'slide2_title' => 'Empowering Small Shops<br>& Local Retailers',
        'slide2_lead' => 'Direct zero-riba working capital for hardworking mothers and roadside grocery kiosks (dukaan) to stock quality goods and build household wealth.',
        'slide2_badge_val' => '0% Riba',
        'slide2_badge_sub' => 'Dukaamada & Qoyska',

        // Services
        'services_eyebrow' => 'OUR SERVICES',
        'services_title' => 'Financial Solutions for Every Stage',
        'srv1_title' => 'Home & Plot Construction',
        'srv1_desc' => 'We construct modern homes for plot owners who lack building capital through ethical zero-riba installment plans.',
        'srv2_title' => 'Murabaha Asset Financing',
        'srv2_desc' => 'We purchase Cars, Motorbikes, Bajaj tuk-tuks, and Smartphones for you with fixed zero-riba monthly installments.',
        'srv3_title' => 'Small Retail & Kiosk Financing',
        'srv3_desc' => 'Direct working capital designed for neighborhood grocery shops, kiosks, and street retailers to expand inventory.',
        'srv4_title' => 'Zero-Riba Micro-Loans',
        'srv4_desc' => 'Direct zero-riba micro-loans provided with transparent terms — repay strictly the principal you borrowed without any extra fee.',

        // Why Us
        'why_eyebrow' => 'WHY CHOOSE US',
        'why_title' => 'More than finance,<br>we build futures.',
        'why1_t' => 'Fast approval',
        'why1_d' => 'Get quick decisions with our dual-threshold officer and manager workflow.',
        'why2_t' => 'Professional support',
        'why2_d' => 'Our branch teams are here to support you at every stage.',
        'why3_t' => 'Flexible payments',
        'why3_d' => 'Weekly or monthly schedules with automatic Friday-to-Saturday shifts.',
        'why4_t' => 'Community focused',
        'why4_d' => 'We invest in people, solidarity groups, and a 100% segregated Charity Fund.',
        'why5_t' => 'Secure transactions',
        'why5_d' => 'Your security, privacy, and decimal-safe ledger accuracy are our priority.',
        'why6_t' => 'Transparent pricing',
        'why6_d' => 'Fixed Murabaha markup locked at origination — never compounding.',

        // Process
        'process_eyebrow' => 'HOW IT WORKS',
        'step1_t' => 'Apply',
        'step1_d' => 'Submit a simple application online or in branch with a guarantor.',
        'step2_t' => 'Verification',
        'step2_d' => 'We review your information, schedule preview, and assess eligibility.',
        'step3_t' => 'Receive Funds',
        'step3_d' => 'Once approved, receive your funds via EVC Plus, Zaad, or eDahab.',

        // Testimonials
        'testi_eyebrow' => 'WHAT OUR CLIENTS VALUE',
        'testi1_q' => '"The Murabaha cost-plus structure is clear and transparent. Every installment is known from day one."',
        'testi1_a' => 'Retail Trader',
        'testi2_q' => '"The process was simple and transparent. Friday due dates automatically shift to Saturday."',
        'testi2_a' => 'Market Vendor',
        'testi3_q' => '"Professional, trustworthy and always ready to assist solidarity groups and small businesses."',
        'testi3_a' => 'Group Leader',

        // Education
        'edu_eyebrow' => 'FINANCIAL EDUCATION',
        'edu1_t' => 'Construction Plans',
        'edu1_d' => 'How to construct a home on your plot with zero-riba installment plans.',
        'edu2_t' => 'How Micro-Loans Work',
        'edu2_d' => 'Understand how Sharia-compliant Murabaha helps achieve your goals.',
        'edu3_t' => 'Investment Basics',
        'edu3_d' => 'Learn simple inventory strategies for long-term growth.',
        'edu4_t' => 'Office Location',
        'edu4_d' => 'Mogadishu Main Boulevard, Hodan • Visit SIMDA office.',
        'btn_read_more' => 'Read More',
        'btn_open_maps' => 'Open in Google Maps',

        // Calculator
        'calc_eyebrow' => 'INTERACTIVE SCHEDULE ENGINE',
        'calc_shift_badge' => 'Friday → Saturday Auto-Shift',
        'calc_label_principal' => 'Amount Given ($ USD)',
        'calc_label_monthly' => 'Monthly Payment ($ USD / Bishii)',
        'calc_label_duration' => 'Duration (Months / Weeks)',
        'calc_opt_months' => 'Months (Bille)',
        'calc_opt_weeks' => 'Weeks (Toddobaadle)',
        'calc_label_extra' => 'Extra / Riba (% — Locked at 0%)',
        'calc_btn_generate' => 'Generate Exact Installment Schedule',
        'calc_quick_amounts' => 'Quick Amounts:',

        // Contact
        'contact_eyebrow' => 'GET IN TOUCH',
        'contact_title' => 'Ready to take the next step?',
        'contact_lead' => 'We are here to help you achieve your ethical financial goals today.',
        'contact_btn_start' => 'Get Started',

        // Footer
        'footer_about' => 'Somali Islamic Microfinance Development Association (SIMDA) is dedicated to ethical financing, small business growth, and community empowerment with 0% Riba.',
        'footer_quick_links' => 'Quick Links',
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
        'btn_learn' => 'Faahfaahin Dheeraad ah',
        'btn_calc' => 'Xisaabiyaha Jadwalka',

        // Slide 1: Murabaha
        'slide1_tag' => 'Muraabaxada Ganacsiga',
        'slide1_eyebrow' => 'MAALGELINTA ISLAAMIGA EE MURAABAXADA',
        'slide1_title' => 'Maalgeli Ganacsigaaga<br>Nidaamka Muraabaxada',
        'slide1_lead' => 'Ku iibso agabka, qalabka shaqada, iyo badeecadaha ganacsigaaga nidaamka Muraabaxada ee 100% xalaasha ah oo leh heshiis hufan iyo 0% Ribo.',
        'slide1_badge_val' => '100% Sharia',
        'slide1_badge_sub' => 'Xalaal & Hufan',

        // Slide 2: Maalgalin / Dukaanada (Hooyada Soomaaliyeed & Dukaanka)
        'slide2_tag' => 'Maalgalinta Dukaamada Yaryar',
        'slide2_eyebrow' => 'MAALGALINTA GANACSIYADA YARYAR',
        'slide2_title' => 'Kobci Dukaankaaga<br>& Ganacsiga Yaryar',
        'slide2_lead' => 'Waxaan taageernaa hooyooyinka iyo ganacsatada leh dukaamada yaryar ee waddooyinka iyo suuqyada si ay u ballaariyaan hantidooda, badeecadahooda iyo dakhliga qoysaska.',
        'slide2_badge_val' => '0% Ribo',
        'slide2_badge_sub' => 'Dukaamada & Qoysaska',

        // Services
        'services_eyebrow' => 'ADEEGYADAYADA',
        'services_title' => 'Xalka Maalgelinta Ganacsiyada',
        'srv1_title' => 'Dhismaha Boosaska & Guryaha',
        'srv1_desc' => 'Dadka boosaska leh ee dhismaha awoodi waayay waxaan u dhisnaa guryo casri ah iyadoo lagu bixinayo heshiis xalaal ah oo 0% Ribo ah.',
        'srv2_title' => 'Muraabaxada (Gaadiid & Qalab)',
        'srv2_desc' => 'Waxaan kuu iibineynaa Baabuur, Mooto, Bajaj, iyo Telefoonno (iPhone) adigoo ku bixinaya bil kasta qayb yar oo aan dulsaar lahayn.',
        'srv3_title' => 'Taageerada Dukaamada Yaryar',
        'srv3_desc' => 'Raasamaal shaqo oo xalaal ah oo dukaamada yaryar iyo meheradaha waddooyinka lagu siiyo si ay u kordhiyaan badeecadahooda.',
        'srv4_title' => 'Dayn Xalaal ah (0% Ribo)',
        'srv4_desc' => 'Dayn toos ah oo degdeg ah oo dadka danyarta ah iyo ganacsatada la siiyo iyadoon waxba lagu darsanayn (waxaad heshay uun ayaad celinaysaa).',

        // Why Us
        'why_eyebrow' => 'MAXAAD NOO DOORANAYSAA',
        'why_title' => 'In ka badan lacag,<br>waxaan dhisnaa mustaqbal.',
        'why1_t' => 'Ansixin Degdeg ah',
        'why1_d' => 'Go\'aanno degdeg ah oo hufan oo ay wada shaqeynayaan saraakiisha iyo maareeyayaasha.',
        'why2_t' => 'Taageero Xirfadeed',
        'why2_d' => 'Kooxaha laamaha waxay diyaar u yihiin inay ku caawiyaan tallaabo kasta.',
        'why3_t' => 'Bixin Dabacsan',
        'why3_d' => 'Jadwal bille ama toddobaadle ah iyadoo maalmaha Jimcaha si toos ah loogu wareejinayo Sabtida.',
        'why4_t' => 'Bulshada oo la Taageero',
        'why4_d' => 'Waxaan maalgelineynaa dadka, kooxaha danta wadaagta ah, iyo Sanduuqa Sadaqada ee gaarka ah.',
        'why5_t' => 'Macaamil Ammaan ah',
        'why5_d' => 'Amnigaaga, xogtaada gaarka ah, iyo saxnaanta xisaabta ayaa ah mudnaantayada koowaad.',
        'why6_t' => 'Qiimeyn Cad & Hufan',
        'why6_d' => 'Faa\'iidada Muraabaxada oo ah mid go\'an oo heshiiska lagu qoray—oo aan marnaba kor u kordhaynin.',

        // Process
        'process_eyebrow' => 'HABKA SHAQADA',
        'step1_t' => 'Codso',
        'step1_d' => 'Ku soo gudbi codsi fudud online ama xafiiska oo wata damiin.',
        'step2_t' => 'Xaqiijin & Qiimeyn',
        'step2_d' => 'Waxaan dib u eegis ku sameyneynaa xogtaada, jadwalka, iyo u qalmidda sharciga.',
        'step3_t' => 'Qaado Maalgelinta',
        'step3_d' => 'Marka laguu ansixiyo, ku hel hantida ama lacagta EVC Plus, Zaad, ama eDahab.',

        // Testimonials
        'testi_eyebrow' => 'ARAGTIDA MACAAMIISHA',
        'testi1_q' => '"Heshiiska Muraabaxada waa mid aad u cad oo hufan. Qayb kasta oo lacag ah maalinta koowaad ayaad ogtahay."',
        'testi1_a' => 'Ganacsade Tukaanka',
        'testi2_q' => '"Nidaamku wuxuu ahaa mid fudud oo hufan. Maalmaha Jimcaha si toos ah ayay Sabtida ugu wareegayaan."',
        'testi2_a' => 'Ganacsade Suuqa',
        'testi3_q' => '"Waa shirkad lagu kalsoonaan karo oo had iyo jeer diyaar u ah inay taageerto ganacsiyada yaryar."',
        'testi3_a' => 'Hoggaamiye Kooxeed',

        // Education
        'edu_eyebrow' => 'WACYIGELINTA MAALIYADDA',
        'edu1_t' => 'Dhismaha Boosaska',
        'edu1_d' => 'Sida aad booskaaga ugu dhisan karto guri adigoo lacagta qayb qayb u bixinaya.',
        'edu2_t' => 'Sida Maalgelintu u Shaqeyso',
        'edu2_d' => 'Fahan sida nidaamka Muraabaxada Islaamiga ahi kuugu caawin karo yoolalkaaga.',
        'edu3_t' => 'Aasaaska Ganacsiga',
        'edu3_d' => 'Baro xeelado fudud oo badeecadaada iyo ganacsigaaga lagu kobciyo.',
        'edu4_t' => 'Goobta Xafiiska',
        'edu4_d' => 'Waddada Weyn ee Muqdisho, Hodan • Booqo xafiiskeena SIMDA.',
        'btn_read_more' => 'Faahfaahin',
        'btn_open_maps' => 'Fur Google Maps',

        // Calculator
        'calc_eyebrow' => 'XISAABIYAHA JADWALKA XALAASHA AH',
        'calc_shift_badge' => 'Jimco → Sabti u wareejin toos ah',
        'calc_label_principal' => 'Lacagta la Bixinayo ($ USD)',
        'calc_label_monthly' => 'Lacagta Bishii la Bixinayo ($ USD)',
        'calc_label_duration' => 'Mudada (Bilood / Toddobaad)',
        'calc_opt_months' => 'Bilood (Bille)',
        'calc_opt_weeks' => 'Toddobaadyo (Toddobaadle)',
        'calc_label_extra' => 'Dulsaar / Ribo (% — Waa 0% Xalaal)',
        'calc_btn_generate' => 'Soo Saar Jadwalka Saxda ah',
        'calc_quick_amounts' => 'Qaddarro Degdeg ah:',

        // Contact
        'contact_eyebrow' => 'NAGA SOO WAAC',
        'contact_title' => 'Diyaar ma u tahay tallaabada xigta?',
        'contact_lead' => 'Waxaan diyaar u nahay inaan kugu caawinno sidii aad u gaari lahayd yoolalkaaga xalaasha ah maanta.',
        'contact_btn_start' => 'Bilow Hadda',

        // Footer
        'footer_about' => 'Ururka Horumarinta Maalgelinta Yar Yar ee Islaamiga ah ee Soomaaliya (SIMDA) waxaa ka go\'an maalgelin xalaal ah, kobcinta ganacsiyada yaryar, iyo dhisidda bulsho leh 0% Ribo.',
        'footer_quick_links' => 'Xiriiryo Degdeg ah',
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
        'btn_learn' => 'اعرف المزيد',
        'btn_calc' => 'حاسبة جدول السداد',

        // Slide 1: Murabaha
        'slide1_tag' => 'تمويل المرابحة',
        'slide1_eyebrow' => 'التمويل الإسلامي • صيغة المرابحة',
        'slide1_title' => 'تمويل المرابحة الإسلامي<br>لشراء الأصول والبضائع',
        'slide1_lead' => 'امتلك بضائع ومعدات مشروعك بصيغة المرابحة الشرعية الشفافة وبدون أي فوائد ربوية (0% ربا) لتمكين رواد الأعمال الصوماليين من النمو بثقة.',
        'slide1_badge_val' => '100% شريعة',
        'slide1_badge_sub' => 'أخلاقي ومتوافق',

        // Slide 2: Maalgalin / Dukaanada
        'slide2_tag' => 'تمويل المتاجر الصغرى',
        'slide2_eyebrow' => 'رأس المال العامل • المشاريع الصغرى',
        'slide2_title' => 'دعم وتطوير المتاجر<br>ومنافذ البيع بالتجزئة',
        'slide2_lead' => 'ندعم الأمهات المكافحات وأصحاب محال البقالة والمتاجر الصغرى على الطرقات بتمويل إسلامي ميسر لتوفير البضائع وتنمية دخل الأسرة.',
        'slide2_badge_val' => '0% ربا',
        'slide2_badge_sub' => 'المتاجر والمجتمع',

        // Services
        'services_eyebrow' => 'خدماتنا',
        'services_title' => 'حلول تمويلية إسلامية لكافة مراحل مشروعك',
        'srv1_title' => 'تمويل بناء الأراضي والمنازل',
        'srv1_desc' => 'نبني منازل عصرية للمواطنين الذين يملكون أراضي وبحاجة للتمويل عبر خطط سداد ميسرة خالية تماماً من الربا.',
        'srv2_title' => 'المرابحة للسيارات والمعدات',
        'srv2_desc' => 'نشتري لك السيارات والدراجات النارية والتوك توك والهواتف الذكية بنظام المرابحة الإسلامية مع أقساط مريحة.',
        'srv3_title' => 'تمويل المتاجر ومحلات التجزئة',
        'srv3_desc' => 'رأس مال عامل ميسر ومباشر لدعم محال البقالة والمتاجر الصغرى على الطرقات لزيادة البضائع وتنمية الدخل.',
        'srv4_title' => 'تمويلات حسنة بدون ربا (0%)',
        'srv4_desc' => 'تمويلات مباشرة بدون أي فوائد ربوية (0% ربا) — تسديد أصل المبلغ المستلم فقط بدقة وأمانة وفق الشريعة.',

        // Why Us
        'why_eyebrow' => 'لماذا تختار سيمدا',
        'why_title' => 'أكثر من مجرد تمويل،<br>نحن نبني مستقبلاً مستداماً.',
        'why1_t' => 'موافقات سريعة وميسرة',
        'why1_d' => 'إجراءات فحص واعتماد سريعة عبر مسؤولي الائتمان ومديري الفروع دون تعقيد.',
        'why2_t' => 'دعم واستشارات مهنية',
        'why2_d' => 'فريقنا في الفروع معك خطوة بخطوة لدعم نجاح مشروعك ونموه.',
        'why3_t' => 'سداد مرن وأقساط مريحة',
        'why3_d' => 'جداول أسبوعية أو شهرية مع ترحيل تلقائي لأيام الجمعة إلى السبت.',
        'why4_t' => 'خدمة المجتمع والتضامن',
        'why4_d' => 'ندعم مجموعات التضامن والتعاون، مع تخصيص كامل لرسوم الخيرات لصندوق التكافل.',
        'why5_t' => 'أمان وشفافية مصرفية كاملة',
        'why5_d' => 'أمان بياناتك ودقة سجلاتك المالية وفق أعلى معايير الحوكمة الإسلامية.',
        'why6_t' => 'تسعير واضح بدون ربا',
        'why6_d' => 'هامش ربح المرابحة محدد وثابت عند التعاقد ولا يتضاعف أبداً مع الوقت.',

        // Process
        'process_eyebrow' => 'كيف نعمل',
        'step1_t' => 'تقديم الطلب',
        'step1_d' => 'قدّم طلباً ميسراً إلكترونياً أو عبر زيارة أقرب فرع مع الكفيل.',
        'step2_t' => 'المراجعة والتحقق',
        'step2_d' => 'ندرس طلبك، ونراجع جدول السداد المقترح والأهلية الائتمانية.',
        'step3_t' => 'استلام التمويل أو الأصل',
        'step3_d' => 'بعد الاعتماد يتم توفير الأصل أو تحويل التمويل عبر EVC Plus أو Zaad أو eDahab.',

        // Testimonials
        'testi_eyebrow' => 'آراء وشهادات عملائنا',
        'testi1_q' => '"صيغة المرابحة في سيمدا واضحة تماماً. جميع الأقساط محددة من اليوم الأول دون أي مفاجآت."',
        'testi1_a' => 'تاجر تجزئة',
        'testi2_q' => '"التعامل كان ميسراً وشفافاً. مواعيد السداد التي تصادف الجمعة تُرَحَّل تلقائياً إلى السبت."',
        'testi2_a' => 'صاحب متجر بالسوق',
        'testi3_q' => '"مؤسسة مهنية وموثوقة تقدم دعماً حقيقياً لأصحاب المشاريع الصغرى والأسر المنتجة."',
        'testi3_a' => 'رئيسة مجموعة تضامن',

        // Education
        'edu_eyebrow' => 'التثقيف والتوعية المالية',
        'edu1_t' => 'تمويل بناء المساكن',
        'edu1_d' => 'كيف تبني منزلك على أرضك بدفعات ميسرة بدون ربا.',
        'edu2_t' => 'كيف يعمل التمويل الإسلامي',
        'edu2_d' => 'تعرف على صيغ المرابحة والقرض الحسن وكيف تنمي بها مشروعك.',
        'edu3_t' => 'أساسيات إدارة التجارة',
        'edu3_d' => 'استراتيجيات بسيطة لإدارة المخزون والتدفقات النقدية لمشروعك.',
        'edu4_t' => 'مقر المكتب الرئيسي',
        'edu4_d' => 'شارع مقديشو الرئيسي، حي هودان • تفضل بزيارة مقر سيمدا.',
        'btn_read_more' => 'اقرأ المزيد',
        'btn_open_maps' => 'فتح في خرائط جوجل',

        // Calculator
        'calc_eyebrow' => 'حاسبة جدول السداد التفاعلية',
        'calc_shift_badge' => 'الجمعة ← السبت ترحيل تلقائي',
        'calc_label_principal' => 'مبلغ التمويل ($ دولار)',
        'calc_label_monthly' => 'القسط الشهري المستهدف ($ دولار)',
        'calc_label_duration' => 'مدة السداد (أشهر / أسابيع)',
        'calc_opt_months' => 'أشهر (شهرياً)',
        'calc_opt_weeks' => 'أسابيع (أسبوعياً)',
        'calc_label_extra' => 'الزيادة الربوية (مغلقة 0% ربا)',
        'calc_btn_generate' => 'إنشاء جدول الأقساط الدقيق',
        'calc_quick_amounts' => 'مبالغ سريعة:',

        // Contact
        'contact_eyebrow' => 'تواصل معنا',
        'contact_title' => 'هل أنت مستعد لبدء مشروعك؟',
        'contact_lead' => 'نحن هنا لمساعدتك في تحقيق أهدافك وتمويل طموحاتك وفق الشريعة الإسلامية.',
        'contact_btn_start' => 'ابدأ الآن',

        // Footer
        'footer_about' => 'الجمعية الصومالية لتنمية التمويل الأصغر الإسلامي (سيمدا) مكرسة للتمويل الأخلاقي، ونمو المشاريع الصغيرة، وبناء مجتمع مُمَكَّن بنسبة 0% ربا.',
        'footer_quick_links' => 'روابط سريعة',
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
$t = $homeI18n[$locale] ?? $homeI18n['en'];

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
    <title>SIMDA — <?= Security::e(Lang::get('app_full_name')) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    keys.forEach(function(k) {
                        if (k !== 'simda-static-shell-v4') { caches.delete(k); }
                    });
                });
            }
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

        <ul class="fid-nav-links" id="homeNavLinks">
            <li><a href="#home" class="active"><?= Security::e($t['nav_home']) ?></a></li>
            <li><a href="?route=about"><?= Security::e($t['nav_about']) ?></a></li>
            <li><a href="#services"><?= Security::e($t['nav_services']) ?></a></li>
            <li><a href="#our-process"><?= Security::e($t['nav_process']) ?></a></li>
            <li><a href="#testimonials"><?= Security::e($t['nav_testimonials']) ?></a></li>
            <li><a href="?route=faq"><?= Security::e($t['nav_faq']) ?></a></li>
            <li><a href="?route=contact"><?= Security::e($t['nav_contact']) ?></a></li>
        </ul>

        <div style="display:flex;align-items:center;gap:10px;">
            <!-- Theme Switcher: Light & Dark Mode Icons -->
            <div class="theme-toggle-pill" id="homeThemeToggle" title="Light Mode / Dark Mode">
                <button type="button" class="theme-pill-btn" data-theme-val="light" aria-label="Light Mode" title="<?= $isSo ? 'Habka Maalintii (Light Mode)' : 'Light Mode' ?>">
                    <?= Icons::svg('sun', 16) ?>
                </button>
                <button type="button" class="theme-pill-btn" data-theme-val="dark" aria-label="Dark Mode" title="<?= $isSo ? 'Habka Habeenkii (Dark Mode)' : 'Dark Mode' ?>">
                    <?= Icons::svg('moon', 16) ?>
                </button>
            </div>

            <!-- Language Dropdown Selector (Active flag shown, tap opens dropdown menu) -->
            <div class="simda-lang-dropdown" id="homeLangDropdown">
                <button type="button" class="simda-lang-trigger" aria-haspopup="true" aria-expanded="false" onclick="event.stopPropagation(); const p = this.closest('.simda-lang-dropdown'); const open = p.classList.toggle('is-open'); this.setAttribute('aria-expanded', open);" title="Change Language / Dooro Luqadda">
                    <img src="<?= Security::e($activeLang['flag']) ?>" alt="<?= Security::e($activeLang['label']) ?>" class="simda-lang-flag-circle">
                    <span class="simda-lang-code"><?= Security::e($activeLang['code']) ?></span>
                    <svg class="simda-lang-caret" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 1 5 5 9 1"></polyline>
                    </svg>
                </button>
                <div class="simda-lang-menu" role="menu">
                    <?php foreach ($languagesList as $code => $info): ?>
                        <a href="?route=home&lang=<?= $code ?>" class="simda-lang-item <?= $locale === $code ? 'is-active' : '' ?>" role="menuitem">
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

<!-- 2. ROUNDED WARM CREAM HERO BANNER CARD (DYNAMIC CAROUSEL: MURABAHA & MAALGALIN / DUKAANADA) -->
<section id="home" class="fid-hero-wrap">
    <div class="fid-container">
        <div class="fid-hero-card fid-hero-carousel animate-in" id="simdaHeroCarousel">
            <!-- SLIDE 1: MURAABAXO (Murabaha Asset Financing) -->
            <div class="fid-hero-slide active" data-slide="0">
                <div class="fid-hero-copy">
                    <span class="fid-eyebrow"><?= Security::e($t['slide1_eyebrow']) ?></span>
                    <h1 class="fid-serif-h1"><?= $t['slide1_title'] ?></h1>
                    <p class="fid-hero-lead">
                        <?= Security::e($t['slide1_lead']) ?>
                    </p>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:28px;">
                        <a href="#services" class="fid-btn-green">
                            <span><?= Security::e($t['btn_learn']) ?></span>
                            <?= Icons::svg('arrow-right', 14) ?>
                        </a>
                        <a href="#calculator" class="btn btn-outline" style="border-radius:999px;padding:10px 18px;border-color:#123B32;color:#123B32;font-weight:600;">
                            <?= Icons::svg('calendar', 14) ?>
                            <span><?= Security::e($t['btn_calc']) ?></span>
                        </a>
                    </div>
                </div>

                <!-- DYNAMIC MURABAHA 5-ASSET SHOWCASE (Switches every 4 seconds) -->
                <?php
                $murabahaAssets = [
                    [
                        'id' => 'car',
                        'img' => 'assets/images/landing/murabaha/murabaha-car.png',
                        'title_so' => 'Gaari MG HS Luxury SUV',
                        'title_en' => 'MG HS Luxury SUV (2024)',
                        'title_ar' => 'سيارة إم جي إتش إس الفاخرة',
                        'category_so' => 'Maalgalinta Gaadiidka',
                        'category_en' => 'Vehicle Financing',
                        'category_ar' => 'تمويل السيارات',
                        'price' => '$16,500',
                        'monthly' => '$345',
                        'badge' => '0% Ribo • Murabaha'
                    ],
                    [
                        'id' => 'scooter',
                        'img' => 'assets/images/landing/murabaha/murabaha-scooter.png',
                        'title_so' => 'Mooto Scooter Pleasure+ (Delivery)',
                        'title_en' => 'Hero Pleasure+ Scooter (Work & Delivery)',
                        'title_ar' => 'سكوتر هيرو بليجر للتوصيل والعمل',
                        'category_so' => 'Gaadiidka Tafaariiqda & Shaqada',
                        'category_en' => 'Delivery & Commuter',
                        'category_ar' => 'وسائل النقل الخفيفة',
                        'price' => '$1,250',
                        'monthly' => '$65',
                        'badge' => '0% Ribo • Murabaha'
                    ],
                    [
                        'id' => 'bike',
                        'img' => 'assets/images/landing/murabaha/murabaha-bike.png',
                        'title_so' => 'Mooto Honda CBR Sport (250cc)',
                        'title_en' => 'Honda CBR Sport Motorcycle',
                        'title_ar' => 'دراجة هوندا سي بي آر الرياضية',
                        'category_so' => 'Mootooyinka Xawaaraha & Shaqada',
                        'category_en' => 'Performance Transport',
                        'category_ar' => 'دراجات نارية متطورة',
                        'price' => '$3,200',
                        'monthly' => '$135',
                        'badge' => '0% Ribo • Murabaha'
                    ],
                    [
                        'id' => 'phone_orange',
                        'img' => 'assets/images/landing/murabaha/murabaha-phone-orange.png',
                        'title_so' => 'iPhone 17 Pro Max (Cosmic Orange)',
                        'title_en' => 'iPhone 17 Pro Max (Cosmic Orange)',
                        'title_ar' => 'آيفون 17 برو ماكس برتقالي',
                        'category_so' => 'Qalabka Casriga ah ee Ganacsiga',
                        'category_en' => 'Smart Enterprise Tech',
                        'category_ar' => 'الأجهزة الذكية للأعمال',
                        'price' => '$1,150',
                        'monthly' => '$95',
                        'badge' => '0% Ribo • Murabaha'
                    ],
                    [
                        'id' => 'phone_red',
                        'img' => 'assets/images/landing/murabaha/murabaha-phone-red.png',
                        'title_so' => 'iPhone 18 Pro Max (Deep Crimson Red)',
                        'title_en' => 'iPhone 18 Pro Max (Deep Crimson Red)',
                        'title_ar' => 'آيفون 18 برو ماكس قرمزي',
                        'category_so' => 'Qalabka Casriga ah ee Ganacsiga',
                        'category_en' => 'Smart Enterprise Tech',
                        'category_ar' => 'الأجهزة الذكية للأعمال',
                        'price' => '$1,280',
                        'monthly' => '$105',
                        'badge' => '0% Ribo • Murabaha'
                    ],
                ];
                ?>

                <div class="fid-hero-media murabaha-asset-showcase" id="murabahaAssetShowcase">
                    <?php foreach ($murabahaAssets as $aIdx => $asset): ?>
                        <div class="murabaha-asset-item <?= $aIdx === 0 ? 'active' : '' ?>" data-asset-idx="<?= $aIdx ?>">
                            <!-- Top Category / Murabaha badge -->
                            <div class="murabaha-asset-cat-chip">
                                <span class="asset-cat-pulse"></span>
                                <span><?= Security::e($asset['category_' . $locale] ?? $asset['category_en']) ?></span>
                            </div>

                            <!-- Centered Clean Product Canvas -->
                            <div class="murabaha-asset-canvas">
                                <img src="<?= Security::e($asset['img']) ?>" alt="<?= Security::e($asset['title_' . $locale] ?? $asset['title_en']) ?>" class="murabaha-asset-img">
                            </div>

                            <!-- Bottom Floating Price Card -->
                            <div class="murabaha-asset-price-box">
                                <?php
                                $dispMonthly = ($locale === 'ar') ? Security::toArabicDigits(str_replace('$', '', $asset['monthly'])) . ' $' : $asset['monthly'];
                                $dispPrice = ($locale === 'ar') ? Security::toArabicDigits(str_replace('$', '', $asset['price'])) . ' $' : $asset['price'];
                                ?>
                                <div class="murabaha-asset-info">
                                    <div class="murabaha-asset-name"><?= Security::e($asset['title_' . $locale] ?? $asset['title_en']) ?></div>
                                    <div class="murabaha-asset-terms">
                                        <span class="num-tabular" style="color:#123B32;font-weight:800;"><?= Security::e($dispMonthly) ?></span>/<?= $locale === 'so' ? 'bishii' : ($locale === 'ar' ? 'شهرياً' : 'mo') ?> • <span style="color:#23845B;font-weight:700;"><?= $locale === 'ar' ? '0% ربا' : ($locale === 'so' ? '0% Ribo' : '0% Riba') ?></span>
                                    </div>
                                </div>
                                <div class="murabaha-asset-price-badge">
                                    <div class="asset-price-label"><?= $locale === 'so' ? 'QIIMAHA' : ($locale === 'ar' ? 'السعر' : 'PRICE') ?></div>
                                    <div class="asset-price-val num-tabular"><?= Security::e($dispPrice) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Bottom Thumbnail Navigator Pills -->
                    <div class="murabaha-asset-indicators" aria-label="Murabaha Products">
                        <?php foreach ($murabahaAssets as $aIdx => $asset): ?>
                            <?php $thumbPrice = ($locale === 'ar') ? Security::toArabicDigits(str_replace('$', '', $asset['price'])) . ' $' : $asset['price']; ?>
                            <button type="button" class="murabaha-ind-btn <?= $aIdx === 0 ? 'active' : '' ?>" data-asset-target="<?= $aIdx ?>" title="<?= Security::e($asset['title_' . $locale] ?? $asset['title_en']) ?>">
                                <span class="murabaha-ind-thumb">
                                    <img src="<?= Security::e($asset['img']) ?>" alt="thumb">
                                </span>
                                <span class="murabaha-ind-price num-tabular"><?= Security::e($thumbPrice) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: MAALGALINTA DUKAAMADA (Somali Mother Roadside Kiosk / Dukaan) -->
            <div class="fid-hero-slide" data-slide="1">
                <div class="fid-hero-copy">
                    <span class="fid-eyebrow" style="color:#23845B;"><?= Security::e($t['slide2_eyebrow']) ?></span>
                    <h1 class="fid-serif-h1"><?= $t['slide2_title'] ?></h1>
                    <p class="fid-hero-lead">
                        <?= Security::e($t['slide2_lead']) ?>
                    </p>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:28px;">
                        <a href="?route=<?= $isLoggedIn ? 'applications' : 'login' ?>" class="fid-btn-green" style="background-color:#194E3F;border-color:#194E3F;">
                            <span><?= Security::e($isLoggedIn ? $t['cta_dashboard'] : $t['cta_start']) ?></span>
                            <?= Icons::svg('arrow-right', 14) ?>
                        </a>
                        <a href="#calculator" class="btn btn-outline" style="border-radius:999px;padding:10px 18px;border-color:#123B32;color:#123B32;font-weight:600;">
                            <?= Icons::svg('calendar', 14) ?>
                            <span><?= Security::e($t['btn_calc']) ?></span>
                        </a>
                    </div>
                </div>

                <div class="fid-hero-media">
                    <img src="assets/images/landing/hero-somali-mama-shop.jpg" alt="Somali Mother Roadside Dukaan Shop">
                    <div class="fid-hero-float-badge">
                        <div class="fid-float-icon" style="background:rgba(35,132,91,0.16);color:#23845B;">
                            <?= Icons::svg('users', 20) ?>
                        </div>
                        <div>
                            <div style="font-size:1.05rem;font-weight:800;color:#123B32;line-height:1.15;"><?= Security::e($t['slide2_badge_val']) ?></div>
                            <div style="font-size:0.74rem;color:#6A756F;font-weight:500;"><?= Security::e($t['slide2_badge_sub']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CAROUSEL FLOATING NAVIGATION CONTROLS (PILLS + ARROWS) -->
            <div class="fid-hero-nav-bar">
                <div class="fid-hero-pills">
                    <button type="button" class="fid-hero-pill active" data-target="0" aria-label="Slide 1: Muraabaxo">
                        <span class="fid-pill-dot"></span>
                        <span><?= Security::e($t['slide1_tag']) ?></span>
                    </button>
                    <button type="button" class="fid-hero-pill" data-target="1" aria-label="Slide 2: Maalgalinta">
                        <span class="fid-pill-dot"></span>
                        <span><?= Security::e($t['slide2_tag']) ?></span>
                    </button>
                </div>
                <div class="fid-hero-arrows">
                    <button type="button" class="fid-hero-arrow" id="heroPrevBtn" aria-label="Previous Slide">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <button type="button" class="fid-hero-arrow" id="heroNextBtn" aria-label="Next Slide">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. OUR SERVICES â€” 4-COLUMN EDITORIAL CARDS -->
<section id="services" class="fid-section">
    <div class="fid-container">
        <div class="fid-section-head">
            <span class="fid-eyebrow"><?= Security::e($t['services_eyebrow']) ?></span>
            <h2 class="fid-serif-h2"><?= Security::e($t['services_title']) ?></h2>
        </div>

        <div class="fid-services-grid">
            <!-- Card 1: Dhismaha Boosaska & Guryaha -->
            <div class="fid-service-card sage reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-construction.jpg" alt="Dhismaha Boosaska & Guryaha">
                    </div>
                    <h3><?= Security::e($t['srv1_title']) ?></h3>
                    <p><?= Security::e($t['srv1_desc']) ?></p>
                </div>
                <div>
                    <a href="?route=<?= $isLoggedIn ? 'applications' : 'login' ?>" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 2: Muraabaxada Gaadiidka & Qalabka (Baabuur, Mooto, Bajaj, Mobile) -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-business.jpg" alt="Muraabaxada Gaadiidka & Qalabka">
                    </div>
                    <h3><?= Security::e($t['srv2_title']) ?></h3>
                    <p><?= Security::e($t['srv2_desc']) ?></p>
                </div>
                <div>
                    <a href="?route=<?= $isLoggedIn ? 'applications' : 'login' ?>" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 3: Taageerada Dukaamada Yaryar -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-credit.jpg" alt="Taageerada Dukaamada Yaryar">
                    </div>
                    <h3><?= Security::e($t['srv3_title']) ?></h3>
                    <p><?= Security::e($t['srv3_desc']) ?></p>
                </div>
                <div>
                    <a href="?route=<?= $isLoggedIn ? 'applications' : 'login' ?>" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 4: Dayn Xalaal ah (0% Ribo) -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-consulting.jpg" alt="Dayn Xalaal ah">
                    </div>
                    <h3><?= Security::e($t['srv4_title']) ?></h3>
                    <p><?= Security::e($t['srv4_desc']) ?></p>
                </div>
                <div>
                    <a href="#calculator" class="fid-circle-arrow" aria-label="Open calculator"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>
        </div>

        <?php if (!empty($products)): ?>
        <!-- Live Configured Database Products Strip -->
        <div style="margin-top:22px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
            <?php foreach ($products as $prod): ?>
                <div class="fid-service-card" style="padding:16px 18px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span class="badge badge-gold"><?= Security::e(strtoupper((string) $prod['product_type'])) ?></span>
                        <span class="num-tabular fid-product-code" style="font-size:0.75rem;"><?= Security::e($prod['product_code']) ?></span>
                    </div>
                    <h4 class="fid-product-title" style="font-size:0.96rem;margin:0 0 4px;"><?= Security::e($prod['name_en']) ?></h4>
                    <div class="fid-product-meta" style="font-size:0.78rem;display:flex;justify-content:space-between;margin-top:6px;padding-top:6px;border-top:1px solid rgba(0,0,0,0.06);">
                        <span>Rate: <strong class="num-tabular"><?= Security::e(number_format((float) ($prod['product_type'] === 'murabaha' ? $prod['murabaha_markup_pct'] : $prod['annual_profit_rate']), 2)) ?>%</strong></span>
                        <span class="num-tabular"><?= Security::formatMoney($prod['min_amount']) ?> – <?= Security::formatMoney($prod['max_amount']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. WHY CHOOSE US â€” 2-COLUMN PHOTO & FEATURE BADGES -->
<section id="why-us" class="fid-section">
    <div class="fid-container">
        <div class="fid-why-grid">
            <div class="fid-why-photo reveal-on-scroll">
                <img src="assets/images/landing/why-choose-us.jpg?v=<?= filemtime(BASE_PATH . '/public/assets/images/landing/why-choose-us.jpg') ?>" alt="SIMDA Executive Leader with Apple MacBook">
            </div>

            <div class="fid-why-panel reveal-on-scroll">
                <span class="fid-eyebrow"><?= Security::e($t['why_eyebrow']) ?></span>
                <h2 class="fid-serif-h2" style="margin-bottom:6px;"><?= $t['why_title'] ?></h2>

                <div class="fid-why-features">
                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('check-circle', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why1_t']) ?></h4>
                            <p><?= Security::e($t['why1_d']) ?></p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('users', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why2_t']) ?></h4>
                            <p><?= Security::e($t['why2_d']) ?></p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('calendar', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why3_t']) ?></h4>
                            <p><?= Security::e($t['why3_d']) ?></p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('heart-handshake', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why4_t']) ?></h4>
                            <p><?= Security::e($t['why4_d']) ?></p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('shield', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why5_t']) ?></h4>
                            <p><?= Security::e($t['why5_d']) ?></p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('award', 18) ?></div>
                        <div>
                            <h4><?= Security::e($t['why6_t']) ?></h4>
                            <p><?= Security::e($t['why6_d']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. HOW IT WORKS (OUR PROCESS) -->
<section id="our-process" class="fid-section">
    <div class="fid-container">
        <div style="text-align:center;margin-bottom:26px;">
            <span class="fid-eyebrow"><?= Security::e($t['process_eyebrow'] ?? 'HOW IT WORKS') ?></span>
            <h2 class="fid-section-title" style="margin-top:6px;"><?= $isSo ? 'Habka Fudud ee Maalgelinta' : 'Simple 3-Step Financing Process' ?></h2>
            <p class="fid-section-subtitle" style="margin:4px auto 0 auto;max-width:540px;"><?= $isSo ? 'U mar heshiiskaaga 3 tallaabo oo hufan oo waafaqsan Shareecada Islaamka.' : 'Get your Sharia-compliant financing approved in three transparent steps.' ?></p>
        </div>
        <div class="fid-steps-grid">
            <div class="fid-step-card reveal-on-scroll">
                <span class="fid-step-num">1</span>
                <div class="fid-step-inner">
                    <img src="assets/images/landing/step-1.jpg" alt="Apply">
                    <div>
                        <h4 style="font-size:0.86rem;margin:0 0 3px;"><?= Security::e($t['step1_t']) ?></h4>
                        <p style="font-size:0.72rem;margin:0;line-height:1.38;"><?= Security::e($t['step1_d']) ?></p>
                    </div>
                </div>
            </div>

            <div class="fid-step-card reveal-on-scroll">
                <span class="fid-step-num">2</span>
                <div class="fid-step-inner">
                    <img src="assets/images/landing/step-2.jpg" alt="Verification">
                    <div>
                        <h4 style="font-size:0.86rem;margin:0 0 3px;"><?= Security::e($t['step2_t']) ?></h4>
                        <p style="font-size:0.72rem;margin:0;line-height:1.38;"><?= Security::e($t['step2_d']) ?></p>
                    </div>
                </div>
            </div>

            <div class="fid-step-card reveal-on-scroll">
                <span class="fid-step-num">3</span>
                <div class="fid-step-inner">
                    <img src="assets/images/landing/step-3.jpg" alt="Receive Funds">
                    <div>
                        <h4 style="font-size:0.86rem;margin:0 0 3px;"><?= Security::e($t['step3_t']) ?></h4>
                        <p style="font-size:0.72rem;margin:0;line-height:1.38;"><?= Security::e($t['step3_d']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. WHAT OUR CLIENTS VALUE (TESTIMONIALS) -->
<section id="testimonials" class="fid-section" style="padding-top:20px;">
    <div class="fid-container">
        <div style="text-align:center;margin-bottom:26px;">
            <span class="fid-eyebrow"><?= Security::e($t['testi_eyebrow'] ?? 'WHAT OUR CLIENTS VALUE') ?></span>
            <h2 class="fid-section-title" style="margin-top:6px;"><?= $isSo ? 'Kalsoonida Macaamiisheenna' : 'What Our Clients Value' ?></h2>
            <p class="fid-section-subtitle" style="margin:4px auto 0 auto;max-width:540px;"><?= $isSo ? 'Aragtiyada ganacsatada iyo qoysaska ka faa\'iidaystay adeegyada SIMDA.' : 'Real experiences from entrepreneurs scaling their businesses with 0% Riba.' ?></p>
        </div>
        <div class="fid-testi-grid">
            <div class="fid-testi-card reveal-on-scroll">
                <div>
                    <div class="fid-stars">★★★★★</div>
                    <p class="fid-testi-quote" style="font-size:0.73rem;line-height:1.45;margin:0;">
                        <?= Security::e($t['testi1_q']) ?>
                    </p>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                    <strong class="fid-testi-author" style="font-size:0.74rem;">– <?= Security::e($t['testi1_a']) ?></strong>
                    <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                </div>
            </div>

            <div class="fid-testi-card reveal-on-scroll">
                <div>
                    <div class="fid-stars">★★★★★</div>
                    <p class="fid-testi-quote" style="font-size:0.73rem;line-height:1.45;margin:0;">
                        <?= Security::e($t['testi2_q']) ?>
                    </p>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                    <strong class="fid-testi-author" style="font-size:0.74rem;">– <?= Security::e($t['testi2_a']) ?></strong>
                    <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                </div>
            </div>

            <div class="fid-testi-card reveal-on-scroll">
                <div>
                    <div class="fid-stars">★★★★★</div>
                    <p class="fid-testi-quote" style="font-size:0.73rem;line-height:1.45;margin:0;">
                        <?= Security::e($t['testi3_q']) ?>
                    </p>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                    <strong class="fid-testi-author" style="font-size:0.74rem;">– <?= Security::e($t['testi3_a']) ?></strong>
                    <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. FINANCIAL EDUCATION — 4 HORIZONTAL MEDIA CARDS + LIVE SCHEDULE CALCULATOR -->
<section id="education" class="fid-section">
    <div class="fid-container">
        <div class="fid-section-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <span class="fid-eyebrow"><?= Security::e($t['edu_eyebrow']) ?></span>
            </div>
            <a href="?route=faq" class="fid-btn-green" style="padding:7px 18px;font-size:0.8rem;border-radius:999px;box-shadow:none;">
                <span><?= Security::e($t['footer_faq']) ?></span>
                <?= Icons::svg('arrow-right', 12) ?>
            </a>
        </div>

        <div class="fid-edu-grid">
            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/srv-construction.jpg" alt="Dhismaha Boosaska & Guryaha">
                <div>
                    <h4><?= Security::e($t['edu1_t']) ?></h4>
                    <p><?= Security::e($t['edu1_d']) ?></p>
                    <a href="#calculator" class="fid-read-more"><?= Security::e($t['btn_read_more']) ?> <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-2.jpg" alt="How Micro-Loans Work">
                <div>
                    <h4><?= Security::e($t['edu2_t']) ?></h4>
                    <p><?= Security::e($t['edu2_d']) ?></p>
                    <a href="#calculator" class="fid-read-more"><?= Security::e($t['btn_read_more']) ?> <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-3.jpg" alt="Investment Basics">
                <div>
                    <h4><?= Security::e($t['edu3_t']) ?></h4>
                    <p><?= Security::e($t['edu3_d']) ?></p>
                    <a href="#calculator" class="fid-read-more"><?= Security::e($t['btn_read_more']) ?> <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener noreferrer" style="display:block;flex-shrink:0;" title="Open in Google Maps">
                    <img src="assets/images/landing/edu-4.jpg" alt="SIMDA Office Location Mogadishu, Somalia">
                </a>
                <div>
                    <h4><?= Security::e($t['edu4_t']) ?></h4>
                    <p><?= Security::e($t['edu4_d']) ?></p>
                    <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener noreferrer" class="fid-read-more">
                        <?= Security::e($t['btn_open_maps']) ?> <?= Icons::svg('arrow-right', 12) ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Interactive Installment Schedule Calculator (Styled in Fiducia Cream & Deep Green) -->
        <div id="calculator" class="fid-why-panel reveal-on-scroll" style="margin-top:28px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                <div>
                    <span class="fid-eyebrow" style="margin-bottom:4px;"><?= Security::e($t['calc_eyebrow']) ?></span>
                    <h3 class="fid-serif-h2" style="font-size:1.45rem;"><?= Security::e(Lang::get('calc_preview_title')) ?></h3>
                </div>
                <span class="badge badge-gold"><?= Icons::svg('calendar', 14) ?> <?= Security::e($t['calc_shift_badge']) ?></span>
            </div>

            <div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;">
                <div class="form-group">
                    <label><?= Security::e($t['calc_label_principal']) ?></label>
                    <input type="number" id="calc_principal" class="form-control num-tabular" value="3000" min="50" max="100000" step="50" placeholder="e.g. 3000">
                </div>
                <div class="form-group">
                    <label><?= Security::e($t['calc_label_monthly']) ?></label>
                    <input type="number" id="calc_monthly_payment" class="form-control num-tabular" value="200" min="10" max="100000" step="10" placeholder="e.g. 200">
                </div>
                <div class="form-group">
                    <label><?= Security::e($t['calc_label_duration']) ?></label>
                    <div style="display:flex;gap:6px;">
                        <input type="number" id="calc_term" class="form-control num-tabular" value="15" min="1" max="120" style="width:82px;">
                        <select id="calc_frequency" class="form-control" style="flex:1;">
                            <option value="monthly"><?= Security::e($t['calc_opt_months']) ?></option>
                            <option value="weekly"><?= Security::e($t['calc_opt_weeks']) ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= Security::e($t['calc_label_extra']) ?></label>
                    <input type="number" id="calc_rate" class="form-control num-tabular" value="0.00" readonly style="background:rgba(35,132,91,0.08);color:#23845B;font-weight:700;">
                    <input type="hidden" id="calc_product_type" value="flat">
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin:10px 0 14px;">
                <button type="button" id="btnPreviewSchedule" class="fid-btn-green">
                    <?= Icons::svg('calendar', 15) ?>
                    <span><?= Security::e($t['calc_btn_generate']) ?></span>
                </button>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span class="calc-quick-label" style="font-size:0.76rem;font-weight:600;"><?= Security::e($t['calc_quick_amounts']) ?></span>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="1000" data-monthly="200"><?= $locale === 'ar' ? '١,٠٠٠ $ (٢٠٠ $/شهرياً)' : ($locale === 'so' ? '$1,000 ($200/bishii)' : '$1,000 ($200/mo)') ?></button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="3000" data-monthly="200"><?= $locale === 'ar' ? '٣,٠٠٠ $ (٢٠٠ $/شهرياً)' : ($locale === 'so' ? '$3,000 ($200/bishii)' : '$3,000 ($200/mo)') ?></button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="5000" data-monthly="500"><?= $locale === 'ar' ? '٥,٠٠٠ $ (٥٠٠ $/شهرياً)' : ($locale === 'so' ? '$5,000 ($500/bishii)' : '$5,000 ($500/mo)') ?></button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="10000" data-monthly="500"><?= $locale === 'ar' ? '١٠,٠٠٠ $ (٥٠٠ $/شهرياً)' : ($locale === 'so' ? '$10,000 ($500/bishii)' : '$10,000 ($500/mo)') ?></button>
                </div>
            </div>

            <div id="schedulePreviewContainer"></div>
        </div>
    </div>
</section>

<!-- 7. GET IN TOUCH + READY TO TAKE THE NEXT STEP + MAP -->
<section id="contact" class="fid-section">
    <div class="fid-container">
        <div class="fid-contact-grid">
            <div class="fid-contact-box reveal-on-scroll">
                <div>
                    <span class="fid-eyebrow" style="margin-bottom:6px;"><?= Security::e($t['contact_eyebrow']) ?></span>
                    <ul class="fid-contact-list">
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('phone', 12) ?></span>
                            <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></span>
                        </li>
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('send', 12) ?></span>
                            <span><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?></span>
                        </li>
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('building', 12) ?></span>
                            <span><?= Security::e($settings['institution_address'] ?? 'Mogadishu, Somalia') ?></span>
                        </li>
                    </ul>
                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <a href="#contact" class="fid-contact-dot" aria-label="Website"><?= Icons::svg('globe', 12) ?></a>
                        <a href="#contact" class="fid-contact-dot" aria-label="Support"><?= Icons::svg('message-square', 12) ?></a>
                        <a href="#contact" class="fid-contact-dot" aria-label="Security"><?= Icons::svg('shield', 12) ?></a>
                        <a href="?route=login" class="fid-contact-dot" aria-label="Portal"><?= Icons::svg('lock', 12) ?></a>
                    </div>
                </div>

                <div style="text-align:center;border-left:1px solid rgba(0,0,0,0.08);padding-left:20px;">
                    <h3 class="fid-serif-h2" style="font-size:1.35rem;margin-bottom:8px;"><?= Security::e($t['contact_title']) ?></h3>
                    <p class="fid-contact-lead" style="font-size:0.82rem;margin-bottom:14px;">
                        <?= Security::e($t['contact_lead']) ?>
                    </p>
                    <a href="?route=<?= $isLoggedIn ? 'dashboard' : 'login' ?>" class="fid-btn-green">
                        <span><?= Security::e($t['contact_btn_start']) ?></span>
                        <?= Icons::svg('arrow-right', 14) ?>
                    </a>
                </div>
            </div>

            <div class="fid-map-card reveal-on-scroll" style="position:relative;">
                <iframe
                    src="https://maps.google.com/maps?q=Anas%20Abdiwahid%2C%20Mogadishu%2C%20Somalia&z=15&output=embed"
                    title="SIMDA Office Location"
                    style="border:0;width:100%;height:100%;min-height:220px;display:block;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
                <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener"
                   style="position:absolute;right:10px;bottom:10px;background:#123B32;color:#FFF;font-size:0.74rem;font-weight:700;padding:6px 12px;border-radius:999px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 6px 14px rgba(0,0,0,0.2);">
                    Open in Google Maps <?= Icons::svg('arrow-right', 12) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 8. SCENIC SKYLINE & MONEY ILLUSTRATION FOOTER (FOOTWAY REFERENCE DESIGN) -->
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
                        <li><a href="#calculator"><?= Security::e($t['footer_schedule_engine']) ?></a></li>
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

            <!-- Bottom Row: Trust Seals (Left) + Group Info & Flags (Center) + Payment Logos (Right) -->
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
                    <div><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?>, <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></span></div>
                    <div style="display:flex;justify-content:center;align-items:center;gap:12px;margin:12px 0;">
                        <a href="?route=home&lang=so" title="Af-Soomaali (Somalia)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'so' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'so' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-so.jpg" alt="Somalia" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=home&lang=en" title="English (USA)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'en' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'en' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="assets/images/flags/flag-us.jpg" alt="USA" style="width:100%;height:100%;object-fit:cover;display:block;">
                        </a>
                        <a href="?route=home&lang=ar" title="العربية (Arabic)" style="width:34px;height:34px;border-radius:50%;overflow:hidden;display:inline-flex;border:2px solid <?= $locale === 'ar' ? '#D6B66C' : 'rgba(255,255,255,0.28)' ?>;box-shadow:<?= $locale === 'ar' ? '0 0 0 3px rgba(214,182,108,0.35)' : '0 2px 6px rgba(0,0,0,0.25)' ?>;transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
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
document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('#homeNavLinks a');
    const hashLinks = document.querySelectorAll('#homeNavLinks a[href^="#"]');
    const sections = [];

    hashLinks.forEach(function (link) {
        const hash = link.getAttribute('href');
        const section = document.querySelector(hash);
        if (section) {
            sections.push({ id: hash, el: section, link: link });
        }
    });

    function setActiveLink(link) {
        navLinks.forEach(function (l) { l.classList.remove('active'); });
        if (link) {
            link.classList.add('active');
        }
    }

    let isClickScrolling = false;

    // 1. Click Handler with Smooth Offset Scroll
    hashLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                setActiveLink(this);
                isClickScrolling = true;
                const header = document.querySelector('.fid-header');
                const headerH = header ? header.offsetHeight : 80;
                const top = targetEl.getBoundingClientRect().top + window.pageYOffset - headerH - 10;
                window.scrollTo({ top: top, behavior: 'smooth' });
                if (history.pushState) {
                    history.pushState(null, null, targetId);
                }
                setTimeout(function () {
                    isClickScrolling = false;
                }, 850);
            }
        });
    });

    // 2. ScrollSpy for on-page sections
    function updateScrollSpy() {
        if (isClickScrolling) return;

        const header = document.querySelector('.fid-header');
        const headerH = header ? header.offsetHeight : 80;
        const triggerPoint = window.pageYOffset + headerH + 60;
        let activeSection = null;

        for (let i = 0; i < sections.length; i++) {
            const secTop = sections[i].el.getBoundingClientRect().top + window.pageYOffset;
            if (secTop <= triggerPoint) {
                activeSection = sections[i];
            }
        }

        if (!activeSection && window.pageYOffset < 200 && sections.length > 0) {
            activeSection = sections[0];
        }

        if (activeSection) {
            setActiveLink(activeSection.link);
        }
    }

    let isScrolling;
    window.addEventListener('scroll', function () {
        window.clearTimeout(isScrolling);
        isScrolling = setTimeout(updateScrollSpy, 30);
    }, { passive: true });

    // 3. Initial check on load (check hash)
    if (window.location.hash) {
        const matching = Array.from(hashLinks).find(function (l) {
            return l.getAttribute('href') === window.location.hash;
        });
        if (matching) {
            setActiveLink(matching);
            setTimeout(function () {
                const target = document.querySelector(window.location.hash);
                if (target) {
                    const header = document.querySelector('.fid-header');
                    const headerH = header ? header.offsetHeight : 80;
                    const top = target.getBoundingClientRect().top + window.pageYOffset - headerH - 10;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
            }, 100);
        }
    } else {
        updateScrollSpy();
    }
});
</script>
</body>
</html>
