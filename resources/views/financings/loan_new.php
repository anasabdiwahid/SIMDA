<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$locale = Lang::locale();
$isSo = ($locale === 'so');
$curUser = $user ?? [];
?>

<div class="dash-page-header" style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <span style="background:rgba(35,132,91,0.12);color:var(--brand-primary,#1B7A53);padding:8px;border-radius:10px;display:inline-flex;">
                    <?= Icons::svg('credit-card', 22) ?>
                </span>
                <h1 style="font-size:1.45rem;font-weight:900;margin:0;color:var(--text-main);">
                    <?= $isSo ? 'Dayn Oo Lacag Ah (Qard Xasan)' : 'New Cash Loan Financing (Qard Hassan)' ?>
                </h1>
            </div>
            <p style="color:var(--text-muted);font-size:0.88rem;margin:0;">
                <?= $isSo
                    ? 'Maalgelinta lacag cadaan ah oo 100% Ribo La\'aan ah — Macmiilku wuxuu bixinayaa kaliya intii la siiyay ($0.00 faa\'iido / ribo ah).'
                    : '100% Zero-Riba Islamic cash financing — The client repays strictly the principal received with zero extra charges.' ?>
            </p>
        </div>
        <div>
            <a href="?route=loans" class="btn btn-outline btn-sm" style="font-weight:700;">
                <?= Icons::svg('arrow-left', 14) ?> <?= $isSo ? 'Ku noqo Liiska Daymaha' : 'Back to Loans' ?>
            </a>
        </div>
    </div>
</div>

