-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 04:38 PM
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
(1, 1, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 14:44:43'),
(2, 1, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:44:43'),
(3, 1, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:44:43'),
(4, 1, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:44:43'),
(5, 1, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-21 14:51:01'),
(6, 1, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-21 14:51:14'),
(8, 3, 4, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 14:57:05'),
(9, 3, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:57:05'),
(10, 3, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:57:05'),
(11, 3, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 14:57:05'),
(12, 4, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-21 15:00:30'),
(13, 4, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-21 15:00:30'),
(14, 1, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-21 15:01:07'),
(15, 3, 4, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 15:05:52'),
(16, 3, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-21 15:06:48'),
(17, 1, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 15:07:44'),
(18, 4, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-21 15:08:33'),
(20, 4, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'تعديل جودة الصورة على برنامج ريميني', '2025-12-22 13:27:56'),
(21, 1, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'داتا غير كاملة', '2025-12-22 13:28:34'),
(22, 6, 4, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-22 13:33:20'),
(23, 6, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-22 13:33:20'),
(24, 7, 4, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-22 13:41:37'),
(25, 7, 4, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-22 13:41:37'),
(26, 4, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-22 13:46:05'),
(27, 4, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-22 13:46:15'),
(28, 4, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'اماكن غاليه \nأو مكان غالي \nالمكس ده مينفعش', '2025-12-22 13:57:16'),
(29, 4, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-22 13:58:43'),
(30, 4, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-22 13:58:47'),
(31, 4, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-22 14:02:51'),
(32, 6, 4, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-22 14:03:32'),
(33, 7, 4, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-22 14:03:35'),
(34, 8, 1, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-22 14:23:36'),
(35, 8, 1, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-22 14:23:36'),
(36, 8, 1, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-22 14:23:36'),
(37, 8, 1, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-22 14:24:00'),
(38, 7, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'محتاج البوست ذي ما هبنزل بالظبط', '2025-12-22 14:33:15'),
(39, 6, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'محتاج البوست ذى ما هينزل بالظبط', '2025-12-22 14:33:49'),
(40, 1, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-25 09:55:56'),
(41, 1, 3, 'comment_added', NULL, NULL, NULL, '2025-12-25 09:58:01'),
(42, 1, 3, 'comment_added', NULL, NULL, NULL, '2025-12-25 09:59:14'),
(43, 1, 2, 'comment_added', NULL, NULL, NULL, '2025-12-25 09:59:57'),
(44, 1, 3, 'comment_added', NULL, NULL, NULL, '2025-12-25 10:00:16'),
(47, 1, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-25 14:10:16'),
(48, 3, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 25, 2025 4:11 PM', '2025-12-25 14:10:29'),
(49, 4, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 26, 2025 7:00 AM', '2025-12-25 14:10:35'),
(50, 3, 4, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-25 14:11:01'),
(51, 11, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-25 15:03:08'),
(52, 12, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-25 15:03:24'),
(53, 13, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-25 15:03:45'),
(54, 13, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-25 15:04:04'),
(55, 13, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-25 15:04:12'),
(56, 14, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-25 15:04:26'),
(57, 14, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-25 15:05:11'),
(58, 13, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-25 15:05:23'),
(59, 4, 3, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-28 09:07:36'),
(60, 14, 3, 'comment_added', NULL, NULL, NULL, '2025-12-28 09:22:39'),
(61, 7, 3, 'comment_added', NULL, NULL, NULL, '2025-12-28 09:23:14'),
(62, 7, 2, 'comment_added', NULL, NULL, NULL, '2025-12-28 09:23:37'),
(63, 14, 2, 'comment_added', NULL, NULL, NULL, '2025-12-28 09:23:48'),
(64, 1, 2, 'comment_added', NULL, NULL, NULL, '2025-12-28 10:14:23'),
(65, 13, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 10:15:01'),
(66, 15, 2, 'created', NULL, NULL, 'Created as IDEA', '2025-12-28 10:52:53'),
(67, 15, 2, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:52:54'),
(68, 15, 2, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:52:54'),
(69, 15, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 10:53:20'),
(70, 16, 3, 'created', NULL, NULL, 'Created as IDEA', '2025-12-28 10:54:29'),
(71, 16, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:54:30'),
(72, 16, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:54:30'),
(73, 16, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:54:30'),
(74, 16, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 10:54:31'),
(75, 6, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 10:55:46'),
(76, 7, 2, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-28 11:09:53'),
(78, 11, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 11:10:01'),
(79, 14, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 11:10:14'),
(80, 13, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 11:10:16'),
(82, 8, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 11:10:21'),
(83, 7, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 11:10:24'),
(84, 1, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 28, 2025 1:15 PM', '2025-12-28 11:10:44'),
(85, 8, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 28, 2025 1:20 PM', '2025-12-28 11:11:17'),
(86, 7, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 29, 2025 7:00 AM', '2025-12-28 11:11:25'),
(87, 7, 2, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-28 11:11:42'),
(88, 7, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 28, 2025 2:00 PM', '2025-12-28 11:12:02'),
(89, 1, 3, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-28 11:15:05'),
(90, 8, 1, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-28 11:20:47'),
(91, 8, 1, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-28 11:20:47'),
(92, 7, 4, 'auto_published', 'SCHEDULED', 'PUBLISHED', 'Automatically published on schedule', '2025-12-28 12:00:47'),
(93, 16, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 12:23:14'),
(94, 15, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 12:32:20'),
(95, 17, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-28 13:15:17'),
(96, 17, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-28 13:15:35'),
(99, 19, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-28 13:17:12'),
(100, 19, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-28 13:17:25'),
(101, 20, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-28 13:17:52'),
(102, 20, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 13:17:52'),
(103, 20, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 13:17:52'),
(104, 20, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 13:17:52'),
(105, 17, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 13:19:11'),
(107, 19, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 13:19:15'),
(108, 20, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 13:19:17'),
(109, 17, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 13:21:45'),
(111, 19, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 13:22:24'),
(112, 20, 2, 'status_changed', 'PENDING_REVIEW', 'APPROVED', NULL, '2025-12-28 13:22:56'),
(113, 17, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Jan 14, 2026 7:00 AM', '2025-12-28 13:23:19'),
(115, 19, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Jan 14, 2026 7:00 AM', '2025-12-28 13:23:34'),
(116, 20, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Jan 14, 2026 7:00 AM', '2025-12-28 13:23:43'),
(117, 21, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-28 14:43:34'),
(118, 21, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 14:43:34'),
(119, 21, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 14:43:34'),
(120, 21, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 14:43:34'),
(121, 21, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 14:44:11'),
(122, 21, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'test request 1', '2025-12-28 14:45:16'),
(123, 21, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-28 14:45:41'),
(124, 21, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'test request 2', '2025-12-28 14:46:08'),
(125, 21, 3, 'updated', NULL, NULL, 'Content updated', '2025-12-28 14:47:01'),
(126, 21, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-28 14:47:12'),
(127, 21, 2, 'status_changed', 'PENDING_REVIEW', 'REVIEWED', NULL, '2025-12-28 14:47:27'),
(128, 21, 5, 'status_changed', 'REVIEWED', 'CHANGES_REQUESTED', 'test manager request 1', '2025-12-28 14:48:27'),
(129, 21, 3, 'status_changed', 'CHANGES_REQUESTED', 'PENDING_REVIEW', NULL, '2025-12-28 14:49:10'),
(130, 21, 2, 'status_changed', 'PENDING_REVIEW', 'REVIEWED', NULL, '2025-12-28 14:49:19'),
(131, 13, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 14:49:43'),
(132, 21, 5, 'status_changed', 'REVIEWED', 'APPROVED', NULL, '2025-12-28 14:50:13'),
(133, 11, 2, 'status_changed', 'PENDING_REVIEW', 'REVIEWED', NULL, '2025-12-28 14:52:23'),
(134, 17, 2, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-28 14:56:10'),
(135, 19, 2, 'status_changed', 'SCHEDULED', 'APPROVED', NULL, '2025-12-28 14:59:35'),
(136, 17, 2, 'status_changed', 'APPROVED', 'SCHEDULED', 'Scheduled for Dec 29, 2025 7:00 AM', '2025-12-28 14:59:40'),
(137, 11, 2, 'updated', NULL, NULL, 'Content updated', '2025-12-28 15:00:48'),
(138, 19, 5, 'status_changed', 'APPROVED', 'REVIEWED', NULL, '2025-12-28 15:05:52'),
(139, 15, 2, 'status_changed', 'IDEA', 'DRAFT', NULL, '2025-12-28 15:08:59'),
(140, 12, 2, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 15:09:12'),
(141, 22, 3, 'created', NULL, NULL, 'Created as DRAFT', '2025-12-28 15:10:47'),
(142, 22, 3, 'media_uploaded', NULL, NULL, 'Media uploaded', '2025-12-28 15:10:48'),
(143, 22, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', NULL, '2025-12-28 15:10:51'),
(144, 12, 3, 'status_changed', 'PENDING_REVIEW', 'DRAFT', NULL, '2025-12-28 15:11:13'),
(145, 22, 3, 'status_changed', 'PENDING_REVIEW', 'DRAFT', NULL, '2025-12-28 15:11:17'),
(146, 11, 2, 'status_changed', 'REVIEWED', 'PENDING_REVIEW', NULL, '2025-12-28 15:12:28'),
(147, 22, 3, 'status_changed', 'DRAFT', 'PENDING_REVIEW', 'Nada Mohammed changed status: PENDING_REVIEW', '2025-12-28 15:32:55'),
(148, 22, 3, 'status_changed', 'PENDING_REVIEW', 'DRAFT', 'Nada Mohammed changed status: Recalled from review - returned to draft', '2025-12-28 15:33:02');

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

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 3, 'رجعهولي', '2025-12-25 09:58:00'),
(2, 1, 3, 'رجعهولي تاني', '2025-12-25 09:59:14'),
(3, 1, 2, 'تمام', '2025-12-25 09:59:57'),
(4, 1, 3, 'شكرا', '2025-12-25 10:00:16'),
(5, 14, 3, 'البابلابا', '2025-12-28 09:22:39'),
(6, 7, 3, 'ثقللق', '2025-12-28 09:23:14'),
(7, 7, 2, 'قلقلثلق', '2025-12-28 09:23:37'),
(8, 14, 2, 'قاقفف', '2025-12-28 09:23:48'),
(9, 1, 2, 'sreg', '2025-12-28 10:14:23');

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
(4, 1, 'البوسكو-مصر-ايطاليا.jpeg', '694808615f38f_e3a4e89e.jpeg', 'uploads/2025/12/694808615f38f_e3a4e89e.jpeg', 'image', 'image/jpeg', 376838, 1, 3, '2025-12-21 14:46:57'),
(5, 3, 'البرج الايقوني كاروسيلArtboard 1.jpg', '69480ac13a977_85e1a4a9.jpg', 'uploads/2025/12/69480ac13a977_85e1a4a9.jpg', 'image', 'image/jpeg', 1042231, 1, 4, '2025-12-21 14:57:05'),
(6, 3, 'البرج الايقوني كاروسيلArtboard 2.jpg', '69480ac13b2fd_671aedce.jpg', 'uploads/2025/12/69480ac13b2fd_671aedce.jpg', 'image', 'image/jpeg', 932875, 0, 4, '2025-12-21 14:57:05'),
(7, 3, 'البرج الايقوني كاروسيلArtboard 3.jpg', '69480ac13ba40_50ad33f9.jpg', 'uploads/2025/12/69480ac13ba40_50ad33f9.jpg', 'image', 'image/jpeg', 939567, 0, 4, '2025-12-21 14:57:05'),
(9, 6, 'نصيحة عقارية.jpg', '694948a0cd9c9_8940e8cc.jpg', 'uploads/2025/12/694948a0cd9c9_8940e8cc.jpg', 'image', 'image/jpeg', 256368, 1, 4, '2025-12-22 13:33:20'),
(13, 7, 'قالب برومان تصميم.jpg', '69494a91281f8_88c3c6de.jpg', 'uploads/2025/12/69494a91281f8_88c3c6de.jpg', 'image', 'image/jpeg', 742616, 1, 4, '2025-12-22 13:41:37'),
(14, 4, 'Untitled design (4).jpg', '69494b9b981c5_a0355805.jpg', 'uploads/2025/12/69494b9b981c5_a0355805.jpg', 'image', 'image/jpeg', 10756, 1, 3, '2025-12-22 13:46:03'),
(15, 8, 'طراز معمارية ايطاليا والعاصمةArtboard 1.jpg', '6949546825bce_46f978da.jpg', 'uploads/2025/12/6949546825bce_46f978da.jpg', 'image', 'image/jpeg', 488177, 1, 1, '2025-12-22 14:23:36'),
(16, 8, 'طراز معمارية ايطاليا والعاصمةArtboard 2.jpg', '6949546826495_c2ea65c1.jpg', 'uploads/2025/12/6949546826495_c2ea65c1.jpg', 'image', 'image/jpeg', 555026, 0, 1, '2025-12-22 14:23:36'),
(17, 15, 'نيو-منصورة-ميديكال-المنصورة-الجديدة-5.webp', '69510c0614de9_1a69c390.webp', 'uploads/2025/12/69510c0614de9_1a69c390.webp', 'image', 'image/webp', 237738, 1, 2, '2025-12-28 10:52:54'),
(18, 15, 'download.jpg', '69510c0626e5f_b0888055.jpg', 'uploads/2025/12/69510c0626e5f_b0888055.jpg', 'image', 'image/jpeg', 10306, 0, 2, '2025-12-28 10:52:54'),
(19, 16, 'Property-PrivateResidential.jpg', '69510c661a8ba_4695372b.jpg', 'uploads/2025/12/69510c661a8ba_4695372b.jpg', 'image', 'image/jpeg', 391964, 1, 3, '2025-12-28 10:54:30'),
(20, 16, 'rock-green-heliopolis.webp', '69510c6650c1b_89a18f1d.webp', 'uploads/2025/12/69510c6650c1b_89a18f1d.webp', 'image', 'image/webp', 42168, 0, 3, '2025-12-28 10:54:30'),
(21, 16, 'todd-kent-178j8tJrNlc-unsplash.jpg', '69510c6663a81_0428a8d2.jpg', 'uploads/2025/12/69510c6663a81_0428a8d2.jpg', 'image', 'image/jpeg', 7243272, 0, 3, '2025-12-28 10:54:30'),
(22, 16, 'Villas-in-THE-ESTATES-SODIC.jpg', '69510c66cdd14_fa8f8f08.jpg', 'uploads/2025/12/69510c66cdd14_fa8f8f08.jpg', 'image', 'image/jpeg', 165154, 0, 3, '2025-12-28 10:54:30'),
(23, 15, 'Dubai real estate is going to X.mp4', '695123526b7a6_175ae327.mp4', 'uploads/2025/12/695123526b7a6_175ae327.mp4', 'video', 'video/mp4', 8371524, 0, 2, '2025-12-28 12:32:18'),
(24, 17, '2b2291ae-e213-4087-9374-38e8679440e0.png', '69512d7668b6e_567e46f7.png', 'uploads/2025/12/69512d7668b6e_567e46f7.png', 'image', 'image/png', 3039100, 1, 3, '2025-12-28 13:15:34'),
(25, 17, '147670929-400x300.jpg', '69512d767b9f3_24ff2431.jpg', 'uploads/2025/12/69512d767b9f3_24ff2431.jpg', 'image', 'image/jpeg', 28518, 0, 3, '2025-12-28 13:15:34'),
(28, 19, 'pexels-binyaminmellish-186077.jpg', '69512de40ac41_4a6f9014.jpg', 'uploads/2025/12/69512de40ac41_4a6f9014.jpg', 'image', 'image/jpeg', 920097, 1, 3, '2025-12-28 13:17:24'),
(29, 19, 'Villas-in-THE-ESTATES-SODIC.jpg', '69512de41d97f_46fc0366.jpg', 'uploads/2025/12/69512de41d97f_46fc0366.jpg', 'image', 'image/jpeg', 165154, 0, 3, '2025-12-28 13:17:24'),
(30, 20, 'rock-green-heliopolis.webp', '69512e00a04b8_7e3a488c.webp', 'uploads/2025/12/69512e00a04b8_7e3a488c.webp', 'image', 'image/webp', 42168, 1, 3, '2025-12-28 13:17:52'),
(31, 20, 'todd-kent-178j8tJrNlc-unsplash.jpg', '69512e00b30b4_94a7f81f.jpg', 'uploads/2025/12/69512e00b30b4_94a7f81f.jpg', 'image', 'image/jpeg', 7243272, 0, 3, '2025-12-28 13:17:52'),
(32, 20, 'Villas-in-THE-ESTATES-SODIC.jpg', '69512e00c767a_f44d3fa5.jpg', 'uploads/2025/12/69512e00c767a_f44d3fa5.jpg', 'image', 'image/jpeg', 165154, 0, 3, '2025-12-28 13:17:52'),
(34, 21, 'WhatsApp Image 2025-12-04 at 16.10.38_dcd117dc.jpg', '6951421641a7d_8f5fe231.jpg', 'uploads/2025/12/6951421641a7d_8f5fe231.jpg', 'image', 'image/jpeg', 224289, 1, 3, '2025-12-28 14:43:34'),
(35, 21, 'WhatsApp Image 2025-12-04 at 16.16.09_99c1d221.jpg', '6951421656a5c_7640b4f5.jpg', 'uploads/2025/12/6951421656a5c_7640b4f5.jpg', 'image', 'image/jpeg', 833289, 0, 3, '2025-12-28 14:43:34'),
(36, 21, 'whatsapp_template_pic.jpg', '695142166989a_9cac3f9e.jpg', 'uploads/2025/12/695142166989a_9cac3f9e.jpg', 'image', 'image/jpeg', 101969, 0, 3, '2025-12-28 14:43:34'),
(37, 22, 'whatsapp_template_pic.jpg', '6951487801517_70c1cb4b.jpg', 'uploads/2025/12/6951487801517_70c1cb4b.jpg', 'image', 'image/jpeg', 101969, 1, 3, '2025-12-28 15:10:48');

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
(1, 1, 'review_needed', 'Review Needed', 'Post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' needs review', 3, 4, 0, '2025-12-21 15:05:52'),
(2, 2, 'review_needed', 'Review Needed', 'Post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' needs review', 3, 4, 1, '2025-12-21 15:05:52'),
(3, 4, 'approved', 'Post Approved', 'Your post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' was approved!', 3, 2, 1, '2025-12-21 15:06:48'),
(4, 1, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 0, '2025-12-21 15:07:44'),
(5, 2, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 1, '2025-12-21 15:07:44'),
(6, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-21 15:08:33'),
(7, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 1, '2025-12-21 15:08:33'),
(8, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\': تعديل جودة الصورة على برنامج ريميني', 4, 2, 1, '2025-12-22 13:27:56'),
(9, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\': داتا غير كاملة', 1, 2, 1, '2025-12-22 13:28:34'),
(10, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:46:15'),
(11, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 1, '2025-12-22 13:46:15'),
(12, 5, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 1, '2025-12-22 13:46:15'),
(13, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\': اماكن غاليه \nأو مكان غالي \nالمكس ده مينفعش', 4, 2, 1, '2025-12-22 13:57:16'),
(14, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:58:47'),
(15, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 1, '2025-12-22 13:58:47'),
(16, 5, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 1, '2025-12-22 13:58:47'),
(17, 3, 'approved', 'Post Approved', 'Your post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' was approved!', 4, 2, 1, '2025-12-22 14:02:51'),
(18, 1, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 0, '2025-12-22 14:03:32'),
(19, 2, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 1, '2025-12-22 14:03:32'),
(20, 5, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 1, '2025-12-22 14:03:32'),
(21, 1, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 0, '2025-12-22 14:03:35'),
(22, 2, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 1, '2025-12-22 14:03:35'),
(23, 5, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 1, '2025-12-22 14:03:35'),
(24, 1, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 0, '2025-12-22 14:24:00'),
(25, 2, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 1, '2025-12-22 14:24:00'),
(26, 5, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 1, '2025-12-22 14:24:00'),
(27, 4, 'changes_requested', 'Changes Requested', 'Changes requested on \'تقول ايه لبرومان فى أخر السنة ؟\': محتاج البوست ذي ما هبنزل بالظبط', 7, 2, 0, '2025-12-22 14:33:15'),
(28, 4, 'changes_requested', 'Changes Requested', 'Changes requested on \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\': محتاج البوست ذى ما هينزل بالظبط', 6, 2, 0, '2025-12-22 14:33:49'),
(29, 1, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 0, '2025-12-25 09:55:56'),
(30, 2, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 1, '2025-12-25 09:55:56'),
(31, 5, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 1, '2025-12-25 09:55:56'),
(32, 1, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': رجعهولي تاني', 1, 3, 0, '2025-12-25 09:59:14'),
(33, 2, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': رجعهولي تاني', 1, 3, 1, '2025-12-25 09:59:14'),
(34, 5, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': رجعهولي تاني', 1, 3, 1, '2025-12-25 09:59:14'),
(35, 3, 'comment', 'New Comment on Your Post', 'John commented on your post: تمام', 1, 2, 1, '2025-12-25 09:59:58'),
(36, 1, 'comment', 'New Comment', 'John commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': تمام', 1, 2, 0, '2025-12-25 09:59:58'),
(37, 5, 'comment', 'New Comment', 'John commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': تمام', 1, 2, 1, '2025-12-25 09:59:58'),
(38, 1, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': شكرا', 1, 3, 0, '2025-12-25 10:00:17'),
(39, 2, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': شكرا', 1, 3, 1, '2025-12-25 10:00:17'),
(40, 5, 'comment', 'New Comment', 'Nada Mohammed commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': شكرا', 1, 3, 1, '2025-12-25 10:00:17'),
(41, 3, 'approved', 'Post Approved', 'Your post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' was approved!', 1, 2, 1, '2025-12-25 14:10:16'),
(42, 4, 'scheduled', 'Post Scheduled', 'Your post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' is scheduled for publishing!', 3, 2, 0, '2025-12-25 14:10:29'),
(43, 3, 'scheduled', 'Post Scheduled', 'Your post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' is scheduled for publishing!', 4, 2, 1, '2025-12-25 14:10:35'),
(44, 4, 'published', 'Post Published', 'Your post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' has been automatically published!', 3, NULL, 0, '2025-12-25 14:11:01'),
(45, 1, 'review_needed', 'Review Needed', 'Post \'ؤضصص\' needs review', 14, 3, 0, '2025-12-25 15:05:11'),
(46, 2, 'review_needed', 'Review Needed', 'Post \'ؤضصص\' needs review', 14, 3, 1, '2025-12-25 15:05:11'),
(47, 5, 'review_needed', 'Review Needed', 'Post \'ؤضصص\' needs review', 14, 3, 1, '2025-12-25 15:05:11'),
(48, 1, 'review_needed', 'Review Needed', 'Post \'صضؤضصؤ\' needs review', 13, 3, 0, '2025-12-25 15:05:24'),
(49, 2, 'review_needed', 'Review Needed', 'Post \'صضؤضصؤ\' needs review', 13, 3, 1, '2025-12-25 15:05:24'),
(50, 5, 'review_needed', 'Review Needed', 'Post \'صضؤضصؤ\' needs review', 13, 3, 1, '2025-12-25 15:05:24'),
(51, 3, 'published', 'Post Published', 'Your post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' has been automatically published!', 4, NULL, 1, '2025-12-28 09:07:36'),
(52, 1, 'comment', 'New Comment', 'Nada Mohammed commented on post \'ؤضصص\': البابلابا', 14, 3, 0, '2025-12-28 09:22:39'),
(53, 2, 'comment', 'New Comment', 'Nada Mohammed commented on post \'ؤضصص\': البابلابا', 14, 3, 1, '2025-12-28 09:22:39'),
(54, 5, 'comment', 'New Comment', 'Nada Mohammed commented on post \'ؤضصص\': البابلابا', 14, 3, 1, '2025-12-28 09:22:39'),
(55, 4, 'comment', 'New Comment on Your Post', 'Nada Mohammed commented on your post: ثقللق', 7, 3, 0, '2025-12-28 09:23:14'),
(56, 1, 'comment', 'New Comment', 'Nada Mohammed commented on post \'تقول ايه لبرومان فى أخر السنة ؟\': ثقللق', 7, 3, 0, '2025-12-28 09:23:14'),
(57, 2, 'comment', 'New Comment', 'Nada Mohammed commented on post \'تقول ايه لبرومان فى أخر السنة ؟\': ثقللق', 7, 3, 1, '2025-12-28 09:23:14'),
(58, 5, 'comment', 'New Comment', 'Nada Mohammed commented on post \'تقول ايه لبرومان فى أخر السنة ؟\': ثقللق', 7, 3, 1, '2025-12-28 09:23:14'),
(59, 4, 'comment', 'New Comment on Your Post', 'John commented on your post: قلقلثلق', 7, 2, 0, '2025-12-28 09:23:37'),
(60, 1, 'comment', 'New Comment', 'John commented on post \'تقول ايه لبرومان فى أخر السنة ؟\': قلقلثلق', 7, 2, 0, '2025-12-28 09:23:37'),
(61, 5, 'comment', 'New Comment', 'John commented on post \'تقول ايه لبرومان فى أخر السنة ؟\': قلقلثلق', 7, 2, 1, '2025-12-28 09:23:37'),
(62, 3, 'comment', 'New Comment on Your Post', 'John commented on your post: قاقفف', 14, 2, 1, '2025-12-28 09:23:48'),
(63, 1, 'comment', 'New Comment', 'John commented on post \'ؤضصص\': قاقفف', 14, 2, 0, '2025-12-28 09:23:48'),
(64, 5, 'comment', 'New Comment', 'John commented on post \'ؤضصص\': قاقفف', 14, 2, 1, '2025-12-28 09:23:48'),
(65, 3, 'comment', 'New Comment on Your Post', 'John commented on your post: sreg', 1, 2, 1, '2025-12-28 10:14:23'),
(66, 1, 'comment', 'New Comment', 'John commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': sreg', 1, 2, 0, '2025-12-28 10:14:23'),
(67, 5, 'comment', 'New Comment', 'John commented on post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستق...\': sreg', 1, 2, 1, '2025-12-28 10:14:23'),
(68, 1, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 2, 0, '2025-12-28 11:09:53'),
(69, 2, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 2, 1, '2025-12-28 11:09:53'),
(70, 5, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 2, 1, '2025-12-28 11:09:53'),
(74, 1, 'review_needed', 'Review Needed', 'Post \'ؤضص\' needs review', 11, 2, 0, '2025-12-28 11:10:01'),
(75, 2, 'review_needed', 'Review Needed', 'Post \'ؤضص\' needs review', 11, 2, 1, '2025-12-28 11:10:01'),
(76, 5, 'review_needed', 'Review Needed', 'Post \'ؤضص\' needs review', 11, 2, 1, '2025-12-28 11:10:01'),
(77, 3, 'approved', 'Post Approved', 'Your post \'ؤضصص\' was approved!', 14, 2, 1, '2025-12-28 11:10:14'),
(78, 3, 'approved', 'Post Approved', 'Your post \'صضؤضصؤ\' was approved!', 13, 2, 1, '2025-12-28 11:10:16'),
(80, 1, 'approved', 'Post Approved', 'Your post \'مقارنة بين ايطاليا والعاصمة الجديدة\' was approved!', 8, 2, 0, '2025-12-28 11:10:21'),
(81, 4, 'approved', 'Post Approved', 'Your post \'تقول ايه لبرومان فى أخر السنة ؟\' was approved!', 7, 2, 0, '2025-12-28 11:10:24'),
(82, 3, 'scheduled', 'Post Scheduled', 'Your post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' is scheduled for publishing!', 1, 2, 1, '2025-12-28 11:10:44'),
(83, 1, 'scheduled', 'Post Scheduled', 'Your post \'مقارنة بين ايطاليا والعاصمة الجديدة\' is scheduled for publishing!', 8, 2, 0, '2025-12-28 11:11:17'),
(84, 4, 'scheduled', 'Post Scheduled', 'Your post \'تقول ايه لبرومان فى أخر السنة ؟\' is scheduled for publishing!', 7, 2, 0, '2025-12-28 11:11:25'),
(85, 4, 'approved', 'Post Approved', 'Your post \'تقول ايه لبرومان فى أخر السنة ؟\' was approved!', 7, 2, 0, '2025-12-28 11:11:42'),
(86, 4, 'scheduled', 'Post Scheduled', 'Your post \'تقول ايه لبرومان فى أخر السنة ؟\' is scheduled for publishing!', 7, 2, 0, '2025-12-28 11:12:02'),
(87, 3, 'published', 'Post Published', 'Your post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' has been automatically published!', 1, NULL, 1, '2025-12-28 11:15:05'),
(88, 1, 'published', 'Post Published', 'Your post \'مقارنة بين ايطاليا والعاصمة الجديدة\' has been automatically published!', 8, NULL, 0, '2025-12-28 11:20:47'),
(89, 1, 'published', 'Post Published', 'Your post \'مقارنة بين ايطاليا والعاصمة الجديدة\' has been automatically published!', 8, NULL, 0, '2025-12-28 11:20:47'),
(90, 4, 'published', 'Post Published', 'Your post \'تقول ايه لبرومان فى أخر السنة ؟\' has been automatically published!', 7, NULL, 0, '2025-12-28 12:00:47'),
(91, 1, 'review_needed', 'Review Needed', 'Post \'1\' needs review', 17, 3, 0, '2025-12-28 13:19:11'),
(92, 2, 'review_needed', 'Review Needed', 'Post \'1\' needs review', 17, 3, 1, '2025-12-28 13:19:11'),
(93, 5, 'review_needed', 'Review Needed', 'Post \'1\' needs review', 17, 3, 1, '2025-12-28 13:19:11'),
(97, 1, 'review_needed', 'Review Needed', 'Post \'3\' needs review', 19, 3, 0, '2025-12-28 13:19:15'),
(98, 2, 'review_needed', 'Review Needed', 'Post \'3\' needs review', 19, 3, 1, '2025-12-28 13:19:15'),
(99, 5, 'review_needed', 'Review Needed', 'Post \'3\' needs review', 19, 3, 1, '2025-12-28 13:19:15'),
(100, 1, 'review_needed', 'Review Needed', 'Post \'4\' needs review', 20, 3, 0, '2025-12-28 13:19:17'),
(101, 2, 'review_needed', 'Review Needed', 'Post \'4\' needs review', 20, 3, 1, '2025-12-28 13:19:17'),
(102, 5, 'review_needed', 'Review Needed', 'Post \'4\' needs review', 20, 3, 1, '2025-12-28 13:19:17'),
(103, 3, 'approved', 'Post Approved', 'Your post \'1\' was approved!', 17, 2, 1, '2025-12-28 13:21:45'),
(105, 3, 'approved', 'Post Approved', 'Your post \'3\' was approved!', 19, 2, 1, '2025-12-28 13:22:24'),
(106, 3, 'approved', 'Post Approved', 'Your post \'4\' was approved!', 20, 2, 1, '2025-12-28 13:22:56'),
(107, 3, 'scheduled', 'Post Scheduled', 'Your post \'1\' is scheduled for publishing!', 17, 2, 1, '2025-12-28 13:23:19'),
(109, 3, 'scheduled', 'Post Scheduled', 'Your post \'3\' is scheduled for publishing!', 19, 2, 1, '2025-12-28 13:23:34'),
(110, 3, 'scheduled', 'Post Scheduled', 'Your post \'4\' is scheduled for publishing!', 20, 2, 1, '2025-12-28 13:23:43'),
(111, 1, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 0, '2025-12-28 14:44:11'),
(112, 2, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 1, '2025-12-28 14:44:11'),
(113, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'test new feat 1\': test request 1', 21, 2, 1, '2025-12-28 14:45:16'),
(114, 1, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 0, '2025-12-28 14:45:41'),
(115, 2, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 1, '2025-12-28 14:45:41'),
(116, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'test new feat 1\': test request 2', 21, 2, 1, '2025-12-28 14:46:08'),
(117, 1, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 0, '2025-12-28 14:47:12'),
(118, 2, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 1, '2025-12-28 14:47:12'),
(119, 5, 'manager_approval_needed', 'Manager Approval Needed', 'Post \'test new feat 1\' needs your final approval', 21, 2, 1, '2025-12-28 14:47:27'),
(120, 3, 'reviewed', 'Post Under Manager Review', 'Your post \'test new feat 1\' is now under manager review', 21, 2, 1, '2025-12-28 14:47:27'),
(121, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'test new feat 1\': test manager request 1', 21, 5, 1, '2025-12-28 14:48:27'),
(122, 1, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 0, '2025-12-28 14:49:10'),
(123, 2, 'review_needed', 'Review Needed', 'Post \'test new feat 1\' needs review', 21, 3, 1, '2025-12-28 14:49:10'),
(124, 5, 'manager_approval_needed', 'Manager Approval Needed', 'Post \'test new feat 1\' needs your final approval', 21, 2, 1, '2025-12-28 14:49:19'),
(125, 3, 'reviewed', 'Post Under Manager Review', 'Your post \'test new feat 1\' is now under manager review', 21, 2, 1, '2025-12-28 14:49:19'),
(126, 3, 'approved', 'Post Approved', 'Your post \'test new feat 1\' was approved!', 21, 5, 1, '2025-12-28 14:50:13'),
(127, 1, 'post_approved', 'Post Ready for Scheduling', 'Post \'test new feat 1\' was approved and is ready for scheduling', 21, 5, 0, '2025-12-28 14:50:13'),
(128, 2, 'post_approved', 'Post Ready for Scheduling', 'Post \'test new feat 1\' was approved and is ready for scheduling', 21, 5, 1, '2025-12-28 14:50:13'),
(129, 5, 'manager_approval_needed', 'Manager Approval Needed', 'Post \'ؤضص\' needs your final approval', 11, 2, 1, '2025-12-28 14:52:24'),
(130, 3, 'reviewed', 'Post Under Manager Review', 'Your post \'ؤضص\' is now under manager review', 11, 2, 1, '2025-12-28 14:52:24'),
(131, 3, 'approved', 'Post Approved', 'Your post \'1\' was approved!', 17, 2, 1, '2025-12-28 14:56:10'),
(132, 1, 'post_approved', 'Post Ready for Scheduling', 'Post \'1\' was approved and is ready for scheduling', 17, 2, 0, '2025-12-28 14:56:10'),
(133, 3, 'approved', 'Post Approved', 'Your post \'3\' was approved!', 19, 2, 1, '2025-12-28 14:59:35'),
(134, 1, 'post_approved', 'Post Ready for Scheduling', 'Post \'3\' was approved and is ready for scheduling', 19, 2, 0, '2025-12-28 14:59:35'),
(135, 3, 'scheduled', 'Post Scheduled', 'Your post \'1\' is scheduled for publishing!', 17, 2, 1, '2025-12-28 14:59:40'),
(136, 5, 'manager_approval_needed', 'Manager Approval Needed', 'Post \'3\' needs your final approval', 19, 5, 1, '2025-12-28 15:05:52'),
(137, 3, 'reviewed', 'Post Under Manager Review', 'Your post \'3\' is now under manager review', 19, 5, 1, '2025-12-28 15:05:52'),
(138, 1, 'review_needed', 'Review Needed', 'Post \'ؤصضؤ\' needs review', 12, 2, 0, '2025-12-28 15:09:12'),
(139, 2, 'review_needed', 'Review Needed', 'Post \'ؤصضؤ\' needs review', 12, 2, 1, '2025-12-28 15:09:12'),
(140, 1, 'review_needed', 'Review Needed', 'Post \'يصضي\' needs review', 22, 3, 0, '2025-12-28 15:10:52'),
(141, 2, 'review_needed', 'Review Needed', 'Post \'يصضي\' needs review', 22, 3, 1, '2025-12-28 15:10:52'),
(142, 1, 'review_needed', 'Review Needed', 'Post \'ؤضص\' needs review', 11, 2, 0, '2025-12-28 15:12:28'),
(143, 2, 'review_needed', 'Review Needed', 'Post \'ؤضص\' needs review', 11, 2, 1, '2025-12-28 15:12:28'),
(144, 1, 'review_needed', 'Review Needed', 'Post \'يصضي\' needs review', 22, 3, 0, '2025-12-28 15:32:55'),
(145, 2, 'review_needed', 'Review Needed', 'Post \'يصضي\' needs review', 22, 3, 1, '2025-12-28 15:32:55');

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
  `status` enum('IDEA','DRAFT','PENDING_REVIEW','REVIEWED','CHANGES_REQUESTED','APPROVED','SCHEDULED','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
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
(1, 1, 'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼', 'تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳', '[\"Facebook\",\"Instagram\"]', 'PUBLISHED', 1, 'normal', 3, 2, '2025-12-28 13:15:00', '2025-12-28 13:15:05', 'داتا غير كاملة', 2, '2025-12-22 15:28:34', '2025-12-21 14:44:43', '2025-12-28 11:15:05'),
(3, 1, '⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.', 'تخيل تبقي ساكن أو مستثمر بجوار  أعلى مبنى في أفريقيا\r\n وواحدًا من أطول الأبراج في العالم.\r\n\r\n☑️البرج على ارتفاع :394 متر \r\n☑️موجود بداخل منطقة الأعمال المركزية (CBD) بالعاصمة الإدارية الجديدة \r\n☑️ يتكون البرج من 80 طابقًا متعدد الاستخدامات :\r\n     مكاتب، فنادق، وحدات سكنية، ومساحات تجارية.\r\n\r\nأهمية البرج مش في ارتفاعه بس،لكن في تأثيره المباشر على القيمة العقارية للمنطقة المحيطة:\r\n✔️رفع الطلب على السكن والاستثمار\r\n✔️جذب استثمارات أجنبية\r\n✔️تعزيز مكانة العاصمة كمركز إقليمي للأعمال\r\n\r\nلو بتدور على فرصة سكن أو استثمار مدروسة بالقرب من أهم معلم عقاري في مصر،\r\n كلمنا وناخد القرار الصح سوا.🤝\r\n\r\nكلمنا على رقم 01115790111 📞\r\nالاستثمار الحقيقي مش في مبنى…الاستثمار في موقع بيقود المستقبل.\r\n\r\n#البرج_الايقوني | #العاصمة _الجديدة | #Cbd  | #استثمار_عقارى | #معلم_عقاري |#برومان | #برومان_العقارية', '[\"Facebook\",\"Instagram\"]', 'PUBLISHED', 1, 'normal', 4, 2, '2025-12-25 16:11:00', '2025-12-25 16:11:01', NULL, NULL, NULL, '2025-12-21 14:57:05', '2025-12-25 14:11:01'),
(4, 1, 'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽', '--', '[\"Facebook\",\"Instagram\"]', 'PUBLISHED', 0, 'normal', 3, 2, '2025-12-26 07:00:00', '2025-12-28 11:07:36', 'اماكن غاليه \nأو مكان غالي \nالمكس ده مينفعش', 2, '2025-12-22 15:57:16', '2025-12-21 15:00:30', '2025-12-28 09:07:36'),
(6, 1, 'مش سنة تجربة… دي سنة قرارات عقارية محسوبة 2026', 'لو هتدخل 2026 بخطوة عقارية\nخليها مبنية على: موقع + توقيت + مطور + مسوق عقاري فاهم \n\nاللي هيفهم السوق في 2026\nهو اللي هيصنع قيمة حقيقية بعد كده.\n\n#عقار # |استثمار_عقاري | #RealEstate  | #شراء_شقة # |2026 |  # |موقع_توقيت_مطور', '[\"Facebook\",\"Instagram\"]', 'CHANGES_REQUESTED', 1, 'normal', 4, 2, NULL, NULL, 'محتاج البوست ذى ما هينزل بالظبط', 2, '2025-12-22 16:33:49', '2025-12-22 13:33:20', '2025-12-28 10:55:46'),
(7, 1, 'تقول ايه لبرومان فى أخر السنة ؟', 'احنا اتكلمنا كتير لكن جه الوقت اللى نسمعك فيه\r\n \r\n منتظرين كومينتتكم \r\n\r\n#برومان | #برومان_العقارية  | #مستشار_عقاري  | #2026', '[\"Facebook\",\"Instagram\"]', 'PUBLISHED', 1, 'normal', 4, 2, '2025-12-28 14:00:00', '2025-12-28 14:00:47', 'محتاج البوست ذي ما هبنزل بالظبط', 2, '2025-12-22 16:33:15', '2025-12-22 13:41:37', '2025-12-28 12:00:47'),
(8, 1, 'مقارنة بين ايطاليا والعاصمة الجديدة', 'طراز معمارية ايطاليا والعاصمة الجديدة', '[\"Facebook\",\"Instagram\"]', 'PUBLISHED', 1, 'normal', 1, 2, '2025-12-28 13:20:00', '2025-12-28 13:20:47', NULL, NULL, NULL, '2025-12-22 14:23:36', '2025-12-28 11:20:47'),
(11, 1, 'ؤضص', 'ضؤصؤصببب', '[\"YouTube\"]', 'PENDING_REVIEW', 0, 'normal', 3, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-25 15:03:08', '2025-12-28 15:12:28'),
(12, 1, 'ؤصضؤ', 'صؤضصؤ', '[\"Snapchat\",\"Website\"]', 'DRAFT', 0, 'normal', 3, 3, NULL, NULL, NULL, NULL, NULL, '2025-12-25 15:03:24', '2025-12-28 15:11:13'),
(13, 1, 'qqqq', 'qqqqqqqqqqq', '[\"Facebook\",\"Instagram\",\"LinkedIn\",\"X\",\"TikTok\",\"YouTube\",\"Snapchat\",\"Website\"]', 'APPROVED', 0, 'normal', 3, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-25 15:03:45', '2025-12-28 14:49:43'),
(14, 1, 'ؤضصص', 'صؤضصؤص', '[\"X\",\"Website\"]', 'APPROVED', 0, 'normal', 3, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-25 15:04:26', '2025-12-28 11:10:13'),
(15, 1, 'dqwqwdw', 'wdwdqwwqdqwdwdqwqwdqwdq', '[\"Facebook\",\"Instagram\",\"LinkedIn\",\"X\",\"TikTok\",\"YouTube\",\"Snapchat\",\"Website\"]', 'DRAFT', 0, 'normal', 2, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-28 10:52:53', '2025-12-28 15:08:59'),
(16, 1, 'asd', 'تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳', '[\"Facebook\",\"Instagram\",\"LinkedIn\",\"X\",\"TikTok\",\"YouTube\",\"Snapchat\",\"Website\"]', 'IDEA', 1, 'normal', 3, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-28 10:54:29', '2025-12-28 12:23:14'),
(17, 1, '1', '111111 \n111111 \n111111 \n111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 \n111111 \n111111 \n111111 \n111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 \n111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111 111111', '[\"Facebook\",\"Instagram\"]', 'SCHEDULED', 0, 'normal', 3, 2, '2025-12-29 07:00:00', NULL, NULL, NULL, NULL, '2025-12-28 13:15:17', '2025-12-28 14:59:40'),
(19, 1, '3', '22222 \n22222 22222 \n22222 22222 22222 \n22222 22222 22222 22222 \n22222 22222 22222 22222 22222 \n22222 22222 22222 22222 22222 22222 \n22222 22222 22222 22222 22222 22222 22222 \n22222 \n22222 22222 \n22222 22222 22222 \n22222 22222 22222 22222 \n22222 22222 22222 22222 22222 \n22222 22222 22222 22222 22222 22222 \n22222 22222 22222 22222 22222 22222 22222', '[\"X\",\"Snapchat\"]', 'REVIEWED', 0, 'normal', 3, 5, '2026-01-14 07:00:00', NULL, NULL, NULL, NULL, '2025-12-28 13:17:12', '2025-12-28 15:05:52'),
(20, 1, '4', '22222 \r\n22222 22222 \r\n22222 22222 22222 \r\n22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 22222 22222 \r\n22222 \r\n22222 22222 \r\n22222 22222 22222 \r\n22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 22222 \r\n22222 22222 22222 22222 22222 22222 22222', '[\"TikTok\",\"YouTube\",\"Snapchat\"]', 'SCHEDULED', 0, 'normal', 3, 2, '2026-01-14 07:00:00', NULL, NULL, NULL, NULL, '2025-12-28 13:17:52', '2025-12-28 13:23:43'),
(21, 1, 'test new feat 1', 'test edit new feat 1', '[\"X\",\"TikTok\"]', 'APPROVED', 0, 'normal', 3, 5, NULL, NULL, 'test manager request 1', 5, '2025-12-28 16:48:27', '2025-12-28 14:43:34', '2025-12-28 14:50:13'),
(22, 1, 'يصضي', 'ضصيي', '[\"X\",\"TikTok\"]', 'DRAFT', 0, 'normal', 3, 3, NULL, NULL, NULL, NULL, NULL, '2025-12-28 15:10:47', '2025-12-28 15:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'IP or action:IP composite key',
  `action` varchar(50) NOT NULL COMMENT 'Action type (login, api, upload)',
  `attempts` int(11) DEFAULT 1,
  `first_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `attempts`, `first_attempt`, `last_attempt`) VALUES
(1, 'login:::1', 'login', 3, '2025-12-28 14:41:34', '2025-12-28 14:42:16'),
(2, 'login:192.168.1.10', 'login', 1, '2025-12-21 15:44:29', '2025-12-21 15:44:29'),
(3, 'login:192.168.1.19', 'login', 1, '2025-12-22 14:13:03', '2025-12-22 14:13:03'),
(4, 'login:192.168.1.18', 'login', 1, '2025-12-22 14:22:32', '2025-12-22 14:22:32'),
(5, 'login:192.168.1.21', 'login', 2, '2025-12-28 13:20:31', '2025-12-28 13:20:54');

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
  `full_name` varchar(100) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','manager') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `avatar_url`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'yassin', '$2y$10$cp2rKBKbcM3gyoO6hOObvuNakH8NRR7nKMlbyewuZW6ePiDbLly3O', 'M. Yassin', NULL, 'admin', 1, '2025-12-22 16:22:33', '2025-12-21 11:46:09', '2025-12-22 14:22:33'),
(2, 'john', '$2y$10$ngSlt6no7Ya./YjFejS/vOQVPceJT6CF3WufUlGQkyz2eZejU4HtW', 'John', NULL, 'admin', 1, '2025-12-28 16:41:35', '2025-12-21 12:01:09', '2025-12-28 14:41:35'),
(3, 'nada', '$2y$10$brpri16CtrP.ZoIXUCNUju3KWF2iVQ2XjnHVhpUehtFZeoV.c8sU2', 'Nada Mohammed', NULL, 'staff', 1, '2025-12-28 16:42:16', '2025-12-18 16:29:57', '2025-12-28 14:42:16'),
(4, 'sara', '$2y$10$uoknCOM.WvqMS6mZQF4GrehVWnljk6IIIydqbx00PqRNg1qOMjnT.', 'Sara Alaa', NULL, 'staff', 1, '2025-12-22 15:30:49', '2025-12-18 14:08:00', '2025-12-22 13:30:49'),
(5, 'alaa', '$2y$10$rMzTqCLTcguZRak/s4glkOkojOTP.RmgD/r6gppU0pntsXDSR7LG.', 'Alaa Almallah', NULL, 'manager', 1, '2025-12-28 16:41:52', '2025-12-21 15:43:48', '2025-12-28 14:41:52');

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
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_identifier_action` (`identifier`,`action`),
  ADD KEY `idx_cleanup` (`last_attempt`);

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
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
