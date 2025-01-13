-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2025 at 09:12 PM
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
  `monthlyInstallments` int(10) NOT NULL
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
  `email` varchar(255) NOT NULL,
  `ic` varchar(20) NOT NULL,
  `maritalStatus` varchar(20) NOT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `nation` varchar(50) DEFAULT NULL,
  `no_pf` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `phoneHome` varchar(20) DEFAULT NULL,
  `monthlySalary` decimal(10,2) DEFAULT NULL
  PRIMARY KEY (`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_member`
--

INSERT INTO `tb_member` (`employeeID`, `memberName`, `email`, `ic`, `maritalStatus`, `sex`, `religion`, `nation`, `no_pf`, `position`, `phoneNumber`, `phoneHome`, `monthlySalary`) VALUES
(1, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', '', '', '', '', '', '', '', 0.00),
(2, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', '', '', '', '', '', '', '', 0.00),
(3, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', '', '', '', '', '', '', '', 0.00),
(5, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', '', '', '', '', '', '', '', 0.00),
(7, 'cheryl', 'cherylxxx@gmail.com', '04111111111', 'Berkahwin', 'Perempuan', 'Hindu', 'India', '456', '789', '123', '123', 1500.00),
(8, 'cheryl', 'cherylxxx@gmail.com', '04111111111', 'Berkahwin', 'Perempuan', 'Hindu', 'India', '456', '789', '123', '123', 1800.00),
(9, 'cheryl', 'cherylxxx@gmail.com', '041214040018', 'Berkahwin', 'Perempuan', 'Buddha', 'Cina', '222', '222', '123', '123', 1500.00);

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
  `memberRegistrationID` int(11) NOT NULL,
  `icFamilyMember` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_memberregistration_feesandcontribution`
--

CREATE TABLE `tb_memberregistration_feesandcontribution` (
  `memberRegistrationID` int(11) NOT NULL,
  `entryFee` int(11) NOT NULL,
  `modalShare` int(11) NOT NULL,
  `feeCapital` int(11) NOT NULL,
  `deposit` int(11) NOT NULL,
  `contribution` int(11) NOT NULL,
  `fixedDeposit` int(11) NOT NULL,
  `others` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `homePostcode` int(6) NOT NULL,
  `homeState` varchar(35) NOT NULL
  PRIMARY KEY (`employeeID`),
  FOREIGN KEY (`employeeID`) REFERENCES `tb_member`(`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  PRIMARY KEY (`employeeID`),
  FOREIGN KEY (`employeeID`) REFERENCES `tb_member`(`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`icGuarantor`);

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
  ADD PRIMARY KEY (`employeeId`);

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
  ADD PRIMARY KEY (`memberRegistrationID`,`icFamilyMember`),
  ADD KEY `memberRegistrationID` (`memberRegistrationID`),
  ADD KEY `icFamilyMember` (`icFamilyMember`);

--
-- Indexes for table `tb_memberregistration_feesandcontribution`
--
ALTER TABLE `tb_memberregistration_feesandcontribution`
  ADD PRIMARY KEY (`memberRegistrationID`);

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
  ADD PRIMARY KEY (`employeeID`,`homeAddress`),
  ADD KEY `employeeID` (`employeeID`);

--
-- Indexes for table `tb_member_memberapplicationdetails`
--
ALTER TABLE `tb_member_memberapplicationdetails`
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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
