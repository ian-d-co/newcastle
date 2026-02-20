-- Migration: Add payment tracking fields to bookings

ALTER TABLE activity_bookings
ADD COLUMN IF NOT EXISTS amount_due DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS payment_notes TEXT;

ALTER TABLE meal_bookings
ADD COLUMN IF NOT EXISTS amount_due DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS payment_notes TEXT;
