-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2025 at 08:10 PM
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
(34, 42, 120, 'Maybank', '5464345353345');

-- --------------------------------------------------------

--
-- Table structure for table `tb_employee`
--

CREATE TABLE `tb_employee` (
  `employeeID` int(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_employee`
--

INSERT INTO `tb_employee` (`employeeID`, `password`, `role`) VALUES
(120, 'neolixin', 'user'),
(322, 'cheryl', 'user'),
(1214, '$2y$10$O4h7W84R0fRb2YsErDZSZ.Lm5bBvj2oY9ZB2mOVVtj9L635O3FYcu', 'user'),
(1234, 'kada2024', 'admin'),
(5522, 'chau', 'user');

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
  `accountID` int(10) NOT NULL,
  `statementID` int(10) NOT NULL,
  `memberSaving` int(10) NOT NULL,
  `alBai` int(10) NOT NULL,
  `alnnah` int(10) NOT NULL,
  `bPulihKenderaan` int(10) NOT NULL,
  `roadTaxInsurance` int(10) NOT NULL,
  `specialScheme` int(10) NOT NULL,
  `alQadrul Hassan` int(10) NOT NULL,
  `dateUpdated` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(15, 42, 120, 'neo', '111111223333', '01164559129', '4321', '12231'),
(16, 42, 120, 'xin', '111111223333', '01164559129', '4321', '2836');

-- --------------------------------------------------------

--
-- Table structure for table `tb_loan`
--

CREATE TABLE `tb_loan` (
  `loanType` varchar(30) NOT NULL,
  `loanID` int(11) NOT NULL,
  `loanApplicationID` int(11) NOT NULL,
  `employeeID` int(4) NOT NULL,
  `amountRequested` decimal(10,2) NOT NULL,
  `financingPeriod` int(11) NOT NULL,
  `monthlyInstallments` decimal(10,2) NOT NULL,
  `employerName` varchar(100) NOT NULL,
  `employerIC` varchar(14) NOT NULL,
  `basicSalary` decimal(10,2) NOT NULL,
  `netSalary` decimal(10,2) NOT NULL,
  `basicSalaryFile` varchar(255) NOT NULL,
  `netSalaryFile` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_loan`
--

INSERT INTO `tb_loan` (`loanType`, `loanID`, `loanApplicationID`, `employeeID`, `amountRequested`, `financingPeriod`, `monthlyInstallments`, `employerName`, `employerIC`, `basicSalary`, `netSalary`, `basicSalaryFile`, `netSalaryFile`, `created_at`, `updated_at`) VALUES
('', 20, 36, 322, 3000.00, 12, 250.00, 'NAMA MAJIKAN', '222222222222', 2000.00, 1500.00, 'uploads/6786a2674a8b4_MAFEST \'25 UNIT HADIAH (2).pdf', 'uploads/6786a2674aa3b_OPERA \'25 UNIT HADIAH (2).pdf', '2025-01-17 13:27:42', '2025-01-17 13:28:55'),
('', 21, 37, 322, 3000.00, 12, 250.00, 'NAMA MAJIKAN2', '222222222222', 2000.00, 1500.00, 'uploads/6786a881ba680_M2U_20250113_1518.pdf', 'uploads/6786a881ba7b5_OPERA \'25 UNIT HADIAH (2).pdf', '2025-01-17 13:27:42', '2025-01-17 13:28:55'),
('', 22, 38, 5522, 4455.00, 4, 1113.75, 'gt', '34', 34.00, 34.00, 'uploads/67872c3b57dd6_Quiz3.pdf', 'uploads/67872c3b58058_Quiz3.pdf', '2025-01-17 13:27:42', '2025-01-17 13:28:55'),
('', 23, 39, 5522, 5555.00, 4, 1388.75, 'miao', '99', 2000.00, 2000.00, 'uploads/678739c3e166e_Quiz3.pdf', 'uploads/678739c3e18c2_Quiz3.pdf', '2025-01-17 13:27:42', '2025-01-17 13:28:55'),
('AL-BAI', 24, 41, 5522, 22222.00, 222, 100.10, 'rr', '22', 2222.00, 2222.00, 'uploads/67877e31ccad5_Quiz3.pdf', 'uploads/67877e31ccd61_Quiz3.pdf', '2025-01-17 13:27:42', '2025-01-17 13:28:55'),
('AL-INAH', 25, 42, 120, 888.00, 3, 296.00, 'Ghazali', '123456789123', 4532.00, 6543.00, 'uploads/678a9846ebe84_Lab 3 Netcom.pdf', 'uploads/678a9846ebf87_Lab 3 Netcom.pdf', '2025-01-17 17:49:58', '2025-01-17 17:49:58');

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
(36, 322, '2025-01-15', 'Pending', 3000.00, 12, 250.00),
(37, 322, '2025-01-15', 'Pending', 3000.00, 12, 250.00),
(38, 5522, '2025-01-15', 'Pending', 4455.00, 4, 1113.75),
(39, 5522, '2025-01-15', 'Pending', 5555.00, 4, 1388.75),
(41, 5522, '2025-01-15', 'Pending', 22222.00, 222, 100.10),
(42, 120, '2025-01-18', 'Pending', 888.00, 3, 296.00);

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
(120, 'NEO LI XIN', 'neolixin@gmail.com', '111111223333', 'Bujang', 'Lelaki', 'Buddha', 'Cina', '4321', 'Pelajar', '01164559129', '041111111', 888.00, '2025-01-17 06:28:49', '2025-01-17 06:28:49'),
(322, 'Cheryl Cheong Kah Voon', 'cheryl@gmail.com', '040322040352', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '322', '322', '1121192282', '035331234', 2000.00, '2025-01-14 11:18:24', '2025-01-14 11:19:10'),
(1214, 'LAU YEE WEN', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '1214', '1214', '123650018', '065293845', 3000.00, '2025-01-13 14:28:42', '2025-01-13 14:28:42'),
(5522, 'Chau Ying Jia', 'chauyingjia04@gmail.com', '040502080634', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '32', 'student', '0175678907', '055417899', 3000.00, '2025-01-15 03:25:16', '2025-01-15 03:25:16');

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
(120, 'Bapa', 'Ahming', '122131-23-2131'),
(120, 'Ibu', 'Ahseng', '324322-13-1223'),
(322, 'Anak', 'mini cheryl', '252222-22-2222'),
(1214, 'Adik-beradik', 'LAU YEE THENG', '980222-04-1111'),
(5522, 'Ibu', 'Lau Yee Wen', '719999-08-5555');

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
(120, 0, 300, 20, 0, 0, 0, 0),
(322, 0, 1000, 0, 0, 0, 1000, 0),
(1214, 0, 300, 0, 0, 0, 0, 0),
(5522, 300, 300, 300, 300, 300, 300, 0);

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
(322, '2025-01-17', 'Belum Selesai'),
(120, '2025-01-17', 'Diluluskan');

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
  `homePostcode` varchar(10) NOT NULL,
  `homeState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_homeaddress`
--

INSERT INTO `tb_member_homeaddress` (`employeeID`, `homeAddress`, `homePostcode`, `homeState`) VALUES
(120, 'Jalan Iman\r\nLebuh', '81310', 'Johor'),
(322, 'M19', '82000', 'Melaka'),
(5522, 'IPOH', '31400', 'Perak');

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_officeaddress`
--

CREATE TABLE `tb_member_officeaddress` (
  `employeeID` int(4) NOT NULL,
  `officeAddress` varchar(255) NOT NULL,
  `officePostcode` varchar(10) NOT NULL,
  `officeState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_officeaddress`
--

INSERT INTO `tb_member_officeaddress` (`employeeID`, `officeAddress`, `officePostcode`, `officeState`) VALUES
(120, 'Jalan Iman\r\nLebuh', '81310', 'Johor'),
(322, 'KTDI', '81310', 'Melaka'),
(5522, 'UTM', '31400', 'Perak');

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
  `transID` int(10) NOT NULL,
  `transType` varchar(10) NOT NULL,
  `transAmt` int(6) NOT NULL,
  `transDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`accountID`);

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
  ADD PRIMARY KEY (`employeeID`,`transID`),
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
  MODIFY `bankID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tb_guarantor`
--
ALTER TABLE `tb_guarantor`
  MODIFY `guarantorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tb_loan`
--
ALTER TABLE `tb_loan`
  MODIFY `loanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tb_loanapplication`
--
ALTER TABLE `tb_loanapplication`
  MODIFY `loanApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
