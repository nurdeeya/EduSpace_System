-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 24, 2026 at 04:07 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edusphere_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `email_submitted` varchar(254) DEFAULT NULL,
  `was_successful` tinyint(1) NOT NULL DEFAULT '0',
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int UNSIGNED NOT NULL,
  `login_identifier` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','lecturer') NOT NULL DEFAULT 'student',
  `failed_login_attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `mfa_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `login_identifier`, `full_name`, `email`, `password_hash`, `role`, `failed_login_attempts`, `locked_until`, `mfa_enabled`, `mfa_secret`, `is_active`, `created_at`) VALUES
(1, 'BSW98876541', 'Test Student', 'test.student@example.com', '$2y$10$Tyfe4LwT5TAFUGK6sHYOsucoZNK4OD68AmH3fKeBGN4o73yCEn6zC', 'student', 0, NULL, 1, 'M2PRYXKPB727BFBTUDIPN43M7Z2A5EQL', 1, '2026-10-20 13:57:21'),
(2, 'BSW98888811', 'Naim Hakim', 'Hakim@gmail.com', '$2y$10$CIcYJJB.1HzfWiWa0mr4OuHa/aDcezIn.pEJr4n2ho3eSYt5FLho.', 'student', 0, NULL, 0, NULL, 1, '2026-10-20 17:49:03'),
(3, 'AhmadDanial', 'Ahmad Danial', 'Danial@gmail.com', '$2y$10$pJGqResjTxYkmwj6tM/vaOHlcV0AFINPYOkiRhMvl/y6h4vtDIcG6', 'lecturer', 0, NULL, 0, NULL, 1, '2026-10-20 17:49:03'),
(4, 'KamalAbdul', 'Kamal Abdullah', 'Kamal@gmail.com', '$2y$10$7McwUQyhdJ3Dw227U.6QBe/.H.KR2BrUkY2bRSZQKy0umitS6b1Su', 'lecturer', 0, NULL, 1, 'BHBESHYUHL46CTKXKHQRN5KZJVIXUPKO', 1, '2026-10-21 15:20:18'),
(5, 'BSW12345678', 'Aqilah Balqis', 'Aqilah@gmail.com', '$2y$10$VhmJUJ0PbcyK/Ki4utgo4uIadRiq6fVr4lCYplSQ5gRhMBxx41XZm', 'student', 5, '2026-10-21 23:58:50', 1, 'EMHTCGK7XZ43M2ZIOFFWIYSQTQWFKBOI', 1, '2026-10-21 15:41:22'),
(6, 'HashimBakar', 'Hashim Bakar', 'Hashim@gmail.com', '$2y$10$iKU6bbv.MOo7RBY/wVoV8eCVAitiu2XLMlRUh4FbeAm6Mwi89BXJG', 'lecturer', 0, NULL, 1, 'BKO74HGSCF7UWHITPW5HTHAM6L2476XQ', 1, '2026-10-21 15:55:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempted_at`),
  ADD KEY `idx_email_time` (`email_submitted`,`attempted_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login_identifier` (`login_identifier`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
