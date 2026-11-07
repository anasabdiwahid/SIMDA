<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Services\MurabahaGoodsService;

$csrf = Security::csrfToken();
$isSo = Lang::locale() === 'so';
$goods = $goods ?? [];
$edit = $editItem ?? null;
$v = static fn(string $k, $default = '') => $edit[$k] ?? $default;
?>

<div class="adm-page-head" style="margin-bottom:20px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;margin:0 0 4px;"><?= $isSo ? 'Alaabta Murabaxada' : 'Murabaha Products' ?></h1>
        <p style="color:var(--text-muted);font-size:0.88rem;margin:0;">
            <?= $isSo
                ? 'Alaabta SIMDA iibiso: magaca, sawirka, qiimaha iyo faahfaahinta. Waxay ka muuqanayaan foomka Murabaxada Cusub.'
                : 'Goods SIMDA sells on Murabaha: name, image, price and description. They appear in the New Murabaha form.' ?>
        </p>
    </div>
    <div class="adm-head-actions">
        <a href="?route=murabaha_new" class="btn btn-outline btn-sm"><?= Icons::svg('shopping-bag', 14) ?> <?= $isSo ? 'Sii Murabaxo' : 'New Murabaha' ?></a>
    </div>
</div>

<div class="mg-layout">
    <form method="POST" action="?route=murabaha_goods_save" enctype="multipart/form-data" class="card mg-form <?= $edit ? 'is-editing' : '' ?>" id="goodsForm">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $v('id', 0) ?>">
        <div class="mg-form-head">
            <span class="mf-step-num"><?= Icons::svg($edit ? 'edit' : 'plus', 14) ?></span>
            <div>
                <h2><?= $edit ? ($isSo ? 'Wax ka beddel sheyga' : 'Edit product') : ($isSo ? 'Ku dar sheyga cusub' : 'Add a new product') ?></h2>
                <p><?= $isSo ? 'Sawirka, magaca, qiimaha iyo faahfaahinta.' : 'Image, name, price and description.' ?></p>
            </div>
        </div>

        <div class="mg-form-body">
            <div class="mg-image-col">
                <label class="mg-image-drop" for="goodsImage">
                    <img id="goodsImagePreview" src="<?= Security::e((string) $v('image_path')) ?>" alt="" <?= $v('image_path') ? '' : 'hidden' ?>>
                    <span class="mg-image-empty" id="goodsImageEmpty" <?= $v('image_path') ? 'hidden' : '' ?>>
                        <?= Icons::svg('download', 20) ?>
                        <span><?= $isSo ? 'Dooro sawir' : 'Choose image' ?></span>
                        <small>JPG · PNG · WebP · 3MB</small>
                    </span>
                </label>
                <input type="file" name="image" id="goodsImage" accept="image/jpeg,image/png,image/webp" hidden>
                <?php if ($v('image_path')): ?>
                    <label class="mg-remove"><input type="checkbox" name="remove_image" value="1"> <?= $isSo ? 'Ka saar sawirka' : 'Remove image' ?></label>
                <?php endif; ?>
            </div>

            <div class="mg-fields">
                <div class="form-group mg-span-2">
                    <label for="goodsName"><?= $isSo ? 'Magaca sheyga *' : 'Product name *' ?></label>
                    <input type="text" name="name" id="goodsName" class="form-control" required maxlength="160" value="<?= Security::e((string) $v('name')) ?>" placeholder="<?= $isSo ? 'Tusaale: Mooto Bajaaj' : 'e.g. Tuk-Tuk Rickshaw' ?>">
                </div>
                <div class="form-group">
                    <label for="goodsCategory"><?= $isSo ? 'Qaybta' : 'Category' ?></label>
                    <input type="text" name="category" id="goodsCategory" class="form-control" maxlength="100" value="<?= Security::e((string) $v('category')) ?>" placeholder="<?= $isSo ? 'Tusaale: Gaadiidka' : 'e.g. Vehicles' ?>">
                </div>
                <div class="form-group">
                    <label for="goodsPrice"><?= $isSo ? 'Qiimaha *' : 'Price *' ?></label>
                    <div class="mf-input mf-input-prefix"><span>$</span>
                        <input type="number" name="price" id="goodsPrice" class="form-control num-tabular" required min="1" step="0.01" value="<?= Security::e((string) $v('price')) ?>" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label for="goodsMarkup"><?= $isSo ? 'Faa\'iidada' : 'Markup' ?></label>
                    <div class="mf-input mf-input-suffix">
                        <input type="number" name="markup_pct" id="goodsMarkup" class="form-control num-tabular" min="0" max="100" step="0.1" value="<?= Security::e((string) $v('markup_pct', '6')) ?>">
                        <span>%</span>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $isSo ? 'Qiimaha iibka' : 'Selling price' ?></label>
                    <div class="mg-selling num-tabular" id="goodsSelling">$0.00</div>
                </div>
                <div class="form-group mg-span-3">
                    <label for="goodsDesc"><?= $isSo ? 'Faahfaahinta' : 'Description' ?></label>
                    <textarea name="description" id="goodsDesc" class="form-control" rows="2" placeholder="<?= $isSo ? 'Sifooyinka sheyga, nooca, cabbirka...' : 'Specs, model, size...' ?>"><?= Security::e((string) $v('description')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="mg-form-actions">
            <?php if ($edit): ?>
                <a href="?route=murabaha_goods" class="btn btn-outline"><?= $isSo ? 'Jooji' : 'Cancel' ?></a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><?= Icons::svg('check', 15) ?> <?= $edit ? ($isSo ? 'Kaydi isbeddelka' : 'Save changes') : ($isSo ? 'Ku dar sheyga' : 'Add product') ?></button>
        </div>
    </form>

    <div>
        <div class="mg-toolbar">
            <strong><?= count($goods) ?> <?= $isSo ? 'shey' : 'products' ?></strong>
            <input type="search" class="form-control" id="goodsSearch" placeholder="<?= $isSo ? 'Raadi sheyga...' : 'Search products...' ?>">
        </div>
        <?php if (!$goods): ?>
            <div class="card" style="text-align:center;padding:40px;color:var(--text-muted);"><?= $isSo ? 'Weli shey lama gelin.' : 'No products yet.' ?></div>
        <?php endif; ?>
        <div class="mg-grid" id="goodsGrid">
            <?php foreach ($goods as $g): ?>
                <article class="card mg-card <?= (int) $g['is_active'] === 1 ? '' : 'is-off' ?>" data-search="<?= Security::e(mb_strtolower($g['name'] . ' ' . $g['category'] . ' ' . $g['description'])) ?>">
                    <div class="mg-thumb">
                        <?php if ($g['image_path']): ?>
                            <img src="<?= Security::e((string) $g['image_path']) ?>" alt="<?= Security::e((string) $g['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= Icons::svg('shopping-bag', 30) ?>
                        <?php endif; ?>
                        <?php if ((int) $g['is_active'] !== 1): ?><span class="mg-off-badge"><?= $isSo ? 'La joojiyay' : 'Hidden' ?></span><?php endif; ?>
                    </div>
                    <div class="mg-body">
                        <?php if ($g['category']): ?><div class="mg-cat"><?= Security::e((string) $g['category']) ?></div><?php endif; ?>
                        <h4 class="mg-name"><?= Security::e((string) $g['name']) ?></h4>
                        <?php if ($g['description']): ?><p class="mg-desc"><?= Security::e((string) $g['description']) ?></p><?php endif; ?>
                        <div class="mg-prices">
                            <div><small><?= $isSo ? 'Qiimaha' : 'Price' ?></small><b class="num-tabular">$<?= number_format((float) $g['price'], 2) ?></b></div>
                            <div><small><?= $isSo ? 'Iibka' : 'Selling' ?> (+<?= rtrim(rtrim(number_format((float) $g['markup_pct'], 2), '0'), '.') ?>%)</small><b class="num-tabular mg-sell">$<?= number_format(MurabahaGoodsService::sellingPrice($g), 2) ?></b></div>
                        </div>
                        <div class="mg-actions">
                            <a href="?route=murabaha_goods&edit=<?= (int) $g['id'] ?>" class="btn btn-sm btn-outline"><?= Icons::svg('edit', 13) ?> <?= $isSo ? 'Beddel' : 'Edit' ?></a>
                            <form method="POST" action="?route=murabaha_goods_toggle">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                                <button class="btn btn-sm btn-outline" type="submit"><?= Icons::svg((int) $g['is_active'] === 1 ? 'eye' : 'check', 13) ?> <?= (int) $g['is_active'] === 1 ? ($isSo ? 'Qari' : 'Hide') : ($isSo ? 'Muuji' : 'Show') ?></button>
                            </form>
                            <form method="POST" action="?route=murabaha_goods_delete" onsubmit="return confirm('<?= $isSo ? 'Ma hubtaa inaad tirtirto sheygan?' : 'Delete this product?' ?>');">
                                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                                <button class="btn btn-sm btn-outline mg-del" type="submit" aria-label="<?= $isSo ? 'Tirtir' : 'Delete' ?>"><?= Icons::svg('x', 13) ?></button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function () {
    const file = document.getElementById('goodsImage');
    const img = document.getElementById('goodsImagePreview');
    const empty = document.getElementById('goodsImageEmpty');
    file.addEventListener('change', function () {
        const f = file.files[0];
        if (!f) return;
        img.src = URL.createObjectURL(f);
        img.hidden = false;
        empty.hidden = true;
    });
    const price = document.getElementById('goodsPrice');
    const markup = document.getElementById('goodsMarkup');
    const out = document.getElementById('goodsSelling');
    function calc() {
        const p = parseFloat(price.value) || 0, m = parseFloat(markup.value) || 0;
        out.textContent = '$' + (p * (1 + m / 100)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    price.addEventListener('input', calc);
    markup.addEventListener('input', calc);
    calc();
    document.getElementById('goodsSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#goodsGrid .mg-card').forEach(function (c) {
            c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none';
        });
    });
})();
</script>
