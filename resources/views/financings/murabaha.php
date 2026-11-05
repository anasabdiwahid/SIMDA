<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$filter = $filter ?? 'all';
$s = $summary ?? [];
?>

<div class="card animate-in" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge badge-gold" style="font-weight:700;">Heshiiska Murabaxada • Asset Financing</span>
                <span class="badge badge-info" style="font-weight:700;"><?= (int) ($s['count_all'] ?? 0) ?> <?= $isSo ? 'Heshiis' : 'Contracts' ?></span>
            </div>
            <h2 style="font-size:1.3rem;font-weight:800;margin:6px 0 2px 0;color:var(--text-main);">
                <?= $isSo ? 'Maalgelinta Hantida (Murabaxo / Murabaha)' : 'Murabaha Asset & Trade Financing' ?>
            </h2>
            <p style="font-size:0.82rem;color:var(--text-muted);margin:0;">
                <?= $isSo ? 'Heshiisyada iibka hantida ee ku salaysan faa\'iidada cad (Cost-plus-profit): Baabuurta, Mootooyinka Bajaj, Qalabka, iyo Ganacsiyada.' : 'Ethical asset and commodity financing with transparent cost-plus markup structure.' ?>
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            <a href="?route=murabaha_new" class="btn btn-sm btn-gold" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('plus', 14) ?>
                <span><?= $isSo ? 'Diwaangeli Murabaxo Cusub' : 'New Murabaha Application' ?></span>
            </a>
            <a href="?route=repayment_record" class="btn btn-sm btn-outline" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <?= Icons::svg('clock', 14) ?>
                <span><?= $isSo ? 'Qabo Bixin' : 'Record Repayment' ?></span>
            </a>
        </div>
    </div>

    <!-- 4 CORE MURABAHA METRICS CARDS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:20px;">
        <!-- 1. INTA QAADATAY (TOTAL DISBURSED COST) -->
        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid var(--simda-green);">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Inta Qaadatay (Wadarta Qiimaha)' : 'Total Murabaha Cost' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:var(--text-main);margin-top:2px;">
                <?= Security::formatMoney($s['sum_cost'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_all'] ?? 0) ?> <?= $isSo ? 'heshiis guud' : 'total contracts' ?></div>
        </div>

        <!-- 2. FAA'IIDADA MURABAXADA (MARKUP) -->
        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid var(--simda-gold);">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Faa\'iidada Murabaxada (Markup)' : 'Agreed Murabaha Markup' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:var(--simda-gold);margin-top:2px;">
                <?= Security::formatMoney($s['sum_markup'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= $isSo ? 'Faa\'iido go\'an oo xalaal ah' : 'Sharia-compliant profit' ?></div>
        </div>

        <!-- 3. INTA SOO CELISAY (REPAID) -->
        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid #1FAF62;">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Inta Soo Celisay (La Bixiyay)' : 'Repaid (Settled)' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:#1FAF62;margin-top:2px;">
                <?= Security::formatMoney($s['sum_paid'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_settled'] ?? 0) ?> <?= $isSo ? 'gabi ahaanba xalliyay' : 'contracts settled' ?></div>
        </div>

        <!-- 4. INTA WELI SOO CELININ (OUTSTANDING) -->
        <div style="background:var(--bg-elevated);padding:14px 16px;border-radius:10px;border-left:3px solid #C94B4B;">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;"><?= $isSo ? 'Inta Weli Soo Celin Lacagta' : 'Outstanding (Unpaid)' ?></div>
            <div class="num-tabular" style="font-size:1.25rem;font-weight:800;color:#C94B4B;margin-top:2px;">
                <?= Security::formatMoney($s['sum_outstanding'] ?? '0.00') ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-muted);"><?= (int) ($s['count_unpaid'] ?? 0) ?> <?= $isSo ? 'heshiis socda/dhiman' : 'contracts unpaid' ?></div>
        </div>
    </div>

    <!-- FILTER TABS (Requested Breakdown) -->
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;border-bottom:1px solid var(--border-color);padding-bottom:12px;margin-bottom:18px;">
        <a href="?route=murabaha&filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-gold' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('layers', 14) ?>
            <span><?= $isSo ? 'Dhammaan Dadka Murabaxada Qaatay' : 'All Murabaha Clients' ?></span>
            <span class="badge <?= $filter === 'all' ? 'badge-dark' : 'badge-gold' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_all'] ?? 0) ?>
            </span>
        </a>

        <a href="?route=murabaha&filter=settled" class="btn btn-sm <?= $filter === 'settled' ? 'btn-success' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('check-circle', 14) ?>
            <span><?= $isSo ? 'Inta Soo Celisay (Wada Bixisay)' : 'Fully Repaid / Settled' ?></span>
            <span class="badge <?= $filter === 'settled' ? 'badge-dark' : 'badge-success' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_settled'] ?? 0) ?>
            </span>
        </a>

        <a href="?route=murabaha&filter=unpaid" class="btn btn-sm <?= $filter === 'unpaid' ? 'btn-danger' : 'btn-outline' ?>" style="font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <?= Icons::svg('alert-triangle', 14) ?>
            <span><?= $isSo ? 'Inta Weli Soo Celin Lacagta' : 'Unpaid / In Progress' ?></span>
            <span class="badge <?= $filter === 'unpaid' ? 'badge-dark' : 'badge-danger' ?>" style="font-size:0.7rem;padding:2px 6px;">
                <?= (int) ($s['count_unpaid'] ?? 0) ?>
            </span>
        </a>
    </div>

    <!-- TABLE OF MURABAHA CONTRACTS -->
    <?php if (empty($contracts)): ?>
        <div style="padding:40px 20px;text-align:center;color:var(--text-muted);">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(214,182,108,0.12);color:var(--simda-gold);display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <?= Icons::svg('shopping-bag', 24) ?>
            </div>
            <div style="font-weight:700;font-size:1rem;color:var(--text-main);margin-bottom:4px;">
                <?= $isSo ? 'Weli ma jiraan heshiisyo Murabaxo ah qaybtan' : 'No Murabaha contracts in this view' ?>
            </div>
            <div style="font-size:0.82rem;max-width:380px;margin:0 auto 16px auto;">
                <?= $isSo ? 'Guji badhanka hoose si aad macmiil ugu bilowdo codsi cusub oo Murabaxo ah (Cost-Plus Markup).' : 'Click below to register a new Murabaha asset financing contract.' ?>
            </div>
            <a href="?route=murabaha_new" class="btn btn-sm btn-gold">
                <?= Icons::svg('plus', 14) ?> <span><?= $isSo ? 'Diwaangeli Murabaxo Cusub' : 'New Murabaha Application' ?></span>
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
                        <th><?= $isSo ? 'Qiimaha Hantida (Cost)' : 'Asset Cost' ?></th>
                        <th><?= $isSo ? 'Faa\'iidada (Markup)' : 'Profit / Markup' ?></th>
                        <th><?= $isSo ? 'Wadarta Heshiiska' : 'Total Due' ?></th>
                        <th><?= $isSo ? 'La Soo Celiyay' : 'Total Repaid' ?></th>
                        <th><?= $isSo ? 'Maqan (Dhiman)' : 'Outstanding' ?></th>
                        <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                        <th><?= $isSo ? 'Muddada' : 'Maturity' ?></th>
                        <th><?= $isSo ? 'Hawlaha' : 'Actions' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td class="num-tabular">
                                <a href="?route=financing_view&id=<?= (int) $c['id'] ?>" style="font-weight:700;color:var(--simda-green);text-decoration:underline;">
                                    <?= Security::e($c['financing_number']) ?>
                                </a>
                            </td>
                            <td>
                                <strong><?= Security::e($c['client_name']) ?></strong>
                                <?php if (!empty($c['client_phone'])): ?>
                                    <div style="font-size:0.74rem;color:var(--text-muted);" class="num-tabular"><?= Security::e($c['client_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= Security::e($c['branch_name']) ?></td>
                            <td class="num-tabular"><?= Security::formatMoney($c['approved_amount']) ?></td>
                            <td class="num-tabular" style="color:var(--simda-gold);font-weight:700;">+<?= Security::formatMoney($c['total_profit']) ?></td>
                            <td class="num-tabular"><strong><?= Security::formatMoney($c['total_due']) ?></strong></td>
                            <td class="num-tabular" style="color:#1FAF62;font-weight:700;"><?= Security::formatMoney($c['total_paid']) ?></td>
                            <td class="num-tabular">
                                <?php if ((float) $c['outstanding_balance'] <= 0): ?>
                                    <span class="badge badge-success" style="font-size:0.7rem;">$0.00 (WADA BIXIYAY)</span>
                                <?php else: ?>
                                    <strong style="color:#C94B4B;"><?= Security::formatMoney($c['outstanding_balance']) ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $c['status'] === 'completed' ? 'success' : ($c['status'] === 'overdue' ? 'danger' : 'info') ?>" style="font-weight:700;">
                                    <?= $c['status'] === 'completed' ? ($isSo ? 'La Wada Bixiyay' : 'Completed') : Security::e(Lang::get('status_' . $c['status'])) ?>
                                </span>
                            </td>
                            <td class="num-tabular" style="font-size:0.78rem;">
                                <?= Security::e($c['maturity_date']) ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="?route=financing_view&id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline" title="<?= $isSo ? 'Fiiri Faahfaahinta' : 'View Details' ?>">
                                        <?= Icons::svg('eye', 13) ?>
                                    </a>
                                    <a href="?route=statement_pdf&id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-gold" title="PDF Statement">
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
