-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 11, 2026 at 03:43 PM
-- Server version: 8.0.46
-- PHP Version: 8.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `glowwell_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `service_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `customer_id`, `staff_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`) VALUES
(1, 2, 1, 1, '2026-11-15', '10:00:00', 'Confirmed', 'Sensitive skin, prefer fragrance-free toner.', '2026-11-08 05:42:17'),
(2, 3, 2, 2, '2026-11-15', '14:00:00', 'Cancelled', 'Focus on upper back tension.', '2026-11-08 05:42:17'),
(3, 2, 3, 3, '2026-11-16', '11:00:00', 'Completed', 'Standard japanse treatment.', '2026-11-08 05:42:17'),
(4, 2, 3, 5, '2026-11-13', '09:00:00', 'Cancelled', 'My scalp needs relaxing it is a bit dry.', '2026-11-09 10:00:45'),
(8, 3, 2, 2, '2026-11-15', '11:00:00', 'Confirmed', '', '2026-11-09 15:33:27'),
(9, 4, 3, 3, '2026-11-24', '16:00:00', 'Pending', 'Could also dye my hair', '2026-11-09 15:49:28'),
(10, 5, 1, 1, '2026-11-29', '16:00:00', 'Confirmed', 'Allergic to peanut better.', '2026-11-10 10:14:37');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int NOT NULL,
  `service_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('Facials','Massages','Hair Treatments','Wellness','Speciality') COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_minutes` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `category`, `duration_minutes`, `price`) VALUES
(1, 'HydraGlow Deep Facial', 'Facials', 60, 630.00),
(2, 'Swedish Full Body Massage', 'Massages', 60, 850.00),
(3, 'Japanese Silk Hair Treatment', 'Hair Treatments', 90, 1200.00),
(4, 'Clay Wrap', 'Wellness', 75, 1290.00),
(5, 'Hot Stone & Scalp Speciality', 'Speciality', 90, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialty` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shift_start` time NOT NULL DEFAULT '09:00:00',
  `shift_end` time NOT NULL DEFAULT '17:00:00',
  `status` enum('Active','On Leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `full_name`, `email`, `specialty`, `shift_start`, `shift_end`, `status`) VALUES
(1, 'Kamogelo Samwell', 'kamogelo@glowwell.co.za', 'Facials & Skincare', '09:00:00', '16:30:00', 'Active'),
(2, 'David van Dyk', 'david@glowwell.co.za', 'Massages & Wellness', '09:00:00', '17:30:00', 'Active'),
(3, 'Shai Verstappenalla', 'Verstappenalla@glowwell.co.za', 'Hair Treatments & Speciality', '09:30:00', '17:00:00', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@glowwell.co.za', '0123456789', '$2y$10$0ZF8J7i3.qkz950NiEUaJ.2HOjup9KCRkMEvB5UX.3QZW/bCdctDy', 'admin', '2026-11-08 05:42:17'),
(2, 'Neo Mokohna', 'Neo@example.com', '0825551020', '$2y$10$z2wL0UAxkGtNabi/KO9kUeo21brC.NprKQRElSBp2hEm7RWv/4cIa', 'customer', '2026-11-08 05:42:17'),
(3, 'Charalene Mashiba', 'charalene@example.com', '0734449876', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2026-11-08 05:42:17'),
(4, 'Rob Camorrson', 'camorroson@gmail.com', '0685507771', '$2y$10$dph/iCIPaDWhQTDcXSrc7eUyugORkHYTCDedwjUJaY6KIbmgZWg1a', 'customer', '2026-11-09 15:47:11'),
(5, 'Neo Mokohna', 'Grim@gmail.com', '0772656661', '$2y$10$KuFuOH5jqxCCchiRb4vm8ehJ5aYle4LRfCpzjk1.ZlOJzmwJCTwua', 'customer', '2026-11-10 10:12:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `uq_staff_slot` (`staff_id`,`appointment_date`,`appointment_time`),
  ADD KEY `fk_appt_customer` (`customer_id`),
  ADD KEY `fk_appt_service` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
