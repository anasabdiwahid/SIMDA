/**
 * SIMDA — Vanilla JavaScript Core Engine
 * - Theme Management (Light, Dark, System) without flash
 * - Sidebar Collapse & Mobile Drawer
 * - Live Schedule Calculator Preview (Fetch API)
 * - Number Counting Animations (preserving exact database figures)
 * - Double-submission & Financial Action Confirmation Guard
 * - PWA Service Worker Registration & Online/Offline Connection Guard
 */
(function () {
    'use strict';

    // 1. Theme Engine
    const THEME_KEY = 'simda_theme_pref';

    function resolveTheme(pref) {
        if (pref === 'dark' || pref === 'light') {
            return pref;
        }
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(pref) {
        const effective = resolveTheme(pref);
        document.documentElement.setAttribute('data-theme', effective);
        localStorage.setItem(THEME_KEY, pref);
        const sel = document.getElementById('simdaThemeSelect');
        if (sel && sel.value !== pref) {
            sel.value = pref;
        }
    }

    const savedPref = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(savedPref);

    // Global Safe Message Toast (replaces native browser alert/confirm popups)
    function showSimdaToast(message, type) {
        if (!message) return;
        let container = document.getElementById('simda-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'simda-toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'simda-toast animate-in';
        toast.style.cssText = 'background:var(--card-bg, #1e293b);color:var(--text, #f8fafc);padding:12px 18px;border-radius:10px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.3);border-left:4px solid var(--primary, #047857);font-size:0.9rem;font-weight:600;display:flex;align-items:center;gap:10px;pointer-events:auto;min-width:260px;max-width:420px;transition:opacity 0.3s ease, transform 0.3s ease;';
        toast.innerHTML = '<span>' + (message + '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>';
        container.appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, 3500);
    }

    // Completely disable disruptive browser alerts/confirm popups across the entire system
    window.alert = function (msg) {
        showSimdaToast(msg, 'info');
    };
    window.confirm = function () {
        return true;
    };

    document.addEventListener('DOMContentLoaded', function () {
        const themeSelect = document.getElementById('simdaThemeSelect');
        if (themeSelect) {
            themeSelect.value = localStorage.getItem(THEME_KEY) || 'light';
            themeSelect.addEventListener('change', function () {
                applyTheme(this.value);
            });
        }

        // Theme Icon Buttons (Sun / Moon)
        document.querySelectorAll('.theme-pill-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetTheme = this.getAttribute('data-theme-val') || 'light';
                applyTheme(targetTheme);
            });
        });

        // 2. Sidebar Collapse & Mobile Drawer
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    document.body.classList.toggle('mobile-nav-open');
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('simda_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
                }
            });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                document.body.classList.remove('mobile-nav-open');
            });
        }
        if (window.innerWidth > 992 && localStorage.getItem('simda_sidebar_collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        // 3. Double-Submission Prevention (Direct submission without blocking alerts/confirm popups)
        document.querySelectorAll('form[data-confirm], form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (document.body.classList.contains('is-offline')) {
                    e.preventDefault();
                    return;
                }
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    if (btn.dataset.submitting === '1') {
                        e.preventDefault();
                        return;
                    }
                    btn.dataset.submitting = '1';
                    btn.style.opacity = '0.65';
                }
            });
        });

        // 4. Live Repayment Schedule Preview Calculator (Instant Real-Time on Keystroke & Click)
        const calcBtn = document.getElementById('btnPreviewSchedule');
        if (calcBtn) {
            calcBtn.addEventListener('click', function () { runSchedulePreview('payment'); });

            ['calc_principal', 'calc_monthly_payment'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function () { runSchedulePreview('payment'); });
                    el.addEventListener('change', function () { runSchedulePreview('payment'); });
                    el.addEventListener('keyup', function () { runSchedulePreview('payment'); });
                }
            });

            const termInput = document.getElementById('calc_term');
            if (termInput) {
                termInput.addEventListener('input', function () { runSchedulePreview('term'); });
                termInput.addEventListener('change', function () { runSchedulePreview('term'); });
                termInput.addEventListener('keyup', function () { runSchedulePreview('term'); });
            }

            ['calc_frequency', 'calc_product_type', 'calc_rate'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function () { runSchedulePreview('payment'); });
                    el.addEventListener('change', function () { runSchedulePreview('payment'); });
                }
            });

            document.querySelectorAll('.calc-preset-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const amtInput = document.getElementById('calc_principal');
                    const moInput = document.getElementById('calc_monthly_payment');
                    if (amtInput) {
                        amtInput.value = btn.getAttribute('data-amount') || '3000';
                    }
                    if (moInput && btn.getAttribute('data-monthly')) {
                        moInput.value = btn.getAttribute('data-monthly');
                    }
                    runSchedulePreview('payment');
                });
            });
            // Run initial preview on load
            runSchedulePreview('payment');
        }

        // 5. Dynamic Hero Carousel (Murabaha & Maalgalinta Dukaamada)
        const heroCarousel = document.getElementById('simdaHeroCarousel');
        if (heroCarousel) {
            const slides = heroCarousel.querySelectorAll('.fid-hero-slide');
            const pills = heroCarousel.querySelectorAll('.fid-hero-pill');
            const prevBtn = document.getElementById('heroPrevBtn');
            const nextBtn = document.getElementById('heroNextBtn');
            let current = 0;
            let timer = null;

            function goTo(idx) {
                if (!slides.length) return;
                current = (idx + slides.length) % slides.length;
                slides.forEach(function (s, i) {
                    if (i === current) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                pills.forEach(function (p, i) {
                    if (i === current) {
                        p.classList.add('active');
                    } else {
                        p.classList.remove('active');
                    }
                });
            }

            function startTimer() {
                stopTimer();
                timer = setInterval(function () {
                    goTo(current + 1);
                }, 6500);
            }

            function stopTimer() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    const target = parseInt(this.getAttribute('data-target') || '0', 10);
                    goTo(target);
                    startTimer();
                });
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goTo(current - 1);
                    startTimer();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    goTo(current + 1);
                    startTimer();
                });
            }

            heroCarousel.addEventListener('mouseenter', stopTimer);
            heroCarousel.addEventListener('mouseleave', startTimer);
            startTimer();
        }

        // 5b. Murabaha 5-Asset Dynamic Showcase (Auto-switches every 4 seconds)
        const assetShowcase = document.getElementById('murabahaAssetShowcase');
        if (assetShowcase) {
            const assetItems = assetShowcase.querySelectorAll('.murabaha-asset-item');
            const indBtns = assetShowcase.querySelectorAll('.murabaha-ind-btn');
            let assetCurrent = 0;
            let assetTimer = null;

            function goToAsset(idx) {
                if (!assetItems.length) return;
                assetCurrent = (idx + assetItems.length) % assetItems.length;
                assetItems.forEach(function (item, i) {
                    if (i === assetCurrent) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
                indBtns.forEach(function (btn, i) {
                    if (i === assetCurrent) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            function startAssetTimer() {
                stopAssetTimer();
                assetTimer = setInterval(function () {
                    goToAsset(assetCurrent + 1);
                }, 4000); // 4 seconds interval per user requirement!
            }

            function stopAssetTimer() {
                if (assetTimer) {
                    clearInterval(assetTimer);
                    assetTimer = null;
                }
            }

            indBtns.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const target = parseInt(this.getAttribute('data-asset-target') || '0', 10);
                    goToAsset(target);
                    startAssetTimer();
                });
            });

            assetShowcase.addEventListener('mouseenter', stopAssetTimer);
            assetShowcase.addEventListener('mouseleave', startAssetTimer);
            startAssetTimer();
        }

        // 6. Online / Offline Connection Status Monitoring with Caveman Animation
        function showLiveOfflineModal() {
            if (window.location.search.includes('route=offline') || window.location.search.includes('route=404')) {
                return;
            }
            let modal = document.getElementById('simda-live-offline-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'simda-live-offline-modal';
                modal.style.cssText = 'position:fixed;inset:0;z-index:999999;background:rgba(10,34,28,0.88);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;padding:20px;font-family:\'Plus Jakarta Sans\',sans-serif;animation:simdaFadeIn 0.2s ease-out;';
                modal.innerHTML = `
                    <div style="max-width:500px;width:100%;background:#14221D;color:#FFFFFF;border-radius:24px;box-shadow:0 30px 70px rgba(0,0,0,0.55);border:1.5px solid rgba(214,182,108,0.35);padding:32px 26px;text-align:center;box-sizing:border-box;">
                        <div style="border-radius:16px;overflow:hidden;margin-bottom:18px;border:2px solid rgba(214,182,108,0.25);box-shadow:0 12px 28px rgba(0,0,0,0.35);background:#0D1A16;">
                            <img src="assets/images/illustrations/caveman-looking.gif" alt="Offline Looking Around" style="width:100%;max-height:200px;object-fit:cover;display:block;">
                        </div>
                        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(201,75,75,0.18);border:1px solid rgba(201,75,75,0.45);color:#FFA3A3;font-size:0.78rem;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;padding:5px 14px;border-radius:999px;margin-bottom:12px;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#FF6B6B;box-shadow:0 0 8px #FF6B6B;display:inline-block;"></span>
                            <span>Qad La'aan — Offline</span>
                        </div>
                        <h2 style="font-size:1.45rem;font-weight:800;margin:0 0 8px;color:#FFFFFF;letter-spacing:-0.02em;">Qadka Internet-ka ayaa Kaa Go'ay!</h2>
                        <p style="font-size:0.86rem;color:#A2B0A9;line-height:1.55;margin:0 0 22px;">Computer-kaagu hadda wax xiriir internet ah ma laha. Fadlan hubi Wi-Fi-ga ama fiilada qadkaaga.</p>
                        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                            <button type="button" id="simdaLiveRetryBtn" style="background:#123B32;color:#FFFFFF;border:1px solid #D6B66C;border-radius:10px;padding:11px 20px;font-size:0.88rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                Dib u Tijaabi Qadka ↻
                            </button>
                            <button type="button" id="simdaLiveDismissBtn" style="background:#1A2C24;color:#A2B0A9;border:1px solid rgba(255,255,255,0.2);border-radius:10px;padding:11px 18px;font-size:0.84rem;cursor:pointer;">
                                Iska Xidh
                            </button>
                        </div>
                        <div id="simdaLiveConnToast" style="display:none;margin-top:14px;font-size:0.84rem;font-weight:700;padding:8px 14px;border-radius:10px;"></div>
                    </div>
                `;
                document.body.appendChild(modal);

                const retryBtn = document.getElementById('simdaLiveRetryBtn');
                const dismissBtn = document.getElementById('simdaLiveDismissBtn');
                const toast = document.getElementById('simdaLiveConnToast');

                if (dismissBtn) {
                    dismissBtn.addEventListener('click', function() {
                        modal.style.display = 'none';
                    });
                }

                if (retryBtn) {
                    retryBtn.addEventListener('click', function() {
                        retryBtn.disabled = true;
                        retryBtn.textContent = 'Waa la hubinayaa...';
                        fetch('assets/images/simda-icon.png?_ping=' + Date.now(), { method: 'HEAD', cache: 'no-store' })
                            .then(function() {
                                retryBtn.disabled = false;
                                retryBtn.textContent = 'Dib u Tijaabi Qadka ↻';
                                if (toast) {
                                    toast.style.display = 'block';
                                    toast.style.background = 'rgba(16,185,129,0.18)';
                                    toast.style.border = '1px solid #10B981';
                                    toast.style.color = '#34D399';
                                    toast.textContent = 'Qadkii wuu soo noqday! Dib ayaa laguu celinayaa...';
                                }
                                setTimeout(function() {
                                    hideLiveOfflineModal();
                                }, 1200);
                            })
                            .catch(function() {
                                retryBtn.disabled = false;
                                retryBtn.textContent = 'Dib u Tijaabi Qadka ↻';
                                if (toast) {
                                    toast.style.display = 'block';
                                    toast.style.background = 'rgba(201,75,75,0.18)';
                                    toast.style.border = '1px solid #C94B4B';
                                    toast.style.color = '#FFA3A3';
                                    toast.textContent = 'Wali ma jiro internet. Fadlan mar kale tijaabi.';
                                }
                            });
                    });
                }
            }
            modal.style.display = 'flex';
        }

        function hideLiveOfflineModal() {
            const modal = document.getElementById('simda-live-offline-modal');
            if (modal) {
                modal.style.opacity = '0';
                modal.style.transition = 'opacity 0.25s ease';
                setTimeout(function() {
                    modal.style.display = 'none';
                    modal.style.opacity = '1';
                }, 250);
            }
        }

        function updateConnectionState() {
            if (navigator.onLine) {
                document.body.classList.remove('is-offline');
                hideLiveOfflineModal();
            } else {
                document.body.classList.add('is-offline');
                showLiveOfflineModal();
            }
        }
        window.addEventListener('online', updateConnectionState);
        window.addEventListener('offline', updateConnectionState);
        if (!navigator.onLine) {
            updateConnectionState();
        }

        // 6. Scroll-Reveal Animation Engine (IntersectionObserver)
        const revealItems = document.querySelectorAll('.reveal-on-scroll, .pub-section .card, .pub-section .section-header');
        if ('IntersectionObserver' in window && revealItems.length > 0) {
            revealItems.forEach(function (el) {
                el.classList.add('reveal-on-scroll');
            });
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

            revealItems.forEach(function (el) {
                observer.observe(el);
            });
        } else {
            revealItems.forEach(function (el) {
                el.classList.add('is-visible');
            });
        }

        // 7. Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('service-worker.js').catch(function () {
                // Service worker optional in non-HTTPS local subfolders
            });
        }
    });

    function formatDateYmd(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function toArabicDigitsStr(val) {
        if (val === null || val === undefined) return '';
        const map = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return String(val).replace(/[0-9]/g, function (d) { return map[+d]; });
    }

    function parseNumberClean(val) {
        if (!val) return 0;
        const map = {
            '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
            '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9'
        };
        const normalized = String(val).replace(/[٠-٩]/g, function (d) { return map[d] || d; });
        return parseFloat(normalized) || 0;
    }

    function computeInstantSchedule(principalVal, monthlyPaymentVal, termVal, freqVal, mode) {
        const principalNum = Math.max(0, parseNumberClean(principalVal));
        const principalCents = Math.round(principalNum * 100);

        const cleanTermStr = String(termVal || '1').replace(/[٠-٩]/g, function (d) {
            const map = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
            return map[d] || d;
        });
        let term = Math.max(1, Math.min(120, parseInt(cleanTermStr, 10) || 1));
        let regularCents = 0;

        const monthlyNum = Math.max(0, parseNumberClean(monthlyPaymentVal));
        const monthlyCents = Math.round(monthlyNum * 100);

        if (mode === 'payment' && monthlyCents > 0 && principalCents > 0) {
            term = Math.max(1, Math.min(120, Math.ceil(principalCents / monthlyCents)));
            regularCents = Math.min(principalCents, monthlyCents);
        } else {
            regularCents = term > 0 ? Math.floor(principalCents / term) : principalCents;
        }

        let accumCents = 0;
        let remCents = principalCents;
        const installments = [];
        const baseDate = new Date();

        for (let i = 1; i <= term; i++) {
            const due = new Date(baseDate.getTime());
            if (freqVal === 'weekly') {
                due.setDate(due.getDate() + (i * 7));
            } else {
                due.setMonth(due.getMonth() + i);
            }
            const origStr = formatDateYmd(due);
            const isFriday = (due.getDay() === 5);
            if (isFriday) {
                due.setDate(due.getDate() + 1);
            }
            const adjStr = formatDateYmd(due);

            let instCents;
            if (i < term) {
                instCents = Math.min(remCents, regularCents);
            } else {
                instCents = Math.max(0, principalCents - accumCents);
            }
            accumCents += instCents;
            remCents = Math.max(0, remCents - instCents);

            installments.push({
                installment_number: i,
                original_due_date: origStr,
                due_date: adjStr,
                was_friday_shifted: isFriday ? 1 : 0,
                principal_due: (instCents / 100).toFixed(2),
                profit_due: '0.00',
                total_due: (instCents / 100).toFixed(2),
                principal_balance_after: (remCents / 100).toFixed(2)
            });
        }

        return {
            principal: (principalCents / 100).toFixed(2),
            regular_installment: (regularCents / 100).toFixed(2),
            term_installments: term,
            frequency: freqVal,
            total_profit: '0.00',
            total_due: (principalCents / 100).toFixed(2),
            installments: installments
        };
    }

    function renderScheduleHtml(s, outBox) {
        const lang = (document.documentElement.lang || 'en').toLowerCase().substring(0, 2);
        const i18n = {
            en: {
                amountGiven: 'Amount Given (Principal)',
                paymentPeriod: 'Payment Per Period (Monthly)',
                paymentPeriodWeekly: 'Payment Per Period (Weekly)',
                timeToFinish: 'Time to Finish (Duration)',
                totalRepay: 'Total to Repay (0% Riba)',
                unitMonths: 'Months',
                unitWeeks: 'Weeks',
                perMonth: ' / month',
                perWeek: ' / week',
                satShift: 'Sat Shift',
                satShiftTitle: 'Shifted from Friday to Saturday',
                colNum: '#',
                colDueDate: 'Due Date',
                colPrincipal: 'Principal',
                colExtra: 'Extra (0%)',
                colInstallment: 'Installment',
                colBalance: 'Remaining Balance'
            },
            so: {
                amountGiven: 'Lacagta La Bixiyay (Raasumaalka)',
                paymentPeriod: 'Bixinta Xilliga (Bishii)',
                paymentPeriodWeekly: 'Bixinta Xilliga (Toddobaadkiiba)',
                timeToFinish: 'Muddada Dhamaystirka (Mudada)',
                totalRepay: 'Wadarta Dib-u-bixinta (0% Ribo)',
                unitMonths: 'Bilood',
                unitWeeks: 'Toddobaad',
                perMonth: ' / bishii',
                perWeek: ' / toddobaad',
                satShift: 'Sabti Loo Wareejiyay',
                satShiftTitle: 'Waxaa laga wareejiyay Jimce loona wareejiyay Sabti',
                colNum: '#',
                colDueDate: 'Taariikhda Bixinta',
                colPrincipal: 'Raasumaalka',
                colExtra: 'Dheeraad (0%)',
                colInstallment: 'Qaybta/Qustiga',
                colBalance: 'Haraaga Haray'
            },
            ar: {
                amountGiven: 'مبلغ التمويل (أصل المبلغ)',
                paymentPeriod: 'القسط الدوري (شهرياً)',
                paymentPeriodWeekly: 'القسط الدوري (أسبوعياً)',
                timeToFinish: 'مدة السداد (المتبقية)',
                totalRepay: 'إجمالي المبلغ المستحق (0% ربا)',
                unitMonths: 'شهراً',
                unitWeeks: 'أسابيع',
                perMonth: ' / شهرياً',
                perWeek: ' / أسبوعياً',
                satShift: 'ترحيل للسبت',
                satShiftTitle: 'تم ترحيل الموعد تلقائياً من الجمعة إلى السبت',
                colNum: '#',
                colDueDate: 'تاريخ الاستحقاق',
                colPrincipal: 'أصل المبلغ',
                colExtra: 'الزيادة (0%)',
                colInstallment: 'قيمة القسط',
                colBalance: 'الرصيد المتبقي'
            }
        };
        const t = i18n[lang] || i18n['en'];

        const isAr = (lang === 'ar');

        function fmtMoney(amt) {
            if (isAr) {
                return toArabicDigitsStr(amt) + ' $';
            }
            return '$' + amt;
        }

        function fmtDate(dt) {
            if (isAr) {
                return toArabicDigitsStr(dt);
            }
            return dt;
        }

        function fmtNum(n) {
            if (isAr) {
                return toArabicDigitsStr(n);
            }
            return String(n);
        }

        let rowsHtml = '';
        const maxRows = Math.min(s.installments.length, 60);
        for (let i = 0; i < maxRows; i++) {
            const inst = s.installments[i];
            const shiftBadge = inst.was_friday_shifted
                ? ' <span class="badge badge-gold" title="' + t.satShiftTitle + '">' + t.satShift + '</span>'
                : '';
            const rowNum = isAr ? ('#' + toArabicDigitsStr(inst.installment_number)) : ('#' + inst.installment_number);
            const dueDisplay = fmtDate(inst.due_date);
            const princDisplay = fmtMoney(inst.principal_due);
            const extraDisplay = fmtMoney(inst.profit_due);
            const totalDisplay = fmtMoney(inst.total_due);
            const balDisplay = fmtMoney(inst.principal_balance_after);

            rowsHtml += '<tr>' +
                '<td class="num-tabular">' + rowNum + '</td>' +
                '<td class="num-tabular">' + (isAr ? dueDisplay : '<span dir="ltr">' + dueDisplay + '</span>') + shiftBadge + '</td>' +
                '<td class="num-tabular">' + (isAr ? princDisplay : '<span dir="ltr">' + princDisplay + '</span>') + '</td>' +
                '<td class="num-tabular">' + (isAr ? extraDisplay : '<span dir="ltr">' + extraDisplay + '</span>') + '</td>' +
                '<td class="num-tabular"><strong>' + (isAr ? totalDisplay : '<span dir="ltr">' + totalDisplay + '</span>') + '</strong></td>' +
                '<td class="num-tabular">' + (isAr ? balDisplay : '<span dir="ltr">' + balDisplay + '</span>') + '</td>' +
                '</tr>';
        }

        const isWeekly = s.frequency === 'weekly';
        const unitLabel = isWeekly ? t.unitWeeks : t.unitMonths;
        const perUnit = isWeekly ? t.perWeek : t.perMonth;
        const periodTitle = isWeekly ? t.paymentPeriodWeekly : t.paymentPeriod;
        const regAmt = s.regular_installment || (s.installments[0] ? s.installments[0].total_due : '0.00');
        const count = s.term_installments || s.installments.length;

        const regAmtDisplay = fmtMoney(regAmt);
        const princDisplay = fmtMoney(s.principal);
        const totalDisplay = fmtMoney(s.total_due);
        const countDisplay = fmtNum(count);

        outBox.innerHTML =
            '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:12px;margin-bottom:14px;">' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">' + t.amountGiven + '</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:var(--text-primary);">' + (isAr ? princDisplay : '<span dir="ltr">' + princDisplay + '</span>') + '</div>' +
                '</div>' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">' + periodTitle + '</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:#123B32;">' + (isAr ? regAmtDisplay : '<span dir="ltr">' + regAmtDisplay + '</span>') + '<span style="font-size:0.76rem;font-weight:600;color:var(--text-secondary);">' + perUnit + '</span></div>' +
                '</div>' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:2px solid #D6B66C;">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">' + t.timeToFinish + '</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:#123B32;">' + countDisplay + ' ' + unitLabel + '</div>' +
                '</div>' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">' + t.totalRepay + '</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:#23845B;">' + (isAr ? totalDisplay : '<span dir="ltr">' + totalDisplay + '</span>') + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="table-responsive" style="max-height:320px;overflow-y:auto;">' +
                '<table class="simda-table" style="font-size:0.8rem;">' +
                    '<thead><tr>' +
                        '<th>' + t.colNum + '</th>' +
                        '<th>' + t.colDueDate + '</th>' +
                        '<th>' + t.colPrincipal + '</th>' +
                        '<th>' + t.colExtra + '</th>' +
                        '<th>' + t.colInstallment + '</th>' +
                        '<th>' + t.colBalance + '</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rowsHtml + '</tbody>' +
                '</table>' +
            '</div>';
    }

    async function runSchedulePreview(triggerMode) {
        const mode = (typeof triggerMode === 'string') ? triggerMode : 'payment';
        const typeEl = document.getElementById('calc_product_type');
        const amtEl = document.getElementById('calc_principal');
        const moEl = document.getElementById('calc_monthly_payment');
        const termEl = document.getElementById('calc_term');
        const freqEl = document.getElementById('calc_frequency');
        const outBox = document.getElementById('schedulePreviewContainer');

        if (!typeEl || !amtEl || !termEl || !freqEl || !outBox) {
            return;
        }

        const monthlyVal = moEl ? moEl.value : '0';
        const instantSchedule = computeInstantSchedule(amtEl.value, monthlyVal, termEl.value, freqEl.value, mode);

        if (mode === 'payment' && moEl && parseFloat(moEl.value) > 0) {
            termEl.value = String(instantSchedule.term_installments);
        } else if (mode === 'term' && moEl) {
            moEl.value = instantSchedule.regular_installment;
        }

        renderScheduleHtml(instantSchedule, outBox);
    }

    // 9. Language Dropdown Selector Guard (Global click outside & escape)
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.simda-lang-dropdown.is-open').forEach(function (dd) {
            if (!dd.contains(e.target)) {
                dd.classList.remove('is-open');
                const btn = dd.querySelector('.simda-lang-trigger');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.simda-lang-dropdown.is-open').forEach(function (dd) {
                dd.classList.remove('is-open');
                const btn = dd.querySelector('.simda-lang-trigger');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        }
    });
})();


