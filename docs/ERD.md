# SIMDA — Entity-Relationship Diagram (ERD)

All 21 relational tables use MySQL 8+ / MariaDB 10.11+ `InnoDB` with `utf8mb4_unicode_ci` collation, strict primary and foreign key constraints, and `DECIMAL(15,2)` monetary columns.

```mermaid
erDiagram
    ROLES ||--o{ USERS : "assigns"
    ROLES ||--o{ ROLE_PERMISSIONS : "grants"
    PERMISSIONS ||--o{ ROLE_PERMISSIONS : "included_in"
    BRANCHES ||--o{ USERS : "employs"
    BRANCHES ||--o{ CLIENTS : "serves"
    BRANCHES ||--o{ GROUPS : "hosts"
    BRANCHES ||--o{ FINANCING_APPLICATIONS : "originates"
    BRANCHES ||--o{ FINANCINGS : "manages"
    BRANCHES ||--o{ REPAYMENTS : "collects"
    BRANCHES ||--o{ CHARITY_CHARGES : "tracks"

    USERS ||--o{ CLIENTS : "portal_account"
    USERS ||--o{ FINANCING_APPLICATIONS : "creates_or_reviews"
    USERS ||--o{ FINANCINGS : "disburses"
    USERS ||--o{ REPAYMENTS : "records"
    USERS ||--o{ CHARITY_CHARGES : "waives"
    USERS ||--o{ AUDIT_LOGS : "triggers"

    CLIENTS ||--|{ GUARANTORS : "guaranteed_by"
    CLIENTS ||--|| CREDIT_SCORES : "scored_by"
    CLIENTS ||--o{ GROUP_MEMBERS : "participates_in"
    CLIENTS ||--o{ FINANCING_APPLICATIONS : "applies"
    CLIENTS ||--o{ FINANCINGS : "holds"
    CLIENTS ||--o{ REPAYMENTS : "pays"
    CLIENTS ||--o{ CHARITY_CHARGES : "assessed"
    CLIENTS ||--o{ SMS_OUTBOX : "receives"
    CLIENTS ||--o{ NOTIFICATIONS : "notified"

    GROUPS ||--|{ GROUP_MEMBERS : "contains_3_to_10"
    GROUPS ||--o{ FINANCING_APPLICATIONS : "group_applications"
    GROUPS ||--o{ FINANCINGS : "group_financings"

    PRODUCTS ||--o{ FINANCING_APPLICATIONS : "defines_terms"
    PRODUCTS ||--o{ FINANCINGS : "governs"

    FINANCING_APPLICATIONS ||--o| FINANCINGS : "disbursed_as"
    FINANCINGS ||--|{ INSTALLMENTS : "scheduled_into"
    FINANCINGS ||--o{ REPAYMENTS : "settled_by"
    FINANCINGS ||--o{ CHARITY_CHARGES : "incurs"
    FINANCINGS ||--o{ SMS_OUTBOX : "triggers"

    INSTALLMENTS ||--o| CHARITY_CHARGES : "late_fee_after_7d"
    INSTALLMENTS ||--o{ SMS_OUTBOX : "reminded_by"
```

