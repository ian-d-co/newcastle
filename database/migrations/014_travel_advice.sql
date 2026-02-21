-- Migration 014: Add travel_advice table
CREATE TABLE IF NOT EXISTS travel_advice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    travel_type VARCHAR(50) NOT NULL,
    from_location VARCHAR(255) NOT NULL,
    to_location VARCHAR(255) NOT NULL,
    supplier VARCHAR(255) DEFAULT '',
    date_researched DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
