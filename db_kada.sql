-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 01, 2025 at 05:41 AM
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
  `staffID` int(4) NOT NULL,
  `staffName` bigint(20) NOT NULL,
  `adminEmail` varchar(255) NOT NULL,
  `adminSex` varchar(6) NOT NULL,
  `adminDOB` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_applicationdetails`
--

CREATE TABLE `tb_applicationdetails` (
  `applicationDate` date NOT NULL,
  `memberStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_employee`
--

CREATE TABLE `tb_employee` (
  `employeeID` int(4) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_familymemberinfo`
--

CREATE TABLE `tb_familymemberinfo` (
  `employeeId` int(4) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `familyMemberName` varchar(100) DEFAULT NULL,
  `icFamilyMember` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_familymemberinfo`
--

INSERT INTO `tb_familymemberinfo` (`employeeId`, `relationship`, `familyMemberName`, `icFamilyMember`, `created_at`) VALUES
(1, 'Isteri', 'lau', '222', '2024-12-30 07:12:31'),
(2, 'Ibu', 'Lai', '222', '2024-12-30 07:33:18'),
(3, 'Anak', 'lau', '111', '2024-12-31 03:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `tb_feesandcontribution`
--

CREATE TABLE `tb_feesandcontribution` (
  `employeeId` int(4) NOT NULL,
  `entryFee` int(255) DEFAULT NULL,
  `modalShare` int(255) DEFAULT NULL,
  `feeCapital` int(255) DEFAULT NULL,
  `deposit` int(255) DEFAULT NULL,
  `contribution` int(255) DEFAULT NULL,
  `fixedDeposit` int(255) DEFAULT NULL,
  `others` int(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_financialstatus`
--

CREATE TABLE `tb_financialstatus` (
  `f_accID` int(10) NOT NULL,
  `f_stateID` int(10) NOT NULL,
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
-- Table structure for table `tb_guarantordetails`
--

CREATE TABLE `tb_guarantordetails` (
  `icGuarantor` int(12) NOT NULL,
  `guarantorName` varchar(30) NOT NULL,
  `memberIdGuarantor` int(4) NOT NULL,
  `telGuarantor` int(11) NOT NULL
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
  `bankName` varchar(20) NOT NULL,
  `bankAccount` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_loanapproval`
--

CREATE TABLE `tb_loanapproval` (
  `loanApprovalDate` date NOT NULL,
  `loanStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_member`
--

CREATE TABLE `tb_member` (
  `employeeId` int(4) NOT NULL,
  `memberName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ic` varchar(20) NOT NULL,
  `maritalStatus` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `poscode` varchar(10) NOT NULL,
  `state` varchar(50) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `nation` varchar(50) DEFAULT NULL,
  `no_anggota` varchar(50) DEFAULT NULL,
  `no_pf` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `officeAddress` text DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `phoneHome` varchar(20) DEFAULT NULL,
  `monthlySalary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member`
--

INSERT INTO `tb_member` (`employeeId`, `memberName`, `email`, `ic`, `maritalStatus`, `address`, `poscode`, `state`, `sex`, `religion`, `nation`, `no_anggota`, `no_pf`, `position`, `officeAddress`, `phoneNumber`, `phoneHome`, `monthlySalary`, `created_at`) VALUES
(1, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:04:14'),
(2, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:06:11'),
(3, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:07:03'),
(5, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:13:36'),
(7, 'cheryl', 'cherylxxx@gmail.com', '04111111111', 'Berkahwin', 'UTM, SKUDAI, JOHOR BAHRU', '90000', 'Johor', 'Perempuan', 'Hindu', 'India', '123', '456', '789', '123456', '123', '123', 1500.00, '2024-12-30 07:12:31'),
(8, 'cheryl', 'cherylxxx@gmail.com', '04111111111', 'Berkahwin', 'AAA', '90000', 'Johor', 'Perempuan', 'Hindu', 'India', '123', '456', '789', '123456789', '123', '123', 1800.00, '2024-12-30 07:33:18'),
(9, 'cheryl', 'cherylxxx@gmail.com', '041214040018', 'Berkahwin', 'JA7583', '90000', 'Melaka', 'Perempuan', 'Buddha', 'Cina', '222', '222', '222', '123', '123', '123', 1500.00, '2024-12-31 03:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaction`
--

CREATE TABLE `tb_transaction` (
  `transID` int(10) NOT NULL,
  `transType` varchar(10) NOT NULL,
  `transAmt` int(6) NOT NULL,
  `transDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`staffID`);

--
-- Indexes for table `tb_employee`
--
ALTER TABLE `tb_employee`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `tb_familymemberinfo`
--
ALTER TABLE `tb_familymemberinfo`
  ADD PRIMARY KEY (`employeeId`);

--
-- Indexes for table `tb_feesandcontribution`
--
ALTER TABLE `tb_feesandcontribution`
  ADD PRIMARY KEY (`employeeId`);

--
-- Indexes for table `tb_financialstatus`
--
ALTER TABLE `tb_financialstatus`
  ADD PRIMARY KEY (`f_accID`);

--
-- Indexes for table `tb_guarantordetails`
--
ALTER TABLE `tb_guarantordetails`
  ADD PRIMARY KEY (`icGuarantor`);

--
-- Indexes for table `tb_loan`
--
ALTER TABLE `tb_loan`
  ADD PRIMARY KEY (`loanApplicationID`);

--
-- Indexes for table `tb_member`
--
ALTER TABLE `tb_member`
  ADD PRIMARY KEY (`employeeId`);

--
-- Indexes for table `tb_transaction`
--
ALTER TABLE `tb_transaction`
  ADD PRIMARY KEY (`transID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
