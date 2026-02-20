-- Add no_booking_required column to activities and meals tables
ALTER TABLE activities 
ADD COLUMN IF NOT EXISTS no_booking_required BOOLEAN DEFAULT FALSE AFTER requires_prepayment;

ALTER TABLE meals 
ADD COLUMN IF NOT EXISTS no_booking_required BOOLEAN DEFAULT FALSE AFTER requires_prepayment;
