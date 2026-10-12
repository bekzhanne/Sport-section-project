-- Run once against an existing kbtu_culturist database (schema.sql's CREATE TABLE IF NOT EXISTS
-- won't add columns to a table that already exists).
USE kbtu_culturist;

ALTER TABLE users
    ADD COLUMN is_admin   TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash,
    ADD COLUMN phone      VARCHAR(20) NULL AFTER is_admin,
    ADD COLUMN birth_date DATE NULL AFTER phone;

-- Promote your own account to admin after registering normally:
-- UPDATE users SET is_admin = 1 WHERE email = 'you@example.com';
