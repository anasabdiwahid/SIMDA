<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$locale = Lang::locale();
$isSo = ($locale === 'so');
$curUser = $user ?? [];

// Standard Sharia-Compliant Murabaha Asset Catalog
$catalog = [
    [
        'id' => 'bajaaj',
        'name' => '🛺 Mooto Bajaaj (Tuk-Tuk Auto Rickshaw)',
        'category' => $isSo ? 'Gaadiidka Fudud' : 'Vehicles',
        'cost' => 2500.00,
        'markup_pct' => 8.0,
        'selling_price' => 2700.00,
        'desc' => $isSo ? 'Tuk-Tuk 3-taayirle cusub, mashiin 200cc (TVS King / Bajaj RE), shidaal yar.' : 'New 3-wheel auto rickshaw, 200cc engine, fuel efficient.',
    ],
    [
        'id' => 'harqaan',
        'name' => '🧵 Mashiinka Harqaanka / Tolida (Industrial Sewing Machine)',
        'category' => $isSo ? 'Qalabka Farsamada' : 'Industrial Equipment',
        'cost' => 600.00,
        'markup_pct' => 6.0,
        'selling_price' => 636.00,
        'desc' => $isSo ? 'Mashiinka dharka tola ee casriga ah (JACK / JUKI Direct Drive), miis iyo matoor wata.' : 'Industrial sewing machine with auto-trimmer, direct drive motor and table.',
    ],
    [
        'id' => 'solar',
        'name' => '☀️ Qalabka Cadceedda (Solar Energy System)',
        'category' => $isSo ? 'Tamarta Cadceedda' : 'Solar & Energy',
        'cost' => 1200.00,
        'markup_pct' => 7.0,
        'selling_price' => 1284.00,
        'desc' => $isSo ? 'Xidhmo buuxda: 4 Panels (450W), Inverter Hybrid 3KW, iyo Batariga Lithium 200Ah.' : 'Complete solar kit: 4x 450W panels, 3KW Hybrid inverter, 200Ah Lithium battery.',
    ],
    [
        'id' => 'grocery_stock',
        'name' => '🛒 Badeecad Dukaan (Wholesale Grocery/Retail Stock)',
        'category' => $isSo ? 'Badeecadaha Dukaamada' : 'Retail Inventory',
        'cost' => 1500.00,
        'markup_pct' => 5.0,
        'selling_price' => 1575.00,
        'desc' => $isSo ? 'Raashin jumlad ah iyo agabka dukaanka (bariis, sonkor, saliid, caano, baasto iwm).' : 'Wholesale commercial grocery inventory per pro-forma invoice.',
    ],
    [
        'id' => 'laptop_tech',
        'name' => '💻 Laptop & Qalabka Xafiiska (Office IT Equipment)',
        'category' => $isSo ? 'Teknolojiyadda' : 'IT & Tech',
        'cost' => 800.00,
        'markup_pct' => 6.0,
        'selling_price' => 848.00,
        'desc' => $isSo ? 'Laptop Core i7 / 16GB RAM / 512GB SSD, Daabace (Multifunction Printer), iyo UPS.' : 'Core i7 business laptop, multifunction printer, and heavy-duty UPS.',
    ],
    [
        'id' => 'freezer',
        'name' => '🧊 Qaboojiyaha Ganacsiga (Commercial Deep Freezer)',
        'category' => $isSo ? 'Qaboojiyeyaasha' : 'Commercial Appliances',
        'cost' => 950.00,
        'markup_pct' => 6.0,
        'selling_price' => 1007.00,
        'desc' => $isSo ? 'Qaboojiye weyn oo ganacsi (500 Liters, Inverter Energy Saver), hilibka iyo cabitaanka.' : '500L energy-saver commercial deep freezer for food preservation.',
    ],
    [
        'id' => 'hardware_tools',
        'name' => '🧱 Qalabka Dhismaha & Farsamada (Hardware Tools)',
        'category' => $isSo ? 'Dhismaha & Farsamada' : 'Construction Tools',
        'cost' => 2000.00,
        'markup_pct' => 7.0,
        'selling_price' => 2140.00,
        'desc' => $isSo ? 'Qalab isugu jira boolal, mashiinnada goynta biraha, alxanka, iyo qalabka dhismaha.' : 'Heavy industrial welding tools, cutting machines, and hardware equipment.',
    ],
    [
        'id' => 'water_pump',
        'name' => '🌾 Matoorka Biyaha Beeraha (Agricultural Water Pump)',
        'category' => $isSo ? 'Beeraha & Waraabka' : 'Agriculture & Irrigation',
        'cost' => 1100.00,
        'markup_pct' => 6.0,
        'selling_price' => 1166.00,
        'desc' => $isSo ? 'Matoor naafto ah (3-inch Diesel Water Pump) oo wata tubbooyin 50 mitir ah.' : '3-inch high-output diesel agricultural water pump with 50m discharge hoses.',
    ],
    [
        'id' => 'custom',
        'name' => '📦 Qalab Kale / Shey Gaar ah (Custom Murabaha Asset)',
        'category' => $isSo ? 'Shey Gaar ah' : 'Custom Asset',
        'cost' => 1000.00,
        'markup_pct' => 5.0,
        'selling_price' => 1050.00,
        'desc' => '',
    ],
];
?>

