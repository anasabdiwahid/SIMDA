-- Three approval levels: Header Office (up to $5,000), Branch Manager (up to $50,000), General Manager (above).
-- Header Office is the existing `loan_officer` role under its business name; its slug does not change.

ALTER TABLE financing_applications
    MODIFY required_approver_role ENUM('loan_officer', 'branch_manager', 'manager') NOT NULL DEFAULT 'loan_officer';

UPDATE roles SET name_so = 'Header Office (Sarkaalka Maalgelinta)', name_en = 'Header Office' WHERE slug = 'loan_officer';
UPDATE roles SET name_so = 'Maamulaha Laanta (Branch Manager)', name_en = 'Branch Manager' WHERE slug = 'branch_manager';

UPDATE system_settings
SET description = 'Maximum financing amount ($ USD) Header Office can approve'
WHERE setting_key = 'approval_officer_threshold';

INSERT IGNORE INTO system_settings (setting_key, setting_value, description)
VALUES ('approval_branch_threshold', '50000.00', 'Maximum financing amount ($ USD) a Branch Manager can approve; above it goes to the General Manager');
