-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `househelp_db`
CREATE DATABASE IF NOT EXISTS `househelp_db`;
USE `househelp_db`;

-- Table structure for table `admin`
CREATE TABLE `admin` (
  `ID` int(11) NOT NULL,
  `First_name` varchar(20) NOT NULL,
  `Second_name` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `admin`
INSERT INTO `admin` (`ID`, `First_name`, `Second_name`, `email`, `password_hash`, `last_login`, `created_at`) VALUES
(1, 'Cyprian', 'Were', 'admin@househelp.info', '$2y$10$hashedpassword', NULL, '2025-06-17 10:00:00');

-- Table structure for table `employee`
CREATE TABLE `employee` (
  `ID` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Age` int(2) NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `county_province` varchar(100) NOT NULL,
  `Skills` varchar(20) NOT NULL,
  `Education_level` varchar(20) NOT NULL,
  `Social_referee` varchar(40) NOT NULL,
  `Language` text NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `residence_type` enum('urban','rural') DEFAULT NULL,
  `verification_status` enum('unverified','pending','verified') DEFAULT 'unverified',
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add missing column after table creation
ALTER TABLE `employee` ADD `profile_pic` VARCHAR(255) NULL;

-- Add agent_id column to employee table
ALTER TABLE `employee` ADD `agent_id` int(11) NULL AFTER `ID`;

-- Add status column to employee table
ALTER TABLE `employee` ADD `status` enum('active','pending','inactive') DEFAULT 'active' AFTER `agent_id`;

-- Add id_passport column to employee table
ALTER TABLE `employee` ADD `id_passport` VARCHAR(255) DEFAULT NULL AFTER `status`;

-- Dumping data for table `employee`
INSERT INTO `employee` (`ID`, `Name`, `Gender`, `Age`, `Contact`, `country`, `county_province`, `Skills`, `Education_level`, `Social_referee`, `Language`, `email`, `password_hash`, `residence_type`, `verification_status`, `created_at`) VALUES
(1, 'Kate', 'Female', 33, '1234567', 'Nairobi', 'Nairobi', 'Cleaning', 'High School', 'John Doe', 'English, Swahili', 'kate@example.com', '$2y$10$hashedpassword', 'urban', 'verified', '2025-06-17 10:00:00'),
(2, 'Jane', 'Female', 28, '7654321', 'Mombasa', 'Mombasa', 'Cooking', 'College', 'Mary Smith', 'English, Swahili', 'jane@example.com', '$2y$10$hashedpassword', 'urban', 'verified', '2025-06-17 10:00:00');

-- Table structure for table `employer`
CREATE TABLE `employer` (
  `ID` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Location` text NOT NULL,
  `Residence_type` enum('urban','rural') NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `Gender` enum('male','female','other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `verification_status` enum('unverified','pending','verified'),
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employer`
INSERT INTO `employer` (`ID`, `Name`, `Location`, `Residence_type`, `Contact`, `Gender`, `email`, `password_hash`, `address`, `verification_status`, `created_at`) VALUES
(1, 'kesh', 'ronga', 'urban', '0722925091', 'male', 'kesh@example.com', '$2y$10$hashedpassword', '127 Ronga Street', 'verified', '2025-06-17 10:00:00');

-- Table structure for table `bookings`
CREATE TABLE `bookings` (
  `ID` int(11) NOT NULL,
  `Homeowner_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Service_type` varchar(40) NOT NULL,
  `Booking_date` date NOT NULL,
  `Status` enum('pending','confirmed','completed','cancelled') NOT NULL,
  `Start_time` time NOT NULL,
  `End_time` time NOT NULL,
  `Special_requirements` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `bookings`
INSERT INTO `bookings` (`ID`, `Homeowner_ID`, `Employee_ID`, `Service_type`, `Booking_date`, `Status`, `Start_time`, `End_time`, `Special_requirements`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Cleaning', '2025-06-20', 'confirmed', '08:00:00', '17:00:00', 'Deep clean kitchen', '2025-06-17 10:00:00', '2025-06-17 10:00:00'),
(2, 1, 2, 'Gardening', '2025-06-27', 'pending', '07:46:56', '17:46:56', 'Weekly maintenance', '2025-06-17 10:00:00', '2025-06-17 10:00:00');

-- Table structure for table `payment`
CREATE TABLE `payment` (
  `ID` int(11) NOT NULL,
  `Booking_ID` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Amount` int(11) NOT NULL,
  `Description` text NOT NULL,
  `Payment_method` enum('mpesa','card','bank','cash') NOT NULL,
  `Transaction_ID` varchar(50) DEFAULT NULL,
  `Status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `payment`
INSERT INTO `payment` (`ID`, `Booking_ID`, `Date`, `Amount`, `Description`, `Payment_method`, `Transaction_ID`, `Status`, `created_at`) VALUES
(1, 1, '2025-06-13', 5000, 'Cleaning service payment', 'mpesa', 'MPE123456789', 'completed', '2025-06-17 10:00:00');

-- Table structure for table `review_table`
CREATE TABLE `review_table` (
  `ID` int(11) NOT NULL,
  `Booking_ID` int(11) NOT NULL,
  `Rating` enum('1','2','3','4','5') NOT NULL,
  `Comment` varchar(255) NOT NULL,
  `Date` date NOT NULL,
  `Reviewer_type` enum('employer','employee') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `review_table`
INSERT INTO `review_table` (`ID`, `Booking_ID`, `Rating`, `Comment`, `Date`, `Reviewer_type`, `created_at`) VALUES
(1, 1, '4', 'Perfect cleaning service, very thorough', '2025-06-13', 'employer', '2025-06-17 10:00:00'),
(2, 1, '3', 'Good employer, clear instructions', '2025-06-13', 'employee', '2025-06-17 10:00:00'),
(3, 2, '3', 'good', '2025-06-07', 'employer', '2025-06-17 10:00:00');

-- Table structure for table `jobs`
CREATE TABLE `jobs` (
  `ID` int(11) NOT NULL,
  `Employer_ID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `Required_skills` varchar(200) NOT NULL,
  `Location` varchar(100) NOT NULL,
  `Salary_min` decimal(10,2) DEFAULT NULL,
  `Salary_max` decimal(10,2) DEFAULT NULL,
  `Job_type` enum('one-time','part-time','full-time') NOT NULL,
  `Start_date` date NOT NULL,
  `Duration_hours` int(11) DEFAULT NULL,
  `Special_requirements` text DEFAULT NULL,
  `Status` enum('active','filled','expired','cancelled') NOT NULL DEFAULT 'active',
  `Expiry_date` date NOT NULL,
  `Created_at` datetime DEFAULT current_timestamp(),
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `job_applications`
CREATE TABLE `job_applications` (
  `ID` int(11) NOT NULL,
  `Job_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Cover_letter` text DEFAULT NULL,
  `Status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `Applied_at` datetime DEFAULT current_timestamp(),
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `agents`
CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `agent_registration_codes`
CREATE TABLE `agent_registration_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `agent_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','used','revoked') DEFAULT 'active',
  `assigned_to` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample agent registration codes
INSERT INTO `agent_registration_codes` (`code`, `agent_id`, `description`, `status`, `assigned_to`) VALUES
('AGENT2024', 1001, 'Primary agent code for John Doe', 'active', 'John Doe'),
('HOUSEHELP2024', 1002, 'Secondary agent code for Jane Smith', 'active', 'Jane Smith'),
('CONNECT2024', 1003, 'Agent code for regional manager', 'active', 'Mike Johnson'),
('SECURE2024', 1004, 'Agent code for security team', 'active', 'Sarah Wilson'),
('TRUST2024', 1005, 'Agent code for trusted partner', 'active', 'David Brown');

-- Indexes
ALTER TABLE `admin` ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `employee` ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `email` (`email`), ADD KEY `contact` (`Contact`);
ALTER TABLE `employer` ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `email` (`email`), ADD KEY `contact` (`Contact`);
ALTER TABLE `bookings` ADD PRIMARY KEY (`ID`), ADD KEY `Homeowner_ID` (`Homeowner_ID`), ADD KEY `Employee_ID` (`Employee_ID`);
ALTER TABLE `payment` ADD PRIMARY KEY (`ID`), ADD KEY `Booking_ID` (`Booking_ID`);
ALTER TABLE `review_table` ADD PRIMARY KEY (`ID`), ADD KEY `Booking_ID` (`Booking_ID`);
ALTER TABLE `jobs` ADD PRIMARY KEY (`ID`), ADD KEY `Employer_ID` (`Employer_ID`), ADD KEY `Status` (`Status`), ADD KEY `Expiry_date` (`Expiry_date`);
ALTER TABLE `job_applications` ADD PRIMARY KEY (`ID`), ADD KEY `Job_ID` (`Job_ID`), ADD KEY `Employee_ID` (`Employee_ID`), ADD UNIQUE KEY `unique_application` (`Job_ID`, `Employee_ID`);
ALTER TABLE `agents` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);

-- AUTO_INCREMENT
ALTER TABLE `admin` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `employee` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `employer` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `bookings` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `payment` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `review_table` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `jobs` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `job_applications` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- Constraints
ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`Homeowner_ID`) REFERENCES `employer` (`ID`) ON DELETE CASCADE,
                          ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE;
ALTER TABLE `payment` ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Booking_ID`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE;
ALTER TABLE `review_table` ADD CONSTRAINT `review_table_ibfk_1` FOREIGN KEY (`Booking_ID`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE;
ALTER TABLE `jobs` ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`Employer_ID`) REFERENCES `employer` (`ID`) ON DELETE CASCADE;
ALTER TABLE `job_applications` ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`Job_ID`) REFERENCES `jobs` (`ID`) ON DELETE CASCADE,
                                ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE;
ALTER TABLE `employee` ADD CONSTRAINT `fk_employee_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;
