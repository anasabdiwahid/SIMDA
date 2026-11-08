<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$partners = $partners ?? [];
$edit = $editItem ?? null;
$v = static fn(string $k, $default = '') => $edit[$k] ?? $default;
$activeCount = count(array_filter($partners, static fn($p) => (int) $p['is_active'] === 1));
?>

<div class="mf-head">
    <div class="mf-head-title">
        <span class="mf-head-icon"><?= Icons::svg('globe', 22) ?></span>
        <div>
            <h1><?= $isSo ? 'La-hawlgalayaasha (Partners)' : 'Partners' ?></h1>
            <p><?= $isSo
                ? 'Logo-yada la-hawlgalayaasha ee ka muuqda bogga Home-ka. Marka logo la riixo, wuxuu furayaa link-ga la-hawlgalaha.'
                : 'Partner logos shown on the public home page. Clicking a logo opens the partner\'s link.' ?></p>
        </div>
    </div>
    <div class="pr-stats">
        <span><b class="num-tabular"><?= count($partners) ?></b> <?= $isSo ? 'wadar' : 'total' ?></span>
        <span class="is-green"><b class="num-tabular"><?= $activeCount ?></b> <?= $isSo ? 'Home-ka ka muuqda' : 'on home page' ?></span>
        <a href="./#partners" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><?= Icons::svg('eye', 14) ?> <?= $isSo ? 'Eeg Home-ka' : 'View home page' ?></a>
    </div>
</div>

<div class="mg-layout">
    <form method="POST" action="?route=partner_save" enctype="multipart/form-data" class="card mg-form <?= $edit ? 'is-editing' : '' ?>">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $v('id', 0) ?>">
        <div class="mg-form-head">
            <span class="mf-step-num"><?= Icons::svg($edit ? 'edit' : 'plus', 14) ?></span>
            <div>
                <h2><?= $edit ? ($isSo ? 'Wax ka beddel la-hawlgalaha' : 'Edit partner') : ($isSo ? 'Ku dar la-hawlgale' : 'Add a partner') ?></h2>
                <p><?= $isSo ? 'Sawirka (logo), magaca iyo link-ga.' : 'Logo, name and link.' ?></p>
            </div>
        </div>

        <div class="mg-form-body pt-form-body">
            <div class="mg-image-col">
                <label class="mg-image-drop pt-logo-drop" for="partnerLogo">
                    <img id="partnerLogoPreview" src="<?= Security::e((string) $v('logo_path')) ?>" alt="" <?= $v('logo_path') ? '' : 'hidden' ?>>
                    <span class="mg-image-empty" id="partnerLogoEmpty" <?= $v('logo_path') ? 'hidden' : '' ?>>
                        <?= Icons::svg('download', 20) ?>
                        <span><?= $isSo ? 'Dooro logo' : 'Choose logo' ?></span>
                        <small>PNG · JPG · WebP · 2MB</small>
                    </span>
                </label>
                <input type="file" name="logo" id="partnerLogo" accept="image/png,image/jpeg,image/webp" hidden>
            </div>

            <div class="mg-fields pt-fields">
                <div class="form-group mg-span-2">
                    <label for="partnerName"><?= $isSo ? 'Magaca *' : 'Name *' ?></label>
                    <input type="text" name="name" id="partnerName" class="form-control" required maxlength="120" value="<?= Security::e((string) $v('name')) ?>" placeholder="<?= $isSo ? 'Tusaale: Hormuud Telecom' : 'e.g. Hormuud Telecom' ?>">
                </div>
                <div class="form-group">
                    <label for="partnerOrder"><?= $isSo ? 'Kala horreynta' : 'Order' ?></label>
                    <input type="number" name="sort_order" id="partnerOrder" class="form-control num-tabular" value="<?= (int) $v('sort_order', 0) ?>" step="1">
                </div>
                <div class="form-group mg-span-3">
                    <label for="partnerLink"><?= $isSo ? 'Link-ga (website)' : 'Link (website)' ?></label>
                    <input type="url" name="link_url" id="partnerLink" class="form-control" maxlength="255" value="<?= Security::e((string) $v('link_url')) ?>" placeholder="https://www.example.com">
                </div>
            </div>
        </div>

        <div class="mg-form-actions">
            <?php if ($edit): ?>
                <a href="?route=partners" class="btn btn-outline"><?= $isSo ? 'Jooji' : 'Cancel' ?></a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><?= Icons::svg('check', 15) ?> <?= $edit ? ($isSo ? 'Kaydi isbeddelka' : 'Save changes') : ($isSo ? 'Ku dar' : 'Add partner') ?></button>
        </div>
    </form>

    <div>
        <div class="mg-toolbar">
            <strong><?= count($partners) ?> <?= $isSo ? 'la-hawlgale' : 'partners' ?></strong>
        </div>
        <?php if (!$partners): ?>
            <div class="card" style="text-align:center;padding:40px;color:var(--text-muted);"><?= $isSo ? 'Weli la-hawlgale lama gelin.' : 'No partners yet.' ?></div>
        <?php endif; ?>
        <div class="pt-grid">
            <?php foreach ($partners as $p): ?>
                <?php $on = (int) $p['is_active'] === 1; ?>
                <article class="card pt-card <?= $on ? '' : 'is-off' ?>">
                    <span class="pt-card-logo"><img src="<?= Security::e((string) $p['logo_path']) ?>" alt="<?= Security::e((string) $p['name']) ?>" loading="lazy"></span>
                    <strong class="pt-card-name"><?= Security::e((string) $p['name']) ?></strong>
                    <?php if (!empty($p['link_url'])): ?>
                        <a class="pt-card-link" href="<?= Security::e((string) $p['link_url']) ?>" target="_blank" rel="noopener noreferrer"><?= Security::e((string) preg_replace('~^https?://(www\.)?~i', '', (string) $p['link_url'])) ?></a>
                    <?php else: ?>
                        <span class="pt-card-link is-none"><?= $isSo ? 'Link ma leh' : 'No link' ?></span>
                    <?php endif; ?>
                    <div class="mg-actions">
                        <a href="?route=partners&edit=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline"><?= Icons::svg('edit', 13) ?> <?= $isSo ? 'Beddel' : 'Edit' ?></a>
                        <form method="POST" action="?route=partner_toggle">
                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="mg-onoff <?= $on ? 'is-on' : '' ?>" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" title="<?= $isSo ? 'Ka muuji / ka qari Home-ka' : 'Show / hide on home page' ?>">
                                <span class="mg-onoff-knob"></span><span class="mg-onoff-label"><?= $on ? 'ON' : 'OFF' ?></span>
                            </button>
                        </form>
                        <form method="POST" action="?route=partner_delete" onsubmit="return confirm('<?= $isSo ? 'Ma hubtaa inaad tirtirto la-hawlgalahan?' : 'Delete this partner?' ?>');">
                            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn btn-sm btn-outline mg-del" type="submit" aria-label="<?= $isSo ? 'Tirtir' : 'Delete' ?>"><?= Icons::svg('x', 13) ?></button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function () {
    const file = document.getElementById('partnerLogo');
    const img = document.getElementById('partnerLogoPreview');
    const empty = document.getElementById('partnerLogoEmpty');
    file.addEventListener('change', function () {
        const f = file.files[0];
        if (!f) return;
        img.src = URL.createObjectURL(f);
        img.hidden = false;
        empty.hidden = true;
    });
})();
</script>
