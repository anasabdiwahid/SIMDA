# SIMDA — Somali Islamic Microfinance Development Association
## Islamic Microfinance Loan Management System
**Tagline:** *Building Trust. Empowering Growth.*

![SIMDA Logo](public/assets/images/simda-logo.png)

---

## 1. Project Description & Problem Statement

**SIMDA** is a centralized, auditable, bilingual (Somali & English) financial management platform engineered for a Somali Islamic microfinance institution providing Sharia-compliant financing.

### Problem Statement
Traditional microfinance operations tracked via fragmented spreadsheets suffer from floating-point rounding drift, lack of branch-level access boundaries, inconsistent late-charge handling, and unverified approval workflows. SIMDA replaces manual spreadsheets with a transaction-safe, role-governed platform that manages the entire financing lifecycle from client onboarding to final PDF statement generation.

> **Sharia Governance Notice:** SIMDA implements the specified Islamic financing calculation and segregated charity late-charge rules. It does not claim formal Sharia board certification; any production deployment requires review and endorsement by a qualified Sharia supervisory adviser.

---

## 2. Complete Feature Matrix (13 Core Functional Modules)

All 13 required features are **Done**. Status per feature and per baseline requirement:

| # | Feature (brief) | Status |
|---|---|---|
| 1 | Client profile (unique ID & phone, guarantor required, Admin moves branches) | ✅ Done |
| 2 | Product setup (in-use products deactivate only, rate changes only affect new applications) | ✅ Done |
| 3 | Financing application (limits, >30-day-late block, schedule preview, drafts) | ✅ Done |
| 4 | Approval workflow (officer ≤ $5,000, Branch Manager > $5,000, creator never approves, logged, one resubmission) | ✅ Done |
| 5 | Schedule generation (3 product types, exact rounding, Friday → Saturday) | ✅ Done |
| 6 | Disbursement (loan officer, mock mobile money, receipt, net = approved − fee, 30-day expiry) | ✅ Done |
| 7 | Repayments (charity first, then oldest installment; partial; Ibra; overpayment credit) | ✅ Done |
| 8 | Late charges to charity (7 days, waiver with reason, charity balance and its use in a report) | ✅ Done |
| 9 | Credit score 300–850 from 600, formula on screen, reason for last change | ✅ Done |
| 10 | Group financing (3–10 members, leader, >30-day lock, leaving needs no active financing) | ✅ Done |
| 11 | Reminders (3 days before, day after a missed date, once per type, none when paid) | ✅ Done |
| 12 | PAR30 per branch and institution, monthly trend chart, 0% for empty branches | ✅ Done |
| 13 | Client statement PDF ("Fully repaid" on closed financings) | ✅ Done |
| B | Baseline: layered architecture, roles & audit log, relational DB, 25 tests + CI, docs, security, 2+ charts, Somali & English, simulated payments/SMS | ✅ Done |

