<?php
/**
 * Long-form details for the four home-page service cards, shown in a dialog
 * when a card is tapped. Keyed by locale, then service number (1–4).
 */

return [
    'en' => [
        'labels' => [
            'who' => 'Who it is for',
            'how' => 'How it works',
            'benefits' => 'Key benefits',
            'needs' => 'What you need',
            'apply' => 'Apply now',
            'calc' => 'Calculate installments',
            'close' => 'Close',
            'more' => 'View details',
        ],
        1 => [
            'lead' => 'Own a plot but cannot afford to build on it? SIMDA finances the construction of your home and you pay it back in fixed installments — with no interest (riba) at all.',
            'who' => 'Families and individuals who own a plot of land with clear ownership papers and want to build a home on it.',
            'how' => [
                'Apply at a SIMDA branch and share your plot details and house plan.',
                'Our team visits the site and agrees the full building cost with you.',
                'SIMDA pays for the construction work as the house is built.',
                'You repay the agreed amount in fixed monthly or weekly installments.',
            ],
            'benefits' => [
                '0% riba — the agreed amount never grows.',
                'You see the full repayment schedule before you sign.',
                'Installments are fixed, so you can plan your budget.',
                'Any late charges go to the community charity fund, not to SIMDA.',
            ],
            'needs' => ['National ID', 'Plot ownership document', 'A guarantor'],
        ],
        2 => [
            'lead' => 'Murabaha is an Islamic sale: SIMDA buys the item you need — a car, motorbike, Bajaj or smartphone — and sells it to you at a fixed price that you pay in installments.',
            'who' => 'Workers, drivers, traders and families who need a vehicle or device now but prefer to pay for it over time.',
            'how' => [
                'Choose an item from our Murabaha catalog or tell us what you need.',
                'SIMDA buys the item and owns it first.',
                'SIMDA sells it to you at a price (cost + agreed profit) you accept upfront.',
                'You take the item home and pay in fixed monthly installments.',
            ],
            'benefits' => [
                'The price is fixed at signing and never increases.',
                'A real asset sale — fully Sharia-compliant.',
                'Cars, motorbikes, Bajaj tuk-tuks and smartphones.',
                'See your monthly payment in the calculator before applying.',
            ],
            'needs' => ['National ID', 'Proof of income or business', 'A guarantor'],
        ],
        3 => [
            'lead' => 'Working capital for neighbourhood grocery shops, kiosks and street traders, so you can buy more stock and grow your daily income.',
            'who' => 'Owners of small shops, kiosks (dukaan) and street businesses — including mothers running roadside stalls.',
            'how' => [
                'Tell us about your shop and the stock you want to buy.',
                'A loan officer visits your shop to understand the business.',
                'The request is approved by the officer and the branch manager.',
                'You receive the capital and repay in small weekly or monthly installments.',
            ],
            'benefits' => [
                'Weekly or monthly schedules that match your sales.',
                'Fast decisions through our officer-and-manager workflow.',
                'Solidarity groups can apply together.',
                '0% riba — no interest on your capital.',
            ],
            'needs' => ['National ID', 'A running shop or kiosk', 'A guarantor or solidarity group'],
        ],
        4 => [
            'lead' => 'Qard Hassan — a good loan. You borrow an amount and repay exactly that amount, nothing more. No interest and no hidden fees.',
            'who' => 'Individuals and small traders who need a short-term cash amount for urgent needs or a small business opportunity.',
            'how' => [
                'Apply at a branch with the amount you need.',
                'Your request is reviewed quickly.',
                'You receive the cash.',
                'You repay only the principal on a clear, agreed schedule.',
            ],
            'benefits' => [
                'Repay only what you borrowed.',
                'No hidden fees — everything is shown upfront.',
                'If a payment day falls on a Friday, it moves to Saturday automatically.',
                'SMS reminders before each installment.',
            ],
            'needs' => ['National ID', 'Phone number for reminders', 'A guarantor'],
        ],
    ],

    'so' => [
        'labels' => [
            'who' => 'Yaa loogu talagalay',
            'how' => 'Sida uu u shaqeeyo',
            'benefits' => 'Faa\'iidooyinka',
            'needs' => 'Waxa aad u baahan tahay',
            'apply' => 'Codso hadda',
            'calc' => 'Xisaabi qaybaha',
            'close' => 'Xir',
            'more' => 'Eeg faahfaahinta',
        ],
        1 => [
            'lead' => 'Ma leedahay boos laakiin ma awoodid inaad dhisato? SIMDA ayaa maalgelinaysa dhismaha gurigaaga, adiguna waxaad ku celinaysaa qaybo go\'an — iyadoo aan wax Ribo ah lagu darin.',
            'who' => 'Qoysaska iyo shakhsiyaadka leh boos dhul ah oo warqadaha lahaanshihiisu cad yihiin, kuna doonaya inay guri ka dhistaan.',
            'how' => [
                'Ka codso laanta SIMDA, keenna xogta boosaskaaga iyo naqshadda guriga.',
                'Kooxdayadu waxay booqanayaan goobta, waxaana kula heshiinaynaa qiimaha dhismaha oo dhan.',
                'SIMDA ayaa bixinaysa kharashka dhismaha inta guriga la dhisayo.',
                'Lacagta lagu heshiiyay waxaad ku celinaysaa qaybo go\'an oo bille ah ama toddobaadle ah.',
            ],
            'benefits' => [
                '0% Ribo — lacagta lagu heshiiyay waligeed ma kordhayso.',
                'Jadwalka bixinta oo dhan ayaad arkaysaa kahor intaadan saxiixin.',
                'Qaybaha waa go\'an, si aad miisaaniyaddaada u qorsheysato.',
                'Ganaax kasta oo dib-u-dhac ah wuxuu galayaa sanduuqa sadaqada bulshada, ma aha SIMDA.',
            ],
            'needs' => ['Kaarka aqoonsiga', 'Warqadda lahaanshaha boosaska', 'Damiin'],
        ],
        2 => [
            'lead' => 'Muraabaxadu waa iib Islaami ah: SIMDA ayaa iibsanaysa alaabta aad u baahan tahay — baabuur, mooto, Bajaj ama telefoon — kadibna adiga ayay kaaga iibinaysaa qiime go\'an oo aad qaybo ku bixinayso.',
            'who' => 'Shaqaalaha, darawallada, ganacsatada iyo qoysaska hadda u baahan gaari ama qalab, laakiin doorbidaya inay waqti ku bixiyaan.',
            'how' => [
                'Ka dooro alaab liiska Muraabaxada ama noo sheeg waxa aad u baahan tahay.',
                'SIMDA ayaa iibsanaysa alaabta, marka hore iyada ayaana leh.',
                'SIMDA ayaa kaaga iibinaysa qiime (kharash + faa\'iido lagu heshiiyay) aad hore u aqbashay.',
                'Alaabta ayaad qaadanaysaa, waxaadna ku bixinaysaa qaybo bille ah oo go\'an.',
            ],
            'benefits' => [
                'Qiimaha waa la go\'aamiyaa marka la saxiixo, waligiisna ma kordho.',
                'Iib dhab ah oo alaab ah — si buuxda u waafaqsan Shareecada.',
                'Baabuur, mooto, Bajaj iyo telefoonno casri ah.',
                'Lacagta bishii ku bixinaysid ka eeg xisaabiyaha kahor intaadan codsan.',
            ],
            'needs' => ['Kaarka aqoonsiga', 'Caddeyn dakhli ama ganacsi', 'Damiin'],
        ],
        3 => [
            'lead' => 'Raasamaal shaqo oo loogu talagalay dukaamada xaafadaha, kiiyoosakada iyo ganacsatada waddooyinka, si aad badeecad badan u iibsato dakhligaaga maalinlahana u kordhiso.',
            'who' => 'Milkiilayaasha dukaamada yaryar, kiiyoosakada iyo meheradaha waddooyinka — oo ay ku jiraan hooyooyinka ganacsiga waddada ka wada.',
            'how' => [
                'Noo sheeg dukaankaaga iyo badeecadda aad rabto inaad iibsato.',
                'Sarkaal dayn ayaa booqanaya dukaankaaga si uu ganacsiga u fahmo.',
                'Codsiga waxaa ansixinaya sarkaalka iyo maamulaha laanta.',
                'Raasamaalka ayaad helaysaa, waxaadna ku celinaysaa qaybo yaryar oo toddobaadle ah ama bille ah.',
            ],
            'benefits' => [
                'Jadwal toddobaadle ah ama bille ah oo la jaanqaadaya iibkaaga.',
                'Go\'aan degdeg ah oo ka dhaca sarkaalka iyo maamulaha.',
                'Kooxaha isku-tiirsanaanta ayaa wada codsan kara.',
                '0% Ribo — raasamaalkaaga wax dulsaar ah laguma darayo.',
            ],
            'needs' => ['Kaarka aqoonsiga', 'Dukaan ama kiiyoosk shaqaynaya', 'Damiin ama koox isku-tiirsan'],
        ],
        4 => [
            'lead' => 'Qardul Xasan — dayn wanaagsan. Lacag ayaad amaahanaysaa, isla lacagtaas uun ayaad celinaysaa, waxba kama badna. Dulsaar ma jiro, kharash qarsoonna ma jiro.',
            'who' => 'Shakhsiyaadka iyo ganacsatada yaryar ee u baahan lacag muddo gaaban ah oo baahi degdeg ah ama fursad ganacsi yar.',
            'how' => [
                'Ka codso laanta adigoo sheegaya lacagta aad u baahan tahay.',
                'Codsigaaga si degdeg ah ayaa loo eegayaa.',
                'Lacagta ayaad helaysaa.',
                'Waxaad celinaysaa asalka lacagta oo kaliya, jadwal cad oo lagu heshiiyay.',
            ],
            'benefits' => [
                'Waxaad celinaysaa oo kaliya waxa aad amaahatay.',
                'Kharash qarsoon ma jiro — wax walba hore ayaa laguu tusayaa.',
                'Haddii maalinta bixintu ku beegan tahay Jimce, si toos ah ayay Sabti ugu wareegaysaa.',
                'Fariin SMS ah ayaa lagu xasuusinayaa qayb kasta kahor.',
            ],
            'needs' => ['Kaarka aqoonsiga', 'Lambar telefoon oo xasuusin loogu diro', 'Damiin'],
        ],
    ],

    'ar' => [
        'labels' => [
            'who' => 'لمن هذه الخدمة',
            'how' => 'كيف تعمل',
            'benefits' => 'أهم المزايا',
            'needs' => 'ما تحتاجه',
            'apply' => 'قدّم الآن',
            'calc' => 'احسب الأقساط',
            'close' => 'إغلاق',
            'more' => 'عرض التفاصيل',
        ],
        1 => [
            'lead' => 'تملك أرضاً ولا تستطيع البناء عليها؟ تموّل سيمدا بناء منزلك وتسدد المبلغ على أقساط ثابتة — دون أي ربا.',
            'who' => 'الأسر والأفراد الذين يملكون أرضاً بوثائق ملكية واضحة ويرغبون في بناء منزل عليها.',
            'how' => [
                'قدّم طلبك في أحد فروع سيمدا مع بيانات الأرض ومخطط المنزل.',
                'يزور فريقنا الموقع ونتفق معك على كامل تكلفة البناء.',
                'تدفع سيمدا تكاليف أعمال البناء أثناء تشييد المنزل.',
                'تسدد المبلغ المتفق عليه على أقساط شهرية أو أسبوعية ثابتة.',
            ],
            'benefits' => [
                '0% ربا — المبلغ المتفق عليه لا يزيد أبداً.',
                'تطّلع على جدول السداد كاملاً قبل التوقيع.',
                'الأقساط ثابتة لتخطط ميزانيتك بسهولة.',
                'أي غرامات تأخير تذهب إلى صندوق الخير المجتمعي وليس إلى سيمدا.',
            ],
            'needs' => ['الهوية الوطنية', 'وثيقة ملكية الأرض', 'كفيل'],
        ],
        2 => [
            'lead' => 'المرابحة بيع إسلامي: تشتري سيمدا السلعة التي تحتاجها — سيارة أو دراجة نارية أو بجاج أو هاتف ذكي — ثم تبيعها لك بسعر ثابت تسدده على أقساط.',
            'who' => 'العمال والسائقون والتجار والأسر الذين يحتاجون مركبة أو جهازاً الآن ويفضّلون الدفع على فترة.',
            'how' => [
                'اختر سلعة من كتالوج المرابحة أو أخبرنا بما تحتاجه.',
                'تشتري سيمدا السلعة وتتملكها أولاً.',
                'تبيعها لك سيمدا بسعر (التكلفة + ربح متفق عليه) توافق عليه مسبقاً.',
                'تستلم السلعة وتدفع على أقساط شهرية ثابتة.',
            ],
            'benefits' => [
                'السعر ثابت عند التوقيع ولا يزيد أبداً.',
                'بيع حقيقي لأصل — متوافق تماماً مع الشريعة.',
                'سيارات ودراجات نارية وبجاج وهواتف ذكية.',
                'اطّلع على قسطك الشهري في الحاسبة قبل التقديم.',
            ],
            'needs' => ['الهوية الوطنية', 'إثبات دخل أو نشاط تجاري', 'كفيل'],
        ],
        3 => [
            'lead' => 'رأس مال عامل لمحلات البقالة والأكشاك وتجار الطرقات، لتشتري بضاعة أكثر وتنمّي دخلك اليومي.',
            'who' => 'أصحاب المحلات الصغيرة والأكشاك والأعمال على الطرقات — ومنهم الأمهات اللواتي يدرن بسطات على جانب الطريق.',
            'how' => [
                'أخبرنا عن محلك والبضاعة التي تريد شراءها.',
                'يزور موظف التمويل محلك لفهم نشاطك.',
                'يعتمد الطلب كلٌّ من الموظف ومدير الفرع.',
                'تستلم رأس المال وتسدده على أقساط صغيرة أسبوعية أو شهرية.',
            ],
            'benefits' => [
                'جداول أسبوعية أو شهرية تناسب مبيعاتك.',
                'قرار سريع عبر الموظف والمدير.',
                'يمكن لمجموعات التضامن التقديم معاً.',
                '0% ربا — لا فوائد على رأس مالك.',
            ],
            'needs' => ['الهوية الوطنية', 'محل أو كشك قائم', 'كفيل أو مجموعة تضامن'],
        ],
        4 => [
            'lead' => 'القرض الحسن: تقترض مبلغاً وتعيد المبلغ نفسه فقط، لا أكثر. بلا فوائد وبلا رسوم خفية.',
            'who' => 'الأفراد وصغار التجار الذين يحتاجون مبلغاً نقدياً قصير الأجل لحاجة عاجلة أو فرصة تجارية صغيرة.',
            'how' => [
                'قدّم طلبك في الفرع مع المبلغ الذي تحتاجه.',
                'يُراجَع طلبك بسرعة.',
                'تستلم المبلغ نقداً.',
                'تسدد أصل المبلغ فقط وفق جدول واضح متفق عليه.',
            ],
            'benefits' => [
                'تسدد ما اقترضته فقط.',
                'لا رسوم خفية — كل شيء واضح مسبقاً.',
                'إذا وافق موعد القسط يوم الجمعة يُنقل تلقائياً إلى السبت.',
                'رسائل SMS للتذكير قبل كل قسط.',
            ],
            'needs' => ['الهوية الوطنية', 'رقم هاتف للتذكير', 'كفيل'],
        ],
    ],
];
