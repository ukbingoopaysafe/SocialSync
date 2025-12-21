-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 11:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `socialsync_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_value` varchar(100) DEFAULT NULL,
  `new_value` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `post_id`, `user_id`, `action`, `old_value`, `new_value`, `description`, `created_at`) VALUES
(27, 7, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-18 16:27:07'),
(29, 9, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-18 16:27:27'),
(31, 11, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-18 16:28:00'),
(32, 12, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-18 16:28:09'),
(33, 13, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-18 16:28:18'),
(34, 13, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-18 16:28:35'),
(35, 12, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-18 16:28:37'),
(36, 11, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-18 16:49:11'),
(37, 11, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-18 16:50:29'),
(38, 11, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-18 16:50:46'),
(40, 13, 1, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'test changes 1', '2025-12-19 13:10:09'),
(41, 13, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-19 13:12:27'),
(42, 13, 2, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-19 13:13:20'),
(43, 13, 1, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'test changes 2', '2025-12-19 13:14:51'),
(44, 13, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-19 13:15:30'),
(45, 13, 2, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-19 13:15:39'),
(46, 13, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-19 13:19:48'),
(47, 13, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 19, 2025 3:22 PM', '2025-12-19 13:20:31'),
(50, 9, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-19 14:42:02'),
(51, 12, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-19 14:42:07'),
(52, 11, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 20, 2025 7:00 AM', '2025-12-19 14:42:13'),
(53, 11, 2, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-20 19:48:28'),
(54, 14, 3, 'created', NULL, NULL, 'Created as IDEA', '2025-12-20 21:36:49'),
(55, 14, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-20 21:36:49'),
(56, 14, 1, 'status_changed', 'IDEA', 'DRAFT', NULL, '2025-12-20 21:37:14'),
(58, 14, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-20 21:38:09'),
(59, 14, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-20 21:38:59'),
(60, 14, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 20, 2025 11:43 PM', '2025-12-20 21:42:24'),
(61, 7, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-20 21:42:57'),
(62, 7, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-20 21:43:00'),
(63, 14, 3, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-20 21:43:00'),
(64, 9, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-20 21:43:01'),
(65, 9, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 21, 2025 7:00 PM', '2025-12-20 21:43:12'),
(66, 7, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 21, 2025 7:00 AM', '2025-12-20 21:43:15'),
(67, 12, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 21, 2025 7:00 AM', '2025-12-20 21:43:17'),
(68, 12, 1, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-20 21:43:36'),
(69, 7, 1, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-20 21:43:38'),
(70, 9, 1, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-20 21:43:41'),
(71, 9, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 20, 2025 11:46 PM', '2025-12-20 21:44:05'),
(72, 7, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 20, 2025 11:53 PM', '2025-12-20 21:44:13'),
(73, 12, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 20, 2025 11:58 PM', '2025-12-20 21:44:27'),
(74, 7, 2, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-20 22:07:26'),
(75, 9, 2, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-20 22:07:27'),
(76, 12, 2, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-20 22:07:27'),
(77, 16, 1, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-20 22:08:18'),
(78, 16, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-20 22:08:25'),
(79, 16, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-20 22:08:27'),
(80, 16, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 22, 2025 12:00 AM', '2025-12-20 22:08:45'),
(81, 16, 1, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-20 22:09:14'),
(82, 16, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 21, 2025 7:00 AM', '2025-12-20 22:09:20'),
(83, 17, 4, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-20 23:30:54'),
(84, 17, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-20 23:30:54'),
(85, 18, 4, 'created', NULL, NULL, 'Created as IDEA', '2025-12-20 23:32:31'),
(86, 18, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-20 23:32:31'),
(87, 18, 1, 'status_changed', 'IDEA', 'DRAFT', NULL, '2025-12-20 23:34:02'),
(88, 17, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-20 23:34:34'),
(89, 17, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-20 23:34:37'),
(90, 17, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 22, 2025 7:00 AM', '2025-12-20 23:34:40'),
(91, 18, 1, 'updated', NULL, NULL, 'Content updated', '2025-12-21 00:04:43'),
(92, 18, 1, 'updated', NULL, NULL, 'Content updated', '2025-12-21 00:05:09'),
(93, 18, 4, 'updated', NULL, NULL, 'Content updated', '2025-12-21 00:08:44'),
(94, 19, 4, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 00:09:33'),
(95, 19, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 00:09:33'),
(96, 19, 4, 'updated', NULL, NULL, 'Content updated', '2025-12-21 00:09:43'),
(97, 16, 1, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-21 09:11:13'),
(98, 19, 1, 'updated', NULL, NULL, 'Content updated', '2025-12-21 09:22:50'),
(99, 19, 1, 'updated', NULL, NULL, 'Content updated', '2025-12-21 09:23:06'),
(100, 19, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 09:23:22'),
(101, 19, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-21 09:23:27'),
(102, 19, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 22, 2025 7:00 AM', '2025-12-21 09:23:31'),
(103, 20, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 09:35:05'),
(104, 20, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 09:40:55'),
(105, 20, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-21 09:40:59'),
(106, 20, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 22, 2025 7:00 AM', '2025-12-21 09:41:07'),
(107, 20, 1, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-21 09:41:15'),
(108, 20, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 21, 2025 11:42 AM', '2025-12-21 09:41:32'),
(109, 21, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 09:45:14'),
(110, 21, 2, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 09:45:15'),
(111, 20, 3, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-21 09:45:15'),
(112, 21, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 09:45:21'),
(113, 21, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-21 09:45:32'),
(114, 21, 1, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 22, 2025 7:00 AM', '2025-12-21 09:45:36'),
(115, 22, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 09:45:49'),
(116, 22, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 09:45:52'),
(117, 22, 1, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-21 09:46:04'),
(118, 23, 2, 'created', NULL, NULL, 'Created as IDEA', '2025-12-21 09:46:22'),
(119, 24, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 09:46:34'),
(120, 25, 2, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 09:46:50'),
(121, 25, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 09:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `primary_color` varchar(20) DEFAULT '#1e3a5f',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `slug`, `logo_url`, `primary_color`, `created_at`) VALUES
(1, 'BroMan', 'broman', 'images/Final_Logo.png', '#1e3a5f', '2025-12-21 09:29:57'),
(2, 'Cible', 'cible', 'images/Logo_Cible.png', '#2563eb', '2025-12-21 09:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `media_files`
--

CREATE TABLE `media_files` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','video','document') NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_files`
--

INSERT INTO `media_files` (`id`, `post_id`, `original_name`, `file_name`, `file_path`, `file_type`, `mime_type`, `file_size`, `is_primary`, `uploaded_by`, `created_at`) VALUES
(4, 13, 'whatsapp_template_pic.jpg', '69454ff0ce91d_ccfae3b8.jpg', 'uploads/2025/12/69454ff0ce91d_ccfae3b8.jpg', 'image', 'image/jpeg', 101969, 1, 2, '2025-12-19 13:15:28'),
(5, 14, 'WhatsApp Image 2025-12-04 at 16.10.38_dcd117dc.jpg', '694716f19e498_edb8fc9a.jpg', 'uploads/2025/12/694716f19e498_edb8fc9a.jpg', 'image', 'image/jpeg', 224289, 1, 3, '2025-12-20 21:36:49'),
(6, 17, 'Villas-in-THE-ESTATES-SODIC.jpg', '694731aed6a5c_83e9df0f.jpg', 'uploads/2025/12/694731aed6a5c_83e9df0f.jpg', 'image', 'image/jpeg', 165154, 1, 4, '2025-12-20 23:30:54'),
(11, 18, 'splash_page_vid1.mp4', '694739d5d00c1_e59ed9be.mp4', 'uploads/2025/12/694739d5d00c1_e59ed9be.mp4', 'video', 'video/mp4', 7562409, 1, 1, '2025-12-21 00:05:41'),
(13, 18, 'Dubai real estate is going to X.mp4', '69473a8a96c51_9a16232e.mp4', 'uploads/2025/12/69473a8a96c51_9a16232e.mp4', 'video', 'video/mp4', 8371524, 0, 4, '2025-12-21 00:08:42'),
(16, 19, 'whatsapp_template_pic.jpg', '6947bc693d2d1_6335754d.jpg', 'uploads/2025/12/6947bc693d2d1_6335754d.jpg', 'image', 'image/jpeg', 101969, 1, 1, '2025-12-21 09:22:49'),
(17, 21, 'pexels-binyaminmellish-186077.jpg', '6947c1ab1bdb2_29c39510.jpg', 'uploads/2025/12/6947c1ab1bdb2_29c39510.jpg', 'image', 'image/jpeg', 920097, 1, 2, '2025-12-21 09:45:15');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `triggered_by` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `post_id`, `triggered_by`, `is_read`, `created_at`) VALUES
(12, 1, 'review_needed', 'Review Needed', 'Post \'سؤسؤ\' needs review', 13, 2, 1, '2025-12-18 16:28:35'),
(13, 1, 'review_needed', 'Review Needed', 'Post \'سؤسؤ\' needs review', 12, 2, 1, '2025-12-18 16:28:37'),
(14, 1, 'review_needed', 'Review Needed', 'Post \'ؤؤس\' needs review', 11, 1, 1, '2025-12-18 16:50:30'),
(15, 2, 'approved', 'Post Approved', 'Your post \'ؤؤس\' was approved!', 11, 1, 1, '2025-12-18 16:50:46'),
(16, 2, 'changes_requested', 'Changes Requested', 'Changes requested on \'سؤسؤ\': test changes 1', 13, 1, 1, '2025-12-19 13:10:09'),
(17, 1, 'review_needed', 'Review Needed', 'Post \'مقال للموقع الإلكتروني\' needs review', 13, 2, 1, '2025-12-19 13:13:20'),
(18, 2, 'changes_requested', 'Changes Requested', 'Changes requested on \'مقال للموقع الإلكتروني\': test changes 2', 13, 1, 1, '2025-12-19 13:14:51'),
(19, 1, 'review_needed', 'Review Needed', 'Post \'مقال للموقع الإلكتروني\' needs review', 13, 2, 1, '2025-12-19 13:15:39'),
(20, 2, 'approved', 'Post Approved', 'Your post \'مقال للموقع الإلكتروني\' was approved!', 13, 1, 1, '2025-12-19 13:19:48'),
(21, 2, 'scheduled', 'Post Scheduled', 'Your post \'مقال للموقع الإلكتروني\' is scheduled for publishing!', 13, 1, 1, '2025-12-19 13:20:31'),
(24, 1, 'review_needed', 'Review Needed', 'Post \'سؤؤس\' needs review', 9, 1, 1, '2025-12-19 14:42:02'),
(25, 2, 'approved', 'Post Approved', 'Your post \'سؤسؤ\' was approved!', 12, 1, 1, '2025-12-19 14:42:07'),
(26, 2, 'scheduled', 'Post Scheduled', 'Your post \'ؤؤس\' is scheduled for publishing!', 11, 1, 1, '2025-12-19 14:42:14'),
(27, 2, 'published', 'Post Published', 'Your post \'ؤؤس\' has been automatically published!', 11, NULL, 1, '2025-12-20 19:48:28'),
(28, 1, 'review_needed', 'Review Needed', 'Post \'ليس من الضروري أن تكون الأفضل لتبدأ\' needs review', 14, 3, 1, '2025-12-20 21:38:10'),
(29, 3, 'approved', 'Post Approved', 'Your post \'ليس من الضروري أن تكون الأفضل لتبدأ\' was approved!', 14, 1, 1, '2025-12-20 21:38:59'),
(30, 3, 'scheduled', 'Post Scheduled', 'Your post \'ليس من الضروري أن تكون الأفضل لتبدأ\' is scheduled for publishing!', 14, 1, 1, '2025-12-20 21:42:24'),
(31, 1, 'review_needed', 'Review Needed', 'Post \'سؤس\' needs review', 7, 1, 1, '2025-12-20 21:42:57'),
(32, 2, 'approved', 'Post Approved', 'Your post \'سؤس\' was approved!', 7, 1, 1, '2025-12-20 21:43:00'),
(33, 3, 'published', 'Post Published', 'Your post \'ليس من الضروري أن تكون الأفضل لتبدأ\' has been automatically published!', 14, NULL, 1, '2025-12-20 21:43:00'),
(34, 2, 'approved', 'Post Approved', 'Your post \'سؤؤس\' was approved!', 9, 1, 1, '2025-12-20 21:43:02'),
(35, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤؤس\' is scheduled for publishing!', 9, 1, 1, '2025-12-20 21:43:12'),
(36, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤس\' is scheduled for publishing!', 7, 1, 1, '2025-12-20 21:43:15'),
(37, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤسؤ\' is scheduled for publishing!', 12, 1, 1, '2025-12-20 21:43:17'),
(38, 2, 'approved', 'Post Approved', 'Your post \'سؤسؤ\' was approved!', 12, 1, 1, '2025-12-20 21:43:36'),
(39, 2, 'approved', 'Post Approved', 'Your post \'سؤس\' was approved!', 7, 1, 1, '2025-12-20 21:43:39'),
(40, 2, 'approved', 'Post Approved', 'Your post \'سؤؤس\' was approved!', 9, 1, 1, '2025-12-20 21:43:41'),
(41, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤؤس\' is scheduled for publishing!', 9, 1, 1, '2025-12-20 21:44:05'),
(42, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤس\' is scheduled for publishing!', 7, 1, 1, '2025-12-20 21:44:13'),
(43, 2, 'scheduled', 'Post Scheduled', 'Your post \'سؤسؤ\' is scheduled for publishing!', 12, 1, 1, '2025-12-20 21:44:27'),
(44, 2, 'published', 'Post Published', 'Your post \'سؤس\' has been automatically published!', 7, NULL, 1, '2025-12-20 22:07:27'),
(45, 2, 'published', 'Post Published', 'Your post \'سؤؤس\' has been automatically published!', 9, NULL, 1, '2025-12-20 22:07:27'),
(46, 2, 'published', 'Post Published', 'Your post \'سؤسؤ\' has been automatically published!', 12, NULL, 1, '2025-12-20 22:07:27'),
(47, 1, 'review_needed', 'Review Needed', 'Post \'نمتن\' needs review', 16, 1, 1, '2025-12-20 22:08:25'),
(48, 1, 'approved', 'Post Approved', 'Your post \'نمتن\' was approved!', 16, 1, 1, '2025-12-20 22:08:27'),
(49, 1, 'scheduled', 'Post Scheduled', 'Your post \'نمتن\' is scheduled for publishing!', 16, 1, 1, '2025-12-20 22:08:45'),
(50, 1, 'approved', 'Post Approved', 'Your post \'نمتن\' was approved!', 16, 1, 1, '2025-12-20 22:09:14'),
(51, 1, 'scheduled', 'Post Scheduled', 'Your post \'نمتن\' is scheduled for publishing!', 16, 1, 1, '2025-12-20 22:09:20'),
(52, 1, 'review_needed', 'Review Needed', 'Post \'rere\' needs review', 17, 1, 1, '2025-12-20 23:34:34'),
(53, 4, 'approved', 'Post Approved', 'Your post \'rere\' was approved!', 17, 1, 1, '2025-12-20 23:34:37'),
(54, 4, 'scheduled', 'Post Scheduled', 'Your post \'rere\' is scheduled for publishing!', 17, 1, 1, '2025-12-20 23:34:40'),
(55, 1, 'published', 'Post Published', 'Your post \'نمتن\' has been automatically published!', 16, NULL, 1, '2025-12-21 09:11:13'),
(56, 1, 'review_needed', 'Review Needed', 'Post \'reregt4\' needs review', 19, 1, 0, '2025-12-21 09:23:22'),
(57, 4, 'approved', 'Post Approved', 'Your post \'reregt4\' was approved!', 19, 1, 1, '2025-12-21 09:23:27'),
(58, 4, 'scheduled', 'Post Scheduled', 'Your post \'reregt4\' is scheduled for publishing!', 19, 1, 1, '2025-12-21 09:23:31'),
(59, 1, 'review_needed', 'Review Needed', 'Post \'vourevbje\' needs review', 20, 1, 0, '2025-12-21 09:40:55'),
(60, 3, 'approved', 'Post Approved', 'Your post \'vourevbje\' was approved!', 20, 1, 1, '2025-12-21 09:40:59'),
(61, 3, 'scheduled', 'Post Scheduled', 'Your post \'vourevbje\' is scheduled for publishing!', 20, 1, 1, '2025-12-21 09:41:07'),
(62, 3, 'approved', 'Post Approved', 'Your post \'vourevbje\' was approved!', 20, 1, 1, '2025-12-21 09:41:15'),
(63, 3, 'scheduled', 'Post Scheduled', 'Your post \'vourevbje\' is scheduled for publishing!', 20, 1, 1, '2025-12-21 09:41:32'),
(64, 3, 'published', 'Post Published', 'Your post \'vourevbje\' has been automatically published!', 20, NULL, 1, '2025-12-21 09:45:15'),
(65, 1, 'review_needed', 'Review Needed', 'Post \'ssf\' needs review', 21, 2, 0, '2025-12-21 09:45:21'),
(66, 2, 'approved', 'Post Approved', 'Your post \'ssf\' was approved!', 21, 1, 1, '2025-12-21 09:45:32'),
(67, 2, 'scheduled', 'Post Scheduled', 'Your post \'ssf\' is scheduled for publishing!', 21, 1, 1, '2025-12-21 09:45:36'),
(68, 1, 'review_needed', 'Review Needed', 'Post \'ؤثضصيء\' needs review', 22, 3, 0, '2025-12-21 09:45:52'),
(69, 3, 'approved', 'Post Approved', 'Your post \'ؤثضصيء\' was approved!', 22, 1, 1, '2025-12-21 09:46:04'),
(70, 1, 'review_needed', 'Review Needed', 'Post \'ثبصب\' needs review', 25, 2, 0, '2025-12-21 09:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `platforms` text DEFAULT NULL,
  `status` enum('IDEA','DRAFT','PENDING_REVIEW','CHANGES_REQUESTED','APPROVED','SCHEDULED','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  `urgency` tinyint(1) DEFAULT 0,
  `priority` enum('low','normal','high','critical') DEFAULT 'normal',
  `author_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `published_date` datetime DEFAULT NULL,
  `change_request_reason` text DEFAULT NULL,
  `change_requested_by` int(11) DEFAULT NULL,
  `change_requested_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `company_id`, `title`, `content`, `platforms`, `status`, `urgency`, `priority`, `author_id`, `reviewer_id`, `scheduled_date`, `published_date`, `change_request_reason`, `change_requested_by`, `change_requested_at`, `created_at`, `updated_at`) VALUES
(7, 1, 'سؤس', 'سؤؤ', '[\"Facebook\"]', 'PUBLISHED', 0, 'normal', 2, 1, '2025-12-20 23:53:00', '2025-12-21 00:07:26', NULL, NULL, NULL, '2025-12-18 16:27:07', '2025-12-20 23:27:43'),
(9, 1, 'سؤؤس', 'سؤس', '[\"X\"]', 'PUBLISHED', 0, 'normal', 2, 1, '2025-12-20 23:46:00', '2025-12-21 00:07:27', NULL, NULL, NULL, '2025-12-18 16:27:27', '2025-12-20 23:27:43'),
(11, 1, 'ؤؤس', 'سؤسؤ', '[\"YouTube\"]', 'PUBLISHED', 0, 'normal', 2, 1, '2025-12-20 07:00:00', '2025-12-20 21:48:28', NULL, NULL, NULL, '2025-12-18 16:28:00', '2025-12-20 23:27:43'),
(12, 1, 'سؤسؤ', 'سؤسؤسؤس', '[\"Snapchat\"]', 'PUBLISHED', 0, 'normal', 2, 1, '2025-12-20 23:58:00', '2025-12-21 00:07:27', NULL, NULL, NULL, '2025-12-18 16:28:09', '2025-12-20 23:27:43'),
(13, 1, 'مقال للموقع الإلكتروني', 'عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة عن العاصمة', '[\"Website\"]', 'PUBLISHED', 1, 'normal', 2, 1, '2025-12-19 15:22:00', '2025-12-19 16:29:23', 'test changes 2', 1, '2025-12-19 15:14:51', '2025-12-18 16:28:18', '2025-12-20 23:27:43'),
(14, 1, 'ليس من الضروري أن تكون الأفضل لتبدأ', 'ليس من الضروري أن تكون الأفضل لتبدأ، لكن يجب أن تبدأ لتكون الأفضل. 🚀\r\nفي 2025، لا تنتظر الوقت المثالي.. الوقت المثالي هو \'الآن\'. ما هي الخطوة التي أجلتها طويلاً وستبدأ بها اليوم؟ 👇', '[\"Facebook\"]', 'PUBLISHED', 0, 'normal', 3, 1, '2025-12-20 23:43:00', '2025-12-20 23:43:00', NULL, NULL, NULL, '2025-12-20 21:36:49', '2025-12-20 23:27:43'),
(16, 1, 'نمتن', 'مكنك', '[\"YouTube\"]', 'PUBLISHED', 0, 'normal', 1, 1, '2025-12-21 07:00:00', '2025-12-21 11:11:13', NULL, NULL, NULL, '2025-12-20 22:08:18', '2025-12-21 09:11:13'),
(17, 1, 'rere', 'rervcqefefbvrv3f43', '[\"Facebook\",\"Instagram\"]', 'SCHEDULED', 0, 'normal', 4, 1, '2025-12-22 07:00:00', NULL, NULL, NULL, NULL, '2025-12-20 23:30:54', '2025-12-20 23:34:40'),
(18, 1, 'yyyhewgr', 'jnthbew3vecwqfwfwefwefw', '[\"TikTok\",\"YouTube\"]', 'DRAFT', 0, 'normal', 4, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-20 23:32:31', '2025-12-20 23:34:02'),
(19, 1, 'reregt4', 'rtvt4v4', '[\"Facebook\",\"Instagram\",\"LinkedIn\",\"X\",\"TikTok\",\"YouTube\",\"Snapchat\"]', 'SCHEDULED', 0, 'normal', 4, 1, '2025-12-22 07:00:00', NULL, NULL, NULL, NULL, '2025-12-21 00:09:32', '2025-12-21 09:23:31'),
(20, 2, 'vourevbje', 'pverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,opverpove,o', '[\"Snapchat\"]', 'PUBLISHED', 0, 'normal', 3, 1, '2025-12-21 11:42:00', '2025-12-21 11:45:15', NULL, NULL, NULL, '2025-12-21 09:35:05', '2025-12-21 09:45:15'),
(21, 2, 'ssf', 'yunyyenenؤؤؤضض', '[\"Facebook\",\"Instagram\"]', 'SCHEDULED', 0, 'normal', 2, 1, '2025-12-22 07:00:00', NULL, NULL, NULL, NULL, '2025-12-21 09:45:14', '2025-12-21 09:45:36'),
(22, 2, 'ؤثضصيء', 'ضرؤثصرصصرصر', '[\"YouTube\"]', 'APPROVED', 1, 'normal', 3, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-21 09:45:49', '2025-12-21 09:46:04'),
(23, 2, 'قبص', 'يصضضص', '[\"TikTok\"]', 'IDEA', 0, 'normal', 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 09:46:22', '2025-12-21 09:46:22'),
(24, 2, 'صثلل', 'قثتث5ناص', '[\"Website\"]', 'DRAFT', 0, 'normal', 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 09:46:34', '2025-12-21 09:46:34'),
(25, 2, 'ثبصب', 'بصثصثبصب', '[\"LinkedIn\"]', 'PENDING_REVIEW', 0, 'normal', 2, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-21 09:46:50', '2025-12-21 09:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `full_name`, `avatar_url`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$Za0dwanLKlHDnLyKvPlnuugpdpfuSSyzh3IdCDfggkwEjg7LEXJpG', 'admin@broman.local', 'System Administrator', NULL, 'admin', 1, '2025-12-21 11:40:51', '2025-12-18 14:08:00', '2025-12-21 09:40:51'),
(2, 'sara', '$2y$10$KnVJTnErch2HpZDBoOPvGO3gdR7C9vMITj.rsyGVBtXrrr7R6Taea', 'staff@broman.local', 'Sara Alaa', NULL, 'staff', 1, '2025-12-21 11:41:51', '2025-12-18 14:08:00', '2025-12-21 09:41:51'),
(3, 'nada', '$2y$10$mFvhqCMWYmDPq0pMcwbyreRniMgsmfCAIMNtk99TXhYKbQkfVa3Ra', 'nada@broman.local', 'Nada Mohamed', NULL, 'staff', 1, '2025-12-21 11:48:47', '2025-12-18 16:29:57', '2025-12-21 09:48:47'),
(4, 'mona', '$2y$10$V2ejU5hP7TYbkwVaMRuZNenDNVEhpGrQ2m.vNgpHFGTg3TK3r/jX2', '', 'Mona', NULL, 'staff', 1, '2025-12-21 11:33:06', '2025-12-20 20:28:25', '2025-12-21 09:33:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_post` (`post_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_post` (`post_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `media_files`
--
ALTER TABLE `media_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_post` (`post_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `triggered_by` (`triggered_by`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `change_requested_by` (`change_requested_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_author` (`author_id`),
  ADD KEY `idx_posts_company` (`company_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_files`
--
ALTER TABLE `media_files`
  ADD CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`triggered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`change_requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