<div class="dash-page-header" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <span style="background:rgba(214,182,108,0.14);color:#B38C2A;padding:8px;border-radius:10px;display:inline-flex;">
                    <?= Icons::svg('shopping-bag', 22) ?>
                </span>
                <h1 style="font-size:1.45rem;font-weight:900;margin:0;color:var(--text-main);">
                    <?= $isSo ? 'Heshiis Murabaxo ah (Murabaha Asset Financing)' : 'New Murabaha Asset Financing' ?>
                </h1>
            </div>
            <p style="color:var(--text-muted);font-size:0.88rem;margin:0;">
                <?= $isSo
                    ? 'Heshiis iib xalaal ah (Cost-Plus Sale) — SIMDA ayaa iibinaysa badeecada/qalabka, macmiilkuna wuxuu ku bixinayaa qiimihii asalka ahaa oo lagu daray faa\'iido go\'an oo bilowga lagu heshiiyo.'
                    : '100% Sharia-compliant Cost-Plus asset sale — SIMDA procures the asset and sells it to the client at cost plus a transparent pre-agreed fixed markup.' ?>
            </p>
        </div>
        <div>
            <a href="?route=murabaha" class="btn btn-outline btn-sm" style="font-weight:700;">
                <?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Ku noqo Liiska Murabaxada' : 'Back to Murabaha' ?>
            </a>
        </div>
    </div>
</div>