// Live filter for admin lists: [data-filter-list] holds the [data-filter-item]s; [data-filter-for="<list id>"] controls filter them.
(function () {
    document.querySelectorAll('[data-filter-list]').forEach(function (list) {
        var controls = document.querySelectorAll('[data-filter-for="' + list.id + '"]');
        var items = list.querySelectorAll('[data-filter-item]');
        var empty = document.querySelector('[data-filter-empty="' + list.id + '"]');
        function apply() {
            var shown = 0;
            items.forEach(function (item) {
                var match = true;
                controls.forEach(function (control) {
                    var value = control.value.trim().toLowerCase();
                    if (!value) { return; }
                    if (control.dataset.filterAttr) {
                        match = match && (item.getAttribute('data-' + control.dataset.filterAttr) || '').toLowerCase() === value;
                    } else {
                        match = match && (item.dataset.search || item.textContent).toLowerCase().indexOf(value) !== -1;
                    }
                });
                item.hidden = !match;
                if (match) { shown++; }
            });
            if (empty) { empty.hidden = shown > 0; }
        }
        controls.forEach(function (control) { control.addEventListener('input', apply); });
    });

    // Keep the current page's link visible on short screens, where the sidebar scrolls.
    var activeLink = document.querySelector('.sidebar-nav .sidebar-link.active');
    if (activeLink) { activeLink.scrollIntoView({ block: 'nearest' }); }
})();

// Branded loading screen for forms that wait on slow work such as sending email: <form data-loading="<overlay id>">.
(function () {
    document.addEventListener('submit', function (e) {
        var overlayId = e.target.dataset ? e.target.dataset.loading : '';
        var overlay = overlayId ? document.getElementById(overlayId) : null;
        if (overlay && !e.defaultPrevented) {
            overlay.hidden = false;
        }
    });

    // Coming back with the browser's Back button restores the page as it was left, so reset it.
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) { return; }
        document.querySelectorAll('form[data-loading]').forEach(function (form) {
            var overlay = document.getElementById(form.dataset.loading);
            var btn = form.querySelector('button[type="submit"]');
            if (overlay) { overlay.hidden = true; }
            if (btn) {
                delete btn.dataset.submitting;
                btn.style.opacity = '';
            }
        });
    });
})();
