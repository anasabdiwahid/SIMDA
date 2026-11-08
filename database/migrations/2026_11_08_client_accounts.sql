-- Client portal accounts: clients sign in with email or phone and the default password 123456@@,
-- and are asked to change it. The app also adds this column on first use (ClientAccountService::ensureSchema)
-- and creates accounts for existing clients that have none (ClientAccountService::backfill).
ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash;
