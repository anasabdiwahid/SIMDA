<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$isLoggedIn = AuthMiddleware::check();
$assetVer = (string) max((int) @filemtime(BASE_PATH . '/public/assets/css/simda.css'), (int) @filemtime(BASE_PATH . '/public/assets/js/simda.js'), 1);
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123B32">
    <title>SIMDA â€” <?= Security::e(Lang::get('app_full_name')) ?></title>
    <link rel="icon" type="image/png" href="assets/images/simda-icon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        (function() {
            var p = localStorage.getItem('simda_theme_pref') || 'light';
            var t = (p === 'dark' || p === 'light') ? p : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    keys.forEach(function(k) {
                        if (k !== 'simda-static-shell-v3') { caches.delete(k); }
                    });
                });
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/css/simda.css?v=<?= Security::e($assetVer) ?>">
</head>
<body class="fid-page">
<div class="offline-banner">
    <?= Security::e(Lang::get('connection_offline')) ?>
</div>

<!-- 1. TOP EDITORIAL NAVIGATION HEADER -->
<header class="fid-header">
    <div class="fid-container fid-nav">
        <a href="?route=home" class="fid-logo-link">
            <img src="assets/images/simda-logo.png" alt="SIMDA Microfinance">
        </a>

        <ul class="fid-nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#why-us">About Us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#our-process">Our Process</a></li>
            <li><a href="#testimonials">Testimonials</a></li>
            <li><a href="#education">FAQ & Education</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>

        <div style="display:flex;align-items:center;gap:10px;">
            <a href="?route=<?= $isLoggedIn ? 'dashboard' : 'login' ?>" class="fid-btn-green">
                <span><?= $isLoggedIn ? 'Dashboard' : 'Get Started' ?></span>
                <?= Icons::svg('arrow-right', 14) ?>
            </a>
        </div>
    </div>
</header>

<!-- 2. ROUNDED WARM CREAM HERO BANNER CARD -->
<section id="home" class="fid-hero-wrap">
    <div class="fid-container">
        <div class="fid-hero-card animate-in">
            <div class="fid-hero-copy">
                <span class="fid-eyebrow">WELCOME TO SIMDA MICROFINANCE</span>
                <h1 class="fid-serif-h1">Your Financial<br>Growth Partner</h1>
                <p class="fid-hero-lead">
                    We help individuals, small businesses, and solidarity groups achieve their financial goals through accessible, ethical, and transparent Sharia-compliant micro-finance services.
                </p>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <a href="#services" class="fid-btn-green">
                        <span>Learn More</span>
                        <?= Icons::svg('arrow-right', 14) ?>
                    </a>
                    <a href="#calculator" class="btn btn-outline" style="border-radius:999px;padding:10px 18px;border-color:#123B32;color:#123B32;font-weight:600;">
                        <?= Icons::svg('calendar', 14) ?>
                        <span>Schedule Simulator</span>
                    </a>
                </div>
            </div>

            <div class="fid-hero-media">
                <img src="assets/images/landing/hero-entrepreneurs.jpg" alt="SIMDA Microfinance Entrepreneurs">
                <div class="fid-hero-float-badge">
                    <div class="fid-float-icon">
                        <?= Icons::svg('users', 20) ?>
                    </div>
                    <div>
                        <div style="font-size:1.05rem;font-weight:800;color:#123B32;line-height:1.15;">100% Sharia</div>
                        <div style="font-size:0.74rem;color:#6A756F;font-weight:500;">Ethical & Transparent</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. OUR SERVICES â€” 4-COLUMN EDITORIAL CARDS -->
