-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2025 at 12:42 PM
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

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`employeeID`, `staffName`) VALUES
(1234, 'Admin');

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
  `bankNo` int(20) NOT NULL,
  `bankName` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_employee`
--

CREATE TABLE `tb_employee` (
  `employeeID` int(4) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_employee`
--

INSERT INTO `tb_employee` (`employeeID`, `password`) VALUES
(1234, '123456');

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
  `employeeID` int(4) NOT NULL,
  `icGuarantor` int(12) NOT NULL,
  `guarantorName` varchar(30) NOT NULL,
  `memberIdGuarantor` int(4) NOT NULL,
  `telGuarantor` int(11) NOT NULL,
  `noPFGuarantor` int(10) NOT NULL,
  `tandatanganGua` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_loan`
--

CREATE TABLE `tb_loan` (
  `loanApplicationID` int(10) NOT NULL,
  `amountRequested` int(10) NOT NULL,
  `financingPeriod` int(3) NOT NULL,
  `monthlyInstallments` int(10) NOT NULL,
  `employerName` varchar(30) NOT NULL,
  `employerIc` int(12) NOT NULL,
  `basicSalary` int(6) NOT NULL,
  `netSalary` int(6) NOT NULL,
  `basicSalaryFile` mediumblob NOT NULL,
  `netSalaryFile` mediumblob NOT NULL,
  `signature` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_loanapplication`
--