<form method="POST" action="?route=murabaha_submit" id="murabahaNewForm">
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
    <input type="hidden" name="client_id" id="selectedClientId" value="<?= (int) ($selectedClientId ?? 0) ?>" required>
    <input type="hidden" name="markup_pct" id="markupPctInput" value="8.0">
    <input type="hidden" name="selling_price" id="sellingPriceHidden" value="2700.00">

    <div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start;">
        
        <!-- LEFT COLUMN: CONTROLS -->
        <div style="display:flex;flex-direction:column;gap:22px;">

            <!-- 1. SEARCHABLE CLIENT PICKER -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:rgba(214,182,108,0.2);color:#B38C2A;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">1</span>
                        <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                            <?= $isSo ? 'Dooro Macmiilka Murabaxada Qaadanaya *' : 'Select Buyer Client *' ?>
                        </h3>
                    </div>
                    <span style="font-size:0.78rem;color:var(--text-muted);">
                        <?= count($clients ?? []) ?> <?= $isSo ? 'macmiil ayaa diiwaangashan' : 'registered clients' ?>
                    </span>
                </div>

                <!-- Client Selected State Box -->
                <div id="selectedClientBox" style="display:none;background:rgba(214,182,108,0.08);border:1.5px solid rgba(214,182,108,0.4);padding:16px 18px;border-radius:12px;margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                        <div style="display:flex;gap:14px;align-items:center;">
                            <div style="width:46px;height:46px;border-radius:50%;background:#0C2D24;color:#D6B66C;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;border:2px solid #D6B66C;" id="clientAvatar">
                                C
                            </div>
                            <div>
                                <div style="font-size:1.05rem;font-weight:800;color:var(--text-main);" id="clientNameDisplay"></div>
                                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:4px;font-size:0.82rem;color:var(--text-muted);">
                                    <span>🆔 <strong id="clientCodeDisplay"></strong></span>
                                    <span>📞 <strong id="clientPhoneDisplay"></strong></span>
                                    <span>✉️ <strong id="clientEmailDisplay"></strong></span>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;margin-top:6px;">
                                    <span style="background:#E7F4EE;color:#0F5132;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:700;" id="clientScoreDisplay">
                                        Score: 600
                                    </span>
                                    <span style="font-size:0.76rem;color:var(--text-muted);" id="clientGuarantorDisplay"></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="clearSelectedClient()" style="padding:4px 10px;font-size:0.78rem;font-weight:700;">
                            <?= $isSo ? 'Beddel Macmiilka' : 'Change' ?>
                        </button>
                    </div>
                </div>

                <!-- Client Search Input & Dropdown -->
                <div id="clientSearchWrapper" style="position:relative;">
                    <input type="text"
                           id="clientSearchInput"
                           class="form-control"
                           placeholder="<?= $isSo ? '🔍 Ku qor magaca, telefoonka, email-ka, ama ID-ga macmiilka...' : '🔍 Search client by name, phone, email, or ID...' ?>"
                           autocomplete="off"
                           style="padding-left:14px;font-weight:600;font-size:0.92rem;height:44px;"
                           onfocus="showClientDropdown()"
                           oninput="filterClients(this.value)">

                    <div id="clientDropdownList" style="display:none;position:absolute;top:100%;left:0;right:0;max-height:280px;overflow-y:auto;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:10px;box-shadow:0 12px 28px rgba(0,0,0,0.15);z-index:100;margin-top:6px;">
                        <?php if (empty($clients)): ?>
                            <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:0.86rem;">
                                <?= $isSo ? 'Weli macmiil ma jiro. Fadlan marka hore diiwaangeli macmiil.' : 'No clients found.' ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($clients as $c): ?>
                                <div class="client-option-item"
                                     data-id="<?= (int) $c['id'] ?>"
                                     data-name="<?= Security::e($c['full_name']) ?>"
                                     data-code="<?= Security::e($c['client_code']) ?>"
                                     data-phone="<?= Security::e($c['phone']) ?>"
                                     data-email="<?= Security::e($c['email'] ?? '') ?>"
                                     data-idnum="<?= Security::e($c['id_number'] ?? '') ?>"
                                     data-score="<?= (int) ($c['current_score'] ?? 600) ?>"
                                     data-branch="<?= Security::e($c['branch_name'] ?? '') ?>"
                                     data-guarantor="<?= Security::e($c['guarantor_name'] ?? '') ?>"
                                     onclick="selectClientFromItem(this)"
                                     style="padding:11px 16px;border-bottom:1px solid var(--border-color);cursor:pointer;display:flex;justify-content:space-between;align-items:center;transition:background 0.15s ease;">
                                    <div>
                                        <div style="font-weight:700;font-size:0.92rem;color:var(--text-main);">
                                            <?= Security::e($c['full_name']) ?>
                                        </div>
                                        <div style="font-size:0.79rem;color:var(--text-muted);margin-top:2px;">
                                            <span style="font-family:monospace;"><?= Security::e($c['client_code']) ?></span>
                                            • 📞 <?= Security::e($c['phone']) ?>
                                            <?php if (!empty($c['email'])): ?>
                                                • ✉️ <?= Security::e($c['email']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <span style="background:#EBF5F0;color:#1B7A53;font-size:0.75rem;padding:2px 8px;border-radius:10px;font-weight:700;">
                                            Score: <?= (int) ($c['current_score'] ?? 600) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. SELECT MURABAHA ITEM / COMMODITY (PRICE SCALES AUTOMATICALLY) -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:rgba(214,182,108,0.2);color:#B38C2A;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">2</span>
                        <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                            <?= $isSo ? 'Dooro Sheyga / Qalabka Murabaxada *' : 'Select Murabaha Commercial Asset / Item *' ?>
                        </h3>
                    </div>
                    <span style="font-size:0.78rem;color:#B38C2A;font-weight:700;background:rgba(214,182,108,0.12);padding:3px 10px;border-radius:12px;">
                        ⚡ Auto-Pricing Active
                    </span>
                </div>

                <!-- Item Selector Dropdown -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Magaca Sheyga Murabaxada (Select Item) *' : 'Select Murabaha Asset Catalog *' ?>
                    </label>
                    <select id="murabahaCatalogSelect" class="form-control" style="font-weight:700;font-size:0.95rem;height:46px;" onchange="onCatalogItemChange()">
                        <?php foreach ($catalog as $item): ?>
                            <option value="<?= Security::e($item['id']) ?>"
                                    data-name="<?= Security::e($item['name']) ?>"
                                    data-cost="<?= (float) $item['cost'] ?>"
                                    data-markup="<?= (float) $item['markup_pct'] ?>"
                                    data-desc="<?= Security::e($item['desc']) ?>">
                                <?= Security::e($item['name']) ?> — Qiimaha Iibka: $<?= number_format($item['selling_price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Custom Item Name Input if needed -->
                <div class="form-group" id="customItemNameGroup" style="display:none;margin-bottom:16px;">
                    <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Geli Magaca Sheyga Cusub *' : 'Enter Custom Item Name *' ?>
                    </label>
                    <input type="text" name="custom_item_name" id="customItemName" class="form-control" placeholder="Tusaale: Mashiinka Alxanka biraha..." style="font-weight:600;">
                </div>

                <!-- Price and Markup Grid (Automatic scaling) -->
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr;gap:14px;background:var(--bg-elevated,#F9FBFA);padding:16px;border-radius:12px;border:1px solid var(--border-color);margin-bottom:16px;">
                    
                    <!-- Cost Price -->
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.8rem;margin-bottom:4px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Qiimaha Asalka ah (Cost) *' : 'Cost Price ($ USD) *' ?>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--text-muted);">$</span>
                            <input type="number"
                                   name="cost_price"
                                   id="murabahaCostInput"
                                   class="form-control"
                                   step="10"
                                   min="100"
                                   max="100000"
                                   value="2500.00"
                                   required
                                   style="padding-left:24px;font-weight:800;font-size:1.05rem;"
                                   oninput="calculateMurabahaPrice()">
                        </div>
                        <div style="font-size:0.73rem;color:var(--text-muted);margin-top:3px;">
                            <?= $isSo ? 'Qiimaha SIMDA soo iibinayso' : 'SIMDA purchase cost' ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.8rem;margin-bottom:4px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Tirada (Quantity) *' : 'Quantity *' ?>
                        </label>
                        <input type="number"
                               name="quantity"
                               id="murabahaQtyInput"
                               class="form-control"
                               min="1"
                               max="100"
                               value="1"
                               required
                               style="font-weight:800;font-size:1.05rem;"
                               oninput="calculateMurabahaPrice()">
                        <div style="font-size:0.73rem;color:var(--text-muted);margin-top:3px;">
                            <?= $isSo ? 'Tirada sheyada la qaadanayo' : 'Number of units' ?>
                        </div>
                    </div>

                    <!-- Markup % -->
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.8rem;margin-bottom:4px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Faa\'iidada (Markup %) *' : 'Profit Markup % *' ?>
                        </label>
                        <div style="position:relative;">
                            <input type="number"
                                   id="displayMarkupPct"
                                   class="form-control"
                                   step="0.5"
                                   min="1"
                                   max="30"
                                   value="8.0"
                                   required
                                   style="padding-right:24px;font-weight:800;font-size:1.05rem;"
                                   oninput="syncMarkupAndCalculate(this.value)">
                            <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--text-muted);">%</span>
                        </div>
                        <div style="font-size:0.73rem;color:var(--text-muted);margin-top:3px;">
                            <?= $isSo ? 'Faa\'iidada go\'an ee heshiiska' : 'Fixed profit percentage' ?>
                        </div>
                    </div>

                </div>

                <!-- Price Increase Display Highlight ("automatic in price-ka u koro") -->
                <div style="background:linear-gradient(135deg,rgba(214,182,108,0.12) 0%,rgba(35,132,91,0.08) 100%);border:1.5px solid rgba(214,182,108,0.4);border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-size:0.78rem;font-weight:800;color:#856214;text-transform:uppercase;letter-spacing:0.5px;">
                            <?= $isSo ? 'Qiimaha Iibka Guud ee Murabaxada (Total Selling Price):' : 'Total Murabaha Selling Price:' ?>
                        </div>
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;" id="priceCalculationFormula">
                            $2,500.00 (Qiimaha) + $200.00 (Faa'iido 8%)
                        </div>
                    </div>
                    <div style="font-size:1.6rem;font-weight:900;color:#0C2D24;font-family:Georgia,serif;" id="calculatedSellingPriceDisplay">
                        $2,700.00
                    </div>
                </div>

            </div>

            <!-- 3. REPAYMENT DURATION (BILAAHA) -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <span style="background:rgba(214,182,108,0.2);color:#B38C2A;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">3</span>
                    <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                        <?= $isSo ? 'Muddada uu ku bixinayo (Repayment Months) *' : 'Repayment Term (Months) *' ?>
                    </h3>
                </div>

                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:18px;">
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Inta Bil oo uu ku bixinayo *' : 'Number of Months *' ?>
                        </label>
                        <select name="term_installments" id="murabahaMonthsSelect" class="form-control" required style="font-weight:700;font-size:0.95rem;height:42px;" onchange="calculateMurabahaPrice()">
                            <option value="3">3 Bilood (3 Months)</option>
                            <option value="6" selected>6 Bilood (6 Months)</option>
                            <option value="9">9 Bilood (9 Months)</option>
                            <option value="12">12 Bilood / 1 Sano (1 Year)</option>
                            <option value="18">18 Bilood (1.5 Years)</option>
                            <option value="24">24 Bilood / 2 Sano (2 Years)</option>
                            <option value="36">36 Bilood / 3 Sano (3 Years)</option>
                            <option value="48">48 Bilood / 4 Sano (4 Years)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Habka Qayb-bixinta' : 'Installment Frequency' ?>
                        </label>
                        <select name="repayment_frequency" class="form-control" style="font-weight:600;font-size:0.92rem;height:42px;">
                            <option value="monthly" selected><?= $isSo ? 'Bille (Monthly Installment)' : 'Monthly' ?></option>
                            <option value="weekly"><?= $isSo ? 'Toddobaadle (Weekly Installment)' : 'Weekly' ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4. DESCRIPTION & GOODS SPECIFICATIONS -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <span style="background:rgba(214,182,108,0.2);color:#B38C2A;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">4</span>
                    <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                        <?= $isSo ? 'Faahfaahinta Sheyga & Sababta (Description) *' : 'Goods Description & Agreement Notes *' ?>
                    </h3>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Faahfaahinta Sheyga / Badeecada (Goods Description) *' : 'Goods & Specifications Description *' ?>
                    </label>
                    <textarea name="murabaha_goods_description"
                              id="murabahaGoodsDescInput"
                              class="form-control"
                              rows="3"
                              required
                              placeholder="<?= $isSo ? 'Tusaale: Nooca mootada, mashiinka lambarkiisa, qalabka cadceedda faahfaahintiisa, iyo heshiiska dukaanka...' : 'e.g. Model, serial number, brand, invoice number, and delivery terms...' ?>"
                              style="font-size:0.9rem;line-height:1.5;"></textarea>
                </div>

                <div class="form-group">
                    <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Ujeeddada Ganacsiga (Purpose) *' : 'Business Purpose *' ?>
                    </label>
                    <input type="text"
                           name="purpose"
                           id="murabahaPurposeInput"
                           class="form-control"
                           required
                           value="<?= $isSo ? 'Maalgelinta qalab ganacsi oo Murabaxo ah' : 'Murabaha commercial asset financing' ?>"
                           style="font-size:0.92rem;font-weight:600;">
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: FINANCIAL TRANSPARENCY CARD -->
        <div style="position:sticky;top:80px;display:flex;flex-direction:column;gap:16px;">
            <div class="card" style="padding:24px;border-radius:18px;background:linear-gradient(145deg,#0C2D24 0%,#184C3E 100%);color:#fff;border:none;box-shadow:0 12px 30px rgba(12,45,36,0.25);">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.12);padding-bottom:14px;margin-bottom:16px;">
                    <div style="font-size:0.88rem;font-weight:700;color:#D6B66C;letter-spacing:1px;text-transform:uppercase;">
                        <?= $isSo ? 'Xisaabinta Murabaxada' : 'Murabaha Contract Breakdown' ?>
                    </div>
                    <span style="background:rgba(214,182,108,0.22);color:#D6B66C;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:800;">
                        COST-PLUS
                    </span>
                </div>

                <div style="margin-bottom:20px;">
                    <div style="font-size:0.82rem;color:rgba(255,255,255,0.72);margin-bottom:4px;">
                        <?= $isSo ? 'Qidmada Billeed ee Macmiilka:' : 'Monthly Installment:' ?>
                    </div>
                    <div style="font-size:2.1rem;font-weight:900;color:#FFFFFF;letter-spacing:0.5px;font-family:Georgia,serif;" id="sideMonthlyInstallment">
                        $450.00
                    </div>
                    <div style="font-size:0.75rem;color:#D6B66C;margin-top:2px;" id="sideTermLabel">
                        <?= $isSo ? 'Bishii hal mar muddo 6 bilood ah' : 'per month for 6 months' ?>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;border-top:1px solid rgba(255,255,255,0.12);padding-top:16px;font-size:0.86rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.75);"><?= $isSo ? 'Qiimaha Asalka ah (Cost):' : 'Original Cost:' ?></span>
                        <strong style="color:#fff;" id="sideCostDisplay">$2,500.00</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.75);"><?= $isSo ? 'Faa\'iidada Xalaasha ah:' : 'Murabaha Profit Markup:' ?></span>
                        <strong style="color:#D6B66C;" id="sideProfitDisplay">+$200.00 (8%)</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px dashed rgba(255,255,255,0.18);">
                        <span style="color:#D6B66C;font-weight:700;"><?= $isSo ? 'Qiimaha Iibka Guud:' : 'Total Selling Price:' ?></span>
                        <strong style="color:#fff;font-size:1.15rem;" id="sideTotalSellingDisplay">$2,700.00</strong>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div style="margin-top:24px;">
                    <button type="submit"
                            id="btnSubmitMurabaha"
                            class="btn btn-primary btn-block"
                            style="width:100%;padding:14px;font-size:1rem;font-weight:800;border-radius:12px;background:#D6B66C;color:#0C2D24;border:none;box-shadow:0 6px 18px rgba(0,0,0,0.2);display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
                        <?= Icons::svg('check-circle', 18) ?>
                        <span><?= $isSo ? 'Abuur Heshiiska Murabaxada' : 'Confirm Murabaha Contract' ?></span>
                    </button>
                </div>

                <div style="margin-top:14px;font-size:0.75rem;color:rgba(255,255,255,0.65);line-height:1.4;text-align:center;">
                    📜 <?= $isSo ? 'Heshiiskan wuxuu toos ugu dhacayaa qaybta Heshiisyada Murabaxada (All Murabaha Contracts).' : 'Will activate immediately and appear under Murabaha Contracts.' ?>
                </div>
            </div>

            <!-- ISLAMIC MURABAHA NOTICE -->
            <div class="card" style="padding:16px 18px;border-radius:14px;background:#FFF9E6;border:1px solid rgba(214,182,108,0.4);">
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <span style="color:#A77D18;margin-top:2px;"><?= Icons::svg('shield', 18) ?></span>
                    <div style="font-size:0.8rem;color:#7A5E14;line-height:1.5;">
                        <strong><?= $isSo ? 'Heshiiska Murabaxada ee Shareecada:' : 'Sharia Murabaha Standards:' ?></strong><br>
                        <?= $isSo
                            ? 'Faa\'iidada waxaa lagu heshiiyaa bilowga heshiiska, weligeedna ma kordho xitaa haddii dib u dhac yimaado (0% Compounding Interest).'
                            : 'The profit markup is strictly agreed upfront and never compounds or escalates, even if installments are delayed.' ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>

<script>
const isSo = <?= json_encode($isSo) ?>;
const catalogItems = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE) ?>;
const clientOptions = Array.from(document.querySelectorAll('.client-option-item'));
const preselectedId = <?= (int) ($selectedClientId ?? 0) ?>;

