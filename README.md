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

1. **Module 1 — Client Profile & Guarantor Management:** Unique ID and phone enforcement, business profile, photo upload with strict MIME verification, mandatory Guarantor requirement, and Admin-only branch transfers.
2. **Module 2 — Financing Product Setup:** Supports **Murabaha**, **Flat**, and **Declining Balance** products. Enforces deletion protection for products in use and locks agreed terms at application origination.
3. **Module 3 — Financing Applications:** Live schedule preview, draft save & resume, guarantor verification, product boundary validation, and automatic blocking of clients $>30$ days overdue on active financing.
4. **Module 4 — Dual-Threshold Approval Workflow:** Loan Officers approve up to and including $\$5,000$; Branch Managers approve above $\$5,000$ and group financings. Application creators are strictly blocked from approving their own applications. Rejected applications can be edited and resubmitted once.
5. **Module 5 — Repayment Schedule Engine (`ScheduleEngine.php`):** Decimal-safe (`bcmath` + integer cents) calculation engine for Murabaha, Flat, and Declining Balance. Reconciles rounding differences on the final installment and automatically shifts **Friday** due dates to **Saturday**.
6. **Module 6 — Simulated Mobile Money Disbursement:** Supports **EVC Plus**, **Zaad**, and **eDahab** (`Net Disbursed = Approved Amount − Admin Fee`), generates unique transaction references, expires undisbursed approvals after 30 days, and activates schedules on the disbursement date.
7. **Module 7 — Repayment Collection & Allocation Engine (`RepaymentEngine.php`):** ACID transaction-safe allocation: **(1) Outstanding Charity Late Charges** first, then **(2) Oldest Unpaid Installment**. Supports full, partial, early repayment with Murabaha *Ibra* profit rebates, and Client Overpayment Credit (`clients.credit_balance`).
8. **Module 8 — Segregated Charity Late Charges (`CharityChargeService.php`):** Installments unpaid $>7$ days after due date receive a fixed late charge directed to a segregated **Charity Account** (never counted as institutional income). Branch Managers can waive charges with a mandatory reason logged in the audit trail.
9. **Module 9 — Deterministic Credit Score Engine (`CreditScoreEngine.php`):** Range **300–850** (starting at **600**). Recalculates automatically on repayments and missed due dates; waived charges do not penalize the client.
10. **Module 10 — Solidarity Group Financing:** Groups of 3–10 clients with a designated Group Leader and Branch Manager approval. If any member is $>30$ days overdue, new group financing is locked for all members; members with active financing cannot leave.
11. **Module 11 — Simulated Bilingual SMS Reminders (`SmsReminderService.php`):** Dispatches Somali and English reminders 3 days prior to due date (`upcoming_3_days`) and 1 day after a missed due date (`overdue_1_day`), deduplicated per installment and type.
12. **Module 12 — Portfolio at Risk (`Par30Service.php`):** Live SQL calculation of `PAR30 = (Outstanding Balance of Financings >30 Days Late / Total Outstanding Balance) * 100` at institution and branch levels (displaying `0.00%` for branches with no active financing).
13. **Module 13 — Downloadable PDF Client Statements (`StatementPdfGenerator.php`):** Native PDF 1.4 generator producing downloadable statements with full installment schedules, payment histories, charity charges, and a permanent `FULLY REPAID / SI BUUXDA LOO BIXIYAY` mark on closed contracts.

---

## 3. Technology Stack & Versions

| Layer | Technology | Version / Details |
|---|---|---|
| **Frontend Structure** | Semantic HTML5 | Bilingual (`so` default, `en` full secondary), accessible landmarks |
| **Styling & Themes** | Custom CSS3 (`simda.css`) | CSS Variables, Light/Dark/System themes, Deep Green (`#123B32`) & Gold (`#D6B66C`), tabular numerals |
| **Frontend Logic** | Vanilla JavaScript (`simda.js`) | Fetch API schedule calculator, theme engine, offline guard, Chart.js 4.4 analytics |
| **Backend & Domain** | PHP | PHP 8.4+ (`declare(strict_types=1)`, `bcmath`, `pdo_mysql`, `fileinfo`) |
| **Database** | MySQL / MariaDB InnoDB | 21 normalized tables, foreign keys, ACID transactions, `DECIMAL(15,2)` monetary columns |
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

## 7. Running the Automated Test Suite (22 Tests)

Run all 22 unit and integration tests against the isolated `simda_test_db` database with a single command:

```powershell
C:\xampp\php\php.exe tests/run_tests.php
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

## 9. Architecture & Documentation Links

- [Entity-Relationship Diagram (`docs/ERD.md`)](docs/ERD.md)
- [Software Architecture Reference (`docs/ARCHITECTURE.md`)](docs/ARCHITECTURE.md)
- [Routes & Module Endpoints Reference (`docs/API.md`)](docs/API.md)

