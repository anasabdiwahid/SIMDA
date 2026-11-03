<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$csrf = Security::csrfToken();
?>

<div class="card animate-in">
    <div style="margin-bottom:18px;">
        <span class="badge badge-gold"><?= Icons::svg('shield', 13) ?> Module 1 — Mandatory Guarantor Enforcement</span>
        <h2 style="font-size:1.25rem;margin-top:6px;"><?= Security::e(Lang::get('menu_register_client')) ?></h2>
        <p style="font-size:0.85rem;color:var(--text-secondary);">
            Every client must have a unique ID number, unique phone number, assigned branch, and at least one verified Guarantor. New clients receive a starting Credit Score of 600.
        </p>
    </div>

    <form method="POST" action="?route=client_register_submit" enctype="multipart/form-data" data-confirm="Register this client and their guarantor in the SIMDA database?">
        <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

        <h4 style="margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border-color);">1. Client Personal & Business Information</h4>
        <div class="form-grid">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" required placeholder="e.g. Fadumo Mohamed Ali">
            </div>
            <div class="form-group">
                <label>ID Type *</label>
                <select name="id_type" class="form-control" required>
                    <option value="national_id">National ID</option>
                    <option value="passport">Passport</option>
                    <option value="voter_card">Voter Card</option>
                    <option value="business_license">Business License</option>
                </select>
            </div>
            <div class="form-group">
                <label>Unique ID Number *</label>
                <input type="text" name="id_number" class="form-control num-tabular" required placeholder="e.g. SOM-ID-884120">
            </div>
            <div class="form-group">
                <label>Unique Phone Number *</label>
                <input type="text" name="phone" class="form-control num-tabular" required placeholder="e.g. +252615778899">
            </div>
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" class="form-control" required value="Mogadishu">
            </div>
            <div class="form-group">
                <label>District *</label>
                <input type="text" name="district" class="form-control" required placeholder="e.g. Hodan">
            </div>
            <div class="form-group">
                <label>Physical Address *</label>
                <input type="text" name="address" class="form-control" required placeholder="Street / Market stall number">
            </div>
            <div class="form-group">
                <label>Business Type *</label>
                <input type="text" name="business_type" class="form-control" required placeholder="e.g. Retail Electronics & Accessories">
            </div>
            <div class="form-group">
                <label>Estimated Monthly Income ($ USD) *</label>
                <input type="number" name="monthly_income" class="form-control num-tabular" step="0.01" min="50" required value="1200.00">
            </div>
            <div class="form-group">
                <label>Branch Assignment *</label>
                <select name="branch_id" class="form-control" required>
                    <?php foreach ($branches as $b): ?>
                        <?php if (($user['role_slug'] ?? '') === 'admin' || (int) ($user['branch_id'] ?? 0) === (int) $b['id']): ?>
                            <option value="<?= (int) $b['id'] ?>"><?= Security::e($b['name'] . ' (' . $b['code'] . ')') ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Client Photograph (Optional — JPG/PNG/WebP)</label>
                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>

        <h4 style="margin:20px 0 12px;padding-bottom:6px;border-bottom:1px solid var(--border-color);">2. Mandatory Primary Guarantor Information</h4>
        <div class="form-grid">
            <div class="form-group">
                <label>Guarantor Full Name *</label>
                <input type="text" name="guarantor_full_name" class="form-control" required placeholder="e.g. Abdullahi Mohamed Ali">
            </div>
            <div class="form-group">
                <label>Guarantor Phone Number *</label>
                <input type="text" name="guarantor_phone" class="form-control num-tabular" required placeholder="e.g. +252615443322">
            </div>
            <div class="form-group">
                <label>Guarantor ID Number *</label>
                <input type="text" name="guarantor_id_number" class="form-control num-tabular" required placeholder="e.g. SOM-GID-551900">
            </div>
            <div class="form-group">
                <label>Relationship to Client *</label>
                <input type="text" name="guarantor_relationship" class="form-control" required placeholder="e.g. Brother / Business Partner">
            </div>
            <div class="form-group">
                <label>Guarantor Occupation</label>
                <input type="text" name="guarantor_occupation" class="form-control" placeholder="e.g. Wholesale Merchant">
            </div>
            <div class="form-group">
                <label>Guarantor Est. Monthly Income ($ USD)</label>
                <input type="number" name="guarantor_income" class="form-control num-tabular" step="0.01" value="1800.00">
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary"><?= Icons::svg('check-circle', 16) ?> <span>Register Client & Guarantor</span></button>
            <a href="?route=clients" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
