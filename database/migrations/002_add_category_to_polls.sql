-- Migration 002: Add category_id column to polls table and foreign key constraint
-- Run this migration after 001_add_poll_categories.sql

ALTER TABLE polls
    ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL AFTER event_id;

ALTER TABLE polls
    DROP FOREIGN KEY IF EXISTS fk_polls_category;

ALTER TABLE polls
    ADD CONSTRAINT fk_polls_category
    FOREIGN KEY (category_id) REFERENCES poll_categories(id) ON DELETE SET NULL;
