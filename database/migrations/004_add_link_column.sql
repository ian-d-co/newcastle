-- Migration: Add link column to activities, meals, and hotels tables
-- Run this migration to add external link support

ALTER TABLE activities ADD COLUMN link VARCHAR(500) NULL AFTER description;
ALTER TABLE meals ADD COLUMN link VARCHAR(500) NULL AFTER description;
ALTER TABLE hotels ADD COLUMN link VARCHAR(500) NULL AFTER website;
