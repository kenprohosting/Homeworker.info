-- Cleaned and updated SQL schema for househelp_db
-- All table and column names are lowercase and consistent with the codebase

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `househelp_db`;
USE `househelp_db`;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(20) NOT NULL,
  `second_name` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee table
CREATE TABLE IF NOT EXISTS `employees` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Age` int(3) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Country` varchar(100) NOT NULL,
  `County_province` varchar(100) NOT NULL,
  `Skills` varchar(100) NOT NULL,
  `Experience` varchar(100) DEFAULT NULL,
  `Education_level` varchar(50) NOT NULL,
  `Social_referee` varchar(100) DEFAULT NULL,
  `Language` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Password_hash` varchar(255) NOT NULL,
  `Residence_type` enum('urban','rural') DEFAULT NULL,
  `Verification_status` enum('unverified','pending','verified') DEFAULT 'unverified',
  `Created_at` datetime DEFAULT current_timestamp(),
  `Reset_token` varchar(255) DEFAULT NULL,
  `Reset_token_expiry` datetime DEFAULT NULL,
  `Agent_id` int(11) DEFAULT NULL,
  `Status` enum('active','pending','inactive') DEFAULT 'active',
  `ID_passport` varchar(255) DEFAULT NULL,
  `Profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `agent_id` (`Agent_id`),
  CONSTRAINT `fk_employee_agent` FOREIGN KEY (`Agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employer table
CREATE TABLE IF NOT EXISTS `employer` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Location` text NOT NULL,
  `Residence_type` enum('urban','rural') NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `Gender` enum('male','female','other') NOT NULL,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Password_hash` varchar(255) NOT NULL,
  `Address` text DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  `Verification_status` enum('unverified','pending','verified') DEFAULT 'unverified',
  `Created_at` datetime DEFAULT current_timestamp(),
  `Reset_token` varchar(255) DEFAULT NULL,
  `Reset_token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agents table
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agent registration codes
CREATE TABLE IF NOT EXISTS `agent_registration_codes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Homeowner_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Service_type` varchar(40) NOT NULL,
  `Booking_date` date NOT NULL,
  `Status` enum('pending','confirmed','completed','cancelled') NOT NULL,
  `Start_time` time DEFAULT NULL,
  `End_time` time DEFAULT NULL,
  `Special_requirements` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `homeowner_id` (`Homeowner_ID`),
  KEY `employee_id` (`Employee_ID`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`Homeowner_ID`) REFERENCES `employer` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payment table
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `amount` int(11) NOT NULL,
  `description` text NOT NULL,
  `payment_method` enum('mpesa','card','bank','cash') NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Review table
CREATE TABLE IF NOT EXISTS `review_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `rating` enum('1','2','3','4','5') NOT NULL,
  `comment` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `reviewer_type` enum('employer','employee') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `review_table_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jobs table
CREATE TABLE IF NOT EXISTS `jobs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
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
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `employer_id` (`Employer_ID`),
  KEY `status` (`Status`),
  KEY `expiry_date` (`Expiry_date`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`Employer_ID`) REFERENCES `employer` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job applications table
CREATE TABLE IF NOT EXISTS `job_applications` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Job_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Cover_letter` text DEFAULT NULL,
  `Status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `Applied_at` datetime DEFAULT current_timestamp(),
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `job_id` (`Job_ID`),
  KEY `employee_id` (`Employee_ID`),
  UNIQUE KEY `unique_application` (`Job_ID`, `Employee_ID`),
  CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`Job_ID`) REFERENCES `jobs` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employees` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
