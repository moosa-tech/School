-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2026 at 01:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_saas`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `school_id`, `name`, `starts_on`, `ends_on`, `is_current`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-2026', '2025-04-01', '2026-03-31', 0, 'active', '2026-10-09 06:56:17', '2026-10-11 07:50:27'),
(2, 1, '2026-2027', '2026-10-10', '2027-10-10', 1, 'active', '2026-10-09 23:59:55', '2026-10-11 07:50:27'),
(3, 3, '2026-2027', '2026-10-10', '2027-10-10', 1, 'active', '2026-10-10 10:42:08', '2026-10-10 10:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `audience` enum('all','teachers','students','parents','staff') NOT NULL DEFAULT 'all',
  `publish_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `school_id`, `title`, `body`, `audience`, `publish_at`, `expires_at`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Parents Teacher Meeting', 'Tomorrow On Sunday is parents teacher meeting be prepare', 'all', '2026-10-11 00:12:46', NULL, 2, 'published', '2026-10-11 00:12:46', '2026-10-11 00:12:46');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_change_logs`
--

CREATE TABLE `attendance_change_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_attendance_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` varchar(20) NOT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `taken_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','submitted','finalized','corrected') NOT NULL DEFAULT 'draft',
  `finalized_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `school_id`, `academic_year_id`, `class_id`, `section_id`, `attendance_date`, `taken_by`, `status`, `finalized_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, 9, '2026-10-09', 2, 'finalized', '2026-10-09 14:14:23', '2026-10-09 14:14:23', '2026-10-09 14:14:23'),
(2, 1, 1, 1, 1, '2026-10-09', 2, 'finalized', '2026-10-10 01:20:47', '2026-10-09 16:26:17', '2026-10-10 01:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_user_id`, `school_id`, `action`, `entity_type`, `entity_id`, `metadata`, `created_at`) VALUES
(1, NULL, NULL, 'login_credentials_verified', 'User', 1, '{\"ip\":\"127.0.0.1\",\"channel\":\"email\"}', '2026-10-09 07:03:31'),
(2, 1, NULL, 'login_success_mfa_completed', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 07:03:40'),
(3, NULL, NULL, 'login_credentials_verified', 'User', 1, '{\"ip\":\"127.0.0.1\",\"channel\":\"email\"}', '2026-10-09 07:19:09'),
(4, 1, NULL, 'login_success_mfa_completed', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 07:19:18'),
(5, NULL, NULL, 'login_credentials_verified', 'User', 1, '{\"ip\":\"127.0.0.1\",\"channel\":\"email\"}', '2026-10-09 07:19:27'),
(6, 1, NULL, 'login_success_mfa_completed', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 07:19:30'),
(7, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 07:19:54'),
(8, NULL, NULL, 'login_credentials_verified', 'User', 2, '{\"ip\":\"127.0.0.1\",\"channel\":\"email\"}', '2026-10-09 07:20:00'),
(9, 2, NULL, 'login_success_mfa_completed', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 07:20:03'),
(10, 2, 1, 'student_status_changed', 'Student', 1, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-09 07:21:33'),
(11, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 07:24:10'),
(12, NULL, NULL, 'login_credentials_verified', 'User', 3, '{\"ip\":\"127.0.0.1\",\"channel\":\"email\"}', '2026-10-09 07:24:16'),
(13, 3, NULL, 'login_success_mfa_completed', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 07:24:19'),
(14, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 11:28:49'),
(15, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 11:30:28'),
(16, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 11:30:58'),
(17, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 11:32:56'),
(18, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 11:33:24'),
(19, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 12:00:37'),
(20, 2, 1, 'student_status_changed', 'Student', 1, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-09 12:01:24'),
(21, 2, 1, 'student_status_changed', 'Student', 1, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-09 12:01:43'),
(22, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 12:02:23'),
(23, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 12:02:50'),
(24, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 12:03:15'),
(25, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 12:03:29'),
(26, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 12:03:50'),
(27, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 12:04:09'),
(28, 5, 1, 'logout', 'User', 5, NULL, '2026-10-09 12:04:28'),
(29, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 12:04:58'),
(30, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:04:33'),
(31, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 14:08:12'),
(32, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:09:04'),
(33, 2, 1, 'student_admitted', 'Student', 2, '{\"admission_number\":\"ADM-0012\",\"name\":\"Muhammad Moosa\"}', '2026-10-09 14:13:29'),
(34, 2, 1, 'attendance_marked', 'AttendanceSession', 1, '{\"date\":\"2026-10-09\",\"class_id\":\"5\",\"status\":\"finalized\"}', '2026-10-09 14:14:23'),
(35, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 14:15:57'),
(36, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:16:07'),
(37, 1, 1, 'subscription_payment_recorded', 'Subscription', 1, '{\"amount\":\"99.00\",\"school_id\":1}', '2026-10-09 14:17:39'),
(38, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 14:18:32'),
(39, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:18:44'),
(40, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 14:20:22'),
(41, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:30:43'),
(42, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 14:33:30'),
(43, 4, NULL, 'login_success', 'User', 4, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 14:33:40'),
(44, 4, 1, 'logout', 'User', 4, NULL, '2026-10-09 14:33:56'),
(45, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:11:26'),
(46, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 15:12:48'),
(47, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:13:01'),
(48, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 15:13:40'),
(49, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:15:42'),
(50, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 15:17:37'),
(51, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:18:18'),
(52, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 15:18:39'),
(53, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:19:01'),
(54, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 15:19:26'),
(55, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:19:32'),
(56, 5, 1, 'logout', 'User', 5, NULL, '2026-10-09 15:20:12'),
(57, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:23:04'),
(58, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 15:23:51'),
(59, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:23:56'),
(60, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 15:26:05'),
(61, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:26:10'),
(62, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 15:26:26'),
(63, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:26:32'),
(64, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 15:26:49'),
(65, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:26:53'),
(66, 5, 1, 'logout', 'User', 5, NULL, '2026-10-09 15:27:03'),
(67, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:42:22'),
(68, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 15:42:45'),
(69, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:42:48'),
(70, 2, 1, 'employee_created', 'Employee', 3, '{\"name\":\"Muhammad Moosa\",\"role\":\"teacher\"}', '2026-10-09 15:45:06'),
(71, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 15:45:17'),
(72, 7, NULL, 'login_success', 'User', 7, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:45:32'),
(73, 7, 1, 'logout', 'User', 7, NULL, '2026-10-09 15:45:51'),
(74, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:48:15'),
(75, 5, 1, 'logout', 'User', 5, NULL, '2026-10-09 15:48:47'),
(76, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 15:48:52'),
(77, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 15:50:49'),
(78, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:15:24'),
(79, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 16:15:39'),
(80, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:15:42'),
(81, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 16:16:45'),
(82, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:16:51'),
(83, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 16:17:00'),
(84, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:22:56'),
(85, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 16:23:09'),
(86, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:23:25'),
(87, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 16:25:18'),
(88, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:25:32'),
(89, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 16:25:47'),
(90, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:25:58'),
(91, 2, 1, 'attendance_marked', 'AttendanceSession', 2, '{\"date\":\"2026-10-09\",\"class_id\":\"1\",\"status\":\"draft\"}', '2026-10-09 16:26:17'),
(92, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 16:27:29'),
(93, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:27:39'),
(94, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 16:28:03'),
(95, 7, NULL, 'login_success', 'User', 7, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:28:30'),
(96, 7, 1, 'logout', 'User', 7, NULL, '2026-10-09 16:29:18'),
(97, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:37:03'),
(98, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 16:37:23'),
(99, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:37:40'),
(100, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 16:38:05'),
(101, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:38:26'),
(102, 5, 1, 'logout', 'User', 5, NULL, '2026-10-09 16:38:45'),
(103, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:50:48'),
(104, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 16:51:03'),
(105, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:53:30'),
(106, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 16:53:41'),
(107, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:53:49'),
(108, 2, 1, 'logout', 'User', 2, NULL, '2026-10-09 16:54:16'),
(109, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:54:23'),
(110, 3, 1, 'logout', 'User', 3, NULL, '2026-10-09 16:54:27'),
(111, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:54:35'),
(112, 6, 1, 'logout', 'User', 6, NULL, '2026-10-09 16:55:25'),
(113, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 16:55:43'),
(114, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-09 16:55:51'),
(115, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-09 23:47:59'),
(116, 2, 1, 'student_status_changed', 'Student', 2, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-09 23:58:42'),
(117, 2, 1, 'student_status_changed', 'Student', 2, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-09 23:58:45'),
(118, 2, 1, 'exam_created', 'Exam', 1, '{\"name\":\"Mid Term 2026-2027\"}', '2026-10-10 00:04:25'),
(119, 2, 1, 'exam_status_toggled', 'Exam', 1, '{\"status\":\"published\"}', '2026-10-10 00:04:28'),
(120, 2, 1, 'exam_status_toggled', 'Exam', 1, '{\"status\":\"locked\"}', '2026-10-10 00:04:32'),
(121, 2, 1, 'exam_status_toggled', 'Exam', 1, '{\"status\":\"published\"}', '2026-10-10 00:04:51'),
(122, 2, 1, 'logout', 'User', 2, NULL, '2026-10-10 00:04:58'),
(123, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:05:10'),
(124, 3, 1, 'logout', 'User', 3, NULL, '2026-10-10 00:05:40'),
(125, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:05:49'),
(126, 6, 1, 'logout', 'User', 6, NULL, '2026-10-10 00:06:12'),
(127, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:39:34'),
(128, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 00:40:11'),
(129, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:40:20'),
(130, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 00:41:35'),
(131, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:41:46'),
(132, 2, 1, 'logout', 'User', 2, NULL, '2026-10-10 00:49:27'),
(133, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:49:36'),
(134, 1, 2, 'school_created', 'School', 2, '{\"name\":\"ABC School\",\"admin_email\":\"abc@gmail.com\",\"plan_id\":\"1\"}', '2026-10-10 00:51:15'),
(135, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 00:53:41'),
(136, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 00:53:51'),
(137, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 00:54:23'),
(138, 1, 2, 'logout', 'User', 1, NULL, '2026-10-10 00:55:03'),
(139, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 00:55:29'),
(140, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 01:01:57'),
(141, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:02:57'),
(142, 2, 1, 'attendance_marked', 'AttendanceSession', 2, '{\"date\":\"2026-10-09\",\"class_id\":\"1\",\"status\":\"finalized\"}', '2026-10-10 01:20:47'),
(143, 2, 1, 'logout', 'User', 2, NULL, '2026-10-10 01:21:45'),
(144, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:22:07'),
(145, 3, 1, 'logout', 'User', 3, NULL, '2026-10-10 01:23:13'),
(146, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:30:44'),
(147, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 01:32:58'),
(148, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:33:07'),
(149, 1, 2, 'school_status_changed', 'School', 2, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-10 01:43:21'),
(150, 1, 2, 'school_status_changed', 'School', 2, '{\"old\":\"active\",\"new\":\"pending\"}', '2026-10-10 01:43:25'),
(151, 1, 2, 'school_status_changed', 'School', 2, '{\"old\":\"pending\",\"new\":\"archived\"}', '2026-10-10 01:43:34'),
(152, 1, 2, 'school_status_changed', 'School', 2, '{\"old\":\"archived\",\"new\":\"active\"}', '2026-10-10 01:43:37'),
(153, 1, 2, 'school_status_changed', 'School', 2, '{\"old\":\"active\",\"new\":\"active\"}', '2026-10-10 01:43:43'),
(154, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 01:43:55'),
(155, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 01:44:11'),
(156, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-10 01:44:45'),
(157, 1, 2, 'logout', 'User', 1, NULL, '2026-10-10 01:45:02'),
(158, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:45:38'),
(159, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 01:45:42'),
(160, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:45:51'),
(161, 2, 1, 'logout', 'User', 2, NULL, '2026-10-10 01:48:13'),
(162, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:48:24'),
(163, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 01:48:49'),
(164, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:50:41'),
(165, 1, 3, 'school_created', 'School', 3, '{\"name\":\"XYZ School\",\"admin_email\":\"zzz@gmail.com\",\"plan_id\":\"1\"}', '2026-10-10 01:51:26'),
(166, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 01:51:37'),
(167, 8, NULL, 'login_success', 'User', 8, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 01:51:51'),
(168, 8, 3, 'logout', 'User', 8, NULL, '2026-10-10 01:52:03'),
(169, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:28:07'),
(170, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-10 10:28:15'),
(171, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:31:23'),
(172, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 10:31:26'),
(173, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:31:48'),
(174, 2, 1, 'logout', 'User', 2, NULL, '2026-10-10 10:32:10'),
(175, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:32:36'),
(176, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 10:32:55'),
(177, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:33:21'),
(178, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-10 10:33:34'),
(179, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:34:03'),
(180, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-10 10:34:09'),
(181, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-10 10:34:15'),
(182, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-10 10:34:24'),
(183, 1, 3, 'logout', 'User', 1, NULL, '2026-10-10 10:35:11'),
(184, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:36:00'),
(185, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-10 10:36:05'),
(186, 1, 3, 'school_status_changed', 'School', 3, '{\"old\":\"active\",\"new\":\"archived\"}', '2026-10-10 10:36:28'),
(187, 1, 3, 'school_status_changed', 'School', 3, '{\"old\":\"archived\",\"new\":\"active\"}', '2026-10-10 10:36:38'),
(188, 1, 3, 'logout', 'User', 1, NULL, '2026-10-10 10:36:48'),
(189, 8, NULL, 'login_success', 'User', 8, '{\"ip\":\"127.0.0.1\"}', '2026-10-10 10:36:55'),
(190, 8, 3, 'logout', 'User', 8, NULL, '2026-10-10 10:42:44'),
(191, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:07:58'),
(192, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 00:10:32'),
(193, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:10:45'),
(194, 2, 1, 'announcement_posted', 'Announcement', 1, '{\"title\":\"Parents Teacher Meeting\"}', '2026-10-11 00:12:46'),
(195, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 00:19:26'),
(196, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:19:37'),
(197, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 00:21:26'),
(198, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:27:30'),
(199, 6, 1, 'logout', 'User', 6, NULL, '2026-10-11 00:30:24'),
(200, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:30:48'),
(201, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 00:31:01'),
(202, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:31:55'),
(203, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 00:32:29'),
(204, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 00:32:49'),
(205, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 00:33:23'),
(206, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 01:00:59'),
(207, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 01:02:45'),
(208, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 01:02:53'),
(209, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 01:03:43'),
(210, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 01:03:55'),
(211, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 01:05:16'),
(212, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 01:05:24'),
(213, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:13:02'),
(214, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:13:03'),
(215, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 03:13:23'),
(216, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:13:31'),
(217, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 03:15:35'),
(218, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:15:52'),
(219, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 03:16:39'),
(220, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:16:48'),
(221, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 03:18:56'),
(222, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 03:19:16'),
(223, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-11 03:19:22'),
(224, 1, 2, 'support_school_context_switch', 'School', 2, '{\"school\":\"ABC School\"}', '2026-10-11 03:19:52'),
(225, 1, 2, 'logout', 'User', 1, NULL, '2026-10-11 03:19:59'),
(226, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:17:26'),
(227, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 06:22:32'),
(228, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:23:57'),
(229, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 06:25:03'),
(230, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:48:03'),
(231, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-11 06:48:15'),
(232, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-11 06:48:20'),
(233, 1, 3, 'logout', 'User', 1, NULL, '2026-10-11 06:48:46'),
(234, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:48:55'),
(235, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 06:49:49'),
(236, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:50:02'),
(237, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:50:03'),
(238, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 06:50:25'),
(239, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:52:08'),
(240, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-11 06:52:14'),
(241, 1, 3, 'logout', 'User', 1, NULL, '2026-10-11 06:52:44'),
(242, 8, NULL, 'login_success', 'User', 8, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:52:54'),
(243, 8, 3, 'logout', 'User', 8, NULL, '2026-10-11 06:55:38'),
(244, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 06:59:51'),
(245, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 07:00:25'),
(246, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:00:51'),
(247, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 07:02:54'),
(248, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:09:25'),
(249, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 07:11:05'),
(250, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:13:22'),
(251, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 07:13:43'),
(252, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:13:58'),
(253, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 07:30:48'),
(254, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:38:34'),
(255, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:48:50'),
(256, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-11 07:48:58'),
(257, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:49:08'),
(258, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 07:50:39'),
(259, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:50:58'),
(260, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 07:51:06'),
(261, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:51:18'),
(262, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 07:52:16'),
(263, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:52:27'),
(264, 3, 1, 'exam_status_toggled', 'Exam', 1, '{\"status\":\"locked\"}', '2026-10-11 07:53:50'),
(265, 3, 1, 'exam_status_toggled', 'Exam', 1, '{\"status\":\"published\"}', '2026-10-11 07:53:53'),
(266, 3, 1, 'logout', 'User', 3, NULL, '2026-10-11 07:53:55'),
(267, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 07:54:10'),
(268, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 21:25:19'),
(269, 1, 3, 'support_school_context_switch', 'School', 3, '{\"school\":\"XYZ School\"}', '2026-10-11 21:25:31'),
(270, 1, 3, 'school_status_changed', 'School', 3, '{\"old\":\"active\",\"new\":\"pending\"}', '2026-10-11 21:26:12'),
(271, 1, 3, 'school_status_changed', 'School', 3, '{\"old\":\"pending\",\"new\":\"suspended\"}', '2026-10-11 21:26:18'),
(272, 1, 3, 'logout', 'User', 1, NULL, '2026-10-11 21:26:42'),
(273, 8, NULL, 'login_success', 'User', 8, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 21:26:49'),
(274, 8, 3, 'logout', 'User', 8, NULL, '2026-10-11 21:27:39'),
(275, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 21:27:55'),
(276, 2, 1, 'student_qr_token_rotated', 'Student', 2, '[]', '2026-10-11 21:28:52'),
(277, 2, 1, 'student_qr_token_revoked', 'Student', 2, '[]', '2026-10-11 21:29:15'),
(278, 2, 1, 'student_qr_token_rotated', 'Student', 2, '[]', '2026-10-11 21:30:06'),
(279, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 21:33:05'),
(280, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 21:34:02'),
(281, 2, 1, 'student_admitted', 'Student', 3, '{\"admission_number\":\"ADM-1475\",\"name\":\"Ali Khan\",\"roll_number\":\"45\",\"login_email\":\"ali.ali@beaconhouse.edu\"}', '2026-10-11 21:35:12'),
(282, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 21:35:28'),
(283, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 21:36:09'),
(284, 1, 3, 'school_status_changed', 'School', 3, '{\"old\":\"suspended\",\"new\":\"active\"}', '2026-10-11 21:36:28'),
(285, 1, 1, 'support_school_context_switch', 'School', 1, '{\"school\":\"Beaconhouse Model School\"}', '2026-10-11 21:36:36'),
(286, 1, 1, 'logout', 'User', 1, NULL, '2026-10-11 21:38:38'),
(287, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 22:20:17'),
(288, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 22:20:30'),
(289, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 22:43:58'),
(290, 9, NULL, 'login_success', 'User', 9, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 23:00:42'),
(291, 9, 1, 'logout', 'User', 9, NULL, '2026-10-11 23:01:56'),
(292, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 23:02:12'),
(293, 2, 1, 'student_admitted', 'Student', 4, '{\"admission_number\":\"ADM-8232\",\"name\":\"Muammad Ammar\",\"roll_number\":\"102\",\"login_email\":\"amar.muammad@beaconhouse.edu\"}', '2026-10-11 23:03:57'),
(294, 2, 1, 'logout', 'User', 2, NULL, '2026-10-11 23:04:39'),
(295, 9, NULL, 'login_success', 'User', 9, '{\"ip\":\"127.0.0.1\"}', '2026-10-11 23:04:51'),
(296, 9, 1, 'logout', 'User', 9, NULL, '2026-10-11 23:05:27'),
(297, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-12 03:44:42'),
(298, 2, 1, 'student_admitted', 'Student', 5, '{\"admission_number\":\"ADM-2746\",\"name\":\"Azlan Saqib\",\"roll_number\":\"89\",\"login_email\":\"azlan.azlan@beaconhouse.edu\"}', '2026-10-12 03:47:27'),
(299, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:02'),
(300, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:04'),
(301, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:05'),
(302, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:26'),
(303, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:33'),
(304, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:22:34'),
(305, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:23:27'),
(306, 2, 1, 'student_admitted', 'Student', 6, '{\"admission_number\":\"ADM-1-0001\",\"name\":\"Tariq Mehmood\",\"roll_number\":\"002\",\"login_email\":\"adm10001@beaconhouse.edu\"}', '2026-10-16 23:23:29'),
(307, 1, NULL, 'login_success', 'User', 1, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:27:13'),
(308, 1, NULL, 'logout', 'User', 1, NULL, '2026-10-16 23:27:37'),
(309, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-16 23:27:46'),
(310, 2, 1, 'logout', 'User', 2, NULL, '2026-10-16 23:28:41'),
(311, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:05:33'),
(312, 2, 1, 'logout', 'User', 2, NULL, '2026-10-17 00:08:45'),
(313, 5, NULL, 'login_success', 'User', 5, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:08:53'),
(314, 5, 1, 'logout', 'User', 5, NULL, '2026-10-17 00:10:08'),
(315, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:10:15'),
(316, 2, 1, 'logout', 'User', 2, NULL, '2026-10-17 00:13:00'),
(317, 3, NULL, 'login_success', 'User', 3, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:13:27'),
(318, 3, 1, 'logout', 'User', 3, NULL, '2026-10-17 00:15:00'),
(319, 6, NULL, 'login_success', 'User', 6, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:15:13'),
(320, 6, 1, 'logout', 'User', 6, NULL, '2026-10-17 00:16:36'),
(321, 2, NULL, 'login_success', 'User', 2, '{\"ip\":\"127.0.0.1\"}', '2026-10-17 00:16:47'),
(322, 2, 1, 'logout', 'User', 2, NULL, '2026-10-17 00:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-boost:mcp:database-schema:mysql::1:0:0:0', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:52:{s:14:\"academic_years\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:10:\"is_current\";s:10:\"tinyint(1)\";s:6:\"status\";s:25:\"enum(\'active\',\'archived\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"announcements\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:5:\"title\";s:12:\"varchar(255)\";s:4:\"body\";s:4:\"text\";s:8:\"audience\";s:51:\"enum(\'all\',\'teachers\',\'students\',\'parents\',\'staff\')\";s:10:\"publish_at\";s:9:\"timestamp\";s:10:\"expires_at\";s:9:\"timestamp\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:36:\"enum(\'draft\',\'published\',\'archived\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:22:\"attendance_change_logs\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:21:\"student_attendance_id\";s:19:\"bigint(20) unsigned\";s:10:\"old_status\";s:11:\"varchar(20)\";s:10:\"new_status\";s:11:\"varchar(20)\";s:10:\"changed_by\";s:19:\"bigint(20) unsigned\";s:6:\"reason\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";}s:19:\"attendance_sessions\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:15:\"attendance_date\";s:4:\"date\";s:8:\"taken_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:49:\"enum(\'draft\',\'submitted\',\'finalized\',\'corrected\')\";s:12:\"finalized_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:10:\"audit_logs\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:13:\"actor_user_id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:6:\"action\";s:12:\"varchar(100)\";s:11:\"entity_type\";s:12:\"varchar(100)\";s:9:\"entity_id\";s:19:\"bigint(20) unsigned\";s:8:\"metadata\";s:8:\"longtext\";s:10:\"created_at\";s:9:\"timestamp\";}s:5:\"cache\";a:3:{s:3:\"key\";s:12:\"varchar(255)\";s:5:\"value\";s:10:\"mediumtext\";s:10:\"expiration\";s:10:\"bigint(20)\";}s:11:\"cache_locks\";a:3:{s:3:\"key\";s:12:\"varchar(255)\";s:5:\"owner\";s:12:\"varchar(255)\";s:10:\"expiration\";s:10:\"bigint(20)\";}s:7:\"classes\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:10:\"sort_order\";s:7:\"int(11)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"class_subjects\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"subject_id\";s:19:\"bigint(20) unsigned\";s:10:\"teacher_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:9:\"employees\";a:14:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:15:\"employee_number\";s:11:\"varchar(50)\";s:4:\"name\";s:12:\"varchar(100)\";s:5:\"email\";s:12:\"varchar(100)\";s:5:\"phone\";s:11:\"varchar(30)\";s:9:\"job_title\";s:12:\"varchar(100)\";s:4:\"role\";s:11:\"varchar(30)\";s:6:\"salary\";s:13:\"decimal(10,2)\";s:12:\"joining_date\";s:4:\"date\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"exams\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:9:\"exam_type\";s:61:\"enum(\'mid_term\',\'final_term\',\'quiz\',\'unit_test\',\'assignment\')\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:6:\"status\";s:34:\"enum(\'draft\',\'published\',\'locked\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"exam_subjects\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"exam_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:9:\"max_marks\";s:12:\"decimal(6,2)\";s:13:\"passing_marks\";s:12:\"decimal(6,2)\";s:9:\"exam_date\";s:4:\"date\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:11:\"failed_jobs\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"uuid\";s:12:\"varchar(255)\";s:10:\"connection\";s:12:\"varchar(255)\";s:5:\"queue\";s:12:\"varchar(255)\";s:7:\"payload\";s:8:\"longtext\";s:9:\"exception\";s:8:\"longtext\";s:9:\"failed_at\";s:9:\"timestamp\";}s:15:\"fee_adjustments\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:10:\"invoice_id\";s:19:\"bigint(20) unsigned\";s:4:\"type\";s:50:\"enum(\'discount\',\'waiver\',\'correction\',\'write_off\')\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:6:\"reason\";s:4:\"text\";s:11:\"approved_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"fee_audit_logs\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"entity_type\";s:11:\"varchar(50)\";s:9:\"entity_id\";s:19:\"bigint(20) unsigned\";s:6:\"action\";s:11:\"varchar(50)\";s:10:\"old_values\";s:8:\"longtext\";s:10:\"new_values\";s:8:\"longtext\";s:8:\"actor_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";}s:14:\"fee_categories\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:11:\"description\";s:4:\"text\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"fee_invoices\";a:16:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:14:\"invoice_number\";s:11:\"varchar(50)\";s:14:\"billing_period\";s:11:\"varchar(50)\";s:10:\"issue_date\";s:4:\"date\";s:8:\"due_date\";s:4:\"date\";s:8:\"subtotal\";s:13:\"decimal(10,2)\";s:14:\"discount_total\";s:13:\"decimal(10,2)\";s:10:\"fine_total\";s:13:\"decimal(10,2)\";s:12:\"total_amount\";s:13:\"decimal(10,2)\";s:11:\"paid_amount\";s:13:\"decimal(10,2)\";s:6:\"status\";s:54:\"enum(\'pending\',\'partial\',\'paid\',\'overdue\',\'cancelled\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"fee_invoice_items\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_invoice_id\";s:19:\"bigint(20) unsigned\";s:15:\"fee_category_id\";s:19:\"bigint(20) unsigned\";s:11:\"description\";s:12:\"varchar(255)\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"fee_payments\";a:13:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:14:\"receipt_number\";s:11:\"varchar(50)\";s:12:\"payment_date\";s:4:\"date\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:6:\"method\";s:53:\"enum(\'cash\',\'bank_transfer\',\'card\',\'cheque\',\'online\')\";s:9:\"reference\";s:12:\"varchar(100)\";s:11:\"received_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:37:\"enum(\'completed\',\'voided\',\'refunded\')\";s:5:\"notes\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:23:\"fee_payment_allocations\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_payment_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_invoice_id\";s:19:\"bigint(20) unsigned\";s:14:\"amount_applied\";s:13:\"decimal(10,2)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"fee_structures\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:15:\"fee_category_id\";s:19:\"bigint(20) unsigned\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:9:\"frequency\";s:42:\"enum(\'monthly\',\'annual\',\'one_time\',\'term\')\";s:7:\"due_day\";s:20:\"smallint(5) unsigned\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"grading_scales\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:11:\"varchar(50)\";s:14:\"min_percentage\";s:12:\"decimal(5,2)\";s:14:\"max_percentage\";s:12:\"decimal(5,2)\";s:5:\"grade\";s:11:\"varchar(10)\";s:7:\"remarks\";s:12:\"varchar(100)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:9:\"guardians\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:5:\"phone\";s:11:\"varchar(30)\";s:5:\"email\";s:12:\"varchar(100)\";s:12:\"relationship\";s:11:\"varchar(50)\";s:7:\"address\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"homework\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:5:\"title\";s:12:\"varchar(255)\";s:11:\"description\";s:4:\"text\";s:11:\"assigned_on\";s:4:\"date\";s:6:\"due_on\";s:4:\"date\";s:15:\"attachment_path\";s:12:\"varchar(255)\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:4:\"jobs\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:5:\"queue\";s:12:\"varchar(255)\";s:7:\"payload\";s:8:\"longtext\";s:8:\"attempts\";s:20:\"smallint(5) unsigned\";s:11:\"reserved_at\";s:16:\"int(10) unsigned\";s:12:\"available_at\";s:16:\"int(10) unsigned\";s:10:\"created_at\";s:16:\"int(10) unsigned\";}s:11:\"job_batches\";a:10:{s:2:\"id\";s:12:\"varchar(255)\";s:4:\"name\";s:12:\"varchar(255)\";s:10:\"total_jobs\";s:7:\"int(11)\";s:12:\"pending_jobs\";s:7:\"int(11)\";s:11:\"failed_jobs\";s:7:\"int(11)\";s:14:\"failed_job_ids\";s:8:\"longtext\";s:7:\"options\";s:10:\"mediumtext\";s:12:\"cancelled_at\";s:7:\"int(11)\";s:10:\"created_at\";s:7:\"int(11)\";s:11:\"finished_at\";s:7:\"int(11)\";}s:20:\"login_otp_challenges\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:7:\"purpose\";s:11:\"varchar(50)\";s:7:\"channel\";s:11:\"varchar(20)\";s:18:\"destination_masked\";s:12:\"varchar(100)\";s:9:\"code_hash\";s:11:\"varchar(64)\";s:10:\"expires_at\";s:9:\"timestamp\";s:11:\"consumed_at\";s:9:\"timestamp\";s:13:\"attempt_count\";s:16:\"int(10) unsigned\";s:12:\"resend_after\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"marks\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:15:\"exam_subject_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:14:\"marks_obtained\";s:12:\"decimal(6,2)\";s:6:\"status\";s:32:\"enum(\'graded\',\'absent\',\'exempt\')\";s:10:\"entered_by\";s:19:\"bigint(20) unsigned\";s:10:\"updated_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:10:\"migrations\";a:3:{s:2:\"id\";s:16:\"int(10) unsigned\";s:9:\"migration\";s:12:\"varchar(255)\";s:5:\"batch\";s:7:\"int(11)\";}s:13:\"notifications\";a:13:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:7:\"channel\";s:33:\"enum(\'in_app\',\'email\',\'whatsapp\')\";s:4:\"type\";s:11:\"varchar(50)\";s:5:\"title\";s:12:\"varchar(200)\";s:7:\"message\";s:4:\"text\";s:7:\"payload\";s:8:\"longtext\";s:7:\"sent_at\";s:9:\"timestamp\";s:7:\"read_at\";s:9:\"timestamp\";s:6:\"status\";s:38:\"enum(\'pending\',\'sent\',\'read\',\'failed\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:21:\"password_reset_tokens\";a:3:{s:5:\"email\";s:12:\"varchar(255)\";s:5:\"token\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";}s:14:\"payment_events\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:8:\"provider\";s:11:\"varchar(50)\";s:8:\"event_id\";s:12:\"varchar(100)\";s:12:\"payload_hash\";s:11:\"varchar(64)\";s:6:\"status\";s:36:\"enum(\'pending\',\'processed\',\'failed\')\";s:12:\"processed_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"plans\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:5:\"price\";s:13:\"decimal(10,2)\";s:8:\"currency\";s:11:\"varchar(10)\";s:16:\"billing_interval\";s:24:\"enum(\'monthly\',\'yearly\')\";s:13:\"student_limit\";s:7:\"int(11)\";s:11:\"staff_limit\";s:7:\"int(11)\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"report_cards\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:7:\"exam_id\";s:19:\"bigint(20) unsigned\";s:12:\"generated_at\";s:9:\"timestamp\";s:12:\"generated_by\";s:19:\"bigint(20) unsigned\";s:9:\"file_path\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:7:\"schools\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:4:\"slug\";s:12:\"varchar(255)\";s:5:\"email\";s:12:\"varchar(255)\";s:5:\"phone\";s:12:\"varchar(255)\";s:7:\"address\";s:4:\"text\";s:8:\"currency\";s:11:\"varchar(10)\";s:8:\"timezone\";s:11:\"varchar(50)\";s:6:\"status\";s:47:\"enum(\'pending\',\'active\',\'suspended\',\'archived\')\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:18:\"school_invitations\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:5:\"email\";s:12:\"varchar(255)\";s:4:\"role\";s:11:\"varchar(30)\";s:10:\"token_hash\";s:11:\"varchar(64)\";s:10:\"expires_at\";s:9:\"timestamp\";s:11:\"accepted_at\";s:9:\"timestamp\";s:10:\"revoked_at\";s:9:\"timestamp\";s:10:\"invited_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:11:\"school_user\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:4:\"role\";s:11:\"varchar(30)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:9:\"joined_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"sections\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:11:\"varchar(50)\";s:8:\"capacity\";s:7:\"int(11)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"sessions\";a:6:{s:2:\"id\";s:12:\"varchar(255)\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:10:\"ip_address\";s:11:\"varchar(45)\";s:10:\"user_agent\";s:4:\"text\";s:7:\"payload\";s:8:\"longtext\";s:13:\"last_activity\";s:7:\"int(11)\";}s:16:\"staff_attendance\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"employee_id\";s:19:\"bigint(20) unsigned\";s:15:\"attendance_date\";s:4:\"date\";s:6:\"status\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"check_in\";s:4:\"time\";s:9:\"check_out\";s:4:\"time\";s:5:\"notes\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"students\";a:15:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:16:\"admission_number\";s:11:\"varchar(50)\";s:10:\"first_name\";s:12:\"varchar(100)\";s:9:\"last_name\";s:12:\"varchar(100)\";s:13:\"date_of_birth\";s:4:\"date\";s:6:\"gender\";s:29:\"enum(\'male\',\'female\',\'other\')\";s:14:\"admission_date\";s:4:\"date\";s:6:\"status\";s:62:\"enum(\'active\',\'pending\',\'transferred\',\'withdrawn\',\'graduated\')\";s:10:\"photo_path\";s:12:\"varchar(255)\";s:7:\"address\";s:4:\"text\";s:15:\"emergency_phone\";s:11:\"varchar(30)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:18:\"student_attendance\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:21:\"attendance_session_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:9:\"marked_by\";s:19:\"bigint(20) unsigned\";s:9:\"marked_at\";s:9:\"timestamp\";s:7:\"remarks\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"student_documents\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:13:\"document_type\";s:11:\"varchar(50)\";s:5:\"title\";s:12:\"varchar(150)\";s:17:\"private_file_path\";s:12:\"varchar(255)\";s:11:\"uploaded_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:19:\"student_enrollments\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:11:\"roll_number\";s:11:\"varchar(30)\";s:6:\"status\";s:53:\"enum(\'enrolled\',\'promoted\',\'transferred\',\'withdrawn\')\";s:11:\"enrolled_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:23:\"student_fee_assignments\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"fee_structure_id\";s:19:\"bigint(20) unsigned\";s:15:\"amount_override\";s:13:\"decimal(10,2)\";s:15:\"discount_amount\";s:13:\"decimal(10,2)\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:16:\"student_guardian\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:11:\"guardian_id\";s:19:\"bigint(20) unsigned\";s:10:\"is_primary\";s:10:\"tinyint(1)\";s:22:\"receives_notifications\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"subjects\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:4:\"code\";s:11:\"varchar(30)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"subscriptions\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"plan_id\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:67:\"enum(\'trial\',\'active\',\'past_due\',\'expired\',\'suspended\',\'cancelled\')\";s:9:\"starts_at\";s:9:\"timestamp\";s:7:\"ends_at\";s:9:\"timestamp\";s:13:\"trial_ends_at\";s:9:\"timestamp\";s:12:\"cancelled_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:21:\"subscription_payments\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:15:\"subscription_id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:8:\"currency\";s:11:\"varchar(10)\";s:8:\"provider\";s:11:\"varchar(50)\";s:18:\"provider_reference\";s:12:\"varchar(100)\";s:6:\"status\";s:47:\"enum(\'pending\',\'completed\',\'failed\',\'refunded\')\";s:7:\"paid_at\";s:9:\"timestamp\";s:12:\"receipt_path\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:19:\"teacher_assignments\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"employee_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"timetable_entries\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:7:\"weekday\";s:76:\"enum(\'Monday\',\'Tuesday\',\'Wednesday\',\'Thursday\',\'Friday\',\'Saturday\',\'Sunday\')\";s:9:\"starts_at\";s:4:\"time\";s:7:\"ends_at\";s:4:\"time\";s:7:\"room_id\";s:11:\"varchar(50)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"users\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:5:\"email\";s:12:\"varchar(255)\";s:17:\"email_verified_at\";s:9:\"timestamp\";s:8:\"password\";s:12:\"varchar(255)\";s:14:\"is_super_admin\";s:10:\"tinyint(1)\";s:6:\"status\";s:11:\"varchar(30)\";s:13:\"last_login_at\";s:9:\"timestamp\";s:14:\"remember_token\";s:12:\"varchar(100)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}}}', 1791722659),
('laravel-cache-boost:mcp:database-schema:mysql::1:1:0:0', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:52:{s:14:\"academic_years\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:10:\"is_current\";s:10:\"tinyint(1)\";s:6:\"status\";s:25:\"enum(\'active\',\'archived\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"announcements\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:5:\"title\";s:12:\"varchar(255)\";s:4:\"body\";s:4:\"text\";s:8:\"audience\";s:51:\"enum(\'all\',\'teachers\',\'students\',\'parents\',\'staff\')\";s:10:\"publish_at\";s:9:\"timestamp\";s:10:\"expires_at\";s:9:\"timestamp\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:36:\"enum(\'draft\',\'published\',\'archived\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:22:\"attendance_change_logs\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:21:\"student_attendance_id\";s:19:\"bigint(20) unsigned\";s:10:\"old_status\";s:11:\"varchar(20)\";s:10:\"new_status\";s:11:\"varchar(20)\";s:10:\"changed_by\";s:19:\"bigint(20) unsigned\";s:6:\"reason\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";}s:19:\"attendance_sessions\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:15:\"attendance_date\";s:4:\"date\";s:8:\"taken_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:49:\"enum(\'draft\',\'submitted\',\'finalized\',\'corrected\')\";s:12:\"finalized_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:10:\"audit_logs\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:13:\"actor_user_id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:6:\"action\";s:12:\"varchar(100)\";s:11:\"entity_type\";s:12:\"varchar(100)\";s:9:\"entity_id\";s:19:\"bigint(20) unsigned\";s:8:\"metadata\";s:8:\"longtext\";s:10:\"created_at\";s:9:\"timestamp\";}s:5:\"cache\";a:3:{s:3:\"key\";s:12:\"varchar(255)\";s:5:\"value\";s:10:\"mediumtext\";s:10:\"expiration\";s:10:\"bigint(20)\";}s:11:\"cache_locks\";a:3:{s:3:\"key\";s:12:\"varchar(255)\";s:5:\"owner\";s:12:\"varchar(255)\";s:10:\"expiration\";s:10:\"bigint(20)\";}s:7:\"classes\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:10:\"sort_order\";s:7:\"int(11)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"class_subjects\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"subject_id\";s:19:\"bigint(20) unsigned\";s:10:\"teacher_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:9:\"employees\";a:14:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:15:\"employee_number\";s:11:\"varchar(50)\";s:4:\"name\";s:12:\"varchar(100)\";s:5:\"email\";s:12:\"varchar(100)\";s:5:\"phone\";s:11:\"varchar(30)\";s:9:\"job_title\";s:12:\"varchar(100)\";s:4:\"role\";s:11:\"varchar(30)\";s:6:\"salary\";s:13:\"decimal(10,2)\";s:12:\"joining_date\";s:4:\"date\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"exams\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:9:\"exam_type\";s:61:\"enum(\'mid_term\',\'final_term\',\'quiz\',\'unit_test\',\'assignment\')\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:6:\"status\";s:34:\"enum(\'draft\',\'published\',\'locked\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"exam_subjects\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"exam_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:9:\"max_marks\";s:12:\"decimal(6,2)\";s:13:\"passing_marks\";s:12:\"decimal(6,2)\";s:9:\"exam_date\";s:4:\"date\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:11:\"failed_jobs\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"uuid\";s:12:\"varchar(255)\";s:10:\"connection\";s:12:\"varchar(255)\";s:5:\"queue\";s:12:\"varchar(255)\";s:7:\"payload\";s:8:\"longtext\";s:9:\"exception\";s:8:\"longtext\";s:9:\"failed_at\";s:9:\"timestamp\";}s:15:\"fee_adjustments\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:10:\"invoice_id\";s:19:\"bigint(20) unsigned\";s:4:\"type\";s:50:\"enum(\'discount\',\'waiver\',\'correction\',\'write_off\')\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:6:\"reason\";s:4:\"text\";s:11:\"approved_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"fee_audit_logs\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"entity_type\";s:11:\"varchar(50)\";s:9:\"entity_id\";s:19:\"bigint(20) unsigned\";s:6:\"action\";s:11:\"varchar(50)\";s:10:\"old_values\";s:8:\"longtext\";s:10:\"new_values\";s:8:\"longtext\";s:8:\"actor_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";}s:14:\"fee_categories\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:11:\"description\";s:4:\"text\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"fee_invoices\";a:16:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:14:\"invoice_number\";s:11:\"varchar(50)\";s:14:\"billing_period\";s:11:\"varchar(50)\";s:10:\"issue_date\";s:4:\"date\";s:8:\"due_date\";s:4:\"date\";s:8:\"subtotal\";s:13:\"decimal(10,2)\";s:14:\"discount_total\";s:13:\"decimal(10,2)\";s:10:\"fine_total\";s:13:\"decimal(10,2)\";s:12:\"total_amount\";s:13:\"decimal(10,2)\";s:11:\"paid_amount\";s:13:\"decimal(10,2)\";s:6:\"status\";s:54:\"enum(\'pending\',\'partial\',\'paid\',\'overdue\',\'cancelled\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"fee_invoice_items\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_invoice_id\";s:19:\"bigint(20) unsigned\";s:15:\"fee_category_id\";s:19:\"bigint(20) unsigned\";s:11:\"description\";s:12:\"varchar(255)\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"fee_payments\";a:13:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:14:\"receipt_number\";s:11:\"varchar(50)\";s:12:\"payment_date\";s:4:\"date\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:6:\"method\";s:53:\"enum(\'cash\',\'bank_transfer\',\'card\',\'cheque\',\'online\')\";s:9:\"reference\";s:12:\"varchar(100)\";s:11:\"received_by\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:37:\"enum(\'completed\',\'voided\',\'refunded\')\";s:5:\"notes\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:23:\"fee_payment_allocations\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_payment_id\";s:19:\"bigint(20) unsigned\";s:14:\"fee_invoice_id\";s:19:\"bigint(20) unsigned\";s:14:\"amount_applied\";s:13:\"decimal(10,2)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"fee_structures\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:15:\"fee_category_id\";s:19:\"bigint(20) unsigned\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:9:\"frequency\";s:42:\"enum(\'monthly\',\'annual\',\'one_time\',\'term\')\";s:7:\"due_day\";s:20:\"smallint(5) unsigned\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:14:\"grading_scales\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:11:\"varchar(50)\";s:14:\"min_percentage\";s:12:\"decimal(5,2)\";s:14:\"max_percentage\";s:12:\"decimal(5,2)\";s:5:\"grade\";s:11:\"varchar(10)\";s:7:\"remarks\";s:12:\"varchar(100)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:9:\"guardians\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:5:\"phone\";s:11:\"varchar(30)\";s:5:\"email\";s:12:\"varchar(100)\";s:12:\"relationship\";s:11:\"varchar(50)\";s:7:\"address\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"homework\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:5:\"title\";s:12:\"varchar(255)\";s:11:\"description\";s:4:\"text\";s:11:\"assigned_on\";s:4:\"date\";s:6:\"due_on\";s:4:\"date\";s:15:\"attachment_path\";s:12:\"varchar(255)\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:4:\"jobs\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:5:\"queue\";s:12:\"varchar(255)\";s:7:\"payload\";s:8:\"longtext\";s:8:\"attempts\";s:20:\"smallint(5) unsigned\";s:11:\"reserved_at\";s:16:\"int(10) unsigned\";s:12:\"available_at\";s:16:\"int(10) unsigned\";s:10:\"created_at\";s:16:\"int(10) unsigned\";}s:11:\"job_batches\";a:10:{s:2:\"id\";s:12:\"varchar(255)\";s:4:\"name\";s:12:\"varchar(255)\";s:10:\"total_jobs\";s:7:\"int(11)\";s:12:\"pending_jobs\";s:7:\"int(11)\";s:11:\"failed_jobs\";s:7:\"int(11)\";s:14:\"failed_job_ids\";s:8:\"longtext\";s:7:\"options\";s:10:\"mediumtext\";s:12:\"cancelled_at\";s:7:\"int(11)\";s:10:\"created_at\";s:7:\"int(11)\";s:11:\"finished_at\";s:7:\"int(11)\";}s:20:\"login_otp_challenges\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:7:\"purpose\";s:11:\"varchar(50)\";s:7:\"channel\";s:11:\"varchar(20)\";s:18:\"destination_masked\";s:12:\"varchar(100)\";s:9:\"code_hash\";s:11:\"varchar(64)\";s:10:\"expires_at\";s:9:\"timestamp\";s:11:\"consumed_at\";s:9:\"timestamp\";s:13:\"attempt_count\";s:16:\"int(10) unsigned\";s:12:\"resend_after\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"marks\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:15:\"exam_subject_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:14:\"marks_obtained\";s:12:\"decimal(6,2)\";s:6:\"status\";s:32:\"enum(\'graded\',\'absent\',\'exempt\')\";s:10:\"entered_by\";s:19:\"bigint(20) unsigned\";s:10:\"updated_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:10:\"migrations\";a:3:{s:2:\"id\";s:16:\"int(10) unsigned\";s:9:\"migration\";s:12:\"varchar(255)\";s:5:\"batch\";s:7:\"int(11)\";}s:13:\"notifications\";a:13:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:7:\"channel\";s:33:\"enum(\'in_app\',\'email\',\'whatsapp\')\";s:4:\"type\";s:11:\"varchar(50)\";s:5:\"title\";s:12:\"varchar(200)\";s:7:\"message\";s:4:\"text\";s:7:\"payload\";s:8:\"longtext\";s:7:\"sent_at\";s:9:\"timestamp\";s:7:\"read_at\";s:9:\"timestamp\";s:6:\"status\";s:38:\"enum(\'pending\',\'sent\',\'read\',\'failed\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:21:\"password_reset_tokens\";a:3:{s:5:\"email\";s:12:\"varchar(255)\";s:5:\"token\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";}s:14:\"payment_events\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:8:\"provider\";s:11:\"varchar(50)\";s:8:\"event_id\";s:12:\"varchar(100)\";s:12:\"payload_hash\";s:11:\"varchar(64)\";s:6:\"status\";s:36:\"enum(\'pending\',\'processed\',\'failed\')\";s:12:\"processed_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"plans\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:5:\"price\";s:13:\"decimal(10,2)\";s:8:\"currency\";s:11:\"varchar(10)\";s:16:\"billing_interval\";s:24:\"enum(\'monthly\',\'yearly\')\";s:13:\"student_limit\";s:7:\"int(11)\";s:11:\"staff_limit\";s:7:\"int(11)\";s:9:\"is_active\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:12:\"report_cards\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:7:\"exam_id\";s:19:\"bigint(20) unsigned\";s:12:\"generated_at\";s:9:\"timestamp\";s:12:\"generated_by\";s:19:\"bigint(20) unsigned\";s:9:\"file_path\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:7:\"schools\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:4:\"slug\";s:12:\"varchar(255)\";s:5:\"email\";s:12:\"varchar(255)\";s:5:\"phone\";s:12:\"varchar(255)\";s:7:\"address\";s:4:\"text\";s:8:\"currency\";s:11:\"varchar(10)\";s:8:\"timezone\";s:11:\"varchar(50)\";s:6:\"status\";s:47:\"enum(\'pending\',\'active\',\'suspended\',\'archived\')\";s:10:\"created_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:18:\"school_invitations\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:5:\"email\";s:12:\"varchar(255)\";s:4:\"role\";s:11:\"varchar(30)\";s:10:\"token_hash\";s:11:\"varchar(64)\";s:10:\"expires_at\";s:9:\"timestamp\";s:11:\"accepted_at\";s:9:\"timestamp\";s:10:\"revoked_at\";s:9:\"timestamp\";s:10:\"invited_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:11:\"school_user\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:4:\"role\";s:11:\"varchar(30)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:9:\"joined_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"sections\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:11:\"varchar(50)\";s:8:\"capacity\";s:7:\"int(11)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"sessions\";a:6:{s:2:\"id\";s:12:\"varchar(255)\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:10:\"ip_address\";s:11:\"varchar(45)\";s:10:\"user_agent\";s:4:\"text\";s:7:\"payload\";s:8:\"longtext\";s:13:\"last_activity\";s:7:\"int(11)\";}s:16:\"staff_attendance\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"employee_id\";s:19:\"bigint(20) unsigned\";s:15:\"attendance_date\";s:4:\"date\";s:6:\"status\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"check_in\";s:4:\"time\";s:9:\"check_out\";s:4:\"time\";s:5:\"notes\";s:4:\"text\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"students\";a:15:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"user_id\";s:19:\"bigint(20) unsigned\";s:16:\"admission_number\";s:11:\"varchar(50)\";s:10:\"first_name\";s:12:\"varchar(100)\";s:9:\"last_name\";s:12:\"varchar(100)\";s:13:\"date_of_birth\";s:4:\"date\";s:6:\"gender\";s:29:\"enum(\'male\',\'female\',\'other\')\";s:14:\"admission_date\";s:4:\"date\";s:6:\"status\";s:62:\"enum(\'active\',\'pending\',\'transferred\',\'withdrawn\',\'graduated\')\";s:10:\"photo_path\";s:12:\"varchar(255)\";s:7:\"address\";s:4:\"text\";s:15:\"emergency_phone\";s:11:\"varchar(30)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:18:\"student_attendance\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:21:\"attendance_session_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:9:\"marked_by\";s:19:\"bigint(20) unsigned\";s:9:\"marked_at\";s:9:\"timestamp\";s:7:\"remarks\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"student_documents\";a:9:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:13:\"document_type\";s:11:\"varchar(50)\";s:5:\"title\";s:12:\"varchar(150)\";s:17:\"private_file_path\";s:12:\"varchar(255)\";s:11:\"uploaded_by\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:19:\"student_enrollments\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:8:\"class_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:11:\"roll_number\";s:11:\"varchar(30)\";s:6:\"status\";s:53:\"enum(\'enrolled\',\'promoted\',\'transferred\',\'withdrawn\')\";s:11:\"enrolled_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:23:\"student_fee_assignments\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:16:\"fee_structure_id\";s:19:\"bigint(20) unsigned\";s:15:\"amount_override\";s:13:\"decimal(10,2)\";s:15:\"discount_amount\";s:13:\"decimal(10,2)\";s:9:\"starts_on\";s:4:\"date\";s:7:\"ends_on\";s:4:\"date\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:16:\"student_guardian\";a:8:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:10:\"student_id\";s:19:\"bigint(20) unsigned\";s:11:\"guardian_id\";s:19:\"bigint(20) unsigned\";s:10:\"is_primary\";s:10:\"tinyint(1)\";s:22:\"receives_notifications\";s:10:\"tinyint(1)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:8:\"subjects\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(100)\";s:4:\"code\";s:11:\"varchar(30)\";s:6:\"status\";s:25:\"enum(\'active\',\'inactive\')\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:13:\"subscriptions\";a:10:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:7:\"plan_id\";s:19:\"bigint(20) unsigned\";s:6:\"status\";s:67:\"enum(\'trial\',\'active\',\'past_due\',\'expired\',\'suspended\',\'cancelled\')\";s:9:\"starts_at\";s:9:\"timestamp\";s:7:\"ends_at\";s:9:\"timestamp\";s:13:\"trial_ends_at\";s:9:\"timestamp\";s:12:\"cancelled_at\";s:9:\"timestamp\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:21:\"subscription_payments\";a:12:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:15:\"subscription_id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:6:\"amount\";s:13:\"decimal(10,2)\";s:8:\"currency\";s:11:\"varchar(10)\";s:8:\"provider\";s:11:\"varchar(50)\";s:18:\"provider_reference\";s:12:\"varchar(100)\";s:6:\"status\";s:47:\"enum(\'pending\',\'completed\',\'failed\',\'refunded\')\";s:7:\"paid_at\";s:9:\"timestamp\";s:12:\"receipt_path\";s:12:\"varchar(255)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:19:\"teacher_assignments\";a:7:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:11:\"employee_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:17:\"timetable_entries\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:9:\"school_id\";s:19:\"bigint(20) unsigned\";s:16:\"academic_year_id\";s:19:\"bigint(20) unsigned\";s:10:\"section_id\";s:19:\"bigint(20) unsigned\";s:16:\"class_subject_id\";s:19:\"bigint(20) unsigned\";s:7:\"weekday\";s:76:\"enum(\'Monday\',\'Tuesday\',\'Wednesday\',\'Thursday\',\'Friday\',\'Saturday\',\'Sunday\')\";s:9:\"starts_at\";s:4:\"time\";s:7:\"ends_at\";s:4:\"time\";s:7:\"room_id\";s:11:\"varchar(50)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}s:5:\"users\";a:11:{s:2:\"id\";s:19:\"bigint(20) unsigned\";s:4:\"name\";s:12:\"varchar(255)\";s:5:\"email\";s:12:\"varchar(255)\";s:17:\"email_verified_at\";s:9:\"timestamp\";s:8:\"password\";s:12:\"varchar(255)\";s:14:\"is_super_admin\";s:10:\"tinyint(1)\";s:6:\"status\";s:11:\"varchar(30)\";s:13:\"last_login_at\";s:9:\"timestamp\";s:14:\"remember_token\";s:12:\"varchar(100)\";s:10:\"created_at\";s:9:\"timestamp\";s:10:\"updated_at\";s:9:\"timestamp\";}}}', 1791698960),
('laravel-cache-boost:mcp:database-schema:mysql:attendance_sessions:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:19:\"attendance_sessions\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"academic_year_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:8:\"class_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"section_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:15:\"attendance_date\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:8:\"taken_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:49:\"enum(\'draft\',\'submitted\',\'finalized\',\'corrected\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:7:\"\'draft\'\";s:14:\"auto_increment\";b:0;}s:12:\"finalized_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:6:{s:44:\"attendance_sessions_academic_year_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"attendance_sessions_class_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:38:\"attendance_sessions_section_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"attendance_sessions_taken_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:8:\"taken_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:18:\"att_session_unique\";a:4:{s:7:\"columns\";a:5:{i:0;s:9:\"school_id\";i:1;s:16:\"academic_year_id\";i:2;s:8:\"class_id\";i:3;s:10:\"section_id\";i:4;s:15:\"attendance_date\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:5:{i:0;a:7:{s:4:\"name\";s:44:\"attendance_sessions_academic_year_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"academic_years\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"attendance_sessions_class_id_foreign\";s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"classes\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:37:\"attendance_sessions_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:38:\"attendance_sessions_section_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"sections\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:4;a:7:{s:4:\"name\";s:36:\"attendance_sessions_taken_by_foreign\";s:7:\"columns\";a:1:{i:0;s:8:\"taken_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791699026);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-boost:mcp:database-schema:mysql:attendance:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:4:{s:22:\"attendance_change_logs\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:21:\"student_attendance_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"old_status\";a:4:{s:4:\"type\";s:11:\"varchar(20)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"new_status\";a:4:{s:4:\"type\";s:11:\"varchar(20)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"changed_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"reason\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:0;s:7:\"default\";s:19:\"current_timestamp()\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:41:\"attendance_change_logs_changed_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"changed_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:40:\"attendance_change_logs_school_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:52:\"attendance_change_logs_student_attendance_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:21:\"student_attendance_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:3:{i:0;a:7:{s:4:\"name\";s:41:\"attendance_change_logs_changed_by_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"changed_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}i:1;a:7:{s:4:\"name\";s:40:\"attendance_change_logs_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:52:\"attendance_change_logs_student_attendance_id_foreign\";s:7:\"columns\";a:1:{i:0;s:21:\"student_attendance_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:18:\"student_attendance\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:19:\"attendance_sessions\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"academic_year_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:8:\"class_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"section_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:15:\"attendance_date\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:8:\"taken_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:49:\"enum(\'draft\',\'submitted\',\'finalized\',\'corrected\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:7:\"\'draft\'\";s:14:\"auto_increment\";b:0;}s:12:\"finalized_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:6:{s:44:\"attendance_sessions_academic_year_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"attendance_sessions_class_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:38:\"attendance_sessions_section_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"attendance_sessions_taken_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:8:\"taken_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:18:\"att_session_unique\";a:4:{s:7:\"columns\";a:5:{i:0;s:9:\"school_id\";i:1;s:16:\"academic_year_id\";i:2;s:8:\"class_id\";i:3;s:10:\"section_id\";i:4;s:15:\"attendance_date\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}}s:12:\"foreign_keys\";a:5:{i:0;a:7:{s:4:\"name\";s:44:\"attendance_sessions_academic_year_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"academic_years\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"attendance_sessions_class_id_foreign\";s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"classes\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:37:\"attendance_sessions_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:38:\"attendance_sessions_section_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"sections\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:4;a:7:{s:4:\"name\";s:36:\"attendance_sessions_taken_by_foreign\";s:7:\"columns\";a:1:{i:0;s:8:\"taken_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:16:\"staff_attendance\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"employee_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:15:\"attendance_date\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:9:\"\'present\'\";s:14:\"auto_increment\";b:0;}s:8:\"check_in\";a:4:{s:4:\"type\";s:4:\"time\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:9:\"check_out\";a:4:{s:4:\"type\";s:4:\"time\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:5:\"notes\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:3:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:36:\"staff_attendance_employee_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:16:\"staff_att_unique\";a:4:{s:7:\"columns\";a:3:{i:0;s:9:\"school_id\";i:1;s:11:\"employee_id\";i:2;s:15:\"attendance_date\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:2:{i:0;a:7:{s:4:\"name\";s:36:\"staff_attendance_employee_id_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:9:\"employees\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:34:\"staff_attendance_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:18:\"student_attendance\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:21:\"attendance_session_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:9:\"\'present\'\";s:14:\"auto_increment\";b:0;}s:9:\"marked_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:9:\"marked_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:0;s:7:\"default\";s:19:\"current_timestamp()\";s:14:\"auto_increment\";b:0;}s:7:\"remarks\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:5:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:58:\"student_attendance_attendance_session_id_student_id_unique\";a:4:{s:7:\"columns\";a:2:{i:0;s:21:\"attendance_session_id\";i:1;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:36:\"student_attendance_marked_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"marked_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:41:\"student_attendance_school_id_status_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:6:\"status\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:37:\"student_attendance_student_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:4:{i:0;a:7:{s:4:\"name\";s:48:\"student_attendance_attendance_session_id_foreign\";s:7:\"columns\";a:1:{i:0;s:21:\"attendance_session_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:19:\"attendance_sessions\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"student_attendance_marked_by_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"marked_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}i:2;a:7:{s:4:\"name\";s:36:\"student_attendance_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:37:\"student_attendance_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791718589),
('laravel-cache-boost:mcp:database-schema:mysql:schools:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:7:\"schools\";a:5:{s:7:\"columns\";a:12:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:4:\"name\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:4:\"slug\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:5:\"email\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:5:\"phone\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:7:\"address\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:8:\"currency\";a:4:{s:4:\"type\";s:11:\"varchar(10)\";s:8:\"nullable\";b:0;s:7:\"default\";s:5:\"\'USD\'\";s:14:\"auto_increment\";b:0;}s:8:\"timezone\";a:4:{s:4:\"type\";s:11:\"varchar(50)\";s:8:\"nullable\";b:0;s:7:\"default\";s:5:\"\'UTC\'\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:47:\"enum(\'pending\',\'active\',\'suspended\',\'archived\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:9:\"\'pending\'\";s:14:\"auto_increment\";b:0;}s:10:\"created_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:26:\"schools_created_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"created_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:19:\"schools_slug_unique\";a:4:{s:7:\"columns\";a:1:{i:0;s:4:\"slug\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:20:\"schools_status_index\";a:4:{s:7:\"columns\";a:1:{i:0;s:6:\"status\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:1:{i:0;a:7:{s:4:\"name\";s:26:\"schools_created_by_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"created_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791718709),
('laravel-cache-boost:mcp:database-schema:mysql:staff_attendance:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:16:\"staff_attendance\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"employee_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:15:\"attendance_date\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:9:\"\'present\'\";s:14:\"auto_increment\";b:0;}s:8:\"check_in\";a:4:{s:4:\"type\";s:4:\"time\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:9:\"check_out\";a:4:{s:4:\"type\";s:4:\"time\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:5:\"notes\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:3:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:36:\"staff_attendance_employee_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:16:\"staff_att_unique\";a:4:{s:7:\"columns\";a:3:{i:0;s:9:\"school_id\";i:1;s:11:\"employee_id\";i:2;s:15:\"attendance_date\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:2:{i:0;a:7:{s:4:\"name\";s:36:\"staff_attendance_employee_id_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:9:\"employees\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:34:\"staff_attendance_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791718724),
('laravel-cache-boost:mcp:database-schema:mysql:student:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:6:{s:8:\"students\";a:5:{s:7:\"columns\";a:15:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:7:\"user_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:16:\"admission_number\";a:4:{s:4:\"type\";s:11:\"varchar(50)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"first_name\";a:4:{s:4:\"type\";s:12:\"varchar(100)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:9:\"last_name\";a:4:{s:4:\"type\";s:12:\"varchar(100)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:13:\"date_of_birth\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"gender\";a:4:{s:4:\"type\";s:29:\"enum(\'male\',\'female\',\'other\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:6:\"\'male\'\";s:14:\"auto_increment\";b:0;}s:14:\"admission_date\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:62:\"enum(\'active\',\'pending\',\'transferred\',\'withdrawn\',\'graduated\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:8:\"\'active\'\";s:14:\"auto_increment\";b:0;}s:10:\"photo_path\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:7:\"address\";a:4:{s:4:\"type\";s:4:\"text\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:15:\"emergency_phone\";a:4:{s:4:\"type\";s:11:\"varchar(30)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:42:\"students_school_id_admission_number_unique\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:16:\"admission_number\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:31:\"students_school_id_status_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:6:\"status\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:24:\"students_user_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:7:\"user_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:2:{i:0;a:7:{s:4:\"name\";s:26:\"students_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:24:\"students_user_id_foreign\";s:7:\"columns\";a:1:{i:0;s:7:\"user_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:18:\"student_attendance\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:21:\"attendance_session_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:41:\"enum(\'present\',\'absent\',\'late\',\'excused\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:9:\"\'present\'\";s:14:\"auto_increment\";b:0;}s:9:\"marked_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:9:\"marked_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:0;s:7:\"default\";s:19:\"current_timestamp()\";s:14:\"auto_increment\";b:0;}s:7:\"remarks\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:5:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:58:\"student_attendance_attendance_session_id_student_id_unique\";a:4:{s:7:\"columns\";a:2:{i:0;s:21:\"attendance_session_id\";i:1;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}s:36:\"student_attendance_marked_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"marked_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:41:\"student_attendance_school_id_status_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:6:\"status\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:37:\"student_attendance_student_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:4:{i:0;a:7:{s:4:\"name\";s:48:\"student_attendance_attendance_session_id_foreign\";s:7:\"columns\";a:1:{i:0;s:21:\"attendance_session_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:19:\"attendance_sessions\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"student_attendance_marked_by_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"marked_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}i:2;a:7:{s:4:\"name\";s:36:\"student_attendance_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:37:\"student_attendance_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:17:\"student_documents\";a:5:{s:7:\"columns\";a:9:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:13:\"document_type\";a:4:{s:4:\"type\";s:11:\"varchar(50)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:5:\"title\";a:4:{s:4:\"type\";s:12:\"varchar(150)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:17:\"private_file_path\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"uploaded_by\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:44:\"student_documents_school_id_student_id_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"student_documents_student_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:37:\"student_documents_uploaded_by_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"uploaded_by\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:3:{i:0;a:7:{s:4:\"name\";s:35:\"student_documents_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"student_documents_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:37:\"student_documents_uploaded_by_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"uploaded_by\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:5:\"users\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:8:\"set null\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:19:\"student_enrollments\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"academic_year_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:8:\"class_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"section_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"roll_number\";a:4:{s:4:\"type\";s:11:\"varchar(30)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:53:\"enum(\'enrolled\',\'promoted\',\'transferred\',\'withdrawn\')\";s:8:\"nullable\";b:0;s:7:\"default\";s:10:\"\'enrolled\'\";s:14:\"auto_increment\";b:0;}s:11:\"enrolled_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:0;s:7:\"default\";s:19:\"current_timestamp()\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:6:{s:21:\"enrollment_lookup_idx\";a:4:{s:7:\"columns\";a:4:{i:0;s:9:\"school_id\";i:1;s:16:\"academic_year_id\";i:2;s:8:\"class_id\";i:3;s:10:\"section_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:44:\"student_enrollments_academic_year_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:36:\"student_enrollments_class_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:38:\"student_enrollments_section_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:38:\"student_enrollments_student_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:5:{i:0;a:7:{s:4:\"name\";s:44:\"student_enrollments_academic_year_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"academic_years\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:36:\"student_enrollments_class_id_foreign\";s:7:\"columns\";a:1:{i:0;s:8:\"class_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"classes\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:37:\"student_enrollments_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:38:\"student_enrollments_section_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"section_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"sections\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:4;a:7:{s:4:\"name\";s:38:\"student_enrollments_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:23:\"student_fee_assignments\";a:5:{s:7:\"columns\";a:10:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"fee_structure_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:15:\"amount_override\";a:4:{s:4:\"type\";s:13:\"decimal(10,2)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:15:\"discount_amount\";a:4:{s:4:\"type\";s:13:\"decimal(10,2)\";s:8:\"nullable\";b:0;s:7:\"default\";s:4:\"0.00\";s:14:\"auto_increment\";b:0;}s:9:\"starts_on\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:7:\"ends_on\";a:4:{s:4:\"type\";s:4:\"date\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:48:\"student_fee_assignments_fee_structure_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"fee_structure_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:50:\"student_fee_assignments_school_id_student_id_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:42:\"student_fee_assignments_student_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:3:{i:0;a:7:{s:4:\"name\";s:48:\"student_fee_assignments_fee_structure_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"fee_structure_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"fee_structures\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:41:\"student_fee_assignments_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:42:\"student_fee_assignments_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}s:16:\"student_guardian\";a:5:{s:7:\"columns\";a:8:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"student_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"guardian_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"is_primary\";a:4:{s:4:\"type\";s:10:\"tinyint(1)\";s:8:\"nullable\";b:0;s:7:\"default\";s:1:\"1\";s:14:\"auto_increment\";b:0;}s:22:\"receives_notifications\";a:4:{s:4:\"type\";s:10:\"tinyint(1)\";s:8:\"nullable\";b:0;s:7:\"default\";s:1:\"1\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:4:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:36:\"student_guardian_guardian_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"guardian_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:34:\"student_guardian_school_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:45:\"student_guardian_student_id_guardian_id_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:10:\"student_id\";i:1;s:11:\"guardian_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:3:{i:0;a:7:{s:4:\"name\";s:36:\"student_guardian_guardian_id_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"guardian_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:9:\"guardians\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:34:\"student_guardian_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:35:\"student_guardian_student_id_foreign\";s:7:\"columns\";a:1:{i:0;s:10:\"student_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:8:\"students\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791718594),
('laravel-cache-boost:mcp:database-schema:mysql:teacher_assignment:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:19:\"teacher_assignments\";a:5:{s:7:\"columns\";a:7:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:9:\"school_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:11:\"employee_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"class_subject_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:16:\"academic_year_id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:5:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:44:\"teacher_assignments_academic_year_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:44:\"teacher_assignments_class_subject_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:16:\"class_subject_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:39:\"teacher_assignments_employee_id_foreign\";a:4:{s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}s:47:\"teacher_assignments_school_id_employee_id_index\";a:4:{s:7:\"columns\";a:2:{i:0;s:9:\"school_id\";i:1;s:11:\"employee_id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:0;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:4:{i:0;a:7:{s:4:\"name\";s:44:\"teacher_assignments_academic_year_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"academic_year_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"academic_years\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:1;a:7:{s:4:\"name\";s:44:\"teacher_assignments_class_subject_id_foreign\";s:7:\"columns\";a:1:{i:0;s:16:\"class_subject_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:14:\"class_subjects\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:2;a:7:{s:4:\"name\";s:39:\"teacher_assignments_employee_id_foreign\";s:7:\"columns\";a:1:{i:0;s:11:\"employee_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:9:\"employees\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}i:3;a:7:{s:4:\"name\";s:37:\"teacher_assignments_school_id_foreign\";s:7:\"columns\";a:1:{i:0;s:9:\"school_id\";}s:14:\"foreign_schema\";s:11:\"school_saas\";s:13:\"foreign_table\";s:7:\"schools\";s:15:\"foreign_columns\";a:1:{i:0;s:2:\"id\";}s:9:\"on_update\";s:8:\"restrict\";s:9:\"on_delete\";s:7:\"cascade\";}}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791698970),
('laravel-cache-boost:mcp:database-schema:mysql:users:0:0:0:1', 'a:2:{s:6:\"engine\";s:5:\"mysql\";s:6:\"tables\";a:1:{s:5:\"users\";a:5:{s:7:\"columns\";a:11:{s:2:\"id\";a:4:{s:4:\"type\";s:19:\"bigint(20) unsigned\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:1;}s:4:\"name\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:5:\"email\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:17:\"email_verified_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:8:\"password\";a:4:{s:4:\"type\";s:12:\"varchar(255)\";s:8:\"nullable\";b:0;s:7:\"default\";N;s:14:\"auto_increment\";b:0;}s:14:\"is_super_admin\";a:4:{s:4:\"type\";s:10:\"tinyint(1)\";s:8:\"nullable\";b:0;s:7:\"default\";s:1:\"0\";s:14:\"auto_increment\";b:0;}s:6:\"status\";a:4:{s:4:\"type\";s:11:\"varchar(30)\";s:8:\"nullable\";b:0;s:7:\"default\";s:8:\"\'active\'\";s:14:\"auto_increment\";b:0;}s:13:\"last_login_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:14:\"remember_token\";a:4:{s:4:\"type\";s:12:\"varchar(100)\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"created_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}s:10:\"updated_at\";a:4:{s:4:\"type\";s:9:\"timestamp\";s:8:\"nullable\";b:1;s:7:\"default\";s:4:\"NULL\";s:14:\"auto_increment\";b:0;}}s:7:\"indexes\";a:2:{s:7:\"primary\";a:4:{s:7:\"columns\";a:1:{i:0;s:2:\"id\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:1;}s:18:\"users_email_unique\";a:4:{s:7:\"columns\";a:1:{i:0;s:5:\"email\";}s:4:\"type\";s:5:\"btree\";s:9:\"is_unique\";b:1;s:10:\"is_primary\";b:0;}}s:12:\"foreign_keys\";a:0:{}s:8:\"triggers\";a:0:{}s:17:\"check_constraints\";a:0:{}}}}', 1791698477),
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:2;', 1791722408),
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1791722408;', 1791722408),
('laravel-cache-login:abc@gmail.com|127.0.0.1', 'i:2;', 1791615005),
('laravel-cache-login:abc@gmail.com|127.0.0.1:timer', 'i:1791615005;', 1791615005),
('laravel-cache-login:admin@beaconhouse.edupassword123|127.0.0.1', 'i:2;', 1791696737),
('laravel-cache-login:admin@beaconhouse.edupassword123|127.0.0.1:timer', 'i:1791696737;', 1791696737),
('laravel-cache-login:daniyal@gmail.com|127.0.0.1', 'i:1;', 1791772477),
('laravel-cache-login:daniyal@gmail.com|127.0.0.1:timer', 'i:1791772477;', 1791772477),
('laravel-cache-login:moosa@gmail.com|127.0.0.1', 'i:1;', 1791772460),
('laravel-cache-login:moosa@gmail.com|127.0.0.1:timer', 'i:1791772460;', 1791772460),
('laravel-cache-login:moosazahd000@gmail.com|127.0.0.1', 'i:2;', 1791717841),
('laravel-cache-login:moosazahd000@gmail.com|127.0.0.1:timer', 'i:1791717841;', 1791717841),
('laravel-cache-login:superadmin@schoosaas.com|127.0.0.1', 'i:1;', 1791719575),
('laravel-cache-login:superadmin@schoosaas.com|127.0.0.1:timer', 'i:1791719575;', 1791719575),
('laravel-cache-login:teacher@school.test|127.0.0.1', 'i:1;', 1792214047),
('laravel-cache-login:teacher@school.test|127.0.0.1:timer', 'i:1792214047;', 1792214047);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-roster:project:v3:0ab929dcc2530a99e8aff480ad5f7ac9', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:28:\"C:\\Users\\User\\Videos\\School\\\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:114:{s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\vendor\\brick\\math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\Users\\User\\Videos\\School\\vendor\\carbonphp\\carbon-doctrine-types\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\dflydev\\dot-access-data\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\doctrine\\inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\doctrine\\lexer\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\dragonmantank\\cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\egulias\\email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\fruitcake\\php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\graham-campbell\\result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\uri-template\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.35.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^13.17\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\framework\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.25\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\prompts\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\uri-interfaces\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\monolog\\monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.14.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\nesbot\\carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.3.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\nette\\schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\nette\\utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\nikic\\php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\nunomaduro\\termwind\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\phpoption\\phpoption\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"C:\\Users\\User\\Videos\\School\\vendor\\psy\\psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\ramsey\\collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\ramsey\\uuid\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php80\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php86\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\process\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\routing\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\translation-contracts\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"C:\\Users\\User\\Videos\\School\\vendor\\tijsverkoyen\\css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.7.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\vlucas\\phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\voku\\portable-ascii\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.5.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\composer\\semver\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\fakerphp\\faker\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\filp\\whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\hamcrest\\hamcrest-php\";}s:22:\"laravel/agent-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"laravel/agent-detector\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\agent-detector\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^2.10\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.2.5\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pail\";}s:11:\"laravel/pao\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/pao\";s:10:\"\0*\0version\";s:5:\"1.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.0.6\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pao\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.27\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\roster\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\mockery\\mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\myclabs\\deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.6\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\nunomaduro\\collision\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\phar-io\\manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\phar-io\\version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.38\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:8:\"^12.5.12\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\Users\\User\\Videos\\School\\vendor\\staabm\\side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\yaml\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\theseer\\tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:114:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:6:{s:17:\"@tailwindcss/vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"@tailwindcss/vite\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\node_modules\\@tailwindcss\\vite\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:6:\"10.0.3\";s:9:\"\0*\0source\";r:1039;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.0.3\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\node_modules\\concurrently\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:3:\"3.1\";s:9:\"\0*\0source\";r:1039;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\node_modules\\laravel-vite-plugin\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1039;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\node_modules\\tailwindcss\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1039;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^8.0.0\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\node_modules\\vite\";}s:18:\"@laravel/multiplex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@laravel/multiplex\";s:10:\"\0*\0version\";s:5:\"0.4.1\";s:9:\"\0*\0source\";r:1039;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^0.4.1\";s:7:\"\0*\0path\";s:59:\"C:\\Users\\User\\Videos\\School\\node_modules\\@laravel\\multiplex\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:6:{i:0;r:1036;i:1;r:1044;i:2;r:1052;i:3;r:1060;i:4;r:1068;i:5;r:1076;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";N;}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:32:\"Laravel\\Roster\\Enums\\Stack:Blade\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:3:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:33:\"Laravel\\Roster\\Enums\\Agent:Cursor\";i:2;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}', 1791722290);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-roster:project:v3:926ce6ad8294e51fb73ea2bc7d20996d', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:28:\"C:\\Users\\User\\Videos\\School\\\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:117:{s:19:\"bacon/bacon-qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"bacon/bacon-qr-code\";s:10:\"\0*\0version\";s:5:\"3.1.1\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\bacon\\bacon-qr-code\";}s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\vendor\\brick\\math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\Users\\User\\Videos\\School\\vendor\\carbonphp\\carbon-doctrine-types\";}s:12:\"dasprid/enum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"dasprid/enum\";s:10:\"\0*\0version\";s:5:\"1.0.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\dasprid\\enum\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\dflydev\\dot-access-data\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\doctrine\\inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\doctrine\\lexer\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\dragonmantank\\cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\egulias\\email-validator\";}s:15:\"endroid/qr-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"endroid/qr-code\";s:10:\"\0*\0version\";s:5:\"6.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^6.1\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\endroid\\qr-code\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\fruitcake\\php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\graham-campbell\\result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\guzzlehttp\\uri-template\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.35.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^13.17\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\framework\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.25\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\prompts\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:5:\"7.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\league\\uri-interfaces\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\monolog\\monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.14.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\nesbot\\carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.3.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\nette\\schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\nette\\utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\nikic\\php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\nunomaduro\\termwind\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\phpoption\\phpoption\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:42:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\psr\\simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:44:\"C:\\Users\\User\\Videos\\School\\vendor\\psy\\psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\ramsey\\collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\ramsey\\uuid\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:5:\"8.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php80\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-php86\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\process\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\routing\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.7.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\translation-contracts\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:5:\"8.1.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"C:\\Users\\User\\Videos\\School\\vendor\\tijsverkoyen\\css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.7.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\vlucas\\phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\voku\\portable-ascii\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.5.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\composer\\semver\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\fakerphp\\faker\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.18.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\filp\\whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\hamcrest\\hamcrest-php\";}s:22:\"laravel/agent-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"laravel/agent-detector\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\agent-detector\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^2.10\";s:7:\"\0*\0path\";s:48:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.2.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.2.5\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pail\";}s:11:\"laravel/pao\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/pao\";s:10:\"\0*\0version\";s:5:\"1.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.0.6\";s:7:\"\0*\0path\";s:46:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pao\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.27\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\laravel\\roster\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\mockery\\mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\myclabs\\deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.6\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\nunomaduro\\collision\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\Users\\User\\Videos\\School\\vendor\\phar-io\\manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\phar-io\\version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.38\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:8:\"^12.5.12\";s:7:\"\0*\0path\";s:50:\"C:\\Users\\User\\Videos\\School\\vendor\\phpunit\\phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:49:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\sebastian\\version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\Users\\User\\Videos\\School\\vendor\\staabm\\side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:5:\"8.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:47:\"C:\\Users\\User\\Videos\\School\\vendor\\symfony\\yaml\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\vendor\\theseer\\tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:117:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;i:116;r:933;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:6:{s:17:\"@tailwindcss/vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"@tailwindcss/vite\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:58:\"C:\\Users\\User\\Videos\\School\\node_modules\\@tailwindcss\\vite\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:6:\"10.0.3\";s:9:\"\0*\0source\";r:1066;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.0.3\";s:7:\"\0*\0path\";s:53:\"C:\\Users\\User\\Videos\\School\\node_modules\\concurrently\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:3:\"3.1\";s:9:\"\0*\0source\";r:1066;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:60:\"C:\\Users\\User\\Videos\\School\\node_modules\\laravel-vite-plugin\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1066;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:52:\"C:\\Users\\User\\Videos\\School\\node_modules\\tailwindcss\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1066;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^8.0.0\";s:7:\"\0*\0path\";s:45:\"C:\\Users\\User\\Videos\\School\\node_modules\\vite\";}s:18:\"@laravel/multiplex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@laravel/multiplex\";s:10:\"\0*\0version\";s:5:\"0.4.1\";s:9:\"\0*\0source\";r:1066;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^0.4.1\";s:7:\"\0*\0path\";s:59:\"C:\\Users\\User\\Videos\\School\\node_modules\\@laravel\\multiplex\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:6:{i:0;r:1063;i:1;r:1071;i:2;r:1079;i:3;r:1087;i:4;r:1095;i:5;r:1103;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";N;}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:32:\"Laravel\\Roster\\Enums\\Stack:Blade\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:3:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:33:\"Laravel\\Roster\\Enums\\Agent:Cursor\";i:2;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}', 1791780529);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `school_id`, `name`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Grade 1', 1, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(2, 1, 'Grade 2', 2, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(3, 1, 'Grade 5', 5, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(4, 1, 'Grade 9', 9, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(5, 1, 'Grade 10', 10, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(6, 3, 'Class 1', 1, 'active', '2026-10-10 10:40:21', '2026-10-10 10:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `school_id`, `academic_year_id`, `class_id`, `subject_id`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, 3, '2026-10-10 00:01:33', '2026-10-11 07:51:57'),
(2, 1, 2, 1, 1, 7, '2026-10-10 00:02:06', '2026-10-10 00:02:06');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_number` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `job_title` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'teacher',
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `joining_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `school_id`, `user_id`, `employee_number`, `name`, `email`, `phone`, `job_title`, `role`, `salary`, `joining_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'EMP-2025-001', 'Ahmed Raza', 'ahmed.teacher@beaconhouse.edu', '+92-300-5555001', 'Mathematics Teacher', 'teacher', 45000.00, '2024-04-01', 'active', '2026-10-09 15:12:03', '2026-10-09 15:12:03'),
(2, 1, 6, 'EMP-2025-002', 'Ayesha Malik', 'accounts@beaconhouse.edu', '+92-300-5555002', 'Accounts Officer', 'accountant', 40000.00, '2024-04-01', 'active', '2026-10-09 15:12:04', '2026-10-09 15:12:04'),
(3, 1, 7, 'EMP-125', 'Muhammad Moosa', 'sardar@gmail.com', '0300000004', 'Math Teacher', 'teacher', 45000.00, '2026-10-09', 'active', '2026-10-09 15:45:06', '2026-10-09 15:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `exam_type` enum('mid_term','final_term','quiz','unit_test','assignment') NOT NULL DEFAULT 'mid_term',
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `status` enum('draft','published','locked') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `school_id`, `academic_year_id`, `name`, `exam_type`, `starts_on`, `ends_on`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Mid Term 2026-2027', 'mid_term', '2026-10-10', '2026-10-27', 'published', '2026-10-10 00:04:25', '2026-10-11 07:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `class_subject_id` bigint(20) UNSIGNED NOT NULL,
  `max_marks` decimal(6,2) NOT NULL DEFAULT 100.00,
  `passing_marks` decimal(6,2) NOT NULL DEFAULT 40.00,
  `exam_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_adjustments`
--

CREATE TABLE `fee_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('discount','waiver','correction','write_off') NOT NULL DEFAULT 'discount',
  `amount` decimal(10,2) NOT NULL,
  `reason` text NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_audit_logs`
--

CREATE TABLE `fee_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_audit_logs`
--

INSERT INTO `fee_audit_logs` (`id`, `school_id`, `entity_type`, `entity_id`, `action`, `old_values`, `new_values`, `actor_id`, `created_at`) VALUES
(1, 1, 'FeeInvoice', 1, 'created', NULL, '{\"school_id\":1,\"student_id\":1,\"academic_year_id\":2,\"invoice_number\":\"INV-001-QUOLBI\",\"billing_period\":\"October 2026\",\"issue_date\":\"2026-10-10T00:00:00.000000Z\",\"due_date\":\"2026-10-20T00:00:00.000000Z\",\"subtotal\":\"10000.00\",\"discount_total\":\"0.00\",\"fine_total\":\"0.00\",\"total_amount\":\"10000.00\",\"paid_amount\":\"0.00\",\"status\":\"pending\",\"updated_at\":\"2026-10-10T06:06:14.000000Z\",\"created_at\":\"2026-10-10T06:06:14.000000Z\",\"id\":1}', 2, '2026-10-10 01:06:14'),
(2, 1, 'FeePayment', 1, 'payment_received', NULL, '{\"school_id\":1,\"student_id\":1,\"receipt_number\":\"REC-001-OCIQ24\",\"payment_date\":\"2026-10-10T00:00:00.000000Z\",\"amount\":\"10000.00\",\"method\":\"cash\",\"reference\":null,\"received_by\":2,\"status\":\"completed\",\"notes\":null,\"updated_at\":\"2026-10-10T06:06:58.000000Z\",\"created_at\":\"2026-10-10T06:06:58.000000Z\",\"id\":1}', 2, '2026-10-10 01:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `fee_categories`
--

CREATE TABLE `fee_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_categories`
--

INSERT INTO `fee_categories` (`id`, `school_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly Tuition Fee', NULL, 1, '2026-10-10 01:03:37', '2026-10-10 01:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `billing_period` varchar(50) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fine_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoices`
--

INSERT INTO `fee_invoices` (`id`, `school_id`, `student_id`, `academic_year_id`, `invoice_number`, `billing_period`, `issue_date`, `due_date`, `subtotal`, `discount_total`, `fine_total`, `total_amount`, `paid_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'INV-001-QUOLBI', 'October 2026', '2026-10-10', '2026-10-20', 10000.00, 0.00, 0.00, 10000.00, 10000.00, 'paid', '2026-10-10 01:06:14', '2026-10-10 01:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoice_items`
--

CREATE TABLE `fee_invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `fee_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `fee_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoice_items`
--

INSERT INTO `fee_invoice_items` (`id`, `school_id`, `fee_invoice_id`, `fee_category_id`, `description`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Monthly Tuition Fee', 5000.00, '2026-10-10 01:06:14', '2026-10-10 01:06:14'),
(2, 1, 1, 1, 'Monthly Tuition Fee', 5000.00, '2026-10-10 01:06:14', '2026-10-10 01:06:14');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','bank_transfer','card','cheque','online') NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('completed','voided','refunded') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `school_id`, `student_id`, `receipt_number`, `payment_date`, `amount`, `method`, `reference`, `received_by`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'REC-001-OCIQ24', '2026-10-10', 10000.00, 'cash', NULL, 2, 'completed', NULL, '2026-10-10 01:06:58', '2026-10-10 01:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payment_allocations`
--

CREATE TABLE `fee_payment_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `fee_payment_id` bigint(20) UNSIGNED NOT NULL,
  `fee_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `amount_applied` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payment_allocations`
--

INSERT INTO `fee_payment_allocations` (`id`, `school_id`, `fee_payment_id`, `fee_invoice_id`, `amount_applied`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 10000.00, '2026-10-10 01:06:58', '2026-10-10 01:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `fee_category_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `frequency` enum('monthly','annual','one_time','term') NOT NULL DEFAULT 'monthly',
  `due_day` smallint(5) UNSIGNED NOT NULL DEFAULT 10,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `school_id`, `academic_year_id`, `class_id`, `fee_category_id`, `amount`, `frequency`, `due_day`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, 2000.00, 'monthly', 10, 1, '2026-10-10 01:03:55', '2026-10-10 01:03:55'),
(2, 1, 2, 2, 1, 2000.00, 'monthly', 10, 1, '2026-10-10 01:04:07', '2026-10-10 01:04:07'),
(3, 1, 2, 5, 1, 3000.00, 'monthly', 10, 1, '2026-10-10 01:04:31', '2026-10-10 01:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `grading_scales`
--

CREATE TABLE `grading_scales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT 'Standard',
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `remarks` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) NOT NULL DEFAULT 'Father',
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`id`, `school_id`, `user_id`, `name`, `phone`, `email`, `relationship`, `address`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'Hassan Ali', '+92-300-1234567', 'parent.hassan@email.com', 'father', '456 Gulberg, Lahore', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(2, 1, NULL, 'Zahid', '9111111111', 'moosa@gmail.com', 'Father', 'Quaidabad', '2026-10-09 14:13:29', '2026-10-09 14:13:29'),
(3, 1, NULL, 'Amin', '05468415544', 'ali@gmail.com', 'Father', 'Quaidabad', '2026-10-11 21:35:12', '2026-10-11 21:35:12'),
(4, 1, NULL, 'Jameel', '03443558434', 'amar@gmail.com', 'Father', 'Quaidabad', '2026-10-11 23:03:57', '2026-10-11 23:03:57'),
(5, 1, NULL, 'aran', '05468415544', 'azlan@gmail.com', 'Father', 'Quaidabad', '2026-10-12 03:47:27', '2026-10-12 03:47:27'),
(6, 1, NULL, 'Rashid Mehmood', NULL, NULL, 'Father', NULL, '2026-10-16 23:23:29', '2026-10-16 23:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `homework`
--

CREATE TABLE `homework` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `class_subject_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `assigned_on` date NOT NULL,
  `due_on` date NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_otp_challenges`
--

CREATE TABLE `login_otp_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `purpose` varchar(50) NOT NULL DEFAULT 'login',
  `channel` varchar(20) NOT NULL DEFAULT 'email',
  `destination_masked` varchar(100) DEFAULT NULL,
  `code_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `consumed_at` timestamp NULL DEFAULT NULL,
  `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `resend_after` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_otp_challenges`
--

INSERT INTO `login_otp_challenges` (`id`, `user_id`, `purpose`, `channel`, `destination_masked`, `code_hash`, `expires_at`, `consumed_at`, `attempt_count`, `resend_after`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'email', 's********n@schoolsaas.com', '$2y$12$rdr4gFNcucpxZqToXkc/0O3y7uJd0wfYPykICVHqbSabnQC0FJa0a', '2026-10-09 12:03:40', '2026-10-09 07:03:40', 1, '2026-10-09 07:04:01', '2026-10-09 07:03:31', '2026-10-09 07:03:40'),
(2, 1, 'login', 'email', 's********n@schoolsaas.com', '$2y$12$afoB28O1IKf2m136r8Kmh.PvGQSKFViX.8b.A40zLLK/CJ8yZ7xzi', '2026-10-09 12:19:18', '2026-10-09 07:19:18', 1, '2026-10-09 07:19:39', '2026-10-09 07:19:09', '2026-10-09 07:19:18'),
(3, 1, 'login', 'email', 's********n@schoolsaas.com', '$2y$12$C34KzD.bKhkzQ38ctjCZmO49024W6BBE0EZrIa/sCgaB9gG5S59Zq', '2026-10-09 12:19:30', '2026-10-09 07:19:30', 1, '2026-10-09 07:19:56', '2026-10-09 07:19:26', '2026-10-09 07:19:30'),
(4, 2, 'login', 'email', 'a***n@beaconhouse.edu', '$2y$12$7EPtUSTyZRus4lzveGecg.LxjIXtrgnJRM9mX0sNG4SWY8D5PWyii', '2026-10-09 12:20:03', '2026-10-09 07:20:03', 1, '2026-10-09 07:20:30', '2026-10-09 07:20:00', '2026-10-09 07:20:03'),
(5, 3, 'login', 'email', 'a***********r@beaconhouse.edu', '$2y$12$oRQuwFGqFBzxEi3p4IHfv.VYd2/4S83jh7KgnyMRCAWm305kgYPrO', '2026-10-09 12:24:19', '2026-10-09 07:24:19', 1, '2026-10-09 07:24:46', '2026-10-09 07:24:16', '2026-10-09 07:24:19');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `exam_subject_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `marks_obtained` decimal(6,2) DEFAULT NULL,
  `status` enum('graded','absent','exempt') NOT NULL DEFAULT 'graded',
  `entered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000001_create_core_platform_tables', 1),
(5, '2026_01_01_000002_create_academic_and_student_tables', 1),
(6, '2026_01_01_000003_create_staff_attendance_fee_tables', 1),
(7, '2026_01_01_000004_create_exams_timetable_notices_audit_tables', 1),
(8, '2026_10_10_163532_add_avatar_path_to_users_table', 2),
(9, '2026_10_11_000001_add_qr_tokens_to_students', 2),
(10, '2026_10_11_000002_create_student_qr_scan_events_table', 2),
(11, '2026_10_12_032043_add_must_change_password_to_users_table', 3),
(12, '2026_10_12_032044_create_whatsapp_credential_deliveries_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `channel` enum('in_app','email','whatsapp') NOT NULL DEFAULT 'in_app',
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','read','failed') NOT NULL DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_events`
--

CREATE TABLE `payment_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(50) NOT NULL,
  `event_id` varchar(100) NOT NULL,
  `payload_hash` varchar(64) NOT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `billing_interval` enum('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `student_limit` int(11) NOT NULL DEFAULT 500,
  `staff_limit` int(11) NOT NULL DEFAULT 50,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price`, `currency`, `billing_interval`, `student_limit`, `staff_limit`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 99.00, 'USD', 'monthly', 500, 50, 1, '2026-10-09 06:56:14', '2026-10-09 06:56:14'),
(2, 'Professional', 199.00, 'USD', 'monthly', 2000, 200, 1, '2026-10-09 06:56:14', '2026-10-09 06:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `report_cards`
--

CREATE TABLE `report_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
  `status` enum('pending','active','suspended','archived') NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `slug`, `email`, `phone`, `address`, `currency`, `timezone`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Beaconhouse Model School', 'beaconhouse', 'info@beaconhouse.edu', '+92-42-3571-0000', '123 Education Lane, Lahore, Punjab', 'PKR', 'Asia/Karachi', 'active', 1, '2026-10-09 06:56:14', '2026-10-09 06:56:14'),
(2, 'ABC School', 'abc-school-FgTp', 'abc@gmail.com', '03000000000', 'Quaidabad', 'PKR', 'Asia/Karachi', 'active', 1, '2026-10-10 00:51:15', '2026-10-10 01:43:37'),
(3, 'XYZ School', 'xyz-school-Om0f', 'zzz@gmail.com', '03001234567', 'Quaidabad', 'PKR', 'Asia/Karachi', 'active', 1, '2026-10-10 01:51:26', '2026-10-11 21:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `school_invitations`
--

CREATE TABLE `school_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'school_admin',
  `token_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `invited_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_invitations`
--

INSERT INTO `school_invitations` (`id`, `school_id`, `email`, `role`, `token_hash`, `expires_at`, `accepted_at`, `revoked_at`, `invited_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'abc@gmail.com', 'school_admin', 'ff1e635b8abb4559f127acb766b239acedd43c15250923b6c4eec08026e07ced', '2026-10-17 00:51:15', NULL, NULL, 1, '2026-10-10 00:51:15', '2026-10-10 00:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `school_user`
--

CREATE TABLE `school_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'school_admin',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_user`
--

INSERT INTO `school_user` (`id`, `school_id`, `user_id`, `role`, `status`, `joined_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'school_admin', 'active', '2026-10-09 15:12:02', '2026-10-09 06:56:15', '2026-10-09 15:12:02'),
(2, 1, 3, 'teacher', 'active', '2026-10-09 15:12:03', '2026-10-09 06:56:15', '2026-10-09 15:12:03'),
(3, 1, 4, 'parent', 'active', '2026-10-09 15:12:05', '2026-10-09 06:56:16', '2026-10-09 15:12:05'),
(4, 1, 5, 'student', 'active', '2026-10-09 15:12:06', '2026-10-09 06:56:17', '2026-10-09 15:12:06'),
(5, 1, 6, 'accountant', 'active', '2026-10-09 15:12:04', '2026-10-09 15:12:04', '2026-10-09 15:12:04'),
(6, 1, 7, 'teacher', 'active', '2026-10-09 15:45:06', '2026-10-09 15:45:06', '2026-10-09 15:45:06'),
(7, 3, 8, 'school_admin', 'active', '2026-10-10 01:51:26', '2026-10-10 01:51:26', '2026-10-10 01:51:26'),
(8, 1, 9, 'student', 'active', '2026-10-11 21:35:12', '2026-10-11 21:35:12', '2026-10-11 21:35:12'),
(9, 1, 10, 'student', 'active', '2026-10-11 23:03:57', '2026-10-11 23:03:57', '2026-10-11 23:03:57'),
(10, 1, 11, 'student', 'active', '2026-10-12 03:47:27', '2026-10-12 03:47:27', '2026-10-12 03:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 40,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `school_id`, `class_id`, `name`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'A', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(2, 1, 1, 'B', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(3, 1, 2, 'A', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(4, 1, 2, 'B', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(5, 1, 3, 'A', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(6, 1, 3, 'B', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(7, 1, 4, 'A', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(8, 1, 4, 'B', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(9, 1, 5, 'A', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(10, 1, 5, 'B', 40, 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(11, 3, 6, 'Section A', 40, 'active', '2026-10-10 10:40:21', '2026-10-10 10:40:21'),
(12, 3, 6, 'Section A', 12, 'active', '2026-10-10 10:40:47', '2026-10-10 10:40:47');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3fdyisj1pPEVxo4EoeI2FZFDxR6shtLHZaVqP7yC', 2, '127.0.0.1', '', 'eyJfdG9rZW4iOiJEdHJtWkl0ZVdiQ01EU0kzS1pWYnNaSEU3eFNyMmN5S0ozemk3RmtKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zY2hvb2wtYWRtaW5cL3JlcG9ydHNcL2ZlZS1jb2xsZWN0aW9uIiwicm91dGUiOiJzY2hvb2wtYWRtaW4ucmVwb3J0cy5mZWUtY29sbGVjdGlvbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyLCJwYXNzd29yZF9oYXNoX3dlYiI6IjgwNzI1OWU5MTMyYjQxMzFiMmU2MjU4YzUwNjlkZjk3Njg0MGVkNjQzNDg2MjgyOGVjYmYwNDViNDg5ODBkMTciLCJjdXJyZW50X3NjaG9vbF9pZCI6MX0=', 1792210952),
('3jSYLzAmKCeilUYhJYEqykkMzJ5JE0UumAAWJMYS', 5, '127.0.0.1', '', 'eyJfdG9rZW4iOiJvdzRUNUxnY21EeTZianlicGVheWdoSENIaDY2UVlHM2c3RlNrenI0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zdHVkZW50XC9kYXNoYm9hcmQiLCJyb3V0ZSI6InN0dWRlbnQuZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjUsInBhc3N3b3JkX2hhc2hfd2ViIjoiMjNiN2VmOWRjMjM2ZjBiZDYxMzVkNTU0NGM1ZjdiNzgwOWJkOGU5ZDUyMzQ0ZjZmY2NjMDg1YjRkODI3ZGQzZCIsImN1cnJlbnRfc2Nob29sX2lkIjoxfQ==', 1792210955),
('7gcwQczgJf3WtUl3lxf45E7CV1aeVBvsJFAeNP4N', 5, '127.0.0.1', '', 'eyJfdG9rZW4iOiJtOHJHcllSTDBJS1F3eVFEUldmd0hXUzlNS0lMVmdGc0xJdkpuODdLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NSwicGFzc3dvcmRfaGFzaF93ZWIiOiIyM2I3ZWY5ZGMyMzZmMGJkNjEzNWQ1NTQ0YzVmN2I3ODA5YmQ4ZTlkNTIzNDRmNmZjY2MwODViNGQ4MjdkZDNkIiwiY3VycmVudF9zY2hvb2xfaWQiOjF9', 1792210924),
('abtJVM9ic9YQejiIoaqtW8ujOZ79DrRShjgvzc1M', NULL, '127.0.0.1', '', 'eyJfdG9rZW4iOiJVV1pqT3NBT2hWcjdsSERUcUlxVExhMmFDcDBxcmVPbkE0SHdXOGloIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3RlYWNoZXJcL2Rhc2hib2FyZCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3RlYWNoZXJcL2Rhc2hib2FyZCIsInJvdXRlIjoidGVhY2hlci5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792210925),
('brrKAnzZStl4WsYiVE3hyatXY5ufX1XMScuhRy6s', 3, '127.0.0.1', '', 'eyJfdG9rZW4iOiI2NHppMGtPRGE1c2lMZldyeDA2dEFsYWs5WENOTTB4YUNBUXQ5VThZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MywicGFzc3dvcmRfaGFzaF93ZWIiOiIyZDhlYzVlNjBkYjEzNjhmMjRkYmY0OThjZTc0NzFjMWI3ZDZjOTRhZGI2MzhlZDc2ODAwZTA0MDYyYzZjYWE1IiwiY3VycmVudF9zY2hvb2xfaWQiOjF9', 1792210925),
('dvIE1wPFEXLaRFkfpB6iX9XwvkrngAA8gDyPbfpn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI2SGxvVThqUDlqMExTWU15MDdCWGJSZTNva3k5TjdrU0VIdWgxTG5sIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==', 1792214255),
('f42Hq9Qr9PLsj2k6OlUSARSLI1aepSEMB2P2bw09', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456', 'eyJfdG9rZW4iOiJJeFRlV3M3QkZwU2xJNTE3cnBxb25Db0VIMkdiRmtHUnJpdzhQZFp2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792210723),
('GOwUvYmkI78y4vCArRw1b44o5sxTW7ymT35WzyFt', 3, '127.0.0.1', '', 'eyJfdG9rZW4iOiJQZFZQTGRUYXFQSGZOZE9PRHRUblJ2ekJtbEVBREV5V3cyMzhGR05iIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zY2hvb2wtYWRtaW5cL2V4YW1zIiwicm91dGUiOiJzY2hvb2wtYWRtaW4uZXhhbXMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MywicGFzc3dvcmRfaGFzaF93ZWIiOiIyZDhlYzVlNjBkYjEzNjhmMjRkYmY0OThjZTc0NzFjMWI3ZDZjOTRhZGI2MzhlZDc2ODAwZTA0MDYyYzZjYWE1IiwiY3VycmVudF9zY2hvb2xfaWQiOjF9', 1792210953),
('K01xVzBK88X0bvcjXuOCLAdRMdeScXcFTmNSrC9M', NULL, '127.0.0.1', '', 'eyJfdG9rZW4iOiJmS3Z0STQ5MmJuUnEzSEpETGl4bmhzWkpidVQ4bjBzYlV0aDNvRWl1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zdXBlci1hZG1pblwvc2Nob29scyIsInJvdXRlIjoic3VwZXItYWRtaW4uc2Nob29scy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6eyJpbnRlbmRlZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zdXBlci1hZG1pblwvc2Nob29scyJ9fQ==', 1792210926),
('kLKi2Qg6UVmE1YiKeZVN8L649ydmHVIzi5YxC7Wx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJCajJOSDNFb2V5d0V0Q25UVE1WRXRkOERBWlVFbUY0NEowck5PWkZSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792210849),
('ox3FNwDN1DuoehcKo1yaUeEsZaQBMkKEjONs8LCf', 2, '127.0.0.1', '', 'eyJfdG9rZW4iOiJaWTl6aU9hanAxTGR4NU1wbmtUSDU2UlR3RWg3QzE3TnBHQ0hCM0l0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zY2hvb2wtYWRtaW5cL3N0dWRlbnRzXC82Iiwicm91dGUiOiJzY2hvb2wtYWRtaW4uc3R1ZGVudHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyLCJwYXNzd29yZF9oYXNoX3dlYiI6IjgwNzI1OWU5MTMyYjQxMzFiMmU2MjU4YzUwNjlkZjk3Njg0MGVkNjQzNDg2MjgyOGVjYmYwNDViNDg5ODBkMTciLCJjdXJyZW50X3NjaG9vbF9pZCI6MX0=', 1792211010),
('qa6FJ3xNUfFohr9n1KjQ0oFBKW1P725EaKpYMriv', NULL, '127.0.0.1', '', 'eyJfdG9rZW4iOiJ0a1Y2d0NTd1Z4UEY4U2tiZWJRSGJUYnZvWTJZTGloeWFSeTV5ZmlXIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3NjaG9vbC1hZG1pblwvcmVwb3J0c1wvZmVlLWNvbGxlY3Rpb24ifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zY2hvb2wtYWRtaW5cL3JlcG9ydHNcL2ZlZS1jb2xsZWN0aW9uIiwicm91dGUiOiJzY2hvb2wtYWRtaW4ucmVwb3J0cy5mZWUtY29sbGVjdGlvbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1792210924),
('QYgw0VxvzrU9Y7TRYtBdFgJskF1o5xqaCGWVRWad', NULL, '127.0.0.1', '', 'eyJfdG9rZW4iOiJSRHpjVGRrTW1GZjVwc3UwQWlqVHRHUzBiN0ZHZW5KdFJzcjdZMjdFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zdXBlci1hZG1pblwvc3Vic2NyaXB0aW9ucyIsInJvdXRlIjoic3VwZXItYWRtaW4uc3Vic2NyaXB0aW9ucy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6eyJpbnRlbmRlZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zdXBlci1hZG1pblwvc3Vic2NyaXB0aW9ucyJ9fQ==', 1792210956),
('Wf6PTPWgbdHjy3K6RvS5w0RPTzxJFJcbFHZ35Q0W', 2, '127.0.0.1', '', 'eyJfdG9rZW4iOiJrM2xYd25kYW1FczVlMkVFQjhaSGVoM2NJcEYxMnJ4YjRBczVpR0xGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MiwicGFzc3dvcmRfaGFzaF93ZWIiOiI4MDcyNTllOTEzMmI0MTMxYjJlNjI1OGM1MDY5ZGY5NzY4NDBlZDY0MzQ4NjI4MjhlY2JmMDQ1YjQ4OTgwZDE3IiwiY3VycmVudF9zY2hvb2xfaWQiOjF9', 1792210923),
('ZOo1CPVeBN6oPlR6VptToJByus8vDMTpWDbsp0jh', NULL, '127.0.0.1', '', 'eyJfdG9rZW4iOiJoRTU0eVZYZDVzcjUxVll3V1FkYVRkd0FKSThObEY1Y0N4cktZbzNQIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3N0dWRlbnRcL2Rhc2hib2FyZCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3N0dWRlbnRcL2Rhc2hib2FyZCIsInJvdXRlIjoic3R1ZGVudC5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792210925);

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admission_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'male',
  `admission_date` date DEFAULT NULL,
  `status` enum('active','pending','transferred','withdrawn','graduated') NOT NULL DEFAULT 'active',
  `photo_path` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_phone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qr_token` text DEFAULT NULL,
  `qr_token_hash` char(64) DEFAULT NULL,
  `qr_token_issued_at` timestamp NULL DEFAULT NULL,
  `qr_token_revoked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `school_id`, `user_id`, `admission_number`, `first_name`, `last_name`, `date_of_birth`, `gender`, `admission_date`, `status`, `photo_path`, `address`, `emergency_phone`, `created_at`, `updated_at`, `qr_token`, `qr_token_hash`, `qr_token_issued_at`, `qr_token_revoked_at`) VALUES
(1, 1, 5, 'BHS-2025-001', 'Ali', 'Hassan', '2018-05-15', 'male', '2026-10-09', 'active', NULL, '456 Gulberg, Lahore', '+92-300-1234567', '2026-10-09 06:56:17', '2026-10-09 06:56:17', 'eyJpdiI6IitUZmRJdjdoN1o5Yno5OUlSdFFOR2c9PSIsInZhbHVlIjoiT2VUSXltRTc4RWdXb0RSY2lrQytTcXJMNVNVNG9RZFNwTS9TdEZqMGRjemdGaFFxREdvNWRlRmlSNjhWbFdDVXNBWnNjNEV2T1Y4OEE2RkxQUjVaclBpamdWek9yZWRwOW4zVHJMejhLdmc9IiwibWFjIjoiOTczNjE2YmEwZDMxM2FhZDQ1ZWU1ODkyMjIzYzJjZjIzNDliMjEzMDRjNmJkNTUyMjg4YzE2Y2JjMDRkMmVmMiIsInRhZyI6IiJ9', 'fa6058486d833da29dfea4c465fecaf835158210ea61239ce7f3b7143fb3bd3b', '2026-10-11 07:47:25', NULL),
(2, 1, NULL, 'ADM-0012', 'Muhammad', 'Moosa', '2022-09-14', 'male', '2026-10-09', 'active', NULL, 'Quaidabad', '0242325542324', '2026-10-09 14:13:29', '2026-10-11 21:30:06', 'eyJpdiI6IjJVRjJIU200RHR3NXd6b2Nta3ZvN2c9PSIsInZhbHVlIjoiUmVEZk14RmpLRG1uNWc4NGt1Rkxja0NzSG5oMDBITk55Y29PcTFZcmRjUEozL1p1SExPZ1EvcG5kN1gwTEFHdm1zdjdwaVZTcjRMTkV2WXJPcXNpZlRCK1JxNXhTNlB5cUlHei9UK3ArSUk9IiwibWFjIjoiZDBkOWFkZDZiOTUyYmJmNzFkNjI3ZDAyMDRiYjY5MGM0MjBkOWFlNTY2Zjc5MzIzMTYxOWI4NTM2Y2I0NWMzNCIsInRhZyI6IiJ9', 'd9905b940c93b245607dd2fc5ffeb717613dbd6a99ff076198609d4f9ad1e41a', '2026-10-11 21:30:06', NULL),
(3, 1, 9, 'ADM-1475', 'Ali', 'Khan', '2026-10-01', 'male', '2026-10-12', 'active', NULL, 'Quaidabad', '0242325542324', '2026-10-11 21:35:12', '2026-10-11 21:35:12', 'eyJpdiI6IjdFUzA3K3RoL2tKb3BXdlErSXZGZXc9PSIsInZhbHVlIjoiNmszOHgvcldmREJkNlJmTmZ4ZmlDdCs1c3pnQzJvTGxnc3l2RGRXTlRyQ1dCNkV4N3ZxQUQxT29YRjlaanFsQVRaVzA1c1dIRjQ4VUN0c1Ird2JHMnlJczZqYlozZ1J3YUUwTUNSUXhSMWc9IiwibWFjIjoiY2VjYTllNTM1OTgxZDFjOWVjZTU1NmRhZDI5MWVhNGExOTRiZjhkODBlOGIwYWJkOTk3N2U3ZDUzYThiNzViMiIsInRhZyI6IiJ9', '739120943a9e7721ce3b12c84d73b3881a28daa0886eef516b4eeb9f07e13c1e', '2026-10-11 21:35:12', NULL),
(4, 1, 10, 'ADM-8232', 'Muammad', 'Ammar', '2026-08-04', 'male', '2026-10-12', 'active', NULL, 'Quaidabad', '03443558434', '2026-10-11 23:03:57', '2026-10-11 23:03:57', 'eyJpdiI6IlhyRXpUeTI1aE5UT3JvUDRzTjdrbGc9PSIsInZhbHVlIjoibkUwVUhRS2hUdExQMmYvQWxuVVF4L0p5SW9RQjJ5c0JzME1GSEZQanE3UlgvS09kem92OHVFZlZIeHNWNXZMWjhGZklqWUxITGE5NmJJNVIydkdlczdobktES29iMFBMejFnakg3NzNFeWc9IiwibWFjIjoiNjBlY2I4YjhkMzc3YTNhMjUyMDUyYjc5ZmZkODdkY2ViMmVhNDE4ZjNkYTdiZTk1OWMxYzFkNzNlZGYyMDA5ZSIsInRhZyI6IiJ9', '847bb04157b522d6415308f3cfaaa5039199bb9b3f679f7921197f5f8a0963ce', '2026-10-11 23:03:57', NULL),
(5, 1, 11, 'ADM-2746', 'Azlan', 'Saqib', '2026-08-05', 'male', '2026-10-12', 'active', NULL, 'Quaidabad', '0242325542324', '2026-10-12 03:47:27', '2026-10-12 03:47:27', 'eyJpdiI6Im1ka2swWkx5aEFlU05wMzNEUVNIYWc9PSIsInZhbHVlIjoiVXJCQnVLVzJyUForUEpRcVh1ck9zbE1IU0pqdUdEUnROZklqYmFTM3JkMDNWLytkNFBWV1A2c0hvckdKdXQrb250bUpqenc4cGxYbU4wMVVLZTNLWFgvWm1ZNVA3eHRYRjJtZ0c5TFJwU2M9IiwibWFjIjoiMjFlZjVmZDk3MTkxY2UyMDk2ZThjMTdkZDM5MmNkNGUzNzIzMjc2NWQwYTdlZTJlNmYzZTUyMWJiNTNhYWEzMyIsInRhZyI6IiJ9', 'b006549bb4db17b988ee0b8b284d908ef6cc6c9fc3d737da5a878ee17743e6f1', '2026-10-12 03:47:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_session_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `marked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id`, `school_id`, `attendance_session_id`, `student_id`, `status`, `marked_by`, `marked_at`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'present', 2, '2026-10-09 14:14:23', NULL, '2026-10-09 14:14:23', '2026-10-09 14:14:23'),
(2, 1, 2, 1, 'present', 2, '2026-10-09 16:26:17', NULL, '2026-10-09 16:26:17', '2026-10-09 16:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `private_file_path` varchar(255) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollments`
--

CREATE TABLE `student_enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `roll_number` varchar(30) DEFAULT NULL,
  `status` enum('enrolled','promoted','transferred','withdrawn') NOT NULL DEFAULT 'enrolled',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_enrollments`
--

INSERT INTO `student_enrollments` (`id`, `school_id`, `student_id`, `academic_year_id`, `class_id`, `section_id`, `roll_number`, `status`, `enrolled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 5, 9, '45', 'enrolled', '2026-10-09 14:13:29', '2026-10-09 14:13:29', '2026-10-09 14:13:29'),
(2, 1, 1, 1, 1, 1, '001', 'enrolled', '2026-10-09 15:12:07', '2026-10-09 15:12:07', '2026-10-09 15:12:07'),
(3, 1, 3, 2, 5, 10, '45', 'enrolled', '2026-10-11 21:35:12', '2026-10-11 21:35:12', '2026-10-11 21:35:12'),
(4, 1, 4, 2, 4, 7, '102', 'enrolled', '2026-10-11 23:03:57', '2026-10-11 23:03:57', '2026-10-11 23:03:57'),
(5, 1, 5, 2, 5, 10, '89', 'enrolled', '2026-10-12 03:47:27', '2026-10-12 03:47:27', '2026-10-12 03:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_assignments`
--

CREATE TABLE `student_fee_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `fee_structure_id` bigint(20) UNSIGNED NOT NULL,
  `amount_override` decimal(10,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_guardian`
--

CREATE TABLE `student_guardian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `guardian_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `receives_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_guardian`
--

INSERT INTO `student_guardian` (`id`, `school_id`, `student_id`, `guardian_id`, `is_primary`, `receives_notifications`, `created_at`, `updated_at`) VALUES
(2, 1, 2, 2, 1, 1, '2026-10-09 14:13:29', '2026-10-09 14:13:29'),
(4, 1, 3, 3, 1, 1, '2026-10-11 21:35:12', '2026-10-11 21:35:12'),
(5, 1, 4, 4, 1, 1, '2026-10-11 23:03:57', '2026-10-11 23:03:57'),
(6, 1, 5, 5, 1, 1, '2026-10-12 03:47:27', '2026-10-12 03:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_qr_scan_events`
--

CREATE TABLE `student_qr_scan_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_session_id` bigint(20) UNSIGNED NOT NULL,
  `scanned_by` bigint(20) UNSIGNED NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `event_type` enum('entry') NOT NULL DEFAULT 'entry',
  `processing_status` enum('pending_review','attendance_recorded','manual_attendance_exists') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `school_id`, `name`, `code`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mathematics', 'MATH', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(2, 1, 'English', 'ENGL', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(3, 1, 'Science', 'SCIE', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(4, 1, 'Urdu', 'URDU', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(5, 1, 'Islamiat', 'ISLA', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(6, 1, 'Computer', 'COMP', 'active', '2026-10-09 06:56:17', '2026-10-09 06:56:17'),
(7, 1, 'Chemistry', 'Chem', 'active', '2026-10-10 00:02:47', '2026-10-10 00:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('trial','active','past_due','expired','suspended','cancelled') NOT NULL DEFAULT 'trial',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `school_id`, `plan_id`, `status`, `starts_at`, `ends_at`, `trial_ends_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'active', '2026-10-09 06:56:14', '2027-11-09 06:56:14', NULL, NULL, '2026-10-09 06:56:14', '2026-10-09 14:17:38'),
(2, 2, 1, 'trial', '2026-10-10 00:51:15', '2026-10-24 00:51:15', '2026-10-24 00:51:15', NULL, '2026-10-10 00:51:15', '2026-10-10 00:51:15'),
(3, 3, 1, 'trial', '2026-10-10 01:51:26', '2026-10-24 01:51:26', '2026-10-24 01:51:26', NULL, '2026-10-10 01:51:26', '2026-10-10 01:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_payments`
--

CREATE TABLE `subscription_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `provider` varchar(50) NOT NULL DEFAULT 'manual',
  `provider_reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `paid_at` timestamp NULL DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_payments`
--

INSERT INTO `subscription_payments` (`id`, `subscription_id`, `school_id`, `amount`, `currency`, `provider`, `provider_reference`, `status`, `paid_at`, `receipt_path`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 99.00, 'USD', 'manual', 'MANUAL-RENEW-6AC93DCD92656', 'completed', '2026-10-09 14:17:38', NULL, '2026-10-09 14:17:38', '2026-10-09 14:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `class_subject_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `school_id`, `employee_id`, `class_subject_id`, `academic_year_id`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 2, 2, '2026-10-10 00:02:06', '2026-10-10 00:02:06'),
(3, 1, 1, 1, 2, '2026-10-11 07:52:09', '2026-10-11 07:52:09');

-- --------------------------------------------------------

--
-- Table structure for table `timetable_entries`
--

CREATE TABLE `timetable_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `class_subject_id` bigint(20) UNSIGNED NOT NULL,
  `weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `starts_at` time NOT NULL,
  `ends_at` time NOT NULL,
  `room_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `must_change_password`, `is_super_admin`, `status`, `last_login_at`, `remember_token`, `created_at`, `updated_at`, `avatar_path`) VALUES
(1, 'Muhammad Moosa', 'superadmin@schoolsaas.com', NULL, '$2y$12$b1P2zolb3winGFS2dlAMyuzzarj/a2nOBoWBA6Bv/vLvXM/o2M3Bm', 0, 1, 'active', '2026-10-16 23:27:13', NULL, '2026-10-09 06:54:21', '2026-10-16 23:27:13', 'profile-photos/B53GEawgX5GZMe2O2KNXQfP37r2hTbCFYZCBzHMR.jpg'),
(2, 'Dr. Sarah Khan', 'admin@beaconhouse.edu', '2026-10-09 06:56:15', '$2y$12$ksGevNrC8BtUb75JxEjG1um.Wh3.vlncOGL8vGOlIB7Co67ZppJB2', 0, 0, 'active', '2026-10-17 00:16:47', NULL, '2026-10-09 06:56:15', '2026-10-17 00:16:47', NULL),
(3, 'Ahmed Raza', 'ahmed.teacher@beaconhouse.edu', '2026-10-09 06:56:15', '$2y$12$3FFfwsr1o6TMj9c0BoQwUOwweL6e991Q.BjSftpSAfNVmALvJECna', 0, 0, 'active', '2026-10-17 00:13:27', NULL, '2026-10-09 06:56:15', '2026-10-17 00:13:27', NULL),
(4, 'Hassan Ali', 'parent.hassan@email.com', '2026-10-09 06:56:16', '$2y$12$WQaIzTqhC/C92FosN9by7OxDNUnkGAiH1Bu0D9Cmm6o5jW05nzXha', 0, 0, 'active', '2026-10-09 14:33:40', NULL, '2026-10-09 06:56:16', '2026-10-09 14:33:40', NULL),
(5, 'Ali Hassan', 'student@beaconhouse.edu', '2026-10-09 06:56:17', '$2y$12$il7KqiQpiZH16rd6CgXsu.6vX/gY1x55hjM0ho8bOxVVwO1Co8HLi', 0, 0, 'active', '2026-10-17 00:08:53', NULL, '2026-10-09 06:56:17', '2026-10-17 00:08:53', NULL),
(6, 'Ayesha Malik', 'accounts@beaconhouse.edu', '2026-10-09 15:12:04', '$2y$12$A6Q7yVi92nPGZgNPGFJfkuluZdx5X/qX0ik/ZUROmQ/YW0z5ipkBu', 0, 0, 'active', '2026-10-17 00:15:13', NULL, '2026-10-09 15:12:04', '2026-10-17 00:15:13', NULL),
(7, 'Muhammad Moosa', 'sardar@gmail.com', '2026-10-09 15:45:06', '$2y$12$V.CG87O5N89vR0cu.8iwYeYYjvNAHNWlLJdYupqC/bsqTz7pilkja', 0, 0, 'active', '2026-10-09 16:28:30', NULL, '2026-10-09 15:45:06', '2026-10-09 16:28:30', NULL),
(8, 'XYZ School Admin', 'zzz@gmail.com', NULL, '$2y$12$HCQD.wF5HeMhMuAAfV3bKufcbx8L58fFLAgPTahn8KQ1Hjty/TFjG', 0, 0, 'active', '2026-10-11 21:26:49', NULL, '2026-10-10 01:51:26', '2026-10-11 21:26:49', NULL),
(9, 'Ali Khan', 'Ali.ali@beachonhouse.edu', NULL, '$2y$12$EX3Spm46mF.YPZJE.fxZk.0w990jlTtlnG9FHnQdRjj1A.PF9yJ.G', 0, 0, 'active', '2026-10-11 23:04:51', NULL, '2026-10-11 21:35:12', '2026-10-11 23:05:23', NULL),
(10, 'Muammad Ammar', 'amar.muammad@beaconhouse.edu', '2026-10-11 23:03:56', '$2y$12$HB9L4D.nB6NLa8qRG5Hs1e6AAyVuSYpxlY76.ThAZcs1C.STdKdZW', 1, 0, 'active', NULL, NULL, '2026-10-11 23:03:57', '2026-10-11 23:03:57', NULL),
(11, 'Azlan Saqib', 'azlan.azlan@beaconhouse.edu', '2026-10-12 03:47:26', '$2y$12$oWxbXCqgAn6FUgzQc3YvreQRSoDJx8bzdr2Bsd2eTqv1dq9eu.Ake', 1, 0, 'active', NULL, NULL, '2026-10-12 03:47:27', '2026-10-12 03:47:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_credential_deliveries`
--

CREATE TABLE `whatsapp_credential_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_number` longtext DEFAULT NULL,
  `username` longtext DEFAULT NULL,
  `temporary_password` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `provider_message_id` varchar(255) DEFAULT NULL,
  `failure_reason` varchar(200) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_years_school_id_is_current_index` (`school_id`,`is_current`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_created_by_foreign` (`created_by`),
  ADD KEY `announcements_school_id_audience_status_index` (`school_id`,`audience`,`status`);

--
-- Indexes for table `attendance_change_logs`
--
ALTER TABLE `attendance_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_change_logs_school_id_foreign` (`school_id`),
  ADD KEY `attendance_change_logs_student_attendance_id_foreign` (`student_attendance_id`),
  ADD KEY `attendance_change_logs_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `att_session_unique` (`school_id`,`academic_year_id`,`class_id`,`section_id`,`attendance_date`),
  ADD KEY `attendance_sessions_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `attendance_sessions_class_id_foreign` (`class_id`),
  ADD KEY `attendance_sessions_section_id_foreign` (`section_id`),
  ADD KEY `attendance_sessions_taken_by_foreign` (`taken_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_school_id_action_index` (`school_id`,`action`),
  ADD KEY `audit_logs_actor_user_id_created_at_index` (`actor_user_id`,`created_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classes_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_subjects_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `class_subjects_class_id_foreign` (`class_id`),
  ADD KEY `class_subjects_subject_id_foreign` (`subject_id`),
  ADD KEY `class_subjects_teacher_id_foreign` (`teacher_id`),
  ADD KEY `class_subjects_school_id_class_id_academic_year_id_index` (`school_id`,`class_id`,`academic_year_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_school_id_employee_number_unique` (`school_id`,`employee_number`),
  ADD KEY `employees_user_id_foreign` (`user_id`),
  ADD KEY `employees_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `exams_school_id_academic_year_id_status_index` (`school_id`,`academic_year_id`,`status`);

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_subjects_exam_id_class_subject_id_unique` (`exam_id`,`class_subject_id`),
  ADD KEY `exam_subjects_school_id_foreign` (`school_id`),
  ADD KEY `exam_subjects_class_subject_id_foreign` (`class_subject_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `fee_adjustments`
--
ALTER TABLE `fee_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_adjustments_student_id_foreign` (`student_id`),
  ADD KEY `fee_adjustments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `fee_adjustments_approved_by_foreign` (`approved_by`),
  ADD KEY `fee_adjustments_school_id_student_id_index` (`school_id`,`student_id`);

--
-- Indexes for table `fee_audit_logs`
--
ALTER TABLE `fee_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_audit_logs_actor_id_foreign` (`actor_id`),
  ADD KEY `fee_audit_logs_school_id_entity_type_entity_id_index` (`school_id`,`entity_type`,`entity_id`);

--
-- Indexes for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_categories_school_id_name_index` (`school_id`,`name`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_invoices_school_id_invoice_number_unique` (`school_id`,`invoice_number`),
  ADD KEY `fee_invoices_student_id_foreign` (`student_id`),
  ADD KEY `fee_invoices_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `fee_invoices_school_id_status_due_date_index` (`school_id`,`status`,`due_date`);

--
-- Indexes for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_invoice_items_school_id_foreign` (`school_id`),
  ADD KEY `fee_invoice_items_fee_category_id_foreign` (`fee_category_id`),
  ADD KEY `fee_invoice_items_fee_invoice_id_index` (`fee_invoice_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_payments_school_id_receipt_number_unique` (`school_id`,`receipt_number`),
  ADD KEY `fee_payments_student_id_foreign` (`student_id`),
  ADD KEY `fee_payments_received_by_foreign` (`received_by`),
  ADD KEY `fee_payments_school_id_payment_date_index` (`school_id`,`payment_date`);

--
-- Indexes for table `fee_payment_allocations`
--
ALTER TABLE `fee_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_payment_allocations_school_id_foreign` (`school_id`),
  ADD KEY `fee_payment_allocations_fee_invoice_id_foreign` (`fee_invoice_id`),
  ADD KEY `fee_payment_allocations_fee_payment_id_fee_invoice_id_index` (`fee_payment_id`,`fee_invoice_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_structures_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `fee_structures_class_id_foreign` (`class_id`),
  ADD KEY `fee_structures_fee_category_id_foreign` (`fee_category_id`),
  ADD KEY `fee_structures_school_id_class_id_index` (`school_id`,`class_id`);

--
-- Indexes for table `grading_scales`
--
ALTER TABLE `grading_scales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grading_scales_school_id_min_percentage_index` (`school_id`,`min_percentage`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guardians_user_id_foreign` (`user_id`),
  ADD KEY `guardians_school_id_email_index` (`school_id`,`email`);

--
-- Indexes for table `homework`
--
ALTER TABLE `homework`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homework_class_subject_id_foreign` (`class_subject_id`),
  ADD KEY `homework_created_by_foreign` (`created_by`),
  ADD KEY `homework_school_id_due_on_index` (`school_id`,`due_on`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_otp_challenges`
--
ALTER TABLE `login_otp_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_otp_challenges_user_id_purpose_index` (`user_id`,`purpose`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `marks_exam_subject_id_student_id_unique` (`exam_subject_id`,`student_id`),
  ADD KEY `marks_student_id_foreign` (`student_id`),
  ADD KEY `marks_entered_by_foreign` (`entered_by`),
  ADD KEY `marks_updated_by_foreign` (`updated_by`),
  ADD KEY `marks_school_id_student_id_index` (`school_id`,`student_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_school_id_foreign` (`school_id`),
  ADD KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_events`
--
ALTER TABLE `payment_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_events_event_id_unique` (`event_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_cards`
--
ALTER TABLE `report_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_cards_student_id_exam_id_unique` (`student_id`,`exam_id`),
  ADD KEY `report_cards_school_id_foreign` (`school_id`),
  ADD KEY `report_cards_exam_id_foreign` (`exam_id`),
  ADD KEY `report_cards_generated_by_foreign` (`generated_by`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schools_slug_unique` (`slug`),
  ADD KEY `schools_created_by_foreign` (`created_by`),
  ADD KEY `schools_status_index` (`status`);

--
-- Indexes for table `school_invitations`
--
ALTER TABLE `school_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_invitations_token_hash_unique` (`token_hash`),
  ADD KEY `school_invitations_invited_by_foreign` (`invited_by`),
  ADD KEY `school_invitations_school_id_email_index` (`school_id`,`email`);

--
-- Indexes for table `school_user`
--
ALTER TABLE `school_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_user_school_id_user_id_unique` (`school_id`,`user_id`),
  ADD KEY `school_user_user_id_foreign` (`user_id`),
  ADD KEY `school_user_school_id_role_index` (`school_id`,`role`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sections_class_id_foreign` (`class_id`),
  ADD KEY `sections_school_id_class_id_index` (`school_id`,`class_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_att_unique` (`school_id`,`employee_id`,`attendance_date`),
  ADD KEY `staff_attendance_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_school_id_admission_number_unique` (`school_id`,`admission_number`),
  ADD UNIQUE KEY `students_qr_token_hash_unique` (`qr_token_hash`),
  ADD KEY `students_user_id_foreign` (`user_id`),
  ADD KEY `students_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_attendance_attendance_session_id_student_id_unique` (`attendance_session_id`,`student_id`),
  ADD KEY `student_attendance_student_id_foreign` (`student_id`),
  ADD KEY `student_attendance_marked_by_foreign` (`marked_by`),
  ADD KEY `student_attendance_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_documents_student_id_foreign` (`student_id`),
  ADD KEY `student_documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `student_documents_school_id_student_id_index` (`school_id`,`student_id`);

--
-- Indexes for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_enrollments_student_id_foreign` (`student_id`),
  ADD KEY `student_enrollments_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `student_enrollments_class_id_foreign` (`class_id`),
  ADD KEY `student_enrollments_section_id_foreign` (`section_id`),
  ADD KEY `enrollment_lookup_idx` (`school_id`,`academic_year_id`,`class_id`,`section_id`);

--
-- Indexes for table `student_fee_assignments`
--
ALTER TABLE `student_fee_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_fee_assignments_student_id_foreign` (`student_id`),
  ADD KEY `student_fee_assignments_fee_structure_id_foreign` (`fee_structure_id`),
  ADD KEY `student_fee_assignments_school_id_student_id_index` (`school_id`,`student_id`);

--
-- Indexes for table `student_guardian`
--
ALTER TABLE `student_guardian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_guardian_school_id_foreign` (`school_id`),
  ADD KEY `student_guardian_guardian_id_foreign` (`guardian_id`),
  ADD KEY `student_guardian_student_id_guardian_id_index` (`student_id`,`guardian_id`);

--
-- Indexes for table `student_qr_scan_events`
--
ALTER TABLE `student_qr_scan_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_qr_scan_session_unique` (`school_id`,`attendance_session_id`,`student_id`,`event_type`),
  ADD KEY `student_qr_scan_events_student_id_foreign` (`student_id`),
  ADD KEY `student_qr_scan_events_attendance_session_id_foreign` (`attendance_session_id`),
  ADD KEY `student_qr_scan_events_scanned_by_foreign` (`scanned_by`),
  ADD KEY `student_qr_scan_review_idx` (`school_id`,`processing_status`,`scanned_at`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subjects_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`),
  ADD KEY `subscriptions_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_payments_subscription_id_foreign` (`subscription_id`),
  ADD KEY `subscription_payments_school_id_status_index` (`school_id`,`status`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_assignments_employee_id_foreign` (`employee_id`),
  ADD KEY `teacher_assignments_class_subject_id_foreign` (`class_subject_id`),
  ADD KEY `teacher_assignments_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `teacher_assignments_school_id_employee_id_index` (`school_id`,`employee_id`);

--
-- Indexes for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetable_entries_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `timetable_entries_section_id_foreign` (`section_id`),
  ADD KEY `timetable_entries_class_subject_id_foreign` (`class_subject_id`),
  ADD KEY `timetable_entries_school_id_section_id_weekday_index` (`school_id`,`section_id`,`weekday`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `whatsapp_credential_deliveries`
--
ALTER TABLE `whatsapp_credential_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_credential_deliveries_provider_message_id_unique` (`provider_message_id`),
  ADD KEY `whatsapp_credential_deliveries_school_id_status_index` (`school_id`,`status`),
  ADD KEY `whatsapp_credential_deliveries_user_id_created_at_index` (`user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_change_logs`
--
ALTER TABLE `attendance_change_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_adjustments`
--
ALTER TABLE `fee_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_audit_logs`
--
ALTER TABLE `fee_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_categories`
--
ALTER TABLE `fee_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_payment_allocations`
--
ALTER TABLE `fee_payment_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grading_scales`
--
ALTER TABLE `grading_scales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `homework`
--
ALTER TABLE `homework`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_otp_challenges`
--
ALTER TABLE `login_otp_challenges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_events`
--
ALTER TABLE `payment_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `report_cards`
--
ALTER TABLE `report_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_invitations`
--
ALTER TABLE `school_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_user`
--
ALTER TABLE `school_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_fee_assignments`
--
ALTER TABLE `student_fee_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_guardian`
--
ALTER TABLE `student_guardian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_qr_scan_events`
--
ALTER TABLE `student_qr_scan_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `whatsapp_credential_deliveries`
--
ALTER TABLE `whatsapp_credential_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD CONSTRAINT `academic_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_change_logs`
--
ALTER TABLE `attendance_change_logs`
  ADD CONSTRAINT `attendance_change_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendance_change_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_change_logs_student_attendance_id_foreign` FOREIGN KEY (`student_attendance_id`) REFERENCES `student_attendance` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_taken_by_foreign` FOREIGN KEY (`taken_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD CONSTRAINT `exam_subjects_class_subject_id_foreign` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_subjects_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_adjustments`
--
ALTER TABLE `fee_adjustments`
  ADD CONSTRAINT `fee_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_adjustments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_adjustments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_adjustments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_audit_logs`
--
ALTER TABLE `fee_audit_logs`
  ADD CONSTRAINT `fee_audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_audit_logs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD CONSTRAINT `fee_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD CONSTRAINT `fee_invoice_items_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_invoice_items_fee_invoice_id_foreign` FOREIGN KEY (`fee_invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoice_items_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_payments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payment_allocations`
--
ALTER TABLE `fee_payment_allocations`
  ADD CONSTRAINT `fee_payment_allocations_fee_invoice_id_foreign` FOREIGN KEY (`fee_invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payment_allocations_fee_payment_id_foreign` FOREIGN KEY (`fee_payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payment_allocations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structures_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structures_fee_category_id_foreign` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grading_scales`
--
ALTER TABLE `grading_scales`
  ADD CONSTRAINT `grading_scales_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guardians_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `homework`
--
ALTER TABLE `homework`
  ADD CONSTRAINT `homework_class_subject_id_foreign` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homework_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `homework_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_otp_challenges`
--
ALTER TABLE `login_otp_challenges`
  ADD CONSTRAINT `login_otp_challenges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_entered_by_foreign` FOREIGN KEY (`entered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `marks_exam_subject_id_foreign` FOREIGN KEY (`exam_subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_cards`
--
ALTER TABLE `report_cards`
  ADD CONSTRAINT `report_cards_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_cards_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `report_cards_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_cards_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `school_invitations`
--
ALTER TABLE `school_invitations`
  ADD CONSTRAINT `school_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `school_invitations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `school_user`
--
ALTER TABLE `school_user`
  ADD CONSTRAINT `school_user_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `school_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sections_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `staff_attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_attendance_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD CONSTRAINT `student_attendance_attendance_session_id_foreign` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendance_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_attendance_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendance_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `student_documents_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD CONSTRAINT `student_enrollments_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_fee_assignments`
--
ALTER TABLE `student_fee_assignments`
  ADD CONSTRAINT `student_fee_assignments_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fee_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fee_assignments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_guardian`
--
ALTER TABLE `student_guardian`
  ADD CONSTRAINT `student_guardian_guardian_id_foreign` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_guardian_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_guardian_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_qr_scan_events`
--
ALTER TABLE `student_qr_scan_events`
  ADD CONSTRAINT `student_qr_scan_events_attendance_session_id_foreign` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_qr_scan_events_scanned_by_foreign` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_qr_scan_events_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_qr_scan_events_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  ADD CONSTRAINT `subscriptions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD CONSTRAINT `subscription_payments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_class_subject_id_foreign` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  ADD CONSTRAINT `timetable_entries_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_entries_class_subject_id_foreign` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_entries_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_entries_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_credential_deliveries`
--
ALTER TABLE `whatsapp_credential_deliveries`
  ADD CONSTRAINT `whatsapp_credential_deliveries_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `whatsapp_credential_deliveries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
