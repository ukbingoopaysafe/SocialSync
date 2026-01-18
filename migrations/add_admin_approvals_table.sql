-- =====================================================
-- Migration: Add Admin Approvals Table
-- Date: 2026-01-18
-- Description: Tracks individual admin approvals for posts in PENDING_REVIEW stage
-- =====================================================

CREATE TABLE IF NOT EXISTS `admin_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_post_admin` (`post_id`, `admin_id`),
  FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
