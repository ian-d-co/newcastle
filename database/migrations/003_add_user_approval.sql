-- Migration 003: Add user approval system
-- Adds approval columns to users table
-- Existing users are grandfathered in as approved

ALTER TABLE users 
    ADD COLUMN approved TINYINT(1) DEFAULT 0 AFTER is_admin,
    ADD COLUMN approved_by INT DEFAULT NULL AFTER approved,
    ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
    ADD KEY idx_approved (approved);

-- Grandfather in all existing users (set approved = 1)
-- New user registrations will have approved = 0 via application code
UPDATE users SET approved = 1 WHERE approved = 0;
