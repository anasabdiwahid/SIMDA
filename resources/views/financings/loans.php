<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$filter = $filter ?? 'active';
$s = $summary ?? [];
?>

<div class="card animate-in" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge badge-gold" style="font-weight:700;">0% Riba • Qard Hassan</span>
                <span class="badge badge-info" style="font-weight:700;"><?= (int) ($s['count_all'] ?? 0) ?> <?= $isSo ? 'Heshiis Guud' : 'Total Loans' ?></span>
            </div>
            <h2 style="font-size:1.3rem;font-weight:800;margin:6px 0 2px 0;color:var(--text-main);">
                <?= $isSo ? 'Maamulka Daymaha (Zero-Riba Cash Loans)' : 'Cash Debt & Loans Management' ?>
            </h2>
            <p style="font-size:0.82rem;color:var(--text-muted);margin:0;">
                <?= $isSo ? 'Kormeerka macaamiisha qaatay deynta tooska ah, kuwa si buuxda ugu bixiyay xilligii loo qabtay, iyo kuwa dib uga dhacay.' : 'Track borrowers who received cash loans, paid strictly on time, or delayed their scheduled repayments.' ?>
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            <a href="?route=applications" class="btn btn-sm btn-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('plus', 14) ?>
                <span><?= $isSo ? 'Codsi Deyn Cusub' : 'New Loan Application' ?></span>
            </a>
            <a href="?route=repayment_record" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('clock', 14) ?>
                <span><?= $isSo ? 'Qabo Bixin' : 'Record Repayment' ?></span>
            </a>
        </div>
    </div>

    <!-- SUMMARY MINI-KPIS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:20px;">
        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid var(--simda-green);">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Wadarta Deynta La Bixiyay' : 'Total Loans Disbursed' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:var(--text-main);margin-top:2px;">
                <?= Security::formatMoney($s['sum_principal'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_all'] ?? 0) ?> <?= $isSo ? 'qandaraas' : 'contracts' ?></div>
        </div>

        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid #1FAF62;">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Inta La Soo Celiyay' : 'Total Repaid' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:#1FAF62;margin-top:2px;">
                <?= Security::formatMoney($s['sum_paid'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_settled_ontime'] ?? 0) + (int) ($s['count_settled_late'] ?? 0) ?> <?= $isSo ? 'la wada xalliyay' : 'fully settled' ?></div>
        </div>

        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid var(--simda-gold);">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Inta Maqan (Socota)' : 'Outstanding Balance' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:var(--simda-gold);margin-top:2px;">
                <?= Security::formatMoney($s['sum_outstanding'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_active'] ?? 0) ?> <?= $isSo ? 'deyn firfircoon' : 'active borrowers' ?></div>
        </div>
    </div>

    <!-- FILTER TABS (Requested Sections) -->
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;border-bottom:1px solid var(--border-color);padding-bottom:12px;margin-bottom:18px;">
        <!-- TAB 1: DADKA DEYNTA QAATAY (ACTIVE) -->
        <a href="?route=loans&filter=active" class="btn btn-sm <?= $filter === 'active' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('activity', 14) ?>
            <span><?= $isSo ? 'Dadka Deynta Qaatay (Firfircoon)' : 'Active Borrowers' ?></span>
            <span class="badge <?= $filter === 'active' ? 'badge-dark' : 'badge-gold' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_active'] ?? 0) ?>
            </span>
        </a>

        <!-- TAB 2: DEYNTA ISKA BIXIYAY (WAQTIGII SAXDA AHAA) -->
        <a href="?route=loans&filter=settled_ontime" class="btn btn-sm <?= $filter === 'settled_ontime' ? 'btn-success' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('check-circle', 14) ?>
            <span><?= $isSo ? 'Deynta Iska Bixiyay (Waqtigii Saxda Ahaa)' : 'Settled On-Time' ?></span>
            <span class="badge <?= $filter === 'settled_ontime' ? 'badge-dark' : 'badge-success' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_settled_ontime'] ?? 0) ?>
            </span>
        </a>

        <!-- TAB 3: DEYNTA ISKA BIXIYAY (DIB UGA DHACAY) -->
        <a href="?route=loans&filter=settled_late" class="btn btn-sm <?= $filter === 'settled_late' ? 'btn-danger' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('clock', 14) ?>
            <span><?= $isSo ? 'Deynta Iska Bixiyay (Dib u Dhacay)' : 'Settled with Delay' ?></span>
            <span class="badge <?= $filter === 'settled_late' ? 'badge-dark' : 'badge-danger' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_settled_late'] ?? 0) ?>
            </span>
        </a>

        <!-- TAB 4: DHAMMAAN -->
        <a href="?route=loans&filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-secondary' : 'btn-outline' ?>" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
            <span><?= $isSo ? 'Dhammaan Daymaha' : 'All Loans' ?></span>
            <span class="badge badge-info" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_all'] ?? 0) ?>
            </span>
        </a>
    </div>

    <!-- TABLE OF LOANS -->
    <?php if (empty($loans)): ?>
        <div style="padding:40px 20px;text-align:center;color:var(--text-muted);">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(214,182,108,0.12);color:var(--simda-gold);display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <?= Icons::svg('file-text', 24) ?>
            </div>
            <div style="font-weight:700;font-size:1rem;color:var(--text-main);margin-bottom:4px;">
                <?= $isSo ? 'Ma jiraan heshiisyo deyn ah qaybtan' : 'No loan contracts found in this view' ?>
            </div>
            <div style="font-size:0.82rem;max-width:380px;margin:0 auto 16px auto;">
                <?= $isSo ? 'Marka macaamiishu qaataan deynta Qard Hassan oo lacagta la bixiyo ama la soo celiyo, halkan ayay ku kala shaandhoobayaan si toos ah.' : 'Borrowers and their repayment timeliness will automatically appear here once recorded.' ?>
            </div>
            <a href="?route=applications" class="btn btn-sm btn-gold">
                <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Gali Codsi Cusub' : 'Create Application' ?></span>
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="simda-table">
                <thead>
                    <tr>
                        <th><?= $isSo ? 'Heshiiska #' : 'Contract #' ?></th>
                        <th><?= $isSo ? 'Macmiilka' : 'Client' ?></th>
                        <th><?= $isSo ? 'Laanta' : 'Branch' ?></th>
                        <th><?= $isSo ? 'Deynta (Principal)' : 'Loan Principal' ?></th>
                        <th><?= $isSo ? 'Wadarta Guud' : 'Total Due' ?></th>
                        <th><?= $isSo ? 'La Bixiyay' : 'Total Repaid' ?></th>
                        <th><?= $isSo ? 'Dhiman (Maqan)' : 'Outstanding' ?></th>
                        <th><?= $isSo ? 'Xaaladda Waqtiga' : 'Timeliness Status' ?></th>
                        <th><?= $isSo ? 'Taariikhda Dhicitaanka' : 'Maturity Date' ?></th>
                        <th><?= $isSo ? 'Warbixin' : 'Actions' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $ln):
                        $isSettled = $ln['status'] === 'completed';
                        $hasDelay = ($ln['closed_at'] && $ln['closed_at'] > $ln['maturity_date']) || ((int) $ln['charity_charges_count'] > 0) || ((int) $ln['late_installments_count'] > 0);
                    ?>
                        <tr>
                            <td class="num-tabular">
                                <a href="?route=financing_view&id=<?= (int) $ln['id'] ?>" style="font-weight:700;color:var(--simda-green);text-decoration:underline;">
                                    <?= Security::e($ln['financing_number']) ?>
                                </a>
                            </td>
                            <td>
                                <strong><?= Security::e($ln['client_name']) ?></strong>
                                <?php if (!empty($ln['client_phone'])): ?>
                                    <div style="font-size:0.74rem;color:var(--text-muted);" class="num-tabular"><?= Security::e($ln['client_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= Security::e($ln['branch_name']) ?></td>
                            <td class="num-tabular" style="font-weight:700;">
                                <?= Security::formatMoney($ln['approved_amount']) ?>
                            </td>
                            <td class="num-tabular">
                                <?= Security::formatMoney($ln['total_due']) ?>
                            </td>
                            <td class="num-tabular" style="color:#1FAF62;font-weight:700;">
                                <?= Security::formatMoney($ln['total_paid']) ?>
                            </td>
                            <td class="num-tabular">
                                <?php if ((float) $ln['outstanding_balance'] <= 0): ?>
                                    <span class="badge badge-success" style="font-size:0.7rem;">$0.00 (WADA BIXIYAY)</span>
                                <?php else: ?>
                                    <strong style="color:var(--simda-gold);"><?= Security::formatMoney($ln['outstanding_balance']) ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isSettled && !$hasDelay): ?>
                                    <span class="badge badge-success" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                        <?= Icons::svg('check-circle', 12) ?> <?= $isSo ? 'Bixiyay: Waqtigii Saxda Ahaa' : 'Paid On-Time' ?>
                                    </span>
                                <?php elseif ($isSettled && $hasDelay): ?>
                                    <span class="badge badge-warning" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;background:#E79E38;color:#FFFFFF;">
                                        <?= Icons::svg('alert-triangle', 12) ?> <?= $isSo ? 'Bixiyay: Dib U Dhac Leh' : 'Settled Late' ?>
                                    </span>
                                <?php elseif ($ln['status'] === 'overdue' || strtotime($ln['maturity_date']) < time()): ?>
                                    <span class="badge badge-danger" style="font-weight:700;">
                                        <?= $isSo ? 'Waqtigii Waa Dhacay' : 'Maturity Overdue' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info" style="font-weight:700;">
                                        <?= $isSo ? 'Socota (Firfircoon)' : 'Active / In Progress' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="num-tabular" style="font-size:0.78rem;">
                                <?= Security::e($ln['maturity_date']) ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="?route=financing_view&id=<?= (int) $ln['id'] ?>" class="btn btn-sm btn-outline" title="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>">
                                        <?= Icons::svg('eye', 13) ?>
                                    </a>
                                    <a href="?route=statement_pdf&id=<?= (int) $ln['id'] ?>" class="btn btn-sm btn-gold" title="PDF Statement">
                                        <?= Icons::svg('download', 13) ?> PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

