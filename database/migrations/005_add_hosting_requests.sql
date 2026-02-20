-- Migration: Add hosting_requests table for request/accept workflow
-- This replaces direct booking with a request/accept flow

CREATE TABLE IF NOT EXISTS hosting_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hosting_offer_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'cancelled') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hosting_offer_id) REFERENCES hosting_offers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_request (hosting_offer_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
