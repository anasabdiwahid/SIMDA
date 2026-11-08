<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$isSo = Lang::locale() === 'so';
$c = $client;
$cid = (int) $c['id'];
$idTypes = [
    'national_id' => $isSo ? 'Kaarka Aqoonsiga' : 'National ID',
    'passport' => $isSo ? 'Baasaboor' : 'Passport',
    'voter_card' => $isSo ? 'Kaarka Codbixinta' : 'Voter Card',
    'business_license' => $isSo ? 'Liisanka Ganacsiga' : 'Business License',
];
$statusTone = ['active' => 'green', 'completed' => 'info', 'overdue' => 'gold', 'written_off' => 'muted'];
$dash = static fn($v): string => ($v === null || $v === '') ? '—' : (string) $v;
?>

<?php if (!empty($isNew)): ?>
    <div class="cv-new-banner">
        <?= Icons::svg('check-circle', 18) ?>
        <?= $isSo ? 'Macmiilka waa la diiwaangeliyay. Hoos ka dooro dayn ama Murabaxo si aad u siiso.' : 'Client registered. Choose a cash loan or Murabaha below to issue financing.' ?>
    </div>
<?php endif; ?>

<div class="card cv-hero">
    <div class="cv-person">
        <div class="cv-avatar"><?= Security::e(mb_strtoupper(mb_substr(trim((string) $c['full_name']), 0, 1))) ?></div>
        <div style="min-width:0;">
            <h1 class="cv-name"><?= Security::e((string) $c['full_name']) ?></h1>
            <div class="cv-meta">
                <span class="num-tabular"><?= Security::e((string) $c['client_code']) ?></span>
                <span><?= Icons::svg('phone', 13) ?> <?= Security::e((string) $c['phone']) ?></span>
                <span><?= Icons::svg('building', 13) ?> <?= Security::e($dash($c['branch_name'])) ?></span>
                <span><?= Icons::svg('activity', 13) ?> <?= $isSo ? 'Dhibcaha' : 'Score' ?> <?= (int) ($c['current_score'] ?? 0) ?>/100 · <?= Security::e(\App\Services\CreditScoreEngine::label((string) ($c['score_category'] ?? 'new'), $isSo)) ?></span>
            </div>
        </div>
    </div>
    <div class="cv-cta">
        <?php
        $owes = array_filter($financings, static fn($f) => in_array($f['status'], ['active', 'overdue'], true) && (float) $f['outstanding_balance'] > 0);
        if ($owes && !empty($canCollect)):
        ?>
            <a href="?route=repayment_record&client_id=<?= $cid ?>" class="btn btn-gold"><?= Icons::svg('wallet', 16) ?> <?= $isSo ? 'Qabo Lacag-celin' : 'Collect Repayment' ?></a>
        <?php endif; ?>
        <a href="?route=loan_new&client_id=<?= $cid ?>" class="btn btn-primary"><?= Icons::svg('dollar-sign', 16) ?> <?= $isSo ? 'Sii Dayn Lacag Ah' : 'Give Cash Loan' ?></a>
        <a href="?route=murabaha_new&client_id=<?= $cid ?>" class="btn btn-outline"><?= Icons::svg('shopping-bag', 16) ?> <?= $isSo ? 'Sii Murabaxo' : 'Give Murabaha' ?></a>
    </div>
</div>

<div class="cv-grid">
    <section class="card">
        <h3 style="font-size:1rem;margin:0 0 10px;"><?= $isSo ? 'Xogta Macmiilka' : 'Client Details' ?></h3>
        <dl class="cv-dl">
            <dt><?= $isSo ? 'Aqoonsiga' : 'ID' ?></dt><dd><?= Security::e(($idTypes[$c['id_type']] ?? $c['id_type']) . ' · ' . $c['id_number']) ?></dd>
            <dt>Email</dt><dd><?= Security::e($dash($c['email'])) ?></dd>
            <dt><?= $isSo ? 'Magaalada' : 'City' ?></dt><dd><?= Security::e($c['city'] . ', ' . $c['district']) ?></dd>
            <dt><?= $isSo ? 'Cinwaanka' : 'Address' ?></dt><dd><?= Security::e($dash($c['address'])) ?></dd>
            <dt><?= $isSo ? 'Ganacsiga' : 'Business' ?></dt><dd><?= Security::e($dash($c['business_type'])) ?></dd>
            <dt><?= $isSo ? 'Dakhliga bishii' : 'Monthly Income' ?></dt><dd class="num-tabular">$<?= number_format((float) $c['monthly_income'], 2) ?></dd>
            <dt><?= $isSo ? 'Xaaladda' : 'Status' ?></dt><dd><?= Security::e(ucfirst((string) $c['status'])) ?></dd>
            <dt><?= $isSo ? 'Waxaa diiwaangeliyay' : 'Registered by' ?></dt><dd><?= Security::e($dash($c['registered_by_name'])) ?> · <?= Security::e(date('d/m/Y', strtotime((string) $c['created_at']))) ?></dd>
        </dl>
    </section>

    <section class="card">
        <h3 style="font-size:1rem;margin:0 0 10px;"><?= $isSo ? 'Damiinka (Guarantor)' : 'Guarantor' ?></h3>
        <?php if (!$guarantors): ?>
            <p style="color:var(--text-muted);margin:0;"><?= $isSo ? 'Damiin lama diiwaangelin.' : 'No guarantor on file.' ?></p>
        <?php endif; ?>
        <?php foreach ($guarantors as $g): ?>
            <dl class="cv-dl" style="margin-bottom:10px;">
                <dt><?= $isSo ? 'Magaca' : 'Name' ?></dt><dd><?= Security::e((string) $g['full_name']) ?></dd>
                <dt><?= $isSo ? 'Telefoonka' : 'Phone' ?></dt><dd><?= Security::e($dash($g['phone'] ?? '')) ?></dd>
                <dt><?= $isSo ? 'Aqoonsiga' : 'ID Number' ?></dt><dd><?= Security::e($dash($g['id_number'] ?? '')) ?></dd>
                <dt><?= $isSo ? 'Xiriirka' : 'Relationship' ?></dt><dd><?= Security::e($dash($g['relationship'] ?? '')) ?></dd>
                <dt><?= $isSo ? 'Shaqada' : 'Occupation' ?></dt><dd><?= Security::e($dash($g['occupation'] ?? '')) ?></dd>
            </dl>
        <?php endforeach; ?>
    </section>
