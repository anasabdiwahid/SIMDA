-- Charity account use (brief, Module 8: the charity balance and its use are shown in a report).
CREATE TABLE IF NOT EXISTS charity_payouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED NULL,
    amount DECIMAL(14,2) NOT NULL,
    recipient VARCHAR(150) NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    paid_on DATE NOT NULL,
    recorded_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cp_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cp_recorder FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_cp_paid_on (paid_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

