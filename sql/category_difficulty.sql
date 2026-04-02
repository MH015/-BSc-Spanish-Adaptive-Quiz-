-- Per-Category Difficulty Tracking Table
-- Allows different difficulty levels for each category per user
-- Run this in phpMyAdmin for the adaptive_quiz database

CREATE TABLE IF NOT EXISTS category_difficulty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
