-- =====================================================
-- BroMan Social - Enterprise Database Schema
-- Social Media Management System v2.0
-- =====================================================
-- WARNING: This script will DROP all existing tables!
-- Run this in phpMyAdmin or MySQL CLI
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS media_files;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- USERS TABLE
-- Team members with role-based access
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NULL,
    avatar_url VARCHAR(255) NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- POSTS TABLE
-- Social media content with advanced workflow states
-- =====================================================
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    platform ENUM('Facebook', 'Instagram', 'LinkedIn', 'X', 'TikTok', 'YouTube', 'Snapchat', 'Website') NOT NULL,
    
    -- Simplified Workflow Status (2-Role System)
    status ENUM(
        'IDEA',
        'DRAFT',
        'PENDING_REVIEW',
        'CHANGES_REQUESTED',
        'APPROVED'
    ) NOT NULL DEFAULT 'DRAFT',
    
    -- Workflow metadata
    urgency BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'normal', 'high', 'critical') DEFAULT 'normal',
    revision_count INT DEFAULT 0,
    
    -- Relationships
    author_id INT NOT NULL,
    reviewer_id INT NULL,
    
    -- Scheduling
    scheduled_date DATETIME NULL,
    published_date DATETIME NULL,
    
    -- Change request handling
    change_request_reason TEXT NULL,
    change_requested_by INT NULL,
    change_requested_at DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (change_requested_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_author (author_id),
    INDEX idx_platform (platform),
    INDEX idx_scheduled (scheduled_date),
    INDEX idx_priority (priority),
    INDEX idx_urgency (urgency),
    INDEX idx_status_scheduled (status, scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MEDIA FILES TABLE
-- Uploaded images/videos attached to posts
-- =====================================================
CREATE TABLE media_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    
    -- File information
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('image', 'video', 'document') NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    
    -- Image dimensions (for images/videos)
    width INT NULL,
    height INT NULL,
    
    -- Metadata
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_post (post_id),
    INDEX idx_type (file_type),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COMMENTS TABLE
-- Team collaboration/discussion on posts
-- =====================================================
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Comment content
    content TEXT NOT NULL,
    
    -- Threading support (optional)
    parent_id INT NULL,
    
    -- Edit tracking
    is_edited BOOLEAN DEFAULT FALSE,
    edited_at DATETIME NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    
    INDEX idx_post (post_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACTIVITY LOG TABLE
-- Audit trail for all post changes
-- =====================================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Action details
    action ENUM(
        'created',
        'updated',
        'status_changed',
        'comment_added',
        'media_uploaded',
        'media_deleted',
        'rejected',
        'approved',
        'scheduled',
        'published'
    ) NOT NULL,
    
    -- Change tracking
    field_name VARCHAR(50) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    
    -- Additional context
    description VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_post (post_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTIFICATIONS TABLE
-- In-app notification system
-- =====================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Notification content
    type ENUM(
        'post_approved',
        'post_rejected',
        'post_needs_review',
        'comment_added',
        'mention',
        'deadline_reminder',
        'system'
    ) NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Related entities
    post_id INT NULL,
    triggered_by INT NULL,
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at),
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SESSIONS TABLE
-- User login session management
-- =====================================================
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_token (session_token),
    INDEX idx_expires (expires_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA
-- Default users (passwords will be set via seed script)
-- =====================================================
-- Note: Run seed_direct.php after applying this schema
-- to create users with proper password hashes

SELECT 'Schema created successfully!' AS Status;
SELECT 'Run seed_direct.php to create user accounts.' AS NextStep;