1. **Module 1 — Client Profile & Guarantor Management:** Unique ID and phone enforcement, business profile, photo upload with strict MIME verification, mandatory Guarantor requirement, and Admin-only branch transfers.
2. **Module 2 — Financing Product Setup:** Supports **Murabaha**, **Flat**, and **Declining Balance** products. Enforces deletion protection for products in use and locks agreed terms at application origination.
3. **Module 3 — Financing Applications:** Live schedule preview, draft save & resume, guarantor verification, product boundary validation, and automatic blocking of clients $>30$ days overdue on active financing.
4. **Module 4 — Dual-Threshold Approval Workflow:** A loan officer approves up to and including $\$5,000$; a Branch Manager approves above $\$5,000$ and all group financings (General Manager and Admin may decide any level). Every new financing, whoever creates it, goes to the approval queue: the creator can never approve their own application (not even Admin) and does not see it in their queue. Every decision is written to the audit log; forwarded applications email the approvers. Rejected applications can be edited and resubmitted once.
5. **Module 5 — Repayment Schedule Engine (`ScheduleEngine.php`):** Decimal-safe (`bcmath` + integer cents) calculation engine for Murabaha, Flat, and Declining Balance. Reconciles rounding differences on the final installment and automatically shifts **Friday** due dates to **Saturday**.
6. **Module 6 — Simulated Mobile Money Disbursement:** Done by the loan officer (or a manager) after approval. Supports **EVC Plus**, **Zaad**, and **eDahab** (`Net Disbursed = Approved Amount − Admin Fee`), generates unique transaction references, expires undisbursed approvals after 30 days, and activates schedules on the disbursement date.
7. **Module 7 — Repayment Collection & Allocation Engine (`RepaymentEngine.php`):** ACID transaction-safe allocation: **(1) Outstanding Charity Late Charges** first, then **(2) Oldest Unpaid Installment**. Supports full, partial, early repayment with Murabaha *Ibra* profit rebates, and Client Overpayment Credit (`clients.credit_balance`).
8. **Module 8 — Segregated Charity Late Charges (`CharityChargeService.php`):** Installments unpaid $>7$ days after due date receive a fixed late charge directed to a segregated **Charity Account** (never counted as institutional income). Branch Managers can waive charges with a mandatory reason logged in the audit trail. The Charity page is the charity account report: assessed, collected, waived and still-owed charges, every payout from the account (recipient, purpose, date, who recorded it) and the balance (collected − given out). A payout can never exceed the balance.
9. **Module 9 — Deterministic Credit Score Engine (`CreditScoreEngine.php`):** Range **300–850** (starting at **600**), formula in section 4 and on the Credit Scores screen ("How is the score calculated?"), with the reason for each client's last change. Recalculates automatically on repayments and missed due dates; waived charges do not penalize the client.
10. **Module 10 — Solidarity Group Financing:** Groups of 3–10 clients with a designated Group Leader and Branch Manager approval. If any member is $>30$ days overdue, new group financing is locked for all members; members with active financing cannot leave.
11. **Module 11 — Simulated Bilingual SMS Reminders (`SmsReminderService.php`):** Dispatches Somali and English reminders 3 days prior to due date (`upcoming_3_days`) and 1 day after a missed due date (`overdue_1_day`), deduplicated per installment and type.
12. **Module 12 — Portfolio at Risk (`Par30Service.php`):** Live SQL calculation of `PAR30 = (Outstanding Balance of Financings >30 Days Late / Total Outstanding Balance) * 100` at institution and branch levels (displaying `0.00%` for branches with no active financing), plus a 12-month trend chart rebuilt from repayment and installment history (`Par30Service::monthlyTrend`).
13. **Module 13 — Downloadable PDF Client Statements (`StatementPdfGenerator.php`):** Native PDF 1.4 generator producing downloadable statements with full installment schedules, payment histories, charity charges, and a permanent `FULLY REPAID / SI BUUXDA LOO BIXIYAY` mark on closed contracts.

---

## 3. Technology Stack & Versions

| Layer | Technology | Version / Details |
|---|---|---|
| **Frontend Structure** | Semantic HTML5 | Bilingual (`so` default, `en` full secondary), accessible landmarks |
| **Styling & Themes** | Custom CSS3 (`simda.css`) | CSS Variables, Light/Dark/System themes, Deep Green (`#123B32`) & Gold (`#D6B66C`), tabular numerals |
| **Frontend Logic** | Vanilla JavaScript (`simda.js`) | Fetch API schedule calculator, theme engine, offline guard, Chart.js 4.4 analytics |
| **Backend & Domain** | PHP | PHP 8.4+ (`declare(strict_types=1)`, `bcmath`, `pdo_mysql`, `fileinfo`) |
| **Database** | MySQL / MariaDB InnoDB | 26 normalized tables, foreign keys, ACID transactions, `DECIMAL(15,2)` monetary columns |
| **PWA** | Web App Manifest + Service Worker | Installable standalone app shell with strict cache exclusion for financial endpoints |

---

## 4. Credit Score Formula (Module 9 Specification)

Every new client starts at a baseline score of **600** (clamped to **[300, 850]**):

