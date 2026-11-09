-- Flexible repayment period: installments can be minutes, hours, days, weeks or months apart,
-- "every N units" (repayment_interval), or one single payment after a chosen time.
-- installments.due_at keeps the exact due moment (needed for minute / hour schedules);
-- due_date stays the calendar day that overdue, charity and reminder rules use.

ALTER TABLE financing_applications
    MODIFY repayment_frequency ENUM('minutely', 'hourly', 'daily', 'weekly', 'monthly') NOT NULL DEFAULT 'monthly',
    ADD COLUMN repayment_interval INT UNSIGNED NOT NULL DEFAULT 1 AFTER repayment_frequency;

ALTER TABLE financings
    MODIFY repayment_frequency ENUM('minutely', 'hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    ADD COLUMN repayment_interval INT UNSIGNED NOT NULL DEFAULT 1 AFTER repayment_frequency;

ALTER TABLE installments
    ADD COLUMN due_at DATETIME NULL AFTER due_date;

ALTER TABLE products
    MODIFY allowed_frequency ENUM('weekly', 'monthly', 'both') NOT NULL DEFAULT 'both';
