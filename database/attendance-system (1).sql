-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2025 at 11:49 PM
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
-- Database: `attendance-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_types`
--

CREATE TABLE `activity_types` (
  `activity_types_id` int(11) NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_types`
--

INSERT INTO `activity_types` (`activity_types_id`, `activity_name`, `created_at`, `created_by`) VALUES
(1, 'Business Trip', '2025-11-02 07:07:44', '2021');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `address_type` enum('Permanent','Present','Work','Other') DEFAULT 'Permanent',
  `house_number` varchar(50) DEFAULT NULL,
  `building_name` varchar(100) DEFAULT NULL,
  `street_name` varchar(150) DEFAULT NULL,
  `subdivision_village` varchar(100) DEFAULT NULL,
  `purok` varchar(100) DEFAULT NULL,
  `sitio` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `municipality_city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `months_of_residency` int(11) DEFAULT NULL,
  `is_owner` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `resident_id`, `address_type`, `house_number`, `building_name`, `street_name`, `subdivision_village`, `purok`, `sitio`, `barangay`, `district`, `municipality_city`, `province`, `region`, `postal_code`, `latitude`, `longitude`, `months_of_residency`, `is_owner`, `created_at`, `updated_at`) VALUES
(1, 2, 'Permanent', 'D5', 'Sakomoto', 'Ave Street', 'Crown Villa', '2-a', NULL, 'ampayon', NULL, 'Butuan City', 'Agusan Del Norte', '13', '8600', NULL, NULL, 6, 0, '2025-11-10 12:43:36', '2025-11-10 12:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `window` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `employee_id`, `timestamp`, `created_at`, `updated_at`, `window`) VALUES
(6, '2021', '2025-10-11 09:04:35', '2025-10-11 09:04:35', '2025-10-11 09:04:35', 'afternoon_out'),
(8, '2021', '2025-10-11 23:51:44', '2025-10-11 23:51:44', '2025-10-11 23:51:44', 'morning_in'),
(10, '2021', '2025-10-12 06:21:33', '2025-10-12 06:21:33', '2025-10-12 06:21:33', 'afternoon_in'),
(11, '2021', '2025-10-14 01:37:58', '2025-10-14 01:37:58', '2025-10-14 01:37:58', 'morning_in'),
(12, '20201188', '2025-10-14 01:51:29', '2025-10-14 01:51:29', '2025-10-14 01:51:29', 'morning_in'),
(13, '2021', '2025-11-01 00:05:16', '2025-11-01 00:05:16', '2025-11-01 00:05:16', 'morning_in'),
(14, '2021', '2025-11-02 00:14:43', '2025-11-02 00:14:43', '2025-11-02 00:14:43', 'morning_in'),
(19, '2021', '2025-12-13 22:37:45', '2025-12-13 22:37:45', '2025-12-13 22:37:45', 'morning_in');

-- --------------------------------------------------------

--
-- Table structure for table `civil_status`
--

CREATE TABLE `civil_status` (
  `civil_status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `civil_status`
--

INSERT INTO `civil_status` (`civil_status_id`, `status_name`) VALUES
(1, 'Single');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`) VALUES
(1, 'Finance', '2025-11-02 22:32:40'),
(2, 'Peace and Order', '2025-11-02 22:32:53');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(50) NOT NULL,
  `resident_id` int(50) NOT NULL,
  `position_id` int(11) NOT NULL,
  `hired_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `resident_id`, `position_id`, `hired_date`, `created_at`, `updated_at`) VALUES
('20201188', 3, 1, '2025-10-14', '2025-10-14 09:51:04', '2025-11-05 16:40:20'),
('20201197', 4, 2, '2025-11-06', '2025-11-06 09:04:02', '2025-11-06 09:04:02'),
('2021', 2, 2, '2025-10-11', '2025-10-11 14:35:45', '2025-11-05 16:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `employee_activity`
--

CREATE TABLE `employee_activity` (
  `employee_activity_id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `activity_types_id` int(11) NOT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_activity`
--

INSERT INTO `employee_activity` (`employee_activity_id`, `employee_id`, `activity_types_id`, `start`, `end`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '20201188', 1, '2025-11-02 20:53:41', '2025-11-04 20:53:52', '2021', '2025-11-02 07:14:20', '2025-11-02 12:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `family_relationships`
--

CREATE TABLE `family_relationships` (
  `relationship_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `relative_id` int(11) NOT NULL,
  `relationship_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_relationships`
--

INSERT INTO `family_relationships` (`relationship_id`, `resident_id`, `relative_id`, `relationship_type`) VALUES
(1, 2, 3, 'brother');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprints`
--

CREATE TABLE `fingerprints` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(256) NOT NULL,
  `template` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fingerprints`
--

INSERT INTO `fingerprints` (`id`, `employee_id`, `template`, `created_at`, `updated_at`) VALUES
(2, '2021', 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48RmlkPjxCeXRlcz5SazFTQUNBeU1BQUJ1QUF6L3Y4QUFBRmxBWWdBeFFERkFRQUFBRlpFZ0tVQktuNWtnSVlBbm9OaFFPY0FrWkZkUUtRQlRYZGRRSkFBS0Z4Y2dOY0FYVXBiUUdrQWpueGJnTWNBTnFOYWdGY0F2aWRYUUVvQXFJTldnTHdBZlpkV2dOQUErSVZWZ0k0QkRpaFZnSzBBcllWVlFKTUF3WVZWZ0h3QTE0RlZRTXdBdW9sVVFMb0F4akZVUUZVQVl4RlRRSjBBWEtSU2dMb0F2VFZTUVBnQXFvMVJRTW9CSENsUmdOUUFiVWhSUUtBQXBvdFFnRVFBdUl0T1FQRUJENGxPUUcwQU1nUk5nRllBaWhkTWdGTUE3VEZNZ0VvQXhpdE1RSWtCSVhSTVFMWUE4NE5MUUowQTU0TkxRSEVBNUh4S1FOb0FaNWxKZ1BjQkVJbEpnUUlCSHpWSmdMTUJVU0JKUUxFQW40MUlRSDRCVGhkSGdMOEFzak5IUUZ3QTVTaEhRSDhCRDNGR1FOUUJOSDFHUUNvQWNoNUZRTDhBcEpCRlFORUJReUZGZ1EwQWVwUkZRQ3dBdlMxRlFIUUE4aWhFUUtVQk5pZERRRDhCTzBsRFFPd0FUNTlDZ1A0QWNFTkNRRlVBeFN0QmdGc0JOeEJBUUc0QlRSSkFnSzBBVWFRL2dRRUJGbzAvUUh3QWFhcy9RSU1Ba1l3L1FIWUFkZzArZ0VJQkZwMCtRRlVCQ1RvK2dDZ0JKajA5Z0h3QWVLazlnUE1CTFRFOUFBQT08L0J5dGVzPjxGb3JtYXQ+MTc2OTQ3MzwvRm9ybWF0PjxWZXJzaW9uPjEuMC4wPC9WZXJzaW9uPjwvRmlkPg==', '2025-10-11 06:25:55', '2025-10-11 14:25:55'),
(3, '20201188', 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48RmlkPjxCeXRlcz5SazFTQUNBeU1BQUJ1QUF6L3Y4QUFBRmxBWWdBeFFERkFRQUFBRlpFZ01RQXpGaGVnSEVBM1d0YVFOZ0FubFpaUU1zQWFsSlpnRzBBcG1kWVFNb0JEbHBVUUZjQTZnOVNnSmtCRlhCUmdSVUE2a1pRZ09jQStrOVFRRzBBckdsT1FPVUFUa3ROZ0VzQW1RdE1RTW9BcUtsTFFQTUF1a3hMUUdnQkJYUktnSVlBM3dWSlFMOEJPNkJKUUw0QlZrWkpRRG9BU1JGSVFHNEFWbUpJUUtvQkRBSklRTUFBMGxoR2dMd0F6RnhGZ0NvQWxTRStnTVFCUXA0OVFEQUFsaUU4Z0JzQWJ6ZzdRS3NCRXdRN1FMRUJFN0E2UUI4QXFDczVnRFVBZWhJNFFHc0F6Zzg0UUM0QTZCNDNnQ1VCSUI0M1FLQUFMYTQyZ0JVQVpJMDJRQ0VBMFJrMkFFUUJUWHMxQUJzQWtZMDBBQ01BYWkwMEFTa0FkRTgwQUlZQXhRUXpBUVVBeDB3ekFLVUFOVlF5QUZFQVB3UXlBSGdBNFhBeUFDa0EvSGN5QU1FQkZWSXlBRFVBZ3c4eEFEUUFqeDR4QUJVQWxva3hBQ2tBNVh3eEFLc0JGbTB4QUtrQlU0c3hBRUVBT1JReEFGWUFRUVF3QUM0QWRoc3dBRG9BbHhJd0FTVUF6YVF2QUNnQTZoc3ZBQ01BeFJrdUFDa0FnVE11QUtNQUlWUXRBS29BSjFndEFPQUJDS0l0QUswQlFxQXRBTEVBSVZJdEFBQT08L0J5dGVzPjxGb3JtYXQ+MTc2OTQ3MzwvRm9ybWF0PjxWZXJzaW9uPjEuMC4wPC9WZXJzaW9uPjwvRmlkPg==', '2025-10-14 01:39:57', '2025-10-14 09:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `occupations`
--

CREATE TABLE `occupations` (
  `occupation_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `employer` varchar(200) DEFAULT NULL,
  `income_bracket` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `occupations`
--

INSERT INTO `occupations` (`occupation_id`, `resident_id`, `job_title`, `employer`, `income_bracket`) VALUES
(1, 2, 'Software Developer', 'Vikings Universe', '100000-200000');

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(50) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`position_id`, `position_name`, `created_at`) VALUES
(1, 'Kagawad', '2025-11-05'),
(2, 'Chairman', '2025-11-05');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `phil_sys_number` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `birthdate` date NOT NULL,
  `place_of_birth_city` varchar(100) DEFAULT NULL,
  `place_of_birth_province` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `civil_status_id` int(11) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`resident_id`, `phil_sys_number`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birthdate`, `place_of_birth_city`, `place_of_birth_province`, `blood_type`, `civil_status_id`, `photo_path`, `created_at`, `updated_at`) VALUES
(2, '02321354542', 'Ric Charles', 'Lucar', 'Paquibot', 'lg', 'Male', '2001-07-23', 'Butuan City', 'Agusan Del Norte', 'AB', 1, 'path/to/photo.jpg', '2025-10-11 06:32:15', '2025-10-11 06:32:15'),
(3, '123', 'Keneth', 'B', 'Arsolon', NULL, 'Male', '2000-10-14', 'Butuan City', 'Agusan Del Norte', 'o', 1, NULL, '2025-10-14 01:50:13', '2025-10-14 01:50:13'),
(4, '12311', 'Trixxie Nicole', 'Guedelosao', 'Petalcorin', NULL, 'Female', '2002-10-16', 'Butuan City', 'Agusan del Norte', 'o', 1, '/to/photo', '2025-11-03 12:40:16', '2025-11-03 12:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `resident_biometrics`
--

CREATE TABLE `resident_biometrics` (
  `biometric_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `biometric_type` enum('Signature','Thumbmark','Fingerprint','Photo') NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_biometrics`
--

INSERT INTO `resident_biometrics` (`biometric_id`, `resident_id`, `biometric_type`, `file_path`) VALUES
(1, 2, 'Fingerprint', '');

-- --------------------------------------------------------

--
-- Table structure for table `resident_contacts`
--

CREATE TABLE `resident_contacts` (
  `contact_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `contact_type` enum('Mobile','Telephone','Email') NOT NULL,
  `contact_value` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resident_ids`
--

CREATE TABLE `resident_ids` (
  `id_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `id_type` varchar(100) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_ids`
--

INSERT INTO `resident_ids` (`id_id`, `resident_id`, `id_type`, `id_number`, `issue_date`, `expiry_date`) VALUES
(1, 2, 'National ID', '0213541021320001545', '2024-11-13', '2025-11-28');

-- --------------------------------------------------------

--
-- Table structure for table `resident_status`
--

CREATE TABLE `resident_status` (
  `status_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `status_type` enum('Senior Citizen','PWD','Solo Parent','Indigent','Other') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_status`
--

INSERT INTO `resident_status` (`status_id`, `resident_id`, `status_type`, `is_active`) VALUES
(1, 2, 'Indigent', 1);

-- --------------------------------------------------------

--
-- Table structure for table `verification_log`
--

CREATE TABLE `verification_log` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_tokens`
--

CREATE TABLE `verification_tokens` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD PRIMARY KEY (`activity_types_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `civil_status`
--
ALTER TABLE `civil_status`
  ADD PRIMARY KEY (`civil_status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `employee_activity`
--
ALTER TABLE `employee_activity`
  ADD PRIMARY KEY (`employee_activity_id`),
  ADD KEY `employee_id` (`employee_id`,`activity_types_id`,`created_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `employee_current_activity` (`activity_types_id`);

--
-- Indexes for table `family_relationships`
--
ALTER TABLE `family_relationships`
  ADD PRIMARY KEY (`relationship_id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `relative_id` (`relative_id`);

--
-- Indexes for table `fingerprints`
--
ALTER TABLE `fingerprints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `occupations`
--
ALTER TABLE `occupations`
  ADD PRIMARY KEY (`occupation_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`),
  ADD UNIQUE KEY `phil_sys_number` (`phil_sys_number`),
  ADD KEY `civil_status_id` (`civil_status_id`);

--
-- Indexes for table `resident_biometrics`
--
ALTER TABLE `resident_biometrics`
  ADD PRIMARY KEY (`biometric_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `resident_contacts`
--
ALTER TABLE `resident_contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `resident_ids`
--
ALTER TABLE `resident_ids`
  ADD PRIMARY KEY (`id_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `resident_status`
--
ALTER TABLE `resident_status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `verification_log`
--
ALTER TABLE `verification_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `token` (`token`),
  ADD KEY `created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_types`
--
ALTER TABLE `activity_types`
  MODIFY `activity_types_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `civil_status`
--
ALTER TABLE `civil_status`
  MODIFY `civil_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_activity`
--
ALTER TABLE `employee_activity`
  MODIFY `employee_activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_relationships`
--
ALTER TABLE `family_relationships`
  MODIFY `relationship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fingerprints`
--
ALTER TABLE `fingerprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `occupations`
--
ALTER TABLE `occupations`
  MODIFY `occupation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `resident_biometrics`
--
ALTER TABLE `resident_biometrics`
  MODIFY `biometric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resident_contacts`
--
ALTER TABLE `resident_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resident_ids`
--
ALTER TABLE `resident_ids`
  MODIFY `id_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resident_status`
--
ALTER TABLE `resident_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `verification_log`
--
ALTER TABLE `verification_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_tokens`
--
ALTER TABLE `verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD CONSTRAINT `activity_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `position` (`position_id`);

--
-- Constraints for table `employee_activity`
--
ALTER TABLE `employee_activity`
  ADD CONSTRAINT `employee_activity_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_activity_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_activity_ibfk_3` FOREIGN KEY (`activity_types_id`) REFERENCES `activity_types` (`activity_types_id`);

--
-- Constraints for table `family_relationships`
--
ALTER TABLE `family_relationships`
  ADD CONSTRAINT `family_relationships_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`),
  ADD CONSTRAINT `family_relationships_ibfk_2` FOREIGN KEY (`relative_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `occupations`
--
ALTER TABLE `occupations`
  ADD CONSTRAINT `occupations_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`civil_status_id`) REFERENCES `civil_status` (`civil_status_id`);

--
-- Constraints for table `resident_biometrics`
--
ALTER TABLE `resident_biometrics`
  ADD CONSTRAINT `resident_biometrics_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `resident_contacts`
--
ALTER TABLE `resident_contacts`
  ADD CONSTRAINT `resident_contacts_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `resident_ids`
--
ALTER TABLE `resident_ids`
  ADD CONSTRAINT `resident_ids_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Constraints for table `resident_status`
--
ALTER TABLE `resident_status`
  ADD CONSTRAINT `resident_status_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