$$\text{Credit Score} = \text{clamp}_{[300, 850]}\Big(600 + 15 \cdot N_{\text{on-time}} + 25 \cdot N_{\text{early}} - P_{\text{paid-late}} - P_{\text{overdue-unpaid}}\Big)$$

Where:
- **On-Time Paid Installment ($N_{\text{on-time}}$):** $+15$ points per installment paid on or before `due_date` (or where a late charge was officially waived).
- **Full Early Settlement ($N_{\text{early}}$):** $+25$ bonus points when a financing contract is settled in full prior to its `maturity_date`.
- **Paid Late Installments ($P_{\text{paid-late}}$):**
  - Paid $1\text{–}7$ days late: $-10$ points
  - Paid $8\text{–}30$ days late: $-25$ points
  - Paid $>30$ days late: $-45$ points
- **Currently Unpaid Overdue Installments ($P_{\text{overdue-unpaid}}$):**
  - Unpaid $1\text{–}7$ days past due: $-15$ points
  - Unpaid $8\text{–}30$ days past due: $-35$ points
  - Unpaid $>30$ days past due: $-60$ points
- **Waiver Rule:** If a Branch Manager waives an installment's charity late charge, its late penalty is neutralized ($0$ penalty).

| Score Range | Category |
|---|---|
| `750 – 850` | `excellent` |
| `670 – 749` | `good` |
| `580 – 669` | `fair` |
| `480 – 579` | `caution` |
| `300 – 479` | `high_risk` |

---

## 5. Schedule Engine Rate Conversion & Rounding Assumptions

1. **Murabaha:** $\text{Total Profit} = \text{round}(\text{Cost} \times \frac{\text{Markup\%}}{100}, 2)$. Total Due is divided equally across $N$ installments; any cent remainder is reconciled on the final installment ($N$).
2. **Flat:** $\text{Total Profit} = \text{round}(\text{Principal} \times \frac{\text{AnnualRate\%}}{100} \times \frac{N}{\text{PeriodsPerYear}}, 2)$, where $\text{PeriodsPerYear} = 12$ for monthly schedules and $52$ for weekly schedules.
3. **Declining Balance:** Uses the standard equal-installment amortization formula $\text{PMT} = P \cdot \frac{r(1+r)^N}{(1+r)^N - 1}$ with periodic rate $r = \frac{\text{AnnualRate\%}}{100 \times \text{PeriodsPerYear}}$. Each period's profit is $\text{round}(\text{RemainingPrincipal} \times r, 2)$, and the final installment clears the exact remaining principal balance.
4. **Friday Adjustment:** Any installment due date falling on a Friday (ISO-8601 day `5`) is automatically shifted to Saturday (`+1 day`), preserving `original_due_date` and flagging `was_friday_shifted = 1`.

---

## 6. Installation & Setup Instructions

1. **Clone or place the project in XAMPP `htdocs`:**
   ```powershell
   cd c:\xampp\htdocs\SIMDA
   ```
2. **Configure Environment:**
   Copy `.env.example` to `.env` and verify your MySQL credentials (`DB_DATABASE=simda_db`, `DB_TEST_DATABASE=simda_test_db`).
3. **Run Database Schema & Baseline Seeder:**
   ```powershell
   C:\xampp\php\php.exe database/setup.php
   ```
   *(To initialize a clean production database without synthetic demo fixtures, pass `--no-demo`: `C:\xampp\php\php.exe database/setup.php --no-demo`)*
4. **Access the Application:**
   - Start Apache and MySQL in XAMPP, or run PHP's built-in server:
     ```powershell
     C:\xampp\php\php.exe -S localhost:8080 -t public
     ```
   - Open `http://localhost/SIMDA/` (Apache) or `http://localhost:8080/` (built-in server).

---

## 7. Running the Automated Test Suite (25 Tests)

Run all 25 unit and integration tests against the isolated `simda_test_db` database with a single command:

```powershell
C:\xampp\php\php.exe tests/run_tests.php
```

The same command runs in CI on every push and pull request (GitHub Actions, `.github/workflows/tests.yml`, PHP 8.4 + MariaDB 10.11). Tests 07–11 prove that the sum of installments equals the total due for every product type; 14–18 prove that a repayment updates the schedule, balance and credit score.