document.addEventListener('DOMContentLoaded', function () {
    if (preselectedId > 0) {
        const item = clientOptions.find(el => el.dataset.id == preselectedId);
        if (item) selectClientFromItem(item);
    }

    onCatalogItemChange();
});

function showClientDropdown() {
    const list = document.getElementById('clientDropdownList');
    if (list) list.style.display = 'block';
}

document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('clientSearchWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        const list = document.getElementById('clientDropdownList');
        if (list) list.style.display = 'none';
    }
});

function filterClients(query) {
    showClientDropdown();
    const q = query.toLowerCase().trim();
    const items = document.querySelectorAll('.client-option-item');
    items.forEach(function (item) {
        const name = (item.dataset.name || '').toLowerCase();
        const code = (item.dataset.code || '').toLowerCase();
        const phone = (item.dataset.phone || '').toLowerCase();
        const email = (item.dataset.email || '').toLowerCase();
        const idnum = (item.dataset.idnum || '').toLowerCase();

        const match = (!q) || name.includes(q) || code.includes(q) || phone.includes(q) || email.includes(q) || idnum.includes(q);
        item.style.display = match ? 'flex' : 'none';
    });
}

function selectClientFromItem(item) {
    const id = item.dataset.id;
    const name = item.dataset.name;
    const code = item.dataset.code;
    const phone = item.dataset.phone;
    const email = item.dataset.email;
    const score = item.dataset.score;
    const guarantor = item.dataset.guarantor;

    document.getElementById('selectedClientId').value = id;
    document.getElementById('clientNameDisplay').textContent = name;
    document.getElementById('clientCodeDisplay').textContent = code;
    document.getElementById('clientPhoneDisplay').textContent = phone;
    document.getElementById('clientEmailDisplay').textContent = email || 'No email';
    document.getElementById('clientScoreDisplay').textContent = 'Score: ' + score;
    document.getElementById('clientGuarantorDisplay').textContent = guarantor ? ('Damiin: ' + guarantor) : 'Damiin: Verified';
    document.getElementById('clientAvatar').textContent = name.trim().charAt(0).toUpperCase();

    document.getElementById('clientSearchWrapper').style.display = 'none';
    document.getElementById('selectedClientBox').style.display = 'block';
    document.getElementById('clientDropdownList').style.display = 'none';
}

