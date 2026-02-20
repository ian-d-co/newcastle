-- Add amount_paid tracking to room reservations
ALTER TABLE room_reservations 
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10,2) DEFAULT 0.00 AFTER total_price;
