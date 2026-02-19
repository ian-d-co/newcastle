-- Migration: Add confirmation and payment deadline columns
-- Add deadline columns to activities
ALTER TABLE activities 
ADD COLUMN IF NOT EXISTS confirmation_deadline DATETIME NULL AFTER price,
ADD COLUMN IF NOT EXISTS payment_deadline DATETIME NULL AFTER confirmation_deadline;

-- Add deadline columns to meals
ALTER TABLE meals 
ADD COLUMN IF NOT EXISTS confirmation_deadline DATETIME NULL AFTER price,
ADD COLUMN IF NOT EXISTS payment_deadline DATETIME NULL AFTER confirmation_deadline;

-- Add deadline columns to hotel_rooms
ALTER TABLE hotel_rooms 
ADD COLUMN IF NOT EXISTS confirmation_deadline DATETIME NULL AFTER description,
ADD COLUMN IF NOT EXISTS payment_deadline DATETIME NULL AFTER confirmation_deadline;