<form method="POST" action="?route=loan_submit" id="loanNewForm">
    <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
    <input type="hidden" name="client_id" id="selectedClientId" value="<?= (int) ($selectedClientId ?? 0) ?>" required>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
        
        <!-- LEFT COLUMN: FORM CONTROLS -->
        <div style="display:flex;flex-direction:column;gap:22px;">

            <!-- 1. SEARCHABLE CLIENT PICKER -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#EBF5F0;color:#1B7A53;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">1</span>
                        <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                            <?= $isSo ? 'Dooro Macmiilka Qaadanaya Daynta *' : 'Select Borrower Client *' ?>
                        </h3>
                    </div>
                    <span style="font-size:0.78rem;color:var(--text-muted);">
                        <?= count($clients ?? []) ?> <?= $isSo ? 'macmiil ayaa diiwaangashan' : 'registered clients' ?>
                    </span>
                </div>

                <!-- Client Selected State Box -->
                <div id="selectedClientBox" style="display:none;background:rgba(27,122,83,0.06);border:1.5px solid rgba(27,122,83,0.35);padding:16px 18px;border-radius:12px;margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                        <div style="display:flex;gap:14px;align-items:center;">
                            <div style="width:46px;height:46px;border-radius:50%;background:#1B7A53;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;" id="clientAvatar">
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
                    <div style="position:relative;">
                        <input type="text"
                               id="clientSearchInput"
                               class="form-control"
                               placeholder="<?= $isSo ? '🔍 Ku qor magaca, telefoonka, email-ka, ama ID-ga macmiilka...' : '🔍 Search client by name, phone, email, or ID...' ?>"
                               autocomplete="off"
                               style="padding-left:14px;font-weight:600;font-size:0.92rem;height:44px;"
                               onfocus="showClientDropdown()"
                               oninput="filterClients(this.value)">
                    </div>

                    <div id="clientDropdownList" style="display:none;position:absolute;top:100%;left:0;right:0;max-height:280px;overflow-y:auto;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:10px;box-shadow:0 12px 28px rgba(0,0,0,0.15);z-index:100;margin-top:6px;">
                        <?php if (empty($clients)): ?>
                            <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:0.86rem;">
                                <?= $isSo ? 'Weli macmiil ma jiro. Fadlan marka hore diiwaangeli macmiil.' : 'No clients found. Please register a client first.' ?>
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

            <!-- 2. LOAN AMOUNT & TERM -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <span style="background:#EBF5F0;color:#1B7A53;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">2</span>
                    <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                        <?= $isSo ? 'Qadarka Daynta & Mudada Bixinta' : 'Loan Amount & Repayment Term' ?>
                    </h3>
                </div>

                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:18px;">
                    <!-- Amount Input -->
                    <div class="form-group" style="grid-column:1 / -1;">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Lacagta uu Rabo Macmiilka ($ USD) *' : 'Requested Cash Amount ($ USD) *' ?>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-weight:800;color:#1B7A53;font-size:1.15rem;">$</span>
                            <input type="number"
                                   name="requested_amount"
                                   id="loanAmountInput"
                                   class="form-control"
                                   min="100"
                                   max="50000"
                                   step="50"
                                   value="1000.00"
                                   required
                                   style="padding-left:32px;font-size:1.2rem;font-weight:800;letter-spacing:0.5px;color:#0C2D24;"
                                   oninput="updateLoanCalculation()">
                        </div>

                        <!-- Quick Amount Chips -->
                        <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
                            <?php foreach ([300, 500, 1000, 2000, 3000, 5000] as $chipAmt): ?>
                                <button type="button"
                                        class="btn btn-outline btn-sm amount-chip-btn"
                                        onclick="setQuickAmount(<?= $chipAmt ?>)"
                                        style="padding:3px 12px;font-size:0.82rem;font-weight:700;border-radius:20px;">
                                    $<?= number_format($chipAmt) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Repayment Months -->
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Muddada uu ku bixinayo (Bilood) *' : 'Repayment Term (Months) *' ?>
                        </label>
                        <select name="term_installments" id="loanMonthsSelect" class="form-control" required style="font-weight:700;font-size:0.95rem;height:42px;" onchange="updateLoanCalculation()">
                            <option value="1">1 Bil (1 Month)</option>
                            <option value="2">2 Bilood (2 Months)</option>
                            <option value="3">3 Bilood (3 Months)</option>
                            <option value="4">4 Bilood (4 Months)</option>
                            <option value="6" selected>6 Bilood (6 Months)</option>
                            <option value="9">9 Bilood (9 Months)</option>
                            <option value="12">12 Bilood / 1 Sano (1 Year)</option>
                            <option value="18">18 Bilood (1.5 Years)</option>
                            <option value="24">24 Bilood / 2 Sano (2 Years)</option>
                            <option value="36">36 Bilood / 3 Sano (3 Years)</option>
                        </select>
                    </div>

                    <!-- Frequency -->
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Habka Qayb-bixinta (Frequency)' : 'Repayment Frequency' ?>
                        </label>
                        <select name="repayment_frequency" class="form-control" style="font-weight:600;font-size:0.92rem;height:42px;" onchange="updateLoanCalculation()">
                            <option value="monthly" selected><?= $isSo ? 'Bille (Monthly Installment)' : 'Monthly' ?></option>
                            <option value="weekly"><?= $isSo ? 'Toddobaadle (Weekly Installment)' : 'Weekly' ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. PURPOSE / DESCRIPTION & MOBILE DISBURSEMENT -->
            <div class="card" style="padding:22px;border-radius:16px;background:var(--bg-surface);border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <span style="background:#EBF5F0;color:#1B7A53;font-weight:800;width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;">3</span>
                    <h3 style="font-size:1.05rem;font-weight:800;margin:0;color:var(--text-main);">
                        <?= $isSo ? 'Sababta Daynta & Bixinta Mobile Money' : 'Purpose & Mobile Money Disbursement' ?>
                    </h3>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                        <?= $isSo ? 'Sababta Daynta loo Qaadanayo (Description / Purpose) *' : 'Reason / Purpose of Loan (Description) *' ?>
                    </label>
                    <textarea name="purpose"
                              id="loanPurposeInput"
                              class="form-control"
                              rows="3"
                              required
                              placeholder="<?= $isSo ? 'Tusaale: Balaarinta ganacsiga dukaanka yar, iibsashada agabka wax soo saarka, ama baahiyo degdeg ah...' : 'e.g. Working capital for retail shop, restocking inventory, or urgent business needs...' ?>"
                              style="font-size:0.9rem;line-height:1.5;"></textarea>
                </div>

                <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:18px;">
                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Adeegga Mobile Money *' : 'Disbursement Provider *' ?>
                        </label>
                        <select name="provider" id="loanProviderSelect" class="form-control" style="font-weight:700;height:42px;">
                            <option value="EVC Plus" selected>EVC Plus (Hormuud)</option>
                            <option value="Zaad">Zaad Service (Telesom)</option>
                            <option value="eDahab">eDahab (Somtel)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:800;font-size:0.86rem;margin-bottom:6px;display:block;color:var(--text-main);">
                            <?= $isSo ? 'Lambarka Lacagta Lagu Dirayo *' : 'Recipient Phone Number *' ?>
                        </label>
                        <input type="text"
                               name="phone"
                               id="loanDisbursePhone"
                               class="form-control"
                               required
                               placeholder="+252 61 0000000"
                               style="font-weight:700;font-family:monospace;font-size:0.92rem;height:42px;">
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: LIVE REPAYMENT SUMMARY CARD -->
        <div style="position:sticky;top:80px;display:flex;flex-direction:column;gap:16px;">
            <div class="card" style="padding:24px;border-radius:18px;background:linear-gradient(145deg,#0C2D24 0%,#164E3F 100%);color:#fff;border:none;box-shadow:0 12px 30px rgba(12,45,36,0.25);">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.12);padding-bottom:14px;margin-bottom:16px;">
                    <div style="font-size:0.88rem;font-weight:700;color:#D6B66C;letter-spacing:1px;text-transform:uppercase;">
                        <?= $isSo ? 'Xisaabinta Daynta (Qard Xasan)' : 'Loan Summary Breakdown' ?>
                    </div>
                    <span style="background:rgba(214,182,108,0.22);color:#D6B66C;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:800;">
                        0% RIBA
                    </span>
                </div>

                <div style="margin-bottom:20px;">
                    <div style="font-size:0.82rem;color:rgba(255,255,255,0.72);margin-bottom:4px;">
                        <?= $isSo ? 'Qidmada Billeed (Monthly Installment):' : 'Monthly Payment:' ?>
                    </div>
                    <div style="font-size:2.1rem;font-weight:900;color:#FFFFFF;letter-spacing:0.5px;font-family:Georgia,serif;" id="summaryMonthly">
                        $166.67
                    </div>
                    <div style="font-size:0.75rem;color:#D6B66C;margin-top:2px;" id="summaryTermLabel">
                        <?= $isSo ? 'Bishii hal mar muddo 6 bilood ah' : 'per month for 6 months' ?>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;border-top:1px solid rgba(255,255,255,0.12);padding-top:16px;font-size:0.86rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.75);"><?= $isSo ? 'Qadarka Daynta:' : 'Principal Amount:' ?></span>
                        <strong style="color:#fff;" id="summaryPrincipal">$1,000.00</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:rgba(255,255,255,0.75);"><?= $isSo ? 'Ribo / Faa\'iido Dheeraad ah:' : 'Interest / Riba:' ?></span>
                        <strong style="color:#72E2A8;">$0.00 (0% Riba)</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px dashed rgba(255,255,255,0.18);">
                        <span style="color:#D6B66C;font-weight:700;"><?= $isSo ? 'Wadarta La Soo Celinayo:' : 'Total to Repay:' ?></span>
                        <strong style="color:#fff;font-size:1.05rem;" id="summaryTotalDue">$1,000.00</strong>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div style="margin-top:24px;">
                    <button type="submit"
                            id="btnSubmitLoan"
                            class="btn btn-primary btn-block"
                            style="width:100%;padding:14px;font-size:1rem;font-weight:800;border-radius:12px;background:#D6B66C;color:#0C2D24;border:none;box-shadow:0 6px 18px rgba(0,0,0,0.2);display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
                        <?= Icons::svg('check-circle', 18) ?>
                        <span><?= $isSo ? 'Bixi Daynta Hadda (Disburse Now)' : 'Disburse Cash Loan Now' ?></span>
                    </button>
                </div>

                <div style="margin-top:14px;font-size:0.75rem;color:rgba(255,255,255,0.65);line-height:1.4;text-align:center;">
                    🔒 <?= $isSo ? 'Dayntan waxay toos uga muuqan doontaa qaybta Dadka Daynta Qaatay (Active Borrowers).' : 'Will activate immediately and appear under Active Borrowers.' ?>
                </div>
            </div>

            <!-- GUARANTOR / SHARIA POLICY NOTICE -->
            <div class="card" style="padding:16px 18px;border-radius:14px;background:#FFF9E6;border:1px solid rgba(214,182,108,0.4);">
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <span style="color:#A77D18;margin-top:2px;"><?= Icons::svg('shield', 18) ?></span>
                    <div style="font-size:0.8rem;color:#7A5E14;line-height:1.5;">
                        <strong><?= $isSo ? 'Shuruudda Shareecada & Damiinka:' : 'Sharia & Guarantor Notice:' ?></strong><br>
                        <?= $isSo
                            ? 'Macmiilku waa inuu leeyahay ugu yaraan hal Damiin-bixiye (Guarantor) xaqiijisan si loo siiyo maalgelin kasta.'
                            : 'Every client must have at least one verified guarantor on file before loan disbursement.' ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isSo = <?= json_encode($isSo) ?>;
    const clientOptions = Array.from(document.querySelectorAll('.client-option-item'));
    const preselectedId = <?= (int) ($selectedClientId ?? 0) ?>;

    if (preselectedId > 0) {
        const item = clientOptions.find(el => el.dataset.id == preselectedId);
        if (item) selectClientFromItem(item);
    }

    updateLoanCalculation();
});