CREATE TABLE `tb_loanapplication` (
  `loanApplicationID` int(11) NOT NULL,
  `bankNo` int(11) NOT NULL,
  `loanApplicationDate` date NOT NULL,
  `loanStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `tb_loanguarantordetails`
--

CREATE TABLE `tb_loanguarantordetails` (
  `loanApplicationID` int(10) NOT NULL,
  `icGuarantor` int(12) NOT NULL
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
(101, 'LALA', 'cherylxxx@gmail.com', '123456789012', 'Berkahwin', 'Perempuan', 'Kristian', 'India', '456', '666', '123650018', '065293845', 1500.00, '2025-01-13 11:41:14', '2025-01-13 11:41:14'),
(111, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:49:32', '2025-01-09 22:49:32'),
(123, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:30:27', '2025-01-09 22:30:27'),
(222, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:29:31', '2025-01-09 22:29:31'),
(333, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:51:46', '2025-01-09 22:51:46'),
(444, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:55:02', '2025-01-09 22:55:02'),
(555, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 22:58:06', '2025-01-09 22:58:06'),
(666, '', NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 05:01:23', '2025-01-10 05:01:23'),
(777, 'LALA', 'polok@gmail.com', '123', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '777', '666', '666', '666', 7777.00, '2025-01-10 05:25:12', '2025-01-10 05:25:12'),
(1214, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'Perempuan', 'Kristian', 'Melayu', '456', '444', '123650018', '065293845', 1500.00, '2025-01-10 15:58:05', '2025-01-10 15:58:05'),
(1234, 'Cheryl', 'yeewen1214@gmail.com', '123456789012', 'Duda/Janda', 'Perempuan', 'Buddha', 'Cina', '1234', '1234', '123650018', '065293845', 3000.00, '2025-01-13 11:31:28', '2025-01-13 11:31:28'),
(8888, 'Lau Yee Wen', 'yeewen1214@gmail.com', '123456789012', 'Bujang', 'Perempuan', 'Islam', 'Cina', '8888', '8888', '123650018', '012345678', 1500.00, '2025-01-10 07:11:32', '2025-01-10 07:54:29'),
(9999, 'Lau Yee Wen', 'yeewen1214@gmail.com', '123456789012', 'Bujang', 'Perempuan', 'Buddha', 'Cina', '999', '789', '123456789', '065293845', 7000.00, '2025-01-10 09:21:46', '2025-01-10 09:21:46');

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
(101, 'Adik-beradik', 'Lai', '041214-04-0018'),
(123, 'Ibu', 'Lai', '041214-04-0018'),
(222, 'Suami', 'lau', '041214-04-0018'),
(444, 'Isteri', 'lau', '041214-04-0018'),
(555, 'Bapa', 'Lai', '041214-04-0018'),
(555, 'Anak', 'Lai', '111'),
(666, 'Suami', 'Lai', '041214-04-0018'),
(777, 'Adik-beradik', 'Cheryl', '043333-33-3333'),
(1214, 'Suami', 'lau', '041214-04-0018'),
(1234, 'Anak', 'Lai', '041214-04-0018'),
(1234, 'Adik-beradik', 'Lai', '111111-11-1111'),
(8888, 'Suami', 'lau', '041214-04-0018'),
(9999, 'Isteri', 'lau', '041214-04-0018'),
(9999, 'Ibu', 'Lai', '111111-11-1111');

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
(101, 0, 1110, 0, 0, 0, 0, 0),
(111, 0, 800, 0, 0, 0, 0, 10),
(333, 0, 900, 0, 0, 0, 0, 0),
(444, 799, 899, 0, 0, 0, 0, 0),
(555, 0, 888, 0, 0, 0, 0, 0),
(666, 6, 666, 6, 6, 6, 6, 6),
(777, 0, 777, 0, 0, 0, 0, 0),
(1214, 0, 400, 0, 0, 0, 0, 0),
(1234, 0, 700, 0, 0, 0, 0, 0),
(8888, 0, 300, 0, 0, 0, 0, 0),
(9999, 0, 900, 0, 0, 0, 0, 10);

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberregistration_memberapplicationdetails`
--

CREATE TABLE `tb_memberregistration_memberapplicationdetails` (
  `memberRegistrationID` int(11) NOT NULL,
  `regisDate` date NOT NULL,
  `regisStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `homePostcode` varchar(10) DEFAULT NULL,
  `homeState` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_homeaddress`
--

INSERT INTO `tb_member_homeaddress` (`employeeID`, `homeAddress`, `homePostcode`, `homeState`) VALUES
(101, '123', '43000', 'WP Putrajaya'),
(1111, '123', '77000', 'Melaka'),
(1214, 'qwer', '45678', 'WP Kuala Lumpur'),
(4567, 'JA7582, JALAN TEMAN 1, TAMAN TEMAN, JASIN', '77000', 'Melaka');

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_memberapplicationdetails`
--

CREATE TABLE `tb_member_memberapplicationdetails` (
  `employeeID` int(11) NOT NULL,
  `regisDate` date NOT NULL,
  `regisStatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_member_officeaddress`
--

CREATE TABLE `tb_member_officeaddress` (
  `employeeID` int(4) NOT NULL,
  `officeAddress` varchar(255) NOT NULL,
  `officePostcode` int(6) NOT NULL,
  `officeState` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member_officeaddress`
--

INSERT INTO `tb_member_officeaddress` (`employeeID`, `officeAddress`, `officePostcode`, `officeState`) VALUES
(101, 'UTM', 81310, 'WP Kuala Lumpur'),
(1111, '111', 81310, 'WP Kuala Lumpur'),
(1214, '1234', 45000, 'WP Labuan'),
(4567, 'UTM', 81310, 'Johor');

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
  ADD PRIMARY KEY (`employeeID`),
  ADD KEY `employeeID` (`employeeID`);

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
  ADD PRIMARY KEY (`bankNo`);

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
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_loan`
--
ALTER TABLE `tb_loan`
  ADD PRIMARY KEY (`loanApplicationID`);

--
-- Indexes for table `tb_loanapplication`
--
ALTER TABLE `tb_loanapplication`
  ADD PRIMARY KEY (`loanApplicationID`,`bankNo`),
  ADD KEY `loanApplicationID` (`loanApplicationID`),
  ADD KEY `bankNo` (`bankNo`);

--
-- Indexes for table `tb_loanapproval`
--
ALTER TABLE `tb_loanapproval`
  ADD PRIMARY KEY (`loanApplicationID`,`bankNo`),
  ADD KEY `loanApplicationID` (`loanApplicationID`),
  ADD KEY `bankNo` (`bankNo`);

--
-- Indexes for table `tb_loanguarantordetails`
--
ALTER TABLE `tb_loanguarantordetails`
  ADD PRIMARY KEY (`loanApplicationID`,`icGuarantor`),
  ADD KEY `icGuarantor` (`icGuarantor`),
  ADD KEY `loanApplicationID` (`loanApplicationID`);

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
  ADD PRIMARY KEY (`employeeID`,`homeAddress`);

--
-- Indexes for table `tb_member_memberapplicationdetails`
--
ALTER TABLE `tb_member_memberapplicationdetails`
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
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD CONSTRAINT `employee` FOREIGN KEY (`employeeID`) REFERENCES `tb_employee` (`employeeID`);

--
-- Constraints for table `tb_loan`
--
ALTER TABLE `tb_loan`
  ADD CONSTRAINT `loan_member` FOREIGN KEY (`loanApplicationID`) REFERENCES `tb_member` (`employeeId`);

--
-- Constraints for table `tb_memberregistration_familymemberinfo`
--
ALTER TABLE `tb_memberregistration_familymemberinfo`
  ADD CONSTRAINT `tb_memberregistration_familymemberinfo_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeId`);

--
-- Constraints for table `tb_memberregistration_feesandcontribution`
--
ALTER TABLE `tb_memberregistration_feesandcontribution`
  ADD CONSTRAINT `fk_member_fees` FOREIGN KEY (`employeeID`) REFERENCES `tb_member` (`employeeId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
