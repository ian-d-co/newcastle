-- Migration: Add book_with_group and group_payment_due fields to hotel_rooms
-- and book_with_group to room_reservations

ALTER TABLE hotel_rooms
ADD COLUMN IF NOT EXISTS book_with_group BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS group_payment_due DATE NULL;

ALTER TABLE room_reservations
ADD COLUMN IF NOT EXISTS book_with_group BOOLEAN DEFAULT FALSE;
