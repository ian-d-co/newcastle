-- Migration: Make check_in and check_out nullable to support occupancy-based reservations
-- (where specific dates are not required, only night selections like Friday/Saturday)

ALTER TABLE room_reservations
MODIFY COLUMN check_in DATE NULL,
MODIFY COLUMN check_out DATE NULL;
