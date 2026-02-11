-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 02:16 AM
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
-- Database: `ukk`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `guru` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nisn`, `guru`) VALUES
(2, 'azizahkhoirotull', '$2y$10$A/qovaF6agBV7J2Xa46eNe/5qucf.tuIUtOjpsxIfr9ame8grHfc2', '12345678', 'ABI'),
(3, 'abi', '$2y$10$hSXoIkxvvEP6.zS54.Xt1e8H7ouDDSwAcPCV82.X.3gld57D6ES1u', '12345678', 'ABI');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `nama_asli` varchar(255) DEFAULT NULL,
  `ukuran` int(11) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Sarana'),
(2, 'Prasarana'),
(3, 'Kendala');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `pelapor` varchar(100) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `isi` text NOT NULL,
  `tanggal` datetime NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','proses','selesai') DEFAULT 'menunggu',
  `tanggapan` varchar(100) DEFAULT NULL,
  `nis` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `pelapor`, `id_kategori`, `lokasi`, `isi`, `tanggal`, `foto`, `status`, `tanggapan`, `nis`) VALUES
(4, 'abi', 2, NULL, 'ada yang jatuh di lantai 2', '2026-01-19 12:46:56', NULL, 'proses', 'okey iki kesana', 0),
(5, 'suep', 3, NULL, 'ada komputer yang rusak di leb', '2026-01-19 15:56:27', NULL, 'proses', '', 0),
(6, 'abidin', 2, NULL, 'hp', '2026-01-21 02:44:56', NULL, 'selesai', '', 0),
(7, 'danu', 3, NULL, 'pc yang rusak di lab', '2026-01-21 15:46:52', NULL, 'selesai', '', 0),
(8, 'farel', 1, NULL, 'kipas anggin di leb rusak', '2026-01-26 02:35:47', NULL, 'selesai', '', 0),
(9, 'yongki', 1, 'kelas c.22', 'kursi rusak', '2026-01-27 02:56:25', NULL, 'proses', '', 0),
(15, 'dwi', 2, 'bali', 'ada kapal jatuh', '2026-02-03 01:59:32', NULL, 'proses', 'okeyy', 0),
(16, 'ferdi', 1, 'lab f.1,2', 'komputer ada yang rusak', '2026-02-03 12:05:47', NULL, 'selesai', '', 0),
(17, 'abi', 1, 'lab c.2.1', 'ss', '2026-02-04 07:44:08', '1770187448_6982eab8ba7a8.jpg', 'menunggu', NULL, 0),
(18, 'AZIZAH', 1, 'lab c.2.1', 'hyuu', '2026-02-04 08:30:51', '1770190251_6982f5abb5664.jpg', 'menunggu', NULL, 0),
(19, 'anjas', 1, 'lab f.1,2', 'hp rusak', '2026-02-05 03:36:14', '1770258974_6984021eefc1c.png', 'proses', '', 0),
(20, 'haidar', 2, 'c.2.1', 'meja rusak ok\r\n', '2026-02-05 04:06:56', '1770260816_698409503a822.png', 'proses', '', 0),
(21, 'AZIZAH', 1, 'lab f.1,2', 'qww', '2026-02-05 04:28:37', '1770262117_69840e6565a35.png', 'menunggu', NULL, 0),
(22, 'abidin', 1, 'lab f.1,22', 'w', '2026-02-05 04:38:35', '1770262715_698410bb99655.png', 'menunggu', NULL, 0),
(23, 'adit', 1, 'lab f.1,22', 'wde', '2026-02-05 07:10:44', '1770271844_698434644655b.png', 'selesai', '', 0),
(24, 'AZIZAH', 1, '123', 'e', '2026-02-05 07:31:34', '1770273094_698439462e355.png', 'proses', '', 0),
(25, 'yongki\'', 2, '123', 'cok jancok', '2026-02-05 07:34:04', '1770273244_698439dc90d1b.png', 'selesai', '', 0),
(26, 'zainal abidin', 1, 'labf.1', 'kipas sudahh', '2026-02-06 03:49:00', '1770346140_6985569c47974.jpg', 'proses', 'okeyy', 24062006);

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nama_siswa`, `nis`, `password`) VALUES
(2, 'abidin', '1297923', '$2y$10$bt1BHZVedbNzOIXnMO.Ike6TNePH9DolORMyOhHvfxxo/ifuRfs1C'),
(6, 'abidin', '1213', '$2y$10$oG/tn6lKzboCkXlEo52GdeCxhAj95HlnOPsKJFS0ylfJ1ez08gZdy'),
(7, 'zainal', '123123', '$2y$10$L2W2rrSSKWPgD0gbzeSgtOoPWPN6kHWkpTBb50/nvvlnT495xsTPG'),
(8, 'zainal', '123456', '$2y$10$X8F7En3OhZqEYoqkFysQVuznDY3mLeU0RKTmRb8U3zZKrjzrIXQWq'),
(9, 'zainal abidin', '141415414', '$2y$10$94IOwIAcMDc.xTE7BXs1AeeZlkys4/WIw2uSg4Pe.Q4fQSrR1Olgi'),
(10, 'abi', '123098', '$2y$10$016ggOassWFqCCncCC2YJ.QnaSPoK/eF25nGkOlTHPmz37//i/8Qe'),
(11, 'moch abidin', '24062006', '$2y$10$9gkqIwGNI/5wkNi4qx7fC.nEuBRWczicfLaktipBsPpwogGoRRoc.'),
(12, 'Farrel', '020228', '$2y$10$CzNSllQFvFvXj49dAd0Em.cEiwIFhxA8rb.AtxUtodMflsIXpJnLO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD KEY `fk_kategori` (`id_kategori`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `fk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
