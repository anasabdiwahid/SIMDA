-- =============================================================================
-- SIMDA — Somali Islamic Microfinance Development Association
-- Complete Relational Database Schema (MySQL 8+ / MariaDB 10.11+ InnoDB)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name_so VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PERMISSIONS
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    module VARCHAR(60) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ROLE PERMISSIONS (Many-to-Many)
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BRANCHES
CREATE TABLE IF NOT EXISTS branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    city VARCHAR(80) NOT NULL,
    district VARCHAR(80) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    manager_name VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. USERS
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NULL,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    preferred_locale VARCHAR(5) NOT NULL DEFAULT 'en',
    preferred_theme VARCHAR(10) NOT NULL DEFAULT 'light',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_synthetic_demo TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at DATETIME NULL,
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    lockout_tier TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    security_block_reason VARCHAR(255) NULL,
    current_session_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_users_role (role_id),
    INDEX idx_users_branch (branch_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. LOGIN ATTEMPTS (Rate Limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(150) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    was_successful TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_login_identifier_time (identifier, attempted_at),
    INDEX idx_login_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.5 PASSWORD RESETS & OTP
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    target_type ENUM('email', 'phone') NOT NULL DEFAULT 'email',
    target_value VARCHAR(150) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    token VARCHAR(64) NOT NULL,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pw_reset_user (user_id),
    INDEX idx_pw_reset_token (token),
    INDEX idx_pw_reset_expires (expires_at),
    CONSTRAINT fk_pw_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.6 ADMIN 2FA CHALLENGES
CREATE TABLE IF NOT EXISTS admin_2fa_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_a2fa_user (user_id),
    INDEX idx_a2fa_expires (expires_at),
    CONSTRAINT fk_a2fa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CLIENTS (Module 1)
CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL UNIQUE,
    branch_id INT UNSIGNED NOT NULL,
    registered_by INT UNSIGNED NULL,
    full_name VARCHAR(140) NOT NULL,
    id_type ENUM('national_id', 'passport', 'voter_card', 'business_license') NOT NULL DEFAULT 'national_id',
    id_number VARCHAR(60) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    city VARCHAR(80) NOT NULL,
    district VARCHAR(80) NOT NULL,
    address VARCHAR(255) NOT NULL,
    business_type VARCHAR(120) NOT NULL,
    monthly_income DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    photo_path VARCHAR(255) NULL,
    credit_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Client overpayment credit balance',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    is_synthetic_demo TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_clients_branch (branch_id),
    INDEX idx_clients_status (status),
    CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_clients_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_clients_reg_by FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. GUARANTORS (Module 1)
CREATE TABLE IF NOT EXISTS guarantors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(140) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    id_number VARCHAR(60) NOT NULL,
    relationship VARCHAR(80) NOT NULL,
    occupation VARCHAR(120) NULL,
    estimated_income DECIMAL(15,2) NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_guarantors_client (client_id),
    CONSTRAINT fk_guarantors_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. CLIENT GROUPS (Module 10 — Group Financing)
CREATE TABLE IF NOT EXISTS groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(30) NOT NULL UNIQUE,
    branch_id INT UNSIGNED NOT NULL,
    group_name VARCHAR(120) NOT NULL,
    leader_client_id INT UNSIGNED NOT NULL,
    status ENUM('pending_approval', 'active', 'locked_overdue', 'inactive') NOT NULL DEFAULT 'pending_approval',
    approved_by INT UNSIGNED NULL,
    approved_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_groups_branch (branch_id),
    CONSTRAINT fk_groups_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_groups_leader FOREIGN KEY (leader_client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_groups_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. GROUP MEMBERS (Module 10)
CREATE TABLE IF NOT EXISTS group_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    role_in_group ENUM('leader', 'secretary', 'member') NOT NULL DEFAULT 'member',
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_group_client_active (group_id, client_id),
    INDEX idx_gm_client (client_id),
    CONSTRAINT fk_gm_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_gm_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. PRODUCTS (Module 2 — Financing Product Setup)
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(30) NOT NULL UNIQUE,
    name_so VARCHAR(140) NOT NULL,
    name_en VARCHAR(140) NOT NULL,
    product_type ENUM('murabaha', 'flat', 'declining_balance') NOT NULL,
    annual_profit_rate DECIMAL(8,4) NOT NULL DEFAULT 0.0000 COMMENT 'Annual rate percentage for Flat & Declining Balance',
    murabaha_markup_pct DECIMAL(8,4) NOT NULL DEFAULT 0.0000 COMMENT 'Fixed markup percentage for Murabaha',
    min_amount DECIMAL(15,2) NOT NULL,
    max_amount DECIMAL(15,2) NOT NULL,
    min_term INT UNSIGNED NOT NULL COMMENT 'Minimum number of installments/months',
    max_term INT UNSIGNED NOT NULL COMMENT 'Maximum number of installments/months',
    allowed_frequency ENUM('weekly', 'monthly', 'both') NOT NULL DEFAULT 'monthly',
    admin_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    description_so TEXT NULL,
    description_en TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_type (product_type),
    INDEX idx_products_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. FINANCING APPLICATIONS (Module 3 & Module 4)
CREATE TABLE IF NOT EXISTS financing_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(35) NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    group_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NOT NULL,
    requested_amount DECIMAL(15,2) NOT NULL,
    term_installments INT UNSIGNED NOT NULL,
    repayment_frequency ENUM('weekly', 'monthly') NOT NULL DEFAULT 'monthly',
    agreed_rate DECIMAL(8,4) NOT NULL COMMENT 'Snapshot of product rate/markup at application time',
    agreed_admin_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    projected_profit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    projected_total_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    purpose VARCHAR(255) NOT NULL,
    murabaha_goods_description TEXT NULL,
    notes TEXT NULL,
    calculation_snapshot JSON NULL COMMENT 'Reproducible record of schedule engine inputs',
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'disbursed', 'expired') NOT NULL DEFAULT 'draft',
    required_approver_role ENUM('loan_officer', 'branch_manager', 'manager') NOT NULL DEFAULT 'loan_officer',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    decision_comments TEXT NULL,
    resubmission_count TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Rejected apps can be edited and resubmitted once',
    submitted_at DATETIME NULL,
    expires_at DATETIME NULL COMMENT 'Approved applications expire 30 days after approval if undisbursed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_apps_client (client_id),
    INDEX idx_apps_branch_status (branch_id, status),
    INDEX idx_apps_status (status),
    CONSTRAINT fk_apps_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_apps_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_apps_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_apps_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL,
    CONSTRAINT fk_apps_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_apps_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. FINANCINGS (Active & Closed Contracts — Module 6, 7, 12, 13)
CREATE TABLE IF NOT EXISTS financings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financing_number VARCHAR(35) NOT NULL UNIQUE,
    application_id INT UNSIGNED NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    group_id INT UNSIGNED NULL,
    disbursed_by INT UNSIGNED NOT NULL,
    product_type ENUM('murabaha', 'flat', 'declining_balance') NOT NULL,
    approved_amount DECIMAL(15,2) NOT NULL COMMENT 'Principal / Cost',
    admin_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    net_disbursed_amount DECIMAL(15,2) NOT NULL COMMENT 'Approved Amount - Admin Fee',
    total_profit DECIMAL(15,2) NOT NULL,
    ibra_discount DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Murabaha early settlement discount granted',
    total_due DECIMAL(15,2) NOT NULL,
    principal_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    profit_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    outstanding_principal DECIMAL(15,2) NOT NULL,
    outstanding_balance DECIMAL(15,2) NOT NULL,
    term_installments INT UNSIGNED NOT NULL,
    repayment_frequency ENUM('weekly', 'monthly') NOT NULL,
    agreed_rate DECIMAL(8,4) NOT NULL,
    disbursement_provider ENUM('EVC Plus', 'Zaad', 'eDahab') NOT NULL,
    disbursement_reference VARCHAR(60) NOT NULL UNIQUE,
    disbursement_phone VARCHAR(30) NOT NULL,
    disbursed_at DATETIME NOT NULL,
    maturity_date DATE NOT NULL,
    closed_at DATETIME NULL,
    status ENUM('active', 'overdue', 'completed', 'written_off') NOT NULL DEFAULT 'active',
    calculation_inputs JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fin_client (client_id),
    INDEX idx_fin_branch_status (branch_id, status),
    INDEX idx_fin_status (status),
    CONSTRAINT fk_fin_app FOREIGN KEY (application_id) REFERENCES financing_applications(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fin_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fin_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fin_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_fin_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL,
    CONSTRAINT fk_fin_disburser FOREIGN KEY (disbursed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. INSTALLMENTS (Module 5 — Repayment Schedule Engine)
CREATE TABLE IF NOT EXISTS installments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financing_id INT UNSIGNED NOT NULL,
    installment_number INT UNSIGNED NOT NULL,
    original_due_date DATE NOT NULL,
    due_date DATE NOT NULL COMMENT 'Shifted from Friday to Saturday when applicable',
    was_friday_shifted TINYINT(1) NOT NULL DEFAULT 0,
    principal_due DECIMAL(15,2) NOT NULL,
    profit_due DECIMAL(15,2) NOT NULL,
    total_due DECIMAL(15,2) NOT NULL,
    principal_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    profit_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    remaining_balance DECIMAL(15,2) NOT NULL,
    principal_balance_after DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fin_installment (financing_id, installment_number),
    INDEX idx_inst_due_status (due_date, status),
    INDEX idx_inst_financing (financing_id),
    CONSTRAINT fk_inst_financing FOREIGN KEY (financing_id) REFERENCES financings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. CHARITY LATE CHARGES (Module 8 — Segregated Charity Account)
CREATE TABLE IF NOT EXISTS charity_charges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    financing_id INT UNSIGNED NOT NULL,
    installment_id INT UNSIGNED NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    remaining_amount DECIMAL(15,2) NOT NULL,
    days_overdue_when_assessed INT UNSIGNED NOT NULL DEFAULT 8,
    status ENUM('unpaid', 'partial', 'paid', 'waived') NOT NULL DEFAULT 'unpaid',
    assessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    waived_by INT UNSIGNED NULL,
    waived_at DATETIME NULL,
    waiver_reason TEXT NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_charity_fin (financing_id),
    INDEX idx_charity_status (status),
    CONSTRAINT fk_charity_fin FOREIGN KEY (financing_id) REFERENCES financings(id) ON DELETE CASCADE,
    CONSTRAINT fk_charity_inst FOREIGN KEY (installment_id) REFERENCES installments(id) ON DELETE CASCADE,
    CONSTRAINT fk_charity_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_charity_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_charity_waiver FOREIGN KEY (waived_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. REPAYMENTS (Module 7 — Repayment Collection & Allocation)
CREATE TABLE IF NOT EXISTS repayments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(35) NOT NULL UNIQUE,
    financing_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    recorded_by INT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    credit_applied DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Existing client credit consumed',
    allocated_to_charity DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    allocated_to_principal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    allocated_to_profit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    overpayment_credited DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    ibra_discount_applied DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('EVC Plus', 'Zaad', 'eDahab', 'Cash') NOT NULL,
    transaction_reference VARCHAR(80) NOT NULL,
    payment_date DATE NOT NULL,
    is_early_settlement TINYINT(1) NOT NULL DEFAULT 0,
    allocation_breakdown JSON NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rep_financing (financing_id),
    INDEX idx_rep_client (client_id),
    INDEX idx_rep_branch_date (branch_id, payment_date),
    CONSTRAINT fk_rep_financing FOREIGN KEY (financing_id) REFERENCES financings(id) ON DELETE RESTRICT,
    CONSTRAINT fk_rep_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_rep_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_rep_recorder FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. CREDIT SCORES (Module 9 — 300 to 850, Default 600)
CREATE TABLE IF NOT EXISTS credit_scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL UNIQUE,
    current_score INT NOT NULL DEFAULT 600,
    previous_score INT NOT NULL DEFAULT 600,
    last_delta INT NOT NULL DEFAULT 0,
    score_category ENUM('excellent', 'good', 'fair', 'caution', 'high_risk') NOT NULL DEFAULT 'fair',
    last_change_reason_so VARCHAR(255) NOT NULL,
    last_change_reason_en VARCHAR(255) NOT NULL,
    on_time_payments_count INT UNSIGNED NOT NULL DEFAULT 0,
    late_payments_count INT UNSIGNED NOT NULL DEFAULT 0,
    early_settlements_count INT UNSIGNED NOT NULL DEFAULT 0,
    history_log JSON NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cs_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. SMS OUTBOX (Module 11 — Simulated Bilingual SMS Reminders)
CREATE TABLE IF NOT EXISTS sms_outbox (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    financing_id INT UNSIGNED NOT NULL,
    installment_id INT UNSIGNED NOT NULL,
    phone VARCHAR(30) NOT NULL,
    reminder_type ENUM('upcoming_3_days', 'overdue_1_day') NOT NULL,
    message_so TEXT NOT NULL,
    message_en TEXT NOT NULL,
    due_date DATE NOT NULL,
    amount_due DECIMAL(15,2) NOT NULL,
    status ENUM('queued', 'sent_simulated') NOT NULL DEFAULT 'sent_simulated',
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_installment_reminder (installment_id, reminder_type),
    INDEX idx_sms_client (client_id),
    CONSTRAINT fk_sms_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_sms_financing FOREIGN KEY (financing_id) REFERENCES financings(id) ON DELETE CASCADE,
    CONSTRAINT fk_sms_installment FOREIGN KEY (installment_id) REFERENCES installments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    client_id INT UNSIGNED NULL,
    branch_id INT UNSIGNED NULL,
    title_so VARCHAR(160) NOT NULL,
    title_en VARCHAR(160) NOT NULL,
    body_so TEXT NOT NULL,
    body_en TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_user_read (user_id, is_read),
    INDEX idx_notif_client_read (client_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. AUDIT LOGS (Module 4, 8 & Security Requirement)
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    branch_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id INT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_action (action),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. SYSTEM SETTINGS & CHARITY DISBURSEMENTS
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) NULL,
    updated_by INT UNSIGNED NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MURABAHA GOODS CATALOG
CREATE TABLE IF NOT EXISTS murabaha_goods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NULL,
    description TEXT NULL,
    price DECIMAL(15,2) NOT NULL COMMENT 'SIMDA purchase (cost) price per unit',
    markup_pct DECIMAL(6,2) NOT NULL DEFAULT 6.00,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_goods_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
