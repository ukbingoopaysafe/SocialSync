-- Migration: Enhance activity_log table for detailed logging
-- Makes post_id nullable and preserves logs on post deletion

-- 1. Drop existing foreign key on post_id
ALTER TABLE `activity_log` DROP FOREIGN KEY `activity_log_ibfk_1`;

-- 2. Make post_id nullable (for non-post actions like user management)
ALTER TABLE `activity_log` MODIFY `post_id` int(11) DEFAULT NULL;

-- 3. Expand old_value and new_value columns to TEXT for storing JSON diffs
ALTER TABLE `activity_log` MODIFY `old_value` TEXT DEFAULT NULL;
ALTER TABLE `activity_log` MODIFY `new_value` TEXT DEFAULT NULL;

-- 4. Re-add foreign key with ON DELETE SET NULL (preserve logs after post deletion)
ALTER TABLE `activity_log` ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL;
