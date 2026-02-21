CREATE TABLE IF NOT EXISTS carshare_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carshare_offer_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('pending', 'accepted', 'declined', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (carshare_offer_id) REFERENCES carshare_offers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_request (carshare_offer_id, user_id)
);
