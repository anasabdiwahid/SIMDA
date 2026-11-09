# SIMDA — Routes & Module Endpoints Reference

All web and JSON routes are dispatched through `public/index.php?route=<route_name>`. Every state-changing `POST` route enforces CSRF token verification (`AuthMiddleware::verifyCsrfOrAbort()`) and server-side role/branch authorization.

| Route (`?route=...`) | Method | Permitted Roles | Description |
|---|---|---|---|
| `home` | GET | Public | Bilingual public website with 11 sections & live schedule calculator |
| `offline` | GET | Public | Offline fallback page for PWA |
| `login` / `login_submit` | GET / POST | Public | Rate-limited authentication with session regeneration |
| `logout` | GET | Authenticated | Destroys session and logs audit event |
| `api_schedule_preview` | GET (JSON) | Public / Auth | Returns JSON schedule preview from `ScheduleEngine::generate()` |
| `dashboard` | GET | All 4 Roles | Live KPI cards & Chart.js analytics scoped by role/branch |
| `clients` | GET | Admin, BM, Officer | Client directory with search, credit score & guarantor counts |
| `client_register` / `client_register_submit` | GET / POST | Admin, Officer | Module 1: Client + mandatory Guarantor registration |
| `client_transfer` | POST | Admin Only | Module 1: Transfer client between branches |
| `guarantors` / `guarantor_add` | GET / POST | Admin, BM, Officer | Module 1: View and add client guarantors |
| `groups` / `group_create` / `group_approve` | GET / POST | Admin, BM, Officer | Module 10: Solidarity groups (3–10 members) & BM approval |
| `products` / `product_save` / `product_toggle` / `product_delete` | GET / POST | Admin (View: BM/Officer) | Module 2: Murabaha, Flat & Declining Balance product setup |
| `applications` / `application_create` / `application_submit_draft` / `application_resubmit` | GET / POST | Admin, BM, Officer | Module 3: Financing applications, draft save, 1x resubmission |
| `approval_queue` / `application_review` | GET / POST | Admin, BM, Officer | Module 4: Dual-threshold approval queue ($\le \$5\text{k}$ Officer, $> \$5\text{k}$ BM) |
| `disbursements` / `disburse_submit` | GET / POST | Admin, BM, Officer | Module 6: Simulated EVC Plus, Zaad & eDahab disbursement |
| `financings` / `schedules` / `statements` | GET | All 4 Roles | Active & completed contracts list scoped by role/branch/client |
| `financing_view` | GET | All 4 Roles | Detailed contract schedule, payment history, charity charges & print view |
| `statement_pdf` | GET (PDF) | All 4 Roles | Module 13: Downloadable native PDF 1.4 Client Statement |
| `repayments` / `repayment_record` / `repayment_submit` | GET / POST | All 4 Roles (POST: Admin/Officer) | Module 7: Record repayment & inspect allocation breakdown |
| `client_pay` / `client_pay_submit` | GET / POST | Client | Client sends a payment on their own financing (simulated EVC Plus, Zaad, eDahab: `financing_id`, `amount`, `provider`, `phone`). Refused when the amount is more than what is owed (installments + unpaid charity charges); opens the receipt on success |
| `charity_charges` / `charity_waive` | GET / POST | All 4 Roles (Waive: Admin/BM) | Module 8: Segregated Charity Account & mandatory-reason waiver |
| `charity_payout` | POST | Admin, Branch Manager | Module 8: records money given out from the charity account (`amount`, `recipient`, `purpose`, `paid_on`); refused above the balance |
| `credit_scores` | GET | All 4 Roles | Module 9: Credit score (300–850) breakdown & reason log |
| `par30` / `reports` | GET | Admin, BM, Officer | Module 12: Live PAR30 institution & branch breakdown |
| `sms_outbox` / `sms_run_dispatcher` | GET / POST | All 4 Roles | Module 11: Bilingual Somali & English SMS reminder outbox |
| `branches` / `users` / `audit_logs` / `settings` | GET / POST | Admin Only | Institution branches, staff users, audit logs & system settings |

