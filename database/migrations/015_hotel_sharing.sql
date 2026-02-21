-- Migration 015: Add open_to_hotel_sharing to users and hotel_sharing_requests table
ALTER TABLE users ADD COLUMN IF NOT EXISTS open_to_hotel_sharing TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS hotel_sharing_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    target_user_id INT NOT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('pending', 'accepted', 'declined', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