<section id="services" class="fid-section">
    <div class="fid-container">
        <div class="fid-section-head">
            <span class="fid-eyebrow">OUR SERVICES</span>
            <h2 class="fid-serif-h2">Financial Solutions for Every Stage</h2>
        </div>

        <div class="fid-services-grid">
            <!-- Card 1 -->
            <div class="fid-service-card sage reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-savings.jpg" alt="Savings & Credit Stewardship">
                    </div>
                    <h3>Savings & Credit Plans</h3>
                    <p>Build your future with flexible repayment schedules and automatic client overpayment credit protection.</p>
                </div>
                <div>
                    <a href="?route=login" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-business.jpg" alt="Small Business Loans">
                    </div>
                    <h3>Small Business Loans</h3>
                    <p>Empowering your business growth with transparent Murabaha cost-plus asset and inventory financing solutions.</p>
                </div>
                <div>
                    <a href="?route=login" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-credit.jpg" alt="Micro-Credit Solutions">
                    </div>
                    <h3>Micro-Credit Solutions</h3>
                    <p>Quick access to ethical micro-financing and solidarity group facilities (3â€“10 members) via EVC Plus, Zaad & eDahab.</p>
                </div>
                <div>
                    <a href="?route=login" class="fid-circle-arrow" aria-label="Learn more"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="fid-service-card reveal-on-scroll">
                <div>
                    <div class="fid-service-illust">
                        <img src="assets/images/landing/srv-consulting.jpg" alt="Financial Consulting">
                    </div>
                    <h3>Financial Consulting</h3>
                    <p>Expert Sharia schedule previews, dynamic credit scoring (300â€“850), and Ibra early-settlement guidance.</p>
                </div>
                <div>
                    <a href="#calculator" class="fid-circle-arrow" aria-label="Open calculator"><?= Icons::svg('arrow-right', 14) ?></a>
                </div>
            </div>
        </div>

        <?php if (!empty($products)): ?>
        <!-- Live Configured Database Products Strip -->
        <div style="margin-top:22px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
            <?php foreach ($products as $prod): ?>
                <div class="fid-service-card" style="padding:16px 18px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span class="badge badge-gold"><?= Security::e(strtoupper((string) $prod['product_type'])) ?></span>
                        <span class="num-tabular" style="font-size:0.75rem;color:#6A756F;"><?= Security::e($prod['product_code']) ?></span>
                    </div>
                    <h4 style="font-size:0.96rem;color:#123B32;margin:0 0 4px;"><?= Security::e($prod['name_en']) ?></h4>
                    <div style="font-size:0.78rem;color:#5A6660;display:flex;justify-content:space-between;margin-top:6px;padding-top:6px;border-top:1px solid rgba(0,0,0,0.06);">
                        <span>Rate: <strong class="num-tabular"><?= Security::e(number_format((float) ($prod['product_type'] === 'murabaha' ? $prod['murabaha_markup_pct'] : $prod['annual_profit_rate']), 2)) ?>%</strong></span>
                        <span class="num-tabular"><?= Security::formatMoney($prod['min_amount']) ?> â€“ <?= Security::formatMoney($prod['max_amount']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. WHY CHOOSE US â€” 2-COLUMN PHOTO & FEATURE BADGES -->
<section id="why-us" class="fid-section">
    <div class="fid-container">
        <div class="fid-why-grid">
            <div class="fid-why-photo reveal-on-scroll">
                <img src="assets/images/landing/why-choose-us.jpg" alt="SIMDA Entrepreneur with Tablet">
            </div>

            <div class="fid-why-panel reveal-on-scroll">
                <span class="fid-eyebrow">WHY CHOOSE US</span>
                <h2 class="fid-serif-h2" style="margin-bottom:6px;">More than finance,<br>we build futures.</h2>

                <div class="fid-why-features">
                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('check-circle', 18) ?></div>
                        <div>
                            <h4>Fast approval</h4>
                            <p>Get quick decisions with our dual-threshold officer and manager workflow.</p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('users', 18) ?></div>
                        <div>
                            <h4>Professional support</h4>
                            <p>Our branch teams are here to support you at every stage.</p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('calendar', 18) ?></div>
                        <div>
                            <h4>Flexible payments</h4>
                            <p>Weekly or monthly schedules with automatic Friday-to-Saturday shifts.</p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('heart-handshake', 18) ?></div>
                        <div>
                            <h4>Community focused</h4>
                            <p>We invest in people, solidarity groups, and a 100% segregated Charity Fund.</p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('shield', 18) ?></div>
                        <div>
                            <h4>Secure transactions</h4>
                            <p>Your security, privacy, and decimal-safe ledger accuracy are our priority.</p>
                        </div>
                    </div>

                    <div class="fid-why-item">
                        <div class="fid-why-badge"><?= Icons::svg('award', 18) ?></div>
                        <div>
                            <h4>Transparent pricing</h4>
                            <p>Fixed Murabaha markup locked at originationâ€”never compounding.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. DUAL SPLIT: HOW IT WORKS (LEFT) & WHAT OUR CLIENTS SAY / PILLARS (RIGHT) -->
<section id="our-process" class="fid-section">
    <div class="fid-container">
        <div class="fid-dual-row">
            <!-- Left: HOW IT WORKS -->
            <div>
                <div style="text-align:center;margin-bottom:14px;">
                    <span class="fid-eyebrow">HOW IT WORKS</span>
                </div>
                <div class="fid-steps-grid">
                    <div class="fid-step-card reveal-on-scroll">
                        <span class="fid-step-num">1</span>
                        <div class="fid-step-inner">
                            <img src="assets/images/landing/step-1.jpg" alt="Apply">
                            <div>
                                <h4 style="font-size:0.86rem;margin:0 0 3px;color:#123B32;">Apply</h4>
                                <p style="font-size:0.72rem;color:#5A6660;margin:0;line-height:1.38;">Submit a simple application online or in branch with a guarantor.</p>
                            </div>
                        </div>
                    </div>

                    <div class="fid-step-card reveal-on-scroll">
                        <span class="fid-step-num">2</span>
                        <div class="fid-step-inner">
                            <img src="assets/images/landing/step-2.jpg" alt="Verification">
                            <div>
                                <h4 style="font-size:0.86rem;margin:0 0 3px;color:#123B32;">Verification</h4>
                                <p style="font-size:0.72rem;color:#5A6660;margin:0;line-height:1.38;">We review your information, schedule preview, and assess eligibility.</p>
                            </div>
                        </div>
                    </div>

                    <div class="fid-step-card reveal-on-scroll">
                        <span class="fid-step-num">3</span>
                        <div class="fid-step-inner">
                            <img src="assets/images/landing/step-3.jpg" alt="Receive Funds">
                            <div>
                                <h4 style="font-size:0.86rem;margin:0 0 3px;color:#123B32;">Receive Funds</h4>
                                <p style="font-size:0.72rem;color:#5A6660;margin:0;line-height:1.38;">Once approved, receive your funds via EVC Plus, Zaad, or eDahab.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: WHAT OUR CLIENTS SAY / SHARIA TRUST HIGHLIGHTS -->
            <div id="testimonials">
                <div style="text-align:center;margin-bottom:14px;">
                    <span class="fid-eyebrow">WHAT OUR CLIENTS VALUE</span>
                </div>
                <div class="fid-testi-grid">
                    <div class="fid-testi-card reveal-on-scroll">
                        <div>
                            <div class="fid-stars">â˜…â˜…â˜…â˜…â˜…</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "The Murabaha cost-plus structure is clear and transparent. Every installment is known from day one."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">â€“ Retail Trader</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>

                    <div class="fid-testi-card reveal-on-scroll">
                        <div>
                            <div class="fid-stars">â˜…â˜…â˜…â˜…â˜…</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "The process was simple and transparent. Friday due dates automatically shift to Saturday."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">â€“ Market Vendor</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>

                    <div class="fid-testi-card reveal-on-scroll">
                        <div>
                            <div class="fid-stars">â˜…â˜…â˜…â˜…â˜…</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "Professional, trustworthy and always ready to assist solidarity groups and small businesses."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">â€“ Group Leader</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. FINANCIAL EDUCATION â€” 4 HORIZONTAL MEDIA CARDS + LIVE SCHEDULE CALCULATOR -->
<section id="education" class="fid-section">
    <div class="fid-container">
        <div class="fid-section-head">
            <span class="fid-eyebrow">FINANCIAL EDUCATION</span>
        </div>

        <div class="fid-edu-grid">
            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-1.jpg" alt="Tips for Saving">
                <div>
                    <h4>Tips for Saving</h4>
                    <p>Practical ways to save money and secure your future.</p>
                    <a href="#calculator" class="fid-read-more">Read More <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-2.jpg" alt="How Micro-Loans Work">
                <div>
                    <h4>How Micro-Loans Work</h4>
                    <p>Understand how Sharia-compliant Murabaha helps achieve your goals.</p>
                    <a href="#calculator" class="fid-read-more">Read More <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-3.jpg" alt="Investment Basics">
                <div>
                    <h4>Investment Basics</h4>
                    <p>Learn simple inventory strategies for long-term growth.</p>
                    <a href="#calculator" class="fid-read-more">Read More <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>

            <div class="fid-edu-card reveal-on-scroll">
                <img src="assets/images/landing/edu-4.jpg" alt="Budget Planning">
                <div>
                    <h4>Budget Planning</h4>
                    <p>Smart budgeting tips to manage your installments better.</p>
                    <a href="#calculator" class="fid-read-more">Read More <?= Icons::svg('arrow-right', 12) ?></a>
                </div>
            </div>
        </div>

        <!-- Interactive Installment Schedule Calculator (Styled in Fiducia Cream & Deep Green) -->
        <div id="calculator" class="fid-why-panel reveal-on-scroll" style="margin-top:28px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                <div>
                    <span class="fid-eyebrow" style="margin-bottom:4px;">INTERACTIVE SCHEDULE ENGINE</span>
                    <h3 class="fid-serif-h2" style="font-size:1.45rem;"><?= Security::e(Lang::get('calc_preview_title')) ?></h3>
                </div>
                <span class="badge badge-gold"><?= Icons::svg('calendar', 14) ?> Friday â†’ Saturday Auto-Shift</span>
            </div>

            <div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;">
                <div class="form-group">
                    <label>Amount Given ($ USD)</label>
                    <input type="number" id="calc_principal" class="form-control num-tabular" value="3000" min="50" max="100000" step="50" placeholder="e.g. 3000">
                </div>
                <div class="form-group">
                    <label>Monthly Payment ($ USD / Bishii)</label>
                    <input type="number" id="calc_monthly_payment" class="form-control num-tabular" value="200" min="10" max="100000" step="10" placeholder="e.g. 200">
                </div>
                <div class="form-group">
                    <label>Duration (Months / Weeks)</label>
                    <div style="display:flex;gap:6px;">
                        <input type="number" id="calc_term" class="form-control num-tabular" value="15" min="1" max="120" style="width:82px;">
                        <select id="calc_frequency" class="form-control" style="flex:1;">
                            <option value="monthly">Months (Bille)</option>
                            <option value="weekly">Weeks (Toddobaadle)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Extra / Riba (% â€” Locked at 0%)</label>
                    <input type="number" id="calc_rate" class="form-control num-tabular" value="0.00" readonly style="background:rgba(35,132,91,0.08);color:#23845B;font-weight:700;">
                    <input type="hidden" id="calc_product_type" value="flat">
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin:10px 0 14px;">
                <button type="button" id="btnPreviewSchedule" class="fid-btn-green">
                    <?= Icons::svg('calendar', 15) ?>
                    <span>Generate Exact Installment Schedule</span>
                </button>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span style="font-size:0.76rem;color:#5A6660;font-weight:600;">Quick Amounts:</span>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="1000" data-monthly="200">$1,000 ($200/mo)</button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="3000" data-monthly="200">$3,000 ($200/mo)</button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="5000" data-monthly="500">$5,000 ($500/mo)</button>
                    <button type="button" class="btn btn-sm btn-outline calc-preset-btn" data-amount="10000" data-monthly="500">$10,000 ($500/mo)</button>
                </div>
            </div>

            <div id="schedulePreviewContainer"></div>
        </div>
    </div>
</section>

<!-- 7. GET IN TOUCH + READY TO TAKE THE NEXT STEP + MAP -->
<section id="contact" class="fid-section">
    <div class="fid-container">
        <div class="fid-contact-grid">
            <div class="fid-contact-box reveal-on-scroll">
                <div>
                    <span class="fid-eyebrow" style="margin-bottom:6px;">GET IN TOUCH</span>
                    <ul class="fid-contact-list">
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('phone', 12) ?></span>
                            <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></span>
                        </li>
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('send', 12) ?></span>
                            <span><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?></span>
                        </li>
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('building', 12) ?></span>
                            <span><?= Security::e($settings['institution_address'] ?? 'Mogadishu, Somalia') ?></span>
                        </li>
                    </ul>
                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <a href="#contact" class="fid-contact-dot" aria-label="Website"><?= Icons::svg('globe', 12) ?></a>
                        <a href="#contact" class="fid-contact-dot" aria-label="Support"><?= Icons::svg('message-square', 12) ?></a>
                        <a href="#contact" class="fid-contact-dot" aria-label="Security"><?= Icons::svg('shield', 12) ?></a>
                        <a href="?route=login" class="fid-contact-dot" aria-label="Portal"><?= Icons::svg('lock', 12) ?></a>
                    </div>
                </div>

                <div style="text-align:center;border-left:1px solid rgba(0,0,0,0.08);padding-left:20px;">
                    <h3 class="fid-serif-h2" style="font-size:1.35rem;margin-bottom:8px;">Ready to take the next step?</h3>
                    <p style="font-size:0.82rem;color:#5A6660;margin-bottom:14px;">
                        We are here to help you achieve your ethical financial goals today.
                    </p>
                    <a href="?route=<?= $isLoggedIn ? 'dashboard' : 'login' ?>" class="fid-btn-green">
                        <span>Get Started</span>
                        <?= Icons::svg('arrow-right', 14) ?>
                    </a>
                </div>
            </div>

            <div class="fid-map-card reveal-on-scroll" style="position:relative;">
                <iframe
                    src="https://maps.google.com/maps?q=Anas%20Abdiwahid%2C%20Mogadishu%2C%20Somalia&z=15&output=embed"
                    title="SIMDA Office Location"
                    style="border:0;width:100%;height:100%;min-height:220px;display:block;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
                <a href="https://share.google/zYlgwxdkUvoUtrjog" target="_blank" rel="noopener"
                   style="position:absolute;right:10px;bottom:10px;background:#123B32;color:#FFF;font-size:0.74rem;font-weight:700;padding:6px 12px;border-radius:999px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 6px 14px rgba(0,0,0,0.2);">
                    Open in Google Maps <?= Icons::svg('arrow-right', 12) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 8. SCENIC SKYLINE & MONEY ILLUSTRATION FOOTER (FOOTWAY REFERENCE DESIGN) -->
<div class="fid-footer-wrap">
    <!-- Scenic Rolling Hills + City Skyline + Money / Cash / Coin Illustrations -->
    <div class="fid-footer-scenic" aria-hidden="true">
        <svg viewBox="0 0 1440 210" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Far Left City Buildings -->
            <g fill="#123B32">
                <rect x="0" y="96" width="18" height="75" />
                <rect x="20" y="108" width="22" height="65" />
                <rect x="44" y="116" width="18" height="55" />
                <rect x="65" y="102" width="20" height="70" />
            </g>
            <!-- Left Building Windows -->
            <g fill="#FFFFFF" opacity="0.88">
                <rect x="4" y="102" width="4" height="5" /><rect x="10" y="102" width="4" height="5" />
                <rect x="4" y="110" width="4" height="5" /><rect x="10" y="110" width="4" height="5" />
                <rect x="4" y="118" width="4" height="5" /><rect x="10" y="118" width="4" height="5" />
                <rect x="24" y="114" width="4" height="5" /><rect x="31" y="114" width="4" height="5" />
                <rect x="24" y="122" width="4" height="5" /><rect x="31" y="122" width="4" height="5" />
                <rect x="69" y="108" width="4" height="5" /><rect x="76" y="108" width="4" height="5" />
                <rect x="69" y="116" width="4" height="5" /><rect x="76" y="116" width="4" height="5" />
            </g>

            <!-- LEFT HILL MONEY / CASH STACK & GOLD COINS ILLUSTRATION (Replaces Left Shoe) -->
            <g transform="translate(95, 78)">
                <!-- Isometric Cash Bundle Stack -->
                <path d="M0,48 L95,28 L155,52 L60,72 Z" fill="#1E5C4C" stroke="#D6B66C" stroke-width="2" />
                <path d="M0,48 L0,64 L60,88 L60,72 Z" fill="#123B32" stroke="#D6B66C" stroke-width="1.5" />
                <path d="M60,72 L60,88 L155,68 L155,52 Z" fill="#16493C" stroke="#D6B66C" stroke-width="1.5" />
                <!-- Currency Band Strap -->
                <path d="M52,37 L82,31 L112,61 L82,67 Z" fill="#D6B66C" />
                <!-- Dollar Emblem on Top Bill -->
                <ellipse cx="78" cy="50" rx="16" ry="8" fill="#123B32" stroke="#FFFFFF" stroke-width="1.5" />
                <text x="78" y="54" text-anchor="middle" fill="#D6B66C" font-size="12" font-weight="800" font-family="sans-serif">$</text>
                <!-- Stacked Gold Coins Beside Cash Bundle -->
                <g transform="translate(132, 26)">
                    <ellipse cx="22" cy="36" rx="18" ry="6" fill="#B89242" />
                    <ellipse cx="22" cy="30" rx="18" ry="6" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                    <ellipse cx="22" cy="24" rx="18" ry="6" fill="#E4C782" stroke="#123B32" stroke-width="1.2" />
                    <ellipse cx="22" cy="18" rx="18" ry="6" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                    <text x="22" y="21" text-anchor="middle" fill="#123B32" font-size="9" font-weight="800" font-family="sans-serif">$</text>
                </g>
            </g>

            <!-- Lollipop Trees & Floating Coins Left-Center -->
            <g fill="#123B32">
                <rect x="313" y="102" width="3" height="28" />
                <circle cx="314.5" cy="96" r="11" />
                <circle cx="300" cy="110" r="6" />
                <rect x="299" y="114" width="2" height="16" />
                <circle cx="334" cy="104" r="8" />
                <rect x="333" y="110" width="2.5" height="18" />
                <circle cx="352" cy="96" r="3.5" fill="#D6B66C" />
                <circle cx="678" cy="100" r="9" />
                <rect x="677" y="106" width="2.5" height="22" />
                <circle cx="702" cy="95" r="4" fill="#D6B66C" />
            </g>

            <!-- Center Financial District Skyline & Wheel -->
            <g fill="#123B32">
                <rect x="720" y="92" width="24" height="75" />
                <!-- Ferris / Growth Wheel -->
                <circle cx="788" cy="112" r="32" fill="none" stroke="#123B32" stroke-width="2.5" />
                <circle cx="788" cy="112" r="24" fill="none" stroke="#D6B66C" stroke-width="1.5" stroke-dasharray="4 4" />
                <circle cx="788" cy="112" r="6" fill="#D6B66C" />
                <line x1="788" y1="80" x2="788" y2="144" stroke="#123B32" stroke-width="1.5" />
                <line x1="756" y1="112" x2="820" y2="112" stroke="#123B32" stroke-width="1.5" />
                <line x1="765" y1="89" x2="811" y2="135" stroke="#123B32" stroke-width="1.5" />
                <line x1="811" y1="89" x2="765" y2="135" stroke="#123B32" stroke-width="1.5" />
                <!-- Center Towers -->
                <rect x="832" y="110" width="20" height="60" />
                <rect x="856" y="96" width="24" height="74" />
                <rect x="896" y="102" width="22" height="68" />
                <rect x="922" y="122" width="18" height="48" />
                <rect x="960" y="114" width="20" height="58" />
            </g>
            <!-- Center Building Windows -->
            <g fill="#FFFFFF" opacity="0.88">
                <rect x="725" y="98" width="5" height="5" /><rect x="734" y="98" width="5" height="5" />
                <rect x="725" y="107" width="5" height="5" /><rect x="734" y="107" width="5" height="5" />
                <rect x="725" y="116" width="5" height="5" /><rect x="734" y="116" width="5" height="5" />
                <rect x="861" y="102" width="5" height="5" /><rect x="870" y="102" width="5" height="5" />
                <rect x="861" y="111" width="5" height="5" /><rect x="870" y="111" width="5" height="5" />
                <rect x="901" y="108" width="4" height="5" /><rect x="909" y="108" width="4" height="5" />
                <rect x="901" y="117" width="4" height="5" /><rect x="909" y="117" width="4" height="5" />
                <rect x="964" y="120" width="4" height="5" /><rect x="971" y="120" width="4" height="5" />
            </g>

            <!-- Floating Hot-Air Money Balloon in Sky -->
            <g transform="translate(925, 22)">
                <path d="M24,0 C38,0 48,11 48,25 C48,39 30,54 27,62 L21,62 C18,54 0,39 0,25 C0,11 10,0 24,0 Z" fill="#123B32" />
                <circle cx="24" cy="23" r="11" fill="#D6B66C" />
                <text x="24" y="28" text-anchor="middle" fill="#123B32" font-size="14" font-weight="800" font-family="sans-serif">$</text>
                <rect x="21" y="65" width="6" height="6" rx="1" fill="#123B32" />
            </g>

            <!-- Right-Center Lollipop Trees -->
            <g fill="#123B32">
                <circle cx="1044" cy="112" r="11" />
                <rect x="1042.5" y="120" width="3" height="28" />
                <circle cx="1064" cy="124" r="7" />
                <rect x="1063" y="129" width="2" height="20" />
                <circle cx="1115" cy="110" r="9" />
                <rect x="1114" y="117" width="2.5" height="24" />
                <circle cx="1135" cy="102" r="4" fill="#D6B66C" />
                <circle cx="1408" cy="96" r="11" />
                <rect x="1406.5" y="105" width="3" height="30" />
                <circle cx="1426" cy="112" r="7" />
                <rect x="1425" y="117" width="2" height="22" />
            </g>

            <!-- RIGHT HILL MONEY WALLET, BANKNOTES & COINS ILLUSTRATION (Replaces Right Shoe) -->
            <g transform="translate(1178, 56)">
                <!-- Fanning Dollar Bills Emerging from Wallet -->
                <g transform="rotate(-12, 60, 35)">
                    <rect x="22" y="6" width="96" height="44" rx="5" fill="#23845B" stroke="#D6B66C" stroke-width="1.8" />
                    <circle cx="70" cy="28" r="11" fill="#123B32" />
                    <text x="70" y="33" text-anchor="middle" fill="#D6B66C" font-size="13" font-weight="800" font-family="sans-serif">$</text>
                </g>
                <g transform="rotate(-3, 75, 35)">
                    <rect x="34" y="12" width="96" height="44" rx="5" fill="#1B5246" stroke="#D6B66C" stroke-width="1.8" />
                </g>
                <!-- Rounded Microfinance Wallet / Vault Body -->
                <rect x="10" y="28" width="142" height="54" rx="14" fill="#123B32" stroke="#D6B66C" stroke-width="2" />
                <path d="M18,68 Q80,82 144,66" fill="none" stroke="#FFFFFF" stroke-width="2.5" opacity="0.85" />
                <!-- Wallet Gold Clasp -->
                <rect x="116" y="45" width="36" height="20" rx="10" fill="#D6B66C" />
                <circle cx="127" cy="55" r="5" fill="#123B32" />
                <!-- Gold Coin Stack Beside Wallet -->
                <ellipse cx="170" cy="72" rx="16" ry="5.5" fill="#B89242" />
                <ellipse cx="170" cy="65" rx="16" ry="5.5" fill="#D6B66C" stroke="#123B32" stroke-width="1.2" />
                <ellipse cx="170" cy="58" rx="16" ry="5.5" fill="#E4C782" stroke="#123B32" stroke-width="1.2" />
                <text x="170" y="61" text-anchor="middle" fill="#123B32" font-size="9" font-weight="800" font-family="sans-serif">$</text>
            </g>

            <!-- Main Rolling Hills Silhouette Path Connecting Seamlessly to Footer -->
            <path d="M0,145 C110,162 210,162 320,122 C430,82 515,74 635,116 C755,158 890,165 1035,142 C1165,120 1265,105 1370,118 C1405,123 1425,132 1440,140 L1440,212 L0,212 Z" fill="#123B32" />
        </svg>
    </div>

    <!-- Main 4-Column Footer Body -->
    <footer class="fid-footer">
        <div class="fid-container">
            <div class="fid-footer-grid">
                <!-- Col 1: Newsletter -->
                <div>
                    <h5>SIMDA's Newsletter</h5>
                    <form class="fid-footway-newsletter" onsubmit="event.preventDefault(); alert('Thank you for subscribing to SIMDA updates!');">
                        <input type="email" placeholder="Your email" required>
                        <button type="submit" class="fid-footway-sub-btn">SUBSCRIBE</button>
                    </form>
                </div>

                <!-- Col 2: Terms and conditions -->
                <div>
                    <h5>Terms and conditions</h5>
                    <ul class="fid-footer-links">
                        <li>0% Riba â€” Principal only!</li>
                        <li>Friday to Saturday shift!</li>
                        <li>100% Segregated Charity Fund!</li>
                    </ul>
                    <div style="margin-top:24px;display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:700;font-size:0.84rem;color:#FFFFFF;">Follow SIMDA</span>
                        <a href="#contact" style="color:#FFFFFF;display:inline-flex;" aria-label="Website"><?= Icons::svg('globe', 18) ?></a>
                        <a href="#contact" style="color:#FFFFFF;display:inline-flex;" aria-label="Community"><?= Icons::svg('users', 18) ?></a>
                    </div>
                </div>

                <!-- Col 3: Information -->
                <div>
                    <h5>Information</h5>
                    <ul class="fid-footer-links">
                        <li><a href="#education">FAQs</a></li>
                        <li><a href="#why-us">About us at SIMDA</a></li>
                        <li><a href="#calculator">SIMDA Schedule Engine</a></li>
                    </ul>
                    <ul class="fid-footer-links" style="margin-top:20px;">
                        <li class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></li>
                        <li><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?></li>
                    </ul>
                </div>

                <!-- Col 4: Contact -->
                <div>
                    <h5>Contact</h5>
                    <ul class="fid-footer-links">
                        <li><strong style="color:#FFFFFF;">SIMDA Microfinance</strong></li>
                        <li>Institution Reg: SIMDA-SO-001</li>
                    </ul>
                    <ul class="fid-footer-links" style="margin-top:18px;">
                        <li><strong style="color:#FFFFFF;">Head office:</strong></li>
                        <li><?= Security::e($settings['institution_address'] ?? 'Mogadishu Main Boulevard, Hodan') ?></li>
                        <li>Mogadishu, Somalia</li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Row: Trust Seals (Left) + Group Info & Flags (Center) + Payment Logos (Right) -->
            <div class="fid-footer-bottom-bar">
                <!-- Left: Trust & Security Seals -->
                <div class="fid-trust-badges">
                    <div class="fid-trust-seal">
                        <div class="fid-trust-seal-circle">0%</div>
                        <div style="font-size:0.72rem;font-weight:800;line-height:1.25;color:#FFFFFF;">
                            ZERO RIBA<br><span style="color:#D6B66C;">GUARANTEED</span>
                        </div>
                    </div>
                    <div class="fid-trust-seal">
                        <div class="fid-trust-seal-circle"><?= Icons::svg('shield', 18) ?></div>
                        <div style="font-size:0.72rem;font-weight:800;line-height:1.25;color:#FFFFFF;">
                            SHARIA &amp; AUDIT<br><span style="color:#23845B;">COMPLIANT</span>
                        </div>
                    </div>
                </div>

                <!-- Center: Group Info, Language Switcher & Copyright -->
                <div style="text-align:center;font-size:0.78rem;color:rgba(244,245,240,0.82);line-height:1.6;">
                    <div>Part of SIMDA (<?= Security::e(Lang::get('app_full_name')) ?>).</div>
                    <div><?= Security::e($settings['institution_email'] ?? 'anasabdiwahidhussein@gmail.com') ?>, <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 6256534') ?></span></div>
                    <div style="display:flex;justify-content:center;gap:8px;margin:10px 0;">
                        <a href="?route=home&lang=so" style="padding:2px 8px;border-radius:4px;background:<?= $locale === 'so' ? '#D6B66C' : 'rgba(255,255,255,0.12)' ?>;color:<?= $locale === 'so' ? '#123B32' : '#FFF' ?>;font-weight:700;font-size:0.7rem;">SO</a>
                        <a href="?route=home&lang=en" style="padding:2px 8px;border-radius:4px;background:<?= $locale === 'en' ? '#D6B66C' : 'rgba(255,255,255,0.12)' ?>;color:<?= $locale === 'en' ? '#123B32' : '#FFF' ?>;font-weight:700;font-size:0.7rem;">EN</a>
                    </div>
                    <div style="font-size:0.74rem;color:rgba(244,245,240,0.68);">&copy; <?= date('Y') ?> SIMDA Microfinance. All rights reserved.</div>
                    <div style="font-size:0.78rem;color:rgba(244,245,240,0.8);margin-top:4px;">Developed by <strong style="color:#D6B66C;font-weight:800;">Anas Abdiwahid</strong></div>
                </div>

                <!-- Right: Supported Payment & Mobile Money Channels -->
                <div class="fid-payment-logos">
                    <span class="fid-pay-pill">EVC Plus</span>
                    <span class="fid-pay-pill" style="border-color:rgba(214,182,108,0.45);color:#D6B66C;">ZAAD</span>
                    <span class="fid-pay-pill">eDahab</span>
                </div>
            </div>
        </div>
    </footer>
</div>

<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>
