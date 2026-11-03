<?php
declare(strict_types=1);

use App\Helpers\Icons;
use App\Helpers\Lang;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

$locale = Lang::locale();
$isLoggedIn = AuthMiddleware::check();
$assetVer = (string) (@filemtime(BASE_PATH . '/public/assets/css/simda.css') ?: time());
?>
<!DOCTYPE html>
<html lang="<?= Security::e($locale) ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123B32">
    <title>SIMDA — <?= Security::e(Lang::get('app_full_name')) ?></title>
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

<!-- 3. OUR SERVICES — 4-COLUMN EDITORIAL CARDS -->
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
                    <p>Quick access to ethical micro-financing and solidarity group facilities (3–10 members) via EVC Plus, Zaad & eDahab.</p>
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
                    <p>Expert Sharia schedule previews, dynamic credit scoring (300–850), and Ibra early-settlement guidance.</p>
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
                        <span class="num-tabular"><?= Security::formatMoney($prod['min_amount']) ?> – <?= Security::formatMoney($prod['max_amount']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. WHY CHOOSE US — 2-COLUMN PHOTO & FEATURE BADGES -->
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
                            <p>Fixed Murabaha markup locked at origination—never compounding.</p>
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
                            <div class="fid-stars">★★★★★</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "The Murabaha cost-plus structure is clear and transparent. Every installment is known from day one."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">– Retail Trader</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>

                    <div class="fid-testi-card reveal-on-scroll">
                        <div>
                            <div class="fid-stars">★★★★★</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "The process was simple and transparent. Friday due dates automatically shift to Saturday."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">– Market Vendor</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>

                    <div class="fid-testi-card reveal-on-scroll">
                        <div>
                            <div class="fid-stars">★★★★★</div>
                            <p style="font-size:0.73rem;color:#4A5751;line-height:1.45;margin:0;">
                                "Professional, trustworthy and always ready to assist solidarity groups and small businesses."
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                            <strong style="font-size:0.74rem;color:#123B32;">– Group Leader</strong>
                            <span class="fid-contact-dot" style="background:#D6B66C;color:#123B32;"><?= Icons::svg('user-check', 12) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. FINANCIAL EDUCATION — 4 HORIZONTAL MEDIA CARDS + LIVE SCHEDULE CALCULATOR -->
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
                <span class="badge badge-gold"><?= Icons::svg('calendar', 14) ?> Friday → Saturday Auto-Shift</span>
            </div>

            <div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;">
                <div class="form-group">
                    <label>Financing Structure</label>
                    <select id="calc_product_type" class="form-control">
                        <option value="flat">Zero-Riba / Qard Hassan (Principal Only)</option>
                        <option value="murabaha">Murabaha Asset Financing</option>
                        <option value="declining_balance">Declining Balance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount Given ($ USD)</label>
                    <input type="number" id="calc_principal" class="form-control num-tabular" value="1000" min="100" max="50000" step="50">
                </div>
                <div class="form-group">
                    <label>Extra / Riba (% — 0% Default)</label>
                    <input type="number" id="calc_rate" class="form-control num-tabular" value="0.00" min="0" max="50" step="0.5">
                </div>
                <div class="form-group">
                    <label>Term & Frequency</label>
                    <div style="display:flex;gap:6px;">
                        <input type="number" id="calc_term" class="form-control num-tabular" value="5" min="1" max="36" style="width:76px;">
                        <select id="calc_frequency" class="form-control" style="flex:1;">
                            <option value="monthly">Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="margin:8px 0 14px;">
                <button type="button" id="btnPreviewSchedule" class="fid-btn-green">
                    <?= Icons::svg('calendar', 15) ?>
                    <span>Generate Exact Installment Schedule</span>
                </button>
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
                            <span class="num-tabular"><?= Security::e($settings['institution_phone'] ?? '+252 61 500 0000') ?></span>
                        </li>
                        <li>
                            <span class="fid-contact-dot"><?= Icons::svg('send', 12) ?></span>
                            <span><?= Security::e($settings['institution_email'] ?? 'info@simda.so') ?></span>
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

            <div class="fid-map-card reveal-on-scroll">
                <img src="assets/images/landing/branch-map.jpg" alt="SIMDA Branch Locations Map">
            </div>
        </div>
    </div>
</section>

<!-- 8. DEEP FOREST GREEN 4-COLUMN FOOTER -->
<footer class="fid-footer">
    <div class="fid-container">
        <div class="fid-footer-grid">
            <div>
                <div style="display:inline-flex;background:#FFF;padding:6px 12px;border-radius:10px;margin-bottom:12px;">
                    <img src="assets/images/simda-logo.png" alt="SIMDA" style="height:26px;">
                </div>
                <p style="font-size:0.8rem;line-height:1.6;color:rgba(244,245,240,0.8);max-width:260px;">
                    Helping communities grow, one ethical opportunity at a time. <?= Security::e(Lang::get('tagline')) ?>
                </p>
            </div>

            <div>
                <h5>Quick Links</h5>
                <ul class="fid-footer-links">
                    <li><a href="#why-us">About Us</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#our-process">Our Process</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#education">FAQ & Education</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div>
                <h5>Legal & Governance</h5>
                <ul class="fid-footer-links">
                    <li><a href="?route=login">Staff & Client Portal</a></li>
                    <li><a href="#calculator">Schedule Simulator</a></li>
                    <li><a href="#why-us">Sharia Governance Note</a></li>
                    <li><a href="#contact">Terms & Conditions</a></li>
                </ul>
            </div>

            <div>
                <h5>Newsletter</h5>
                <p style="font-size:0.78rem;color:rgba(244,245,240,0.78);margin-bottom:8px;">
                    Subscribe to get ethical financial tips and institutional updates.
                </p>
                <form class="fid-newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing to SIMDA updates.');">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" aria-label="Subscribe"><?= Icons::svg('arrow-right', 14) ?></button>
                </form>
                <div style="margin-top:10px;display:flex;gap:8px;align-items:center;">
                    <a href="?route=home&lang=en" class="badge <?= $locale === 'en' ? 'badge-gold' : '' ?>" style="color:#FFF;">EN</a>
                    <a href="?route=home&lang=so" class="badge <?= $locale === 'so' ? 'badge-gold' : '' ?>" style="color:#FFF;">SO</a>
                </div>
            </div>
        </div>

        <div style="padding-top:18px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;font-size:0.75rem;color:rgba(244,245,240,0.68);">
            <div>&copy; <?= date('Y') ?> SIMDA (<?= Security::e(Lang::get('app_full_name')) ?>). All Rights Reserved.</div>
            <div style="max-width:580px;"><?= Security::e(Lang::get('sharia_disclaimer')) ?></div>
        </div>
    </div>
</footer>

<script src="assets/js/simda.js?v=<?= Security::e($assetVer) ?>"></script>
</body>
</html>
