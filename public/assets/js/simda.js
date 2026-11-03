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

    document.addEventListener('DOMContentLoaded', function () {
        const themeSelect = document.getElementById('simdaThemeSelect');
        if (themeSelect) {
            themeSelect.value = localStorage.getItem(THEME_KEY) || 'light';
            themeSelect.addEventListener('change', function () {
                applyTheme(this.value);
            });
        }

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

        // 3. Financial Action Confirmation & Double-Submission Prevention
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (document.body.classList.contains('is-offline')) {
                    e.preventDefault();
                    alert('Offline Mode: Financial operations cannot be submitted without an active server connection.');
                    return;
                }
                const msg = form.getAttribute('data-confirm') || 'Confirm this financial operation?';
                if (!window.confirm(msg)) {
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
            calcBtn.addEventListener('click', runSchedulePreview);
            ['calc_principal', 'calc_term', 'calc_frequency', 'calc_product_type', 'calc_rate'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', runSchedulePreview);
                    el.addEventListener('change', runSchedulePreview);
                    el.addEventListener('keyup', runSchedulePreview);
                }
            });
            document.querySelectorAll('.calc-preset-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const amtInput = document.getElementById('calc_principal');
                    if (amtInput) {
                        amtInput.value = btn.getAttribute('data-amount') || '1000';
                        runSchedulePreview();
                    }
                });
            });
            // Run initial preview on load
            runSchedulePreview();
        }

        // 5. Online / Offline Connection Status Monitoring
        function updateConnectionState() {
            if (navigator.onLine) {
                document.body.classList.remove('is-offline');
            } else {
                document.body.classList.add('is-offline');
            }
        }
        window.addEventListener('online', updateConnectionState);
        window.addEventListener('offline', updateConnectionState);
        updateConnectionState();

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

    function computeInstantSchedule(principalVal, termVal, freqVal) {
        const principalNum = Math.max(0, parseFloat(principalVal) || 0);
        const term = Math.max(1, Math.min(60, parseInt(termVal, 10) || 1));
        const principalCents = Math.round(principalNum * 100);
        const regularCents = Math.floor(principalCents / term);
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
            const instCents = (i < term) ? regularCents : (principalCents - accumCents);
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
            total_profit: '0.00',
            total_due: (principalCents / 100).toFixed(2),
            installments: installments
        };
    }

    function renderScheduleHtml(s, outBox) {
        let rowsHtml = '';
        const maxRows = Math.min(s.installments.length, 36);
        for (let i = 0; i < maxRows; i++) {
            const inst = s.installments[i];
            const shiftBadge = inst.was_friday_shifted
                ? ' <span class="badge badge-gold" title="Shifted from Friday (' + inst.original_due_date + ') to Saturday">Sat Shift</span>'
                : '';
            rowsHtml += '<tr>' +
                '<td class="num-tabular">#' + inst.installment_number + '</td>' +
                '<td class="num-tabular">' + inst.due_date + shiftBadge + '</td>' +
                '<td class="num-tabular">$' + inst.principal_due + '</td>' +
                '<td class="num-tabular">$' + inst.profit_due + '</td>' +
                '<td class="num-tabular"><strong>$' + inst.total_due + '</strong></td>' +
                '<td class="num-tabular">$' + inst.principal_balance_after + '</td>' +
                '</tr>';
        }

        outBox.innerHTML =
            '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px;">' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">Amount Given (Principal)</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:var(--text-primary);">$' + s.principal + '</div>' +
                '</div>' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">Extra / Riba (0%)</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:#23845B;">$' + s.total_profit + '</div>' +
                '</div>' +
                '<div style="background:var(--bg-surface);padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);">' +
                    '<div style="font-size:0.74rem;color:var(--text-secondary);">Total to Repay (Exact Same Amount)</div>' +
                    '<div class="num-tabular" style="font-size:1.15rem;font-weight:800;color:var(--text-primary);">$' + s.total_due + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="table-responsive" style="max-height:280px;overflow-y:auto;">' +
                '<table class="simda-table" style="font-size:0.8rem;">' +
                    '<thead><tr><th>#</th><th>Due Date</th><th>Principal</th><th>Extra</th><th>Installment</th><th>Remaining Balance</th></tr></thead>' +
                    '<tbody>' + rowsHtml + '</tbody>' +
                '</table>' +
            '</div>';
    }

    async function runSchedulePreview() {
        const typeEl = document.getElementById('calc_product_type');
        const amtEl = document.getElementById('calc_principal');
        const rateEl = document.getElementById('calc_rate');
        const termEl = document.getElementById('calc_term');
        const freqEl = document.getElementById('calc_frequency');
        const outBox = document.getElementById('schedulePreviewContainer');

        if (!typeEl || !amtEl || !termEl || !freqEl || !outBox) {
            return;
        }

        // 1. Instant 0ms update as the user types 1000, 3000, 5000, etc.
        const instantSchedule = computeInstantSchedule(amtEl.value, termEl.value, freqEl.value);
        renderScheduleHtml(instantSchedule, outBox);

        // 2. Also sync with server ScheduleEngine
        const params = new URLSearchParams({
            route: 'api_schedule_preview',
            product_type: typeEl.value,
            principal: amtEl.value || '0',
            rate: '0',
            term: termEl.value || '1',
            frequency: freqEl.value
        });

        try {
            const resp = await fetch('?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) {
                return;
            }
            const data = await resp.json();
            if (data && data.schedule) {
                renderScheduleHtml(data.schedule, outBox);
            }
        } catch (err) {
            // Instant client-side schedule is already rendered
        }
    }
})();

