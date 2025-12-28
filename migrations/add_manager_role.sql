-- =====================================================
-- Migration: Add Manager Role and REVIEWED Status
-- Date: 2025-12-28
-- Description: Adds 'manager' role and 'REVIEWED' workflow stage
-- =====================================================

-- Step 1: Add 'manager' to the role enum in users table
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin', 'staff', 'manager') NOT NULL DEFAULT 'staff';

-- Step 2: Add 'REVIEWED' to the status enum in posts table
-- The new workflow is: IDEA -> DRAFT -> PENDING_REVIEW -> REVIEWED -> APPROVED -> SCHEDULED -> PUBLISHED
ALTER TABLE `posts` 
MODIFY COLUMN `status` ENUM('IDEA','DRAFT','PENDING_REVIEW','REVIEWED','CHANGES_REQUESTED','APPROVED','SCHEDULED','PUBLISHED') NOT NULL DEFAULT 'DRAFT';

-- Verification queries (optional - run to confirm changes)
-- SHOW COLUMNS FROM users LIKE 'role';
-- SHOW COLUMNS FROM posts LIKE 'status';