</div>

<section class="card" style="margin-top:18px;">
    <h3 style="font-size:1rem;margin:0 0 10px;"><?= $isSo ? 'Maalgelinta Macmiilka' : 'Financing History' ?></h3>
    <?php if (!$financings): ?>
        <p style="color:var(--text-muted);margin:0;"><?= $isSo ? 'Macmiilkan weli dayn lama siin.' : 'This client has no financing yet.' ?></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="simda-table">
                <thead><tr>
                    <th><?= $isSo ? 'Heshiiska' : 'Contract' ?></th>
                    <th><?= $isSo ? 'Nooca' : 'Type' ?></th>
                    <th><?= $isSo ? 'Lacagta' : 'Amount' ?></th>
                    <th><?= $isSo ? 'Haraaga' : 'Outstanding' ?></th>
                    <th><?= $isSo ? 'Xaaladda' : 'Status' ?></th>
                    <th><?= $isSo ? 'Taariikhda' : 'Date' ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($financings as $f): ?>
                    <tr>
                        <td><a href="?route=financing_view&id=<?= (int) $f['id'] ?>" class="num-tabular" style="font-weight:700;"><?= Security::e((string) $f['financing_number']) ?></a></td>
                        <td><?= $f['product_type'] === 'murabaha' ? ($isSo ? 'Murabaxo' : 'Murabaha') : ($isSo ? 'Dayn Cadaan' : 'Cash Loan') ?></td>
                        <td class="num-tabular">$<?= number_format((float) $f['approved_amount'], 2) ?></td>
                        <td class="num-tabular">$<?= number_format((float) $f['outstanding_balance'], 2) ?></td>
                        <td><span class="adm-role dash-tone-<?= $statusTone[$f['status']] ?? 'muted' ?>"><?= Security::e(ucfirst(str_replace('_', ' ', (string) $f['status']))) ?></span></td>
                        <td><?= Security::e(date('d/m/Y', strtotime((string) $f['disbursed_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="card" style="margin-top:18px;">
    <h3 style="font-size:1rem;margin:0 0 10px;"><?= $isSo ? 'Rasiidhada Lacag-celinta' : 'Repayment Receipts' ?></h3>
    <?php if (empty($repayments)): ?>
        <p style="color:var(--text-muted);margin:0;"><?= $isSo ? 'Macmiilkan weli lacag ma soo celin.' : 'No repayments from this client yet.' ?></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="simda-table">
                <thead><tr>
                    <th><?= $isSo ? 'Rasiidka' : 'Receipt' ?></th>
                    <th><?= $isSo ? 'Heshiiska' : 'Contract' ?></th>
                    <th><?= $isSo ? 'Lacagta' : 'Amount' ?></th>
                    <th><?= $isSo ? 'Habka' : 'Method' ?></th>
                    <th><?= $isSo ? 'Taariikhda' : 'Date' ?></th>
                    <th><?= $isSo ? 'Qabtay' : 'Recorded by' ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($repayments as $rp): ?>
                    <tr>
                        <td><a href="?route=repayment_receipt&id=<?= (int) $rp['id'] ?>" class="num-tabular" style="font-weight:700;"><?= Security::e((string) $rp['receipt_number']) ?></a></td>
                        <td class="num-tabular"><?= Security::e((string) $rp['financing_number']) ?></td>
                        <td class="num-tabular" style="font-weight:700;color:var(--color-success);">$<?= number_format((float) $rp['amount'], 2) ?></td>
                        <td><?= Security::e((string) $rp['payment_method']) ?></td>
                        <td><?= Security::e(date('d/m/Y', strtotime((string) $rp['payment_date']))) ?></td>
                        <td><?= Security::e($dash($rp['recorded_by_name'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