function showClientDropdown() {
    const list = document.getElementById('clientDropdownList');
    if (list) list.style.display = 'block';
}

function hideClientDropdown() {
    setTimeout(() => {
        const list = document.getElementById('clientDropdownList');
        if (list) list.style.display = 'none';
    }, 250);
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

    // Auto-fill disbursement phone
    const disbursePhoneInput = document.getElementById('loanDisbursePhone');
    if (disbursePhoneInput && !disbursePhoneInput.value) {
        disbursePhoneInput.value = phone;
    }

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

function setQuickAmount(amt) {
    const input = document.getElementById('loanAmountInput');
    if (input) {
        input.value = amt.toFixed(2);
        updateLoanCalculation();
    }
}

function updateLoanCalculation() {
    const amt = parseFloat(document.getElementById('loanAmountInput')?.value || 0);
    const months = parseInt(document.getElementById('loanMonthsSelect')?.value || 6, 10);

    const principal = isNaN(amt) || amt < 0 ? 0 : amt;
    const term = isNaN(months) || months < 1 ? 1 : months;

    const totalDue = principal; // 0% Riba
    const monthly = term > 0 ? (totalDue / term) : totalDue;

    document.getElementById('summaryMonthly').textContent = '$' + monthly.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('summaryPrincipal').textContent = '$' + principal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('summaryTotalDue').textContent = '$' + totalDue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const termLabel = document.getElementById('summaryTermLabel');
    if (termLabel) {
        termLabel.textContent = (document.documentElement.lang === 'so' || true)
            ? ('Bishii hal mar muddo ' + term + ' bilood ah')
            : ('per month for ' + term + ' months');
    }
}

// Form validation check before submit
document.getElementById('loanNewForm')?.addEventListener('submit', function (e) {
    const clientId = document.getElementById('selectedClientId')?.value;
    if (!clientId || parseInt(clientId, 10) <= 0) {
        e.preventDefault();
        alert('Fadlan marka hore dooro macmiilka daynta qaadanaya!');
        showClientDropdown();
        document.getElementById('clientSearchInput')?.focus();
        return false;
    }
});
</script>
