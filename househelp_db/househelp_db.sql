-- phpMyAdmin SQL 

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `househelp_db`
--

-- --------------------------------------------------------

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

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID`, `First_name`, `Second_name`, `email`, `password_hash`, `last_login`, `created_at`) VALUES
(1, 'Cyprian', 'Were', 'admin@househelp.info', '$2y$10$hashedpassword', NULL, '2025-06-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `ID` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Age` int(2) NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `Location` text NOT NULL,
  `Skills` varchar(20) NOT NULL,
  `Education_level` varchar(20) NOT NULL,
  `Social_referee` varchar(40) NOT NULL,
  `Language` text NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `residence_type` enum('urban','rural') DEFAULT NULL,
  `verification_status` enum('unverified','pending','verified') DEFAULT 'unverified',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`ID`, `Name`, `Gender`, `Age`, `Contact`, `Location`, `Skills`, `Education_level`, `Social_referee`, `Language`, `email`, `password_hash`, `residence_type`, `verification_status`, `created_at`) VALUES
(1, 'Kate', 'Female', 33, '1234567', 'Nairobi', 'Cleaning', 'High School', 'John Doe', 'English, Swahili', 'kate@example.com', '$2y$10$hashedpassword', 'urban', 'verified', '2025-06-17 10:00:00'),
(2, 'Jane', 'Female', 28, '7654321', 'Mombasa', 'Cooking', 'College', 'Mary Smith', 'English, Swahili', 'jane@example.com', '$2y$10$hashedpassword', 'urban', 'verified', '2025-06-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `employer`
--

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
  `verification_status` enum('unverified','pending','verified') DEFAULT 'unverified',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer`
--

INSERT INTO `employer` (`ID`, `Name`, `Location`, `Residence_type`, `Contact`, `Gender`, `email`, `password_hash`, `address`, `verification_status`, `created_at`) VALUES
(1, 'kesh', 'ronga', 'urban', '0722925091', 'male', 'kesh@example.com', '$2y$10$hashedpassword', '127 Ronga Street', 'verified', '2025-06-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

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

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`ID`, `Homeowner_ID`, `Employee_ID`, `Service_type`, `Booking_date`, `Status`, `Start_time`, `End_time`, `Special_requirements`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Cleaning', '2025-06-20', 'confirmed', '08:00:00', '17:00:00', 'Deep clean kitchen', '2025-06-17 10:00:00', '2025-06-17 10:00:00'),
(2, 1, 2, 'Gardening', '2025-06-27', 'pending', '07:46:56', '17:46:56', 'Weekly maintenance', '2025-06-17 10:00:00', '2025-06-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`ID`, `Booking_ID`, `Date`, `Amount`, `Description`, `Payment_method`, `Transaction_ID`, `Status`, `created_at`) VALUES
(1, 1, '2025-06-13', 5000, 'Cleaning service payment', 'mpesa', 'MPE123456789', 'completed', '2025-06-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `review_table`
--

CREATE TABLE `review_table` (
  `ID` int(11) NOT NULL,
  `Booking_ID` int(11) NOT NULL,
  `Rating` enum('1','2','3','4','5') NOT NULL,
  `Comment` varchar(255) NOT NULL,
  `Date` date NOT NULL,
  `Reviewer_type` enum('employer','employee') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_table`
--

INSERT INTO `review_table` (`ID`, `Booking_ID`, `Rating`, `Comment`, `Date`, `Reviewer_type`, `created_at`) VALUES
(1, 1, '4', 'Perfect cleaning service, very thorough', '2025-06-13', 'employer', '2025-06-17 10:00:00'),
(2, 1, '3', 'Good employer, clear instructions', '2025-06-13', 'employee', '2025-06-17 10:00:00'),
(3, 2, '3', 'good', '2025-06-07', 'employer', '2025-06-17 10:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `contact` (`Contact`);

--
-- Indexes for table `employer`
--
ALTER TABLE `employer`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `contact` (`Contact`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Homeowner_ID` (`Homeowner_ID`),
  ADD KEY `Employee_ID` (`Employee_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Booking_ID` (`Booking_ID`);

--
-- Indexes for table `review_table`
--
ALTER TABLE `review_table`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Booking_ID` (`Booking_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employer`
--
ALTER TABLE `employer`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `review_table`
--
ALTER TABLE `review_table`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`Homeowner_ID`) REFERENCES `employer` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Booking_ID`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `review_table`
--
ALTER TABLE `review_table`
  ADD CONSTRAINT `review_table_ibfk_1` FOREIGN KEY (`Booking_ID`) REFERENCES `bookings` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