function clearSelectedClient() {
    document.getElementById('selectedClientId').value = '';
    document.getElementById('selectedClientBox').style.display = 'none';
    document.getElementById('clientSearchWrapper').style.display = 'block';
    document.getElementById('clientSearchInput').value = '';
    document.getElementById('clientSearchInput').focus();
    filterClients('');
}

function onCatalogItemChange() {
    const select = document.getElementById('murabahaCatalogSelect');
    if (!select) return;

    const opt = select.options[select.selectedIndex];
    const itemId = opt.value;
    const cost = parseFloat(opt.dataset.cost || 1000);
    const markup = parseFloat(opt.dataset.markup || 6);
    const desc = opt.dataset.desc || '';
    const name = opt.dataset.name || '';

    // Show/hide custom name input
    const customGroup = document.getElementById('customItemNameGroup');
    if (customGroup) {
        customGroup.style.display = (itemId === 'custom') ? 'block' : 'none';
    }

    document.getElementById('murabahaCostInput').value = cost.toFixed(2);
    document.getElementById('displayMarkupPct').value = markup.toFixed(1);
    document.getElementById('markupPctInput').value = markup.toFixed(1);

    if (desc) {
        document.getElementById('murabahaGoodsDescInput').value = desc;
    }

    calculateMurabahaPrice();
}

