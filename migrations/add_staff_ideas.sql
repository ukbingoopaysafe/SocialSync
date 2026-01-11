-- Ideas Workspace Feature Migration
-- Created: 2026-01-08
-- Updated: 2026-01-11 - Added media support
-- Purpose: Add staff_ideas table with media support and migrate IDEA posts to DRAFT

-- Step 1: Create staff_ideas table for personal ideas workspace
CREATE TABLE IF NOT EXISTS `staff_ideas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_company` (`company_id`),
  CONSTRAINT `fk_staff_ideas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_staff_ideas_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Create idea_media table for attachments
CREATE TABLE IF NOT EXISTS `idea_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idea_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_idea` (`idea_id`),
  CONSTRAINT `fk_idea_media_idea` FOREIGN KEY (`idea_id`) REFERENCES `staff_ideas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Migrate all existing IDEA posts to DRAFT status
UPDATE posts SET status = 'DRAFT' WHERE status = 'IDEA';

-- Step 4: Remove IDEA from status ENUM (safe after migration)
ALTER TABLE `posts` 
MODIFY COLUMN `status` ENUM('DRAFT','PENDING_REVIEW','REVIEWED','CHANGES_REQUESTED','APPROVED','SCHEDULED','PUBLISHED') NOT NULL DEFAULT 'DRAFT';
