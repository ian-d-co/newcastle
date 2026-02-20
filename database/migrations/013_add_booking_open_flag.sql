-- Add booking_open column to activities, meals, and hotel_rooms tables
ALTER TABLE activities 
ADD COLUMN IF NOT EXISTS booking_open BOOLEAN DEFAULT TRUE AFTER no_booking_required;

ALTER TABLE meals 
ADD COLUMN IF NOT EXISTS booking_open BOOLEAN DEFAULT TRUE AFTER no_booking_required;

ALTER TABLE hotel_rooms 
ADD COLUMN IF NOT EXISTS booking_open BOOLEAN DEFAULT TRUE AFTER status;
