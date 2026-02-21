-- Migration 017: Add occupant_name column to hotel_room_occupants
ALTER TABLE hotel_room_occupants 
ADD COLUMN occupant_name VARCHAR(255) NULL AFTER occupant_number;
