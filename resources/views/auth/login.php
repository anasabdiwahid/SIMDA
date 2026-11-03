<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;

$locale = Lang::locale();
$csrf = Security::csrfToken();
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e(Lang::get('nav_login')) ?> — SIMDA</title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/simda.css">
</head>
<body style="min-height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;background:linear-gradient(135deg,#0D2B24 0%,#123B32 60%,#194B3F 100%);padding:20px;">

<div style="max-width:440px;width:100%;margin:0 auto;">
    <div class="card animate-in" style="padding:34px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
            <a href="?route=home" class="brand-logo-pill" style="border:1px solid var(--border-color);">
                <img src="assets/images/simda-logo.png" alt="SIMDA Logo" style="height:28px;">
            </a>
            <div style="display:flex;gap:5px;">
                <a href="?route=login&lang=en" class="btn btn-sm <?= $locale === 'en' ? 'btn-primary' : 'btn-outline' ?>">EN</a>
                <a href="?route=login&lang=so" class="btn btn-sm <?= $locale === 'so' ? 'btn-primary' : 'btn-outline' ?>">SO</a>
            </div>
        </div>

        <h2 style="font-size:1.4rem;margin-bottom:4px;"><?= Security::e(Lang::get('nav_login')) ?></h2>
        <p style="color:var(--text-secondary);font-size:0.86rem;margin-bottom:20px;">
            <?= Security::e(Lang::get('tagline')) ?>
        </p>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?= Icons::svg('alert-triangle', 18) ?>
                <span><?= Security::e($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_warning'])): ?>
            <div class="alert alert-warning">
                <?= Icons::svg('info', 18) ?>
                <span><?= Security::e($_SESSION['flash_warning']) ?></span>
            </div>
            <?php unset($_SESSION['flash_warning']); ?>
        <?php endif; ?>

        <form method="POST" action="?route=login_submit">
            <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input type="text" id="identifier" name="identifier" class="form-control" required autofocus placeholder="Enter your username or email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;margin-top:8px;">
                <span><?= Security::e(Lang::get('nav_login')) ?></span>
                <?= Icons::svg('arrow-right', 16) ?>
            </button>
        </form>

        <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;font-size:0.82rem;">
            <a href="?route=home" style="color:var(--text-secondary);display:inline-flex;align-items:center;gap:5px;">
                <?= Icons::svg('arrow-left', 14) ?>
                <span><?= Security::e(Lang::get('nav_home')) ?></span>
            </a>
            <span style="color:var(--text-muted);font-size:0.75rem;">Secured with Argon2id &amp; CSRF</span>
        </div>
    </div>
</div>

<script src="assets/js/simda.js"></script>
</body>
</html>
