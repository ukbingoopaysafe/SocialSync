ALTER TABLE `posts`
  ADD COLUMN `source` varchar(50) DEFAULT NULL AFTER `change_requested_at`,
  ADD COLUMN `source_id` int(11) DEFAULT NULL AFTER `source`,
  ADD COLUMN `submitted_by` int(11) DEFAULT NULL AFTER `source_id`,
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_posts_source` (`source`,`source_id`),
  ADD CONSTRAINT `posts_ibfk_4` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

CREATE TABLE `designer_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('pending','changes_requested','converted','rejected') NOT NULL DEFAULT 'pending',
  `review_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_designer_submissions_company` (`company_id`),
  KEY `idx_designer_submissions_created_by` (`created_by`),
  KEY `idx_designer_submissions_status` (`status`),
  KEY `idx_designer_submissions_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_designer_submissions_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_designer_submissions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_designer_submissions_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `submission_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_submission_attachments_submission` (`submission_id`),
  KEY `idx_submission_attachments_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_submission_attachments_submission` FOREIGN KEY (`submission_id`) REFERENCES `designer_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_submission_attachments_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
