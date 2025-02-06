-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2025 at 06:17 PM
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
-- Database: `db_kada`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `employeeID` int(4) NOT NULL,
  `staffName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin_loanapproval`
--

CREATE TABLE `tb_admin_loanapproval` (
  `employeeID` int(11) NOT NULL,
  `loanApproveDate` date NOT NULL,
  `loanStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin_member`
--

CREATE TABLE `tb_admin_member` (
  `employeeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin_memberapproval`
--

CREATE TABLE `tb_admin_memberapproval` (
  `employeeID` int(11) NOT NULL,
  `staffName` int(11) NOT NULL,
  `regisApproveDate` int(11) NOT NULL,
  `regStatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_bank`
--

CREATE TABLE `tb_bank` (
  `bankID` int(11) NOT NULL,
  `loanApplicationID` int(11) NOT NULL,
  `employeeID` int(4) NOT NULL,
  `bankName` varchar(100) NOT NULL,
  `accountNo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_bank`
--

INSERT INTO `tb_bank` (`bankID`, `loanApplicationID`, `employeeID`, `bankName`, `accountNo`) VALUES
(28, 36, 322, 'Affin Bank', '1234'),
(29, 37, 322, 'Affin Bank', '1234'),
(30, 38, 5522, 'UOB Bank', '34'),
(31, 39, 5522, 'Bank Rakyat', '54'),
(33, 41, 5522, 'Public Bank', '22'),
(34, 42, 4567, 'Public Bank', '1234567891'),
(36, 49, 5522, 'Maybank', '77');

-- --------------------------------------------------------

--
-- Table structure for table `tb_employee`
--

CREATE TABLE `tb_employee` (
  `employeeID` int(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) DEFAULT 'user',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_employee`
--

INSERT INTO `tb_employee` (`employeeID`, `password`, `role`, `reset_token`, `reset_token_expiry`, `email`) VALUES
(322, 'Cheryl@0322', 'user', NULL, NULL, ''),
(1214, '$2y$10$yuQEnUt.H5bgoAJ9FT5.aeZn8YZA9QLLBLsotJqNEclAi6Se3iOeu', 'user', 'ee98e61d73e8a9cbcc6faf7991a5db82a63f3abb7d0ec6b1d80a8ebdb2e8a23b', '2025-02-03 19:15:30', 'yeewen1214@gmail.com'),
(1234, 'kada2024', 'admin', NULL, NULL, ''),
(4567, 'Cheryl@0322', 'user', NULL, NULL, ''),
(5522, 'chau', 'user', NULL, NULL, ''),
(9876, 'Cheryl@0322', 'user', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `tb_employee_member`
--

CREATE TABLE `tb_employee_member` (
  `employeeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_familymemberinfo`
--

CREATE TABLE `tb_familymemberinfo` (
  `icFamilyMember` int(15) NOT NULL,
  `employeeID` int(4) DEFAULT NULL,
  `relationship` varchar(20) DEFAULT NULL,
  `familyMemberName` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_financialstatus`
--

CREATE TABLE `tb_financialstatus` (
  `employeeID` int(4) NOT NULL,
  `modalShare` int(11) NOT NULL,
  `feeCapital` int(11) NOT NULL,
  `contribution` int(11) NOT NULL,
  `fixedDeposit` int(11) NOT NULL,
  `dateUpdated` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_financialstatus`
--

INSERT INTO `tb_financialstatus` (`employeeID`, `modalShare`, `feeCapital`, `contribution`, `fixedDeposit`, `dateUpdated`) VALUES
(322, 0, 0, 0, 0, 2147483647),
(1234, 0, 0, 0, 0, 2147483647),
(5522, 0, 0, 0, 0, 2147483647),
(9876, 900, 60, 60, 60, 1737350531);

-- --------------------------------------------------------

--
-- Table structure for table `tb_guarantor`
--

CREATE TABLE `tb_guarantor` (
  `guarantorID` int(11) NOT NULL,
  `loanApplicationID` int(11) DEFAULT NULL,
  `employeeID` int(11) DEFAULT NULL,
  `guarantorName` varchar(255) NOT NULL,
  `guarantorIC` varchar(20) NOT NULL,
  `guarantorPhone` varchar(20) NOT NULL,
  `guarantorPFNo` varchar(20) NOT NULL,
  `guarantorMemberNo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_guarantor`
--

INSERT INTO `tb_guarantor` (`guarantorID`, `loanApplicationID`, `employeeID`, `guarantorName`, `guarantorIC`, `guarantorPhone`, `guarantorPFNo`, `guarantorMemberNo`) VALUES
(5, 36, 322, 'CHERYL', '111111111111', '1111111111', '111', '1111'),
(6, 36, 322, 'LALA', '222222222222', '222222222222', '222', '2222'),
(7, 37, 322, 'CHERYL', '111111111111', '1111111111', '111', '1111'),
(8, 37, 322, 'LALA', '222222222222', '222222222222', '222', '2222'),
(9, 38, 5522, 'gt', '34', '34', '34', '34'),
(10, 38, 5522, 'gt', '34', '66', '34', '34'),
(11, 39, 5522, 'mama', '66', '66', '66', '666'),
(12, 39, 5522, 'baba', '45', '54', '54', '54'),
(13, 41, 5522, 'ff', '22', '222', '22', '22'),
(14, 41, 5522, 'gg', '22', '22', '22', '22'),
(15, 42, 4567, 'Ha Ha Ha', '123456789', '22222222', '1235', '1235'),
(16, 42, 4567, 'dfshjakfgdshajkfgdshaj', '21234656', '45678945', '1214', '1214'),
(17, 43, 9876, 'Ha Ha Ha', '12345678910', '22222222', '1234', '1235'),
(18, 43, 9876, 'Liy,LIyLi', '21234656', '45678945', '1214', '1214'),
(27, 49, 5522, 'chauchauchau', '77', '77', '77', '77'),
(28, 49, 5522, 'jiajiajia', '77', '77', '77', '77');

-- --------------------------------------------------------

--
-- Table structure for table `tb_loan`
--

CREATE TABLE `tb_loan` (
  `loanType` varchar(30) NOT NULL,
  `loanID` int(11) NOT NULL,
  `loanApplicationID` int(11) NOT NULL,
  `employeeID` int(4) NOT NULL,
  `amountRequested` decimal(10,2) NOT NULL COMMENT 'amount that want to pinjam',
  `financingPeriod` int(11) NOT NULL,
  `monthlyInstallments` decimal(10,2) NOT NULL,
  `employerName` varchar(100) NOT NULL,
  `employerIC` varchar(14) NOT NULL,
  `basicSalary` decimal(10,2) NOT NULL,
  `netSalary` decimal(10,2) NOT NULL,
  `netSalaryFile` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_loan`
--

INSERT INTO `tb_loan` (`loanType`, `loanID`, `loanApplicationID`, `employeeID`, `amountRequested`, `financingPeriod`, `monthlyInstallments`, `employerName`, `employerIC`, `basicSalary`, `netSalary`, `netSalaryFile`, `balance`, `created_at`, `updated_at`) VALUES
('AL-BAI', 20, 36, 322, 3000.00, 12, 250.00, 'NAMA MAJIKAN', '222222222222', 2000.00, 1500.00, 'uploads/6786a2674aa3b_OPERA \'25 UNIT HADIAH (2).pdf', NULL, '2025-01-18 16:46:10', '2025-01-18 16:47:01'),
('AL-BAI', 21, 37, 322, 3000.00, 12, 250.00, 'NAMA MAJIKAN2', '222222222222', 2000.00, 1500.00, 'uploads/6786a881ba7b5_OPERA \'25 UNIT HADIAH (2).pdf', 2250.00, '2025-01-18 16:46:10', '2025-01-19 01:47:15'),
('', 22, 38, 5522, 4455.00, 4, 1113.75, 'gt', '34', 34.00, 34.00, 'uploads/67872c3b58058_Quiz3.pdf', NULL, '2025-01-18 16:46:10', '2025-01-18 16:47:01'),
('', 23, 39, 5522, 5555.00, 4, 1388.75, 'miao', '99', 2000.00, 2000.00, 'uploads/678739c3e18c2_Quiz3.pdf', NULL, '2025-01-18 16:46:10', '2025-01-18 16:47:01'),
('AL-BAI', 24, 41, 5522, 22222.00, 222, 100.10, 'rr', '22', 2222.00, 2222.00, 'uploads/67877e31ccd61_Quiz3.pdf', 22222.00, '2025-01-18 16:46:10', '2025-01-18 17:10:34'),
('AL-INAH', 25, 42, 4567, 3000.00, 4, 750.00, 'fdsfaghJKLDGSHAJKLGFD', '12123445645', 5000.00, 5000.00, 'uploads/678be359daa55_SDT_2024_Test (1).pdf', 2250.00, '2025-01-18 17:22:33', '2025-01-18 20:15:29'),
('AL-BAI', 26, 43, 9876, 3000.00, 12, 250.00, 'fdsfaghJKLDGSHAJKLGFD', '12123445645', 5000.00, 5000.00, 'uploads/678c6e1fe238a_UTM - Course Registration Slip.pdf', 2250.00, '2025-01-19 03:14:39', '2025-01-20 04:58:29'),
('AL-BAI', 31, 49, 5522, 7777.00, 7, 1111.00, 'yingyingying', '77', 7777.00, 6666.00, '678e603446a37_Quiz3.pdf', NULL, '2025-01-20 14:39:48', '2025-01-20 14:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `tb_loanapplication`
--

CREATE TABLE `tb_loanapplication` (
  `loanApplicationID` int(11) NOT NULL,
  `employeeID` int(4) NOT NULL,
  `loanApplicationDate` date NOT NULL,
  `loanStatus` varchar(20) DEFAULT 'Pending',
  `amountRequested` decimal(10,2) NOT NULL,
  `financingPeriod` int(11) NOT NULL,
  `monthlyInstallments` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_loanapplication`
--

INSERT INTO `tb_loanapplication` (`loanApplicationID`, `employeeID`, `loanApplicationDate`, `loanStatus`, `amountRequested`, `financingPeriod`, `monthlyInstallments`) VALUES
(36, 322, '2025-01-15', 'Diluluskan', 3000.00, 12, 250.00),
(37, 322, '2025-01-15', 'Diluluskan', 3000.00, 12, 250.00),
(38, 5522, '2025-01-15', 'Pending', 4455.00, 4, 1113.75),
(39, 5522, '2025-01-15', 'Pending', 5555.00, 4, 1388.75),
(41, 5522, '2025-01-15', 'Diluluskan', 22222.00, 222, 100.10),
(42, 4567, '2025-01-19', 'Diluluskan', 3000.00, 4, 750.00),
(43, 9876, '2025-01-19', 'Diluluskan', 3000.00, 12, 250.00),
(49, 5522, '2025-01-20', 'Pending', 7777.00, 7, 1111.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_loanapproval`
--

CREATE TABLE `tb_loanapproval` (
  `loanApplicationID` int(11) NOT NULL,
  `bankNo` int(11) NOT NULL,
  `loanApproveDate` date NOT NULL,
  `loanStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_member`
--

CREATE TABLE `tb_member` (
  `employeeID` int(4) NOT NULL,
  `memberName` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT 'Email address',
  `ic` varchar(20) NOT NULL,
  `maritalStatus` varchar(20) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `nation` varchar(50) DEFAULT NULL,
  `no_pf` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL COMMENT 'Job position',
  `phoneNumber` varchar(20) DEFAULT NULL COMMENT 'Primary contact number',
  `phoneHome` varchar(20) DEFAULT NULL COMMENT 'Home contact number',
  `monthlySalary` decimal(10,2) DEFAULT NULL COMMENT 'Monthly salary amount',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member`
--

INSERT INTO `tb_member` (`employeeID`, `memberName`, `email`, `ic`, `maritalStatus`, `sex`, `religion`, `nation`, `no_pf`, `position`, `phoneNumber`, `phoneHome`, `monthlySalary`, `created_at`, `updated_at`) VALUES
(322, 'Cheryl Cheong Kah Voon', 'cherylcheong88@gmail.com', '040322040352', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '322', '322', '01121192282', '035331234', 2000.00, '2025-01-14 11:18:24', '2025-01-19 14:18:55'),
(1214, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'Perempuan', 'Islam', 'Cina', '222', 'STUDENT', '60123650018', '0612345678', 3000.00, '2025-02-03 14:33:04', '2025-02-03 16:50:53'),
(4567, 'LIM MEI MEI', 'meimei@gmail.com', '123445678912', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '4567', 'Gred 40', '0123456789', '0123456789', 5000.00, '2025-01-18 17:14:58', '2025-01-18 17:14:58'),
(5522, 'Chau Ying Jia', 'chauyingjia04@gmail.com', '040502080634', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '32', 'student', '0175678907', '055417899', 3000.00, '2025-01-15 03:25:16', '2025-01-15 03:25:16'),
(9876, 'GUI KAH SIN', 'guigui@gmail.com', '123445678912', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '9876', 'Gred 40', '01121192282', '0123456789', 6000.00, '2025-01-19 03:06:59', '2025-01-19 03:09:22');

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberapplicationdetails`
--

CREATE TABLE `tb_memberapplicationdetails` (
  `emplyeeID` int(4) NOT NULL,
  `applicationDate` date NOT NULL,
  `loanStatus` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberapproval_memberregistration`
--

CREATE TABLE `tb_memberapproval_memberregistration` (
  `memberRegistrationID` int(11) NOT NULL,
  `regisApproveDate` date NOT NULL,
  `regisStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberregistration_familymemberinfo`
--

CREATE TABLE `tb_memberregistration_familymemberinfo` (
  `employeeID` int(4) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icFamilyMember` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_memberregistration_familymemberinfo`
--

INSERT INTO `tb_memberregistration_familymemberinfo` (`employeeID`, `relationship`, `name`, `icFamilyMember`) VALUES
(322, 'Anak', 'mini cheryl', '252222-22-2222'),
(1214, 'Adik-beradik', 'LAU YEE THENG', '041214-04-0018'),
(4567, 'Suami', 'Lau Lau Lau', '040600-99-63'),
(5522, 'Ibu', 'Lau Yee Wen', '719999-08-5555'),
(9876, 'Suami', 'Lau Lau Lau', '040600-99-63');

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberregistration_feesandcontribution`
--

CREATE TABLE `tb_memberregistration_feesandcontribution` (
  `employeeID` int(4) NOT NULL,
  `entryFee` int(11) NOT NULL DEFAULT 0,
  `modalShare` int(11) NOT NULL DEFAULT 0,
  `feeCapital` int(11) NOT NULL DEFAULT 0,
  `deposit` int(11) NOT NULL DEFAULT 0,
  `contribution` int(11) NOT NULL DEFAULT 0,
  `fixedDeposit` int(11) NOT NULL DEFAULT 0,
  `others` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_memberregistration_feesandcontribution`
--

INSERT INTO `tb_memberregistration_feesandcontribution` (`employeeID`, `entryFee`, `modalShare`, `feeCapital`, `deposit`, `contribution`, `fixedDeposit`, `others`) VALUES
(322, 100, 1000, 0, 0, 600, 1200, 0),
(1214, 50, 300, 100, 100, 100, 100, 0),
(4567, 60, 300, 40, 40, 40, 40, 0),
(5522, 300, 300, 300, 300, 300, 300, 0),
(9876, 20, 300, 20, 20, 20, 20, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberregistration_memberapplicationdetails`
--

CREATE TABLE `tb_memberregistration_memberapplicationdetails` (
  `memberRegistrationID` int(11) NOT NULL,
  `regisDate` date NOT NULL,
  `regisStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_memberregistration_memberapplicationdetails`
--

INSERT INTO `tb_memberregistration_memberapplicationdetails` (`memberRegistrationID`, `regisDate`, `regisStatus`) VALUES
(5522, '2025-01-15', 'Diluluskan'),
(322, '2025-01-18', 'Diluluskan'),
(4567, '2025-01-18', 'Diluluskan'),
(9876, '2025-01-19', 'Diluluskan'),
(9876, '2025-01-19', 'Diluluskan'),
(1214, '2025-02-03', 'Belum Selesai'),
(1214, '2025-02-03', 'Belum Selesai'),
(1214, '2025-02-03', 'Belum Selesai'),
(1214, '2025-02-03', 'Belum Selesai'),
(1214, '2025-02-03', 'Belum Selesai'),
(1214, '2025-02-03', 'Belum Selesai');

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_financialstatus`
--

CREATE TABLE `tb_member_financialstatus` (
  `employeeID` int(11) NOT NULL,
  `accountID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_homeaddress`
--

CREATE TABLE `tb_member_homeaddress` (
  `employeeID` int(4) NOT NULL,
  `homeAddress` varchar(255) NOT NULL,
  `homePostcode` int(10) NOT NULL,
  `homeState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_homeaddress`
--

INSERT INTO `tb_member_homeaddress` (`employeeID`, `homeAddress`, `homePostcode`, `homeState`) VALUES
(322, 'M19', 82000, 'Melaka'),
(1214, 'Jasin', 77000, 'Melaka'),
(4567, 'KTDI', 81600, 'Johor'),
(5522, 'IPOH', 31400, 'Perak'),
(9876, 'KTDI', 81810, 'Johor');

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_officeaddress`
--

CREATE TABLE `tb_member_officeaddress` (
  `employeeID` int(4) NOT NULL,
  `officeAddress` varchar(255) NOT NULL,
  `officePostcode` int(10) NOT NULL,
  `officeState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_officeaddress`
--

INSERT INTO `tb_member_officeaddress` (`employeeID`, `officeAddress`, `officePostcode`, `officeState`) VALUES
(322, 'KTDI', 81316, 'Melaka'),
(1214, 'KTDI', 81310, 'Melaka'),
(4567, 'UTM', 81810, 'Johor'),
(5522, 'UTM', 31400, 'Perak'),
(9876, 'UTM', 81800, 'Johor');

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_transaction`
--

CREATE TABLE `tb_member_transaction` (
  `employeeID` int(11) NOT NULL,
  `transID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaction`
--

CREATE TABLE `tb_transaction` (
  `employeeID` int(4) NOT NULL,
  `transType` varchar(10) NOT NULL,
  `transAmt` int(6) NOT NULL,
  `transDate` date NOT NULL,
  `transID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaction`
--

INSERT INTO `tb_transaction` (`employeeID`, `transType`, `transAmt`, `transDate`, `transID`) VALUES
(4567, 'Simpanan-M', 300, '2025-01-19', 53),
(4567, 'Simpanan-Y', 40, '2025-01-19', 54),
(4567, 'Simpanan-S', 40, '2025-01-19', 55),
(4567, 'Simpanan-T', 40, '2025-01-19', 56),
(4567, 'Bayaran Ba', 750, '2025-01-19', 57),
(322, 'Simpanan-M', 1000, '2025-01-03', 58),
(322, 'Simpanan-S', 1200, '2025-01-03', 59),
(322, 'Simpanan-T', 600, '2025-01-03', 60),
(322, 'Simpanan-M', 1000, '2025-01-03', 61),
(322, 'Simpanan-S', 1200, '2025-01-03', 62),
(322, 'Simpanan-T', 600, '2025-01-03', 63),
(322, 'Bayaran Ba', 250, '2025-01-11', 64),
(322, 'Bayaran Ba', 250, '2025-01-11', 65),
(322, 'Bayaran Ba', 250, '2025-01-10', 66),
(9876, 'Entry Fee', 20, '2025-01-19', 80),
(9876, 'Deposit', 20, '2025-01-19', 81),
(9876, 'Simpanan-M', 300, '2025-01-19', 82),
(9876, 'Simpanan-Y', 20, '2025-01-19', 83),
(9876, 'Simpanan-S', 20, '2025-01-19', 84),
(9876, 'Simpanan-T', 20, '2025-01-19', 85),
(9876, 'Entry Fee', 20, '2025-02-19', 86),
(9876, 'Deposit', 20, '2025-02-19', 87),
(9876, 'Simpanan-M', 300, '2025-02-19', 88),
(9876, 'Simpanan-Y', 20, '2025-02-19', 89),
(9876, 'Simpanan-S', 20, '2025-02-19', 90),
(9876, 'Simpanan-T', 20, '2025-02-19', 91),
(9876, 'Entry Fee', 20, '2025-03-19', 92),
(9876, 'Deposit', 20, '2025-03-19', 93),
(9876, 'Simpanan-M', 300, '2025-03-19', 94),
(9876, 'Simpanan-Y', 20, '2025-03-19', 95),
(9876, 'Simpanan-S', 20, '2025-03-19', 96),
(9876, 'Simpanan-T', 20, '2025-03-19', 97),
(9876, 'Bayaran Ba', 250, '2025-01-11', 98),
(9876, 'Bayaran Ba', 250, '2025-02-11', 99),
(9876, 'Bayaran Ba', 250, '2025-03-11', 101);

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaction_financialstatus`
--

CREATE TABLE `tb_transaction_financialstatus` (
  `transID` int(11) NOT NULL,
  `accountID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_admin_loanapproval`
--
ALTER TABLE `tb_admin_loanapproval`
  ADD PRIMARY KEY (`employeeID`),
  ADD KEY `employeeID` (`employeeID`);

--
-- Indexes for table `tb_admin_member`
--
ALTER TABLE `tb_admin_member`
  ADD PRIMARY KEY (`employeeID`),
  ADD KEY `employeeID` (`employeeID`);

--
-- Indexes for table `tb_admin_memberapproval`
--
ALTER TABLE `tb_admin_memberapproval`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_bank`
--
ALTER TABLE `tb_bank`
  ADD PRIMARY KEY (`bankID`),
  ADD KEY `loanApplicationID` (`loanApplicationID`);

--
-- Indexes for table `tb_employee`
--
ALTER TABLE `tb_employee`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_employee_member`
--
ALTER TABLE `tb_employee_member`
  ADD PRIMARY KEY (`employeeID`),
  ADD KEY `employeeID` (`employeeID`);

--
-- Indexes for table `tb_familymemberinfo`
--
ALTER TABLE `tb_familymemberinfo`
  ADD PRIMARY KEY (`icFamilyMember`),
  ADD KEY `familyMemberInfo` (`employeeID`);

--
-- Indexes for table `tb_financialstatus`
--
ALTER TABLE `tb_financialstatus`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_guarantor`
--
ALTER TABLE `tb_guarantor`
  ADD PRIMARY KEY (`guarantorID`);

--
-- Indexes for table `tb_loan`
--
ALTER TABLE `tb_loan`
  ADD PRIMARY KEY (`loanID`),
  ADD KEY `loanApplicationID` (`loanApplicationID`);

--
-- Indexes for table `tb_loanapplication`
--
ALTER TABLE `tb_loanapplication`
  ADD PRIMARY KEY (`loanApplicationID`);

--
-- Indexes for table `tb_loanapproval`
--
ALTER TABLE `tb_loanapproval`
  ADD PRIMARY KEY (`loanApplicationID`,`bankNo`),
  ADD KEY `loanApplicationID` (`loanApplicationID`),
  ADD KEY `bankNo` (`bankNo`);

--
-- Indexes for table `tb_member`
--
ALTER TABLE `tb_member`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_memberapplicationdetails`
--
ALTER TABLE `tb_memberapplicationdetails`
  ADD PRIMARY KEY (`emplyeeID`),
  ADD KEY `emplyeeID` (`emplyeeID`);

--
-- Indexes for table `tb_memberapproval_memberregistration`
--
ALTER TABLE `tb_memberapproval_memberregistration`
  ADD PRIMARY KEY (`memberRegistrationID`);

--
-- Indexes for table `tb_memberregistration_familymemberinfo`
--
ALTER TABLE `tb_memberregistration_familymemberinfo`
  ADD PRIMARY KEY (`employeeID`,`icFamilyMember`);

--
-- Indexes for table `tb_memberregistration_feesandcontribution`
--
ALTER TABLE `tb_memberregistration_feesandcontribution`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_member_financialstatus`
--
ALTER TABLE `tb_member_financialstatus`
  ADD PRIMARY KEY (`employeeID`,`accountID`),
  ADD KEY `employeeID` (`employeeID`),
  ADD KEY `accountID` (`accountID`);

--
-- Indexes for table `tb_member_homeaddress`
--
ALTER TABLE `tb_member_homeaddress`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_member_officeaddress`
--
ALTER TABLE `tb_member_officeaddress`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_member_transaction`
--
ALTER TABLE `tb_member_transaction`
  ADD PRIMARY KEY (`employeeID`,`transID`),
  ADD KEY `employeeID` (`employeeID`),
  ADD KEY `transID` (`transID`);

--
-- Indexes for table `tb_transaction`
--
ALTER TABLE `tb_transaction`
  ADD PRIMARY KEY (`transID`,`employeeID`) USING BTREE,
  ADD KEY `employeeID` (`employeeID`);

--
-- Indexes for table `tb_transaction_financialstatus`
--
ALTER TABLE `tb_transaction_financialstatus`
  ADD PRIMARY KEY (`transID`,`accountID`),
  ADD KEY `transID` (`transID`),
  ADD KEY `accountID` (`accountID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_bank`
--
ALTER TABLE `tb_bank`
  MODIFY `bankID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `tb_guarantor`
--
ALTER TABLE `tb_guarantor`
  MODIFY `guarantorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tb_loan`
--
ALTER TABLE `tb_loan`
  MODIFY `loanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tb_loanapplication`
--
ALTER TABLE `tb_loanapplication`
  MODIFY `loanApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `tb_transaction`
--
ALTER TABLE `tb_transaction`
  MODIFY `transID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD CONSTRAINT `tb_admin_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `tb_employee` (`employeeID`);

--
-- Constraints for table `tb_bank`
--
ALTER TABLE `tb_bank`
  ADD CONSTRAINT `tb_bank_ibfk_1` FOREIGN KEY (`loanApplicationID`) REFERENCES `tb_loanapplication` (`loanApplicationID`);

--
-- Constraints for table `tb_loan`
--
ALTER TABLE `tb_loan`
  ADD CONSTRAINT `tb_loan_ibfk_1` FOREIGN KEY (`loanApplicationID`) REFERENCES `tb_loanapplication` (`loanApplicationID`);

--
-- Constraints for table `tb_memberregistration_familymemberinfo`
--
ALTER TABLE `tb_memberregistration_familymemberinfo`
  ADD CONSTRAINT `tb_memberregistration_familymemberinfo_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeID`);

--
-- Constraints for table `tb_memberregistration_feesandcontribution`
--
ALTER TABLE `tb_memberregistration_feesandcontribution`
  ADD CONSTRAINT `fk_member_fees` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_member_homeaddress`
--
ALTER TABLE `tb_member_homeaddress`
  ADD CONSTRAINT `tb_member_homeaddress_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeID`);

--
-- Constraints for table `tb_member_officeaddress`
--
ALTER TABLE `tb_member_officeaddress`
  ADD CONSTRAINT `tb_member_officeaddress_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
