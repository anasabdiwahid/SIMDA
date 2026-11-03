<?php
declare(strict_types=1);

use App\Helpers\Lang;
use App\Helpers\Security;
?>
<!DOCTYPE html>
<html lang="<?= Security::e(Lang::locale()) ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — SIMDA</title>
    <link rel="stylesheet" href="assets/css/simda.css">
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center;">
    <div class="card" style="max-width:480px;">
        <span class="brand-logo-pill" style="margin-bottom:16px;">
            <img src="assets/images/simda-logo.png" alt="SIMDA" style="height:30px;">
        </span>
        <h2 style="margin-bottom:10px;"><?= Security::e(Lang::get('connection_offline')) ?></h2>
        <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:20px;">
            To protect financial integrity, SIMDA does not process or complete approvals, disbursements, or repayments while offline. Please restore your internet connection and refresh.
        </p>
        <button type="button" onclick="window.location.reload()" class="btn btn-gold">
            Refresh &amp; Reconnect
        </button>
    </div>
</body>
</html>