**Upgrading an existing database:** run the files in `database/migrations/` that are newer than your database, then recalculate scores once:

```powershell
C:\xampp\php\php.exe database/recalculate_scores.php
```

---

## 8. Synthetic Evaluation Accounts (Development / Demo Mode)

When `database/setup.php` is run in default development mode, the following clearly labeled synthetic evaluation accounts (`is_synthetic_demo = 1`) are created so you can test all 4 roles:

| Role | Username | Password | Scope & Capabilities |
|---|---|---|---|
| **Admin** | `admin` | `Admin@123` | Full institution governance, branches, products, users, audit logs, branch transfers |
| **Branch Manager** | `manager` | `Manager@123` | Mogadishu Branch oversight, $>\$5,000$ & Group approvals, Charity late-charge waivers |
| **Loan Officer** | `officer` | `Officer@123` | Client & guarantor registration, applications, $\le\$5,000$ peer approvals, disbursements, repayments |
| **Client** | `client` | `Client@123` | Self-service portal for own financing, schedule, repayment history, credit score & PDF statements |

---

The admin password on a fresh install comes from `SEED_ADMIN_PASSWORD` in `.env` (default `Admin@123`); no real password is stored in the code. Change it after the first sign-in.

---

## Assumptions

Choices made where the brief leaves room (brief: "choose a sensible approach yourself and record it"):

1. **Approval levels:** the brief names two levels. The General Manager and Admin rank above the Branch Manager and may decide any level; nothing above $5,000 requires more than a Branch Manager.
2. **Who creates financing:** Admin, General Manager and Branch Manager can also create applications, but they go through the same queue as everyone else, so someone else always approves.
3. **Repayment frequency:** products are weekly or monthly as the brief requires. The application form also offers daily, hourly and minute-based schedules for demonstrations; the same rounding and Friday rules apply.
4. **Charity account:** the account is per branch for Branch Managers and institution-wide for Admin. Payouts are recorded manually (recipient, purpose, date) and cannot exceed the balance.
5. **Credit score categories:** `new` until the first repayment, then 750+ excellent, 670+ good, 580+ fair, 480+ caution, below 480 high risk.
6. **PAR30 trend:** month-end values are rebuilt from repayments (`payment_date`) and installments (`paid_at`), so the chart always comes from live data.
7. **Real messages:** besides the in-app SMS outbox required by the brief, SIMDA can also send real email and WhatsApp messages when configured; with them switched off it works fully on the simulated adapters.

## Known limitations

- Screenshots of the main screens are not yet included in this README.
- The PAR30 trend uses the installment's `paid_at` (when it became fully paid); a partly paid installment counts as unpaid until it is fully paid.
- WhatsApp needs the local gateway (`whatsapp-gateway/`, Node.js) running on a computer linked to the WhatsApp account.

---

## 9. Architecture & Documentation Links

- [Entity-Relationship Diagram (`docs/ERD.md`)](docs/ERD.md)
- [Software Architecture Reference (`docs/ARCHITECTURE.md`)](docs/ARCHITECTURE.md)
- [Routes & Module Endpoints Reference (`docs/API.md`)](docs/API.md)


## SIMDA AI (website assistant)

The robot button on the public pages (Home, About, Contact, FAQ) opens **SIMDA AI**. With an Anthropic API key it answers with Claude, using a live snapshot of SIMDA's **public** data (products, Murabaha catalog and prices, branches, partners, contacts, policies, summary counts). It never receives individual client or staff records, because the chat is public and needs no login.

1. Install the PHP dependencies once: `php composer.phar install` (creates `vendor/`).
2. Add `ANTHROPIC_API_KEY=...` to `.env` (optional: `SIMDA_AI_MODEL`, default `claude-opus-5-5`).

Without a key, or if the API is unavailable, the assistant falls back to the built-in offline answers. Each visitor is limited to 25 AI replies per 10 minutes (`app/Services/SimdaAiService.php`).
