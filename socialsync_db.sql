-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 03:37 PM
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
(39, 6, 2, 'status_changed', 'PENDING_REVIEW', 'CHANGES_REQUESTED', 'محتاج البوست ذى ما هينزل بالظبط', '2025-12-22 14:33:49');

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
(4, 1, 'البوسكو-مصر-ايطاليا.jpeg', '694808615f38f_e3a4e89e.jpeg', 'uploads/2025/12/694808615f38f_e3a4e89e.jpeg', 'image', 'image/jpeg', 376838, 1, 3, '2025-12-21 14:46:57'),
(5, 3, 'البرج الايقوني كاروسيلArtboard 1.jpg', '69480ac13a977_85e1a4a9.jpg', 'uploads/2025/12/69480ac13a977_85e1a4a9.jpg', 'image', 'image/jpeg', 1042231, 1, 4, '2025-12-21 14:57:05'),
(6, 3, 'البرج الايقوني كاروسيلArtboard 2.jpg', '69480ac13b2fd_671aedce.jpg', 'uploads/2025/12/69480ac13b2fd_671aedce.jpg', 'image', 'image/jpeg', 932875, 0, 4, '2025-12-21 14:57:05'),
(7, 3, 'البرج الايقوني كاروسيلArtboard 3.jpg', '69480ac13ba40_50ad33f9.jpg', 'uploads/2025/12/69480ac13ba40_50ad33f9.jpg', 'image', 'image/jpeg', 939567, 0, 4, '2025-12-21 14:57:05'),
(9, 6, 'نصيحة عقارية.jpg', '694948a0cd9c9_8940e8cc.jpg', 'uploads/2025/12/694948a0cd9c9_8940e8cc.jpg', 'image', 'image/jpeg', 256368, 1, 4, '2025-12-22 13:33:20'),
(13, 7, 'قالب برومان تصميم.jpg', '69494a91281f8_88c3c6de.jpg', 'uploads/2025/12/69494a91281f8_88c3c6de.jpg', 'image', 'image/jpeg', 742616, 1, 4, '2025-12-22 13:41:37'),
(14, 4, 'Untitled design (4).jpg', '69494b9b981c5_a0355805.jpg', 'uploads/2025/12/69494b9b981c5_a0355805.jpg', 'image', 'image/jpeg', 10756, 1, 3, '2025-12-22 13:46:03'),
(15, 8, 'طراز معمارية ايطاليا والعاصمةArtboard 1.jpg', '6949546825bce_46f978da.jpg', 'uploads/2025/12/6949546825bce_46f978da.jpg', 'image', 'image/jpeg', 488177, 1, 1, '2025-12-22 14:23:36'),
(16, 8, 'طراز معمارية ايطاليا والعاصمةArtboard 2.jpg', '6949546826495_c2ea65c1.jpg', 'uploads/2025/12/6949546826495_c2ea65c1.jpg', 'image', 'image/jpeg', 555026, 0, 1, '2025-12-22 14:23:36');

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
(2, 2, 'review_needed', 'Review Needed', 'Post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' needs review', 3, 4, 0, '2025-12-21 15:05:52'),
(3, 4, 'approved', 'Post Approved', 'Your post \'⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.\' was approved!', 3, 2, 1, '2025-12-21 15:06:48'),
(4, 1, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 0, '2025-12-21 15:07:44'),
(5, 2, 'review_needed', 'Review Needed', 'Post \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\' needs review', 1, 3, 0, '2025-12-21 15:07:44'),
(6, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-21 15:08:33'),
(7, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-21 15:08:33'),
(8, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\': تعديل جودة الصورة على برنامج ريميني', 4, 2, 0, '2025-12-22 13:27:56'),
(9, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼\': داتا غير كاملة', 1, 2, 0, '2025-12-22 13:28:34'),
(10, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:46:15'),
(11, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:46:15'),
(12, 5, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:46:15'),
(13, 3, 'changes_requested', 'Changes Requested', 'Changes requested on \'لما تروح تعمل معاينه علي مكان غاليه و تحاول تهرب منهم..👇🏽\': اماكن غاليه \nأو مكان غالي \nالمكس ده مينفعش', 4, 2, 0, '2025-12-22 13:57:16'),
(14, 1, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:58:47'),
(15, 2, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:58:47'),
(16, 5, 'review_needed', 'Review Needed', 'Post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' needs review', 4, 3, 0, '2025-12-22 13:58:47'),
(17, 3, 'approved', 'Post Approved', 'Your post \'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽\' was approved!', 4, 2, 0, '2025-12-22 14:02:51'),
(18, 1, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 0, '2025-12-22 14:03:32'),
(19, 2, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 0, '2025-12-22 14:03:32'),
(20, 5, 'review_needed', 'Review Needed', 'Post \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\' needs review', 6, 4, 0, '2025-12-22 14:03:32'),
(21, 1, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 0, '2025-12-22 14:03:35'),
(22, 2, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 0, '2025-12-22 14:03:35'),
(23, 5, 'review_needed', 'Review Needed', 'Post \'تقول ايه لبرومان فى أخر السنة ؟\' needs review', 7, 4, 0, '2025-12-22 14:03:35'),
(24, 1, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 0, '2025-12-22 14:24:00'),
(25, 2, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 0, '2025-12-22 14:24:00'),
(26, 5, 'review_needed', 'Review Needed', 'Post \'مقارنة بين ايطاليا والعاصمة الجديدة\' needs review', 8, 1, 0, '2025-12-22 14:24:00'),
(27, 4, 'changes_requested', 'Changes Requested', 'Changes requested on \'تقول ايه لبرومان فى أخر السنة ؟\': محتاج البوست ذي ما هبنزل بالظبط', 7, 2, 0, '2025-12-22 14:33:15'),
(28, 4, 'changes_requested', 'Changes Requested', 'Changes requested on \'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026\': محتاج البوست ذى ما هينزل بالظبط', 6, 2, 0, '2025-12-22 14:33:49');

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
(1, 1, 'نظام غير شكل الحياه في ميلانو ايطاليا  وبرؤية مستقبليه حضارية لتجميع العالم داخل مصر 👉🏼', 'تعرف ان العاصمة الادراية بقت جزء من الطبيعه بعد مشروع بوسكو\nتصميم علي طراز معماري راقي مدمج بحياة استوائية وغابات وحدائق خضراء \nبجانب بحيرات صناعية تضيف تجديد مستمر للحياة في المنطقة\nده مش مجرد سكن ده توازن وحياة انقي 🏡🌳', '[\"Facebook\",\"Instagram\"]', 'CHANGES_REQUESTED', 1, 'normal', 3, 2, NULL, NULL, 'داتا غير كاملة', 2, '2025-12-22 15:28:34', '2025-12-21 14:44:43', '2025-12-22 13:28:34'),
(3, 1, '⭕مش كل برج بيبقى أيقونة…ومش كل مشروع بيغيّر خريطة الاستثمار.', 'تخيل تبقي ساكن أو مستثمر بجوار  أعلى مبنى في أفريقيا\r\n وواحدًا من أطول الأبراج في العالم.\r\n\r\n☑️البرج على ارتفاع :394 متر \r\n☑️موجود بداخل منطقة الأعمال المركزية (CBD) بالعاصمة الإدارية الجديدة \r\n☑️ يتكون البرج من 80 طابقًا متعدد الاستخدامات :\r\n     مكاتب، فنادق، وحدات سكنية، ومساحات تجارية.\r\n\r\nأهمية البرج مش في ارتفاعه بس،لكن في تأثيره المباشر على القيمة العقارية للمنطقة المحيطة:\r\n✔️رفع الطلب على السكن والاستثمار\r\n✔️جذب استثمارات أجنبية\r\n✔️تعزيز مكانة العاصمة كمركز إقليمي للأعمال\r\n\r\nلو بتدور على فرصة سكن أو استثمار مدروسة بالقرب من أهم معلم عقاري في مصر،\r\n كلمنا وناخد القرار الصح سوا.🤝\r\n\r\nكلمنا على رقم 01115790111 📞\r\nالاستثمار الحقيقي مش في مبنى…الاستثمار في موقع بيقود المستقبل.\r\n\r\n#البرج_الايقوني | #العاصمة _الجديدة | #Cbd  | #استثمار_عقارى | #معلم_عقاري |#برومان | #برومان_العقارية', '[\"Facebook\",\"Instagram\"]', 'APPROVED', 1, 'normal', 4, 2, NULL, NULL, NULL, NULL, NULL, '2025-12-21 14:57:05', '2025-12-21 15:06:48'),
(4, 1, 'لما تروح تعمل معاينه علي مكان غالي وتحاول تهرب منهم..👇🏽', '--', '[\"Facebook\",\"Instagram\"]', 'APPROVED', 0, 'normal', 3, 2, NULL, NULL, 'اماكن غاليه \nأو مكان غالي \nالمكس ده مينفعش', 2, '2025-12-22 15:57:16', '2025-12-21 15:00:30', '2025-12-22 14:02:51'),
(6, 1, 'مش سنة تجربة… دي سنة قرارات عقارية محسوبة2026', 'لو هتدخل 2026 بخطوة عقارية\r\nخليها مبنية على: موقع + توقيت + مطور + مسوق عقاري فاهم \r\n\r\nاللي هيفهم السوق في 2026\r\nهو اللي هيصنع قيمة حقيقية بعد كده.\r\n\r\n#عقار # |استثمار_عقاري | #RealEstate  | #شراء_شقة # |2026 |  # |موقع_توقيت_مطور', '[\"Facebook\",\"Instagram\"]', 'CHANGES_REQUESTED', 1, 'normal', 4, 2, NULL, NULL, 'محتاج البوست ذى ما هينزل بالظبط', 2, '2025-12-22 16:33:49', '2025-12-22 13:33:20', '2025-12-22 14:33:49'),
(7, 1, 'تقول ايه لبرومان فى أخر السنة ؟', 'احنا اتكلمنا كتير لكن جه الوقت اللى نسمعك فيه\r\n \r\n منتظرين كومينتتكم \r\n\r\n#برومان | #برومان_العقارية  | #مستشار_عقاري  | #2026', '[\"Facebook\",\"Instagram\"]', 'CHANGES_REQUESTED', 1, 'normal', 4, 2, NULL, NULL, 'محتاج البوست ذي ما هبنزل بالظبط', 2, '2025-12-22 16:33:15', '2025-12-22 13:41:37', '2025-12-22 14:33:15'),
(8, 1, 'مقارنة بين ايطاليا والعاصمة الجديدة', 'طراز معمارية ايطاليا والعاصمة الجديدة', '[\"Facebook\",\"Instagram\"]', 'PENDING_REVIEW', 1, 'normal', 1, 1, NULL, NULL, NULL, NULL, NULL, '2025-12-22 14:23:36', '2025-12-22 14:24:00');

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
(1, 'login:::1', 'login', 1, '2025-12-21 13:36:24', '2025-12-21 13:36:24'),
(2, 'login:192.168.1.10', 'login', 1, '2025-12-21 15:44:29', '2025-12-21 15:44:29'),
(3, 'login:192.168.1.19', 'login', 1, '2025-12-22 14:13:03', '2025-12-22 14:13:03'),
(4, 'login:192.168.1.18', 'login', 1, '2025-12-22 14:22:32', '2025-12-22 14:22:32');

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
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
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
(2, 'john', '$2y$10$cy3JBP.jyzir38.45xN6UOCPj65T1CFOPu1Q7UcKud1urj/0TTKFu', 'John', NULL, 'admin', 1, '2025-12-22 15:27:12', '2025-12-21 12:01:09', '2025-12-22 13:27:12'),
(3, 'nada', '$2y$10$IvExQlPa07ldw6Qe.oPW1Of.arp3s3o1lubx9hb3TFAQ1t1PKVFtC', 'Nada Mohammed', NULL, 'staff', 1, '2025-12-22 15:29:35', '2025-12-18 16:29:57', '2025-12-22 13:29:35'),
(4, 'sara', '$2y$10$uoknCOM.WvqMS6mZQF4GrehVWnljk6IIIydqbx00PqRNg1qOMjnT.', 'Sara Alaa', NULL, 'staff', 1, '2025-12-22 15:30:49', '2025-12-18 14:08:00', '2025-12-22 13:30:49'),
(5, 'alaa', '$2y$10$rMzTqCLTcguZRak/s4glkOkojOTP.RmgD/r6gppU0pntsXDSR7LG.', 'Alaa Almallah', NULL, 'admin', 1, '2025-12-22 12:38:53', '2025-12-21 15:43:48', '2025-12-22 10:38:53');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