function syncMarkupAndCalculate(val) {
    const num = parseFloat(val || 0);
    document.getElementById('markupPctInput').value = num.toFixed(1);
    calculateMurabahaPrice();
}

// Core Automatic Price Calculation ("items-ka automatic in price-ka u koro")
function calculateMurabahaPrice() {
    const unitCost = parseFloat(document.getElementById('murabahaCostInput')?.value || 0);
    const qty = parseInt(document.getElementById('murabahaQtyInput')?.value || 1, 10);
    const markupPct = parseFloat(document.getElementById('markupPctInput')?.value || 0);
    const months = parseInt(document.getElementById('murabahaMonthsSelect')?.value || 6, 10);

    const validCost = (isNaN(unitCost) || unitCost < 0) ? 0 : unitCost;
    const validQty = (isNaN(qty) || qty < 1) ? 1 : qty;
    const validMarkup = (isNaN(markupPct) || markupPct < 0) ? 0 : markupPct;
    const validMonths = (isNaN(months) || months < 1) ? 1 : months;

    const totalBaseCost = validCost * validQty;
    const profitAmount = totalBaseCost * (validMarkup / 100);
    const totalSellingPrice = totalBaseCost + profitAmount;
    const monthlyInstallment = validMonths > 0 ? (totalSellingPrice / validMonths) : totalSellingPrice;

    // Update hidden field for form submission
    document.getElementById('sellingPriceHidden').value = totalSellingPrice.toFixed(2);

    // Update formula display
    const formulaText = '$' + totalBaseCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        + ' (' + (isSo ? 'Qiimaha Asalka ah' : 'Base Cost') + ') + $'
        + profitAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        + ' (' + (isSo ? 'Faa\'iido ' : 'Markup ') + validMarkup + '%)';
    document.getElementById('priceCalculationFormula').textContent = formulaText;

    // Update highlighted selling price
    document.getElementById('calculatedSellingPriceDisplay').textContent = '$' + totalSellingPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Update sidebar card numbers
    document.getElementById('sideCostDisplay').textContent = '$' + totalBaseCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('sideProfitDisplay').textContent = '+$' + profitAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (' + validMarkup + '%)';
    document.getElementById('sideTotalSellingDisplay').textContent = '$' + totalSellingPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('sideMonthlyInstallment').textContent = '$' + monthlyInstallment.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const termLabel = document.getElementById('sideTermLabel');
    if (termLabel) {
        termLabel.textContent = isSo
            ? ('Bishii hal mar muddo ' + validMonths + ' bilood ah')
            : ('per month for ' + validMonths + ' months');
    }
}

// Form validation check before submit
document.getElementById('murabahaNewForm')?.addEventListener('submit', function (e) {
    const clientId = document.getElementById('selectedClientId')?.value;
    if (!clientId || parseInt(clientId, 10) <= 0) {
        e.preventDefault();
        alert('Fadlan marka hore dooro macmiilka heshiiska Murabaxada qaadanaya!');
        showClientDropdown();
        document.getElementById('clientSearchInput')?.focus();
        return false;
    }
});
</script>
