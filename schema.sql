-- SocialSync Database Schema
-- Social Media Team Management System
-- Created: 2025-12-16

-- Drop tables if they exist (for clean reinstall)
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

-- Users table: Store team members with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('creator', 'approver') NOT NULL DEFAULT 'creator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table: Store social media content with workflow status
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    platform ENUM('Facebook', 'Instagram', 'LinkedIn', 'Twitter', 'TikTok', 'Website') NOT NULL,
    status ENUM('IDEA', 'DRAFT', 'PENDING_REVIEW', 'APPROVED', 'REJECTED', 'SCHEDULED', 'PUBLISHED', 'ARCHIVED') NOT NULL DEFAULT 'DRAFT',
    urgency BOOLEAN DEFAULT FALSE,
    author_id INT NOT NULL,
    reviewer_id INT NULL,
    scheduled_date DATETIME NULL,
    rejected_reason VARCHAR(255) NULL,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_author (author_id),
    INDEX idx_platform (platform),
    INDEX idx_scheduled (scheduled_date),
    INDEX idx_status_scheduled (status, scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table: Manage user login sessions
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default test users (passwords are hashed with PASSWORD: 'password123')
-- In production, use stronger passwords and generate these manually
INSERT INTO users (username, email, role, password_hash) VALUES
('creator1', 'creator@socialsync.com', 'creator', '$2y$10$YourHashedPasswordHere1'),
('approver1', 'approver@socialsync.com', 'approver', '$2y$10$YourHashedPasswordHere2');

-- Sample posts for testing (optional)
-- Uncomment after creating users
/*
INSERT INTO posts (title, content, platform, status, urgency, author_id, scheduled_date) VALUES
('Monday Motivation', 'Start your week strong! 💪 #MondayMotivation #Success', 'Instagram', 'DRAFT', FALSE, 1, '2025-12-16 09:00:00'),
('Product Launch', 'Exciting news coming tomorrow! Stay tuned 🚀', 'Facebook', 'PENDING_REVIEW', TRUE, 1, '2025-12-17 14:00:00'),
('Industry Insights', 'Check out our latest blog post on digital marketing trends', 'LinkedIn', 'APPROVED', FALSE, 1, '2025-12-18 10:00:00');
*/
