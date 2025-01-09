-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2024 at 11:25 PM
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
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `nama_penuh` varchar(255) NOT NULL,
  `alamat_emel` varchar(255) NOT NULL,
  `mykad_passport` varchar(20) NOT NULL,
  `taraf_perkahwinan` varchar(20) NOT NULL,
  `alamat_rumah` text NOT NULL,
  `poskod` varchar(10) NOT NULL,
  `negeri` varchar(50) NOT NULL,
  `jantina` varchar(10) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `bangsa` varchar(50) DEFAULT NULL,
  `no_anggota` varchar(50) DEFAULT NULL,
  `no_pf` varchar(50) DEFAULT NULL,
  `jawatan_gred` varchar(100) DEFAULT NULL,
  `alamat_pejabat` text DEFAULT NULL,
  `no_tel_bimbit` varchar(20) DEFAULT NULL,
  `no_tel_rumah` varchar(20) DEFAULT NULL,
  `gaji_bulanan` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `nama_penuh`, `alamat_emel`, `mykad_passport`, `taraf_perkahwinan`, `alamat_rumah`, `poskod`, `negeri`, `jantina`, `agama`, `bangsa`, `no_anggota`, `no_pf`, `jawatan_gred`, `alamat_pejabat`, `no_tel_bimbit`, `no_tel_rumah`, `gaji_bulanan`, `created_at`) VALUES
(1, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:04:14'),
(2, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:06:11'),
(3, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:07:03'),
(4, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:12:02'),
(5, 'Lau Yee Wen', 'yeewen1214@gmail.com', '041214040018', 'Bujang', 'hhhh', '77000', 'Melaka', '', '', '', '', '', '', '', '', '', 0.00, '2024-12-20 15:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `member_fees`
--

CREATE TABLE `member_fees` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `fee_masuk` decimal(10,2) DEFAULT NULL,
  `modal_syer` decimal(10,2) DEFAULT NULL,
  `modal_yuran` decimal(10,2) DEFAULT NULL,
  `wang_deposit` decimal(10,2) DEFAULT NULL,
  `sumbangan_tabung` decimal(10,2) DEFAULT NULL,
  `simpanan_tetap` decimal(10,2) DEFAULT NULL,
  `lain_lain` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_waris`
--

CREATE TABLE `member_waris` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `hubungan` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `no_kp` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member_fees`
--
ALTER TABLE `member_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `member_waris`
--
ALTER TABLE `member_waris`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `member_fees`
--
ALTER TABLE `member_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member_waris`
--
ALTER TABLE `member_waris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `member_fees`
--
ALTER TABLE `member_fees`
  ADD CONSTRAINT `member_fees_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_waris`
--
ALTER TABLE `member_waris`
  ADD CONSTRAINT `member_waris_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
