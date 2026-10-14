-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 05:06 PM
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
-- Database: `bank_sampah`
--

-- --------------------------------------------------------

--
-- Table structure for table `sampah`
--

CREATE TABLE `sampah` (
  `id_sampah` int(11) NOT NULL,
  `jenis_sampah` varchar(100) NOT NULL,
  `harga_per_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sampah`
--

INSERT INTO `sampah` (`id_sampah`, `jenis_sampah`, `harga_per_kg`, `harga_jual`, `stok_kg`) VALUES
(1, 'Plastik', 2000.00, 2500.00, 10.00),
(2, 'Kardus', 1500.00, 1900.00, 18.00),
(3, 'Kertas', 1200.00, 1500.00, 6.00),
(4, 'Kaleng/Logam', 3500.00, 4400.00, 10.00),
(5, 'Botol Kaca', 800.00, 1000.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `tarik_saldo`
--

CREATE TABLE `tarik_saldo` (
  `id_tarik` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tarik_saldo`
--

INSERT INTO `tarik_saldo` (`id_tarik`, `id_user`, `nominal`, `tanggal`, `status`) VALUES
(1, 3, 10000.00, '2026-09-17 20:31:41', 'selesai'),
(2, 2, 1000.00, '2026-09-17 20:48:28', 'ditolak'),
(3, 3, 4000.00, '2026-09-21 18:09:17', 'selesai'),
(4, 5, 10000.00, '2026-09-22 21:24:04', 'selesai'),
(5, 3, 26000.00, '2026-09-29 21:03:42', 'selesai'),
(6, 3, 30000.00, '2026-09-29 21:03:55', 'selesai');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_sampah` int(11) DEFAULT NULL,
  `tipe` varchar(20) NOT NULL DEFAULT 'setor',
  `berat_kg` decimal(10,2) NOT NULL,
  `total_rp` decimal(12,2) NOT NULL,
  `metode_topup` varchar(30) DEFAULT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_sampah`, `tipe`, `berat_kg`, `total_rp`, `metode_topup`, `tanggal`) VALUES
(1, 3, 1, 'setor', 2.00, 4000.00, NULL, '2026-09-01 00:00:00'),
(2, 3, 2, 'setor', 6.00, 9000.00, NULL, '2026-08-28 00:00:00'),
(3, 2, 5, 'setor', 3.00, 2400.00, NULL, '2026-09-17 00:00:00'),
(4, 3, 4, 'setor', 2.40, 8400.00, NULL, '2026-09-21 07:33:35'),
(5, 3, 4, 'setor', 4.00, 14000.00, NULL, '2026-09-21 07:33:46'),
(6, 3, 4, 'setor', 4.80, 16800.00, NULL, '2026-09-21 13:04:59'),
(7, 5, 4, 'setor', 20.00, 70000.00, NULL, '2026-09-21 13:07:14'),
(8, 3, 5, 'setor', 3.00, 2400.00, NULL, '2026-09-22 16:25:13'),
(9, 4, NULL, 'topup', 0.00, 200000.00, 'M-Banking', '2026-09-29 19:41:43'),
(10, 4, 4, 'penjualan', 1.20, 5280.00, NULL, '2026-09-29 19:44:00'),
(11, 4, 5, 'penjualan', 6.00, 6000.00, NULL, '2026-09-29 19:44:12'),
(12, 2, 3, 'setor', 6.80, 8160.00, NULL, '2026-09-29 14:45:11'),
(13, 3, 1, 'setor', 40.00, 80000.00, NULL, '2026-09-29 14:46:12'),
(14, 7, NULL, 'topup', 0.00, 500000.00, 'Indomaret', '2026-09-29 20:22:22'),
(15, 7, NULL, 'topup', 0.00, 100000.00, 'M-Banking', '2026-09-29 20:22:39'),
(16, 7, 1, 'penjualan', 20.00, 50000.00, NULL, '2026-09-29 20:23:03'),
(17, 4, NULL, 'topup', 0.00, 100000.00, 'Alfamart', '2026-09-30 07:25:57'),
(18, 4, NULL, 'topup', 0.00, 200000.00, 'E-Money', '2026-09-30 07:26:05'),
(19, 4, 4, 'penjualan', 20.00, 88000.00, NULL, '2026-09-30 07:30:31'),
(20, 4, 3, 'penjualan', 0.80, 1200.00, NULL, '2026-09-30 07:30:50'),
(21, 4, 1, 'penjualan', 10.00, 25000.00, NULL, '2026-09-30 07:31:09'),
(22, 3, 5, 'setor', 60.00, 48000.00, NULL, '2026-09-30 03:59:26'),
(23, 3, 2, 'setor', 18.00, 27000.00, NULL, '2026-09-30 03:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa','pengepul') NOT NULL,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `saldo`) VALUES
(1, 'Admin Sekolah', 'admin', '$2b$10$19EvrB6F3fAFnLfmh6/CfezYM37PRNjB005vx5pH3uCiUdtlt4e0a', 'admin', 0.00),
(2, 'Bu Sari, S.Pd.', 'guru', '$2b$10$3zZ6/YinKGLgr7qEgh9xUOMhzobNSNGm8WKTQGWXARJJNo9ajJGgy', 'guru', 25560.00),
(3, 'Nadine Karima', 'nadine', '$2y$10$O1UCgqTMHymquo8MsNxNne8KMbxLNYFLPnRJF3QANSNTUBit3Nsaq', 'siswa', 171600.00),
(4, 'Pak Pengepul', 'pengepul', '$2b$10$TUlz95utpOQ8YDIC1HQNEurgf64P0o0Nd3069bz8Z1GUCWRF4I3sy', 'pengepul', 374520.00),
(5, 'Heru Prabawa', 'heru', '$2y$10$jb3zdRNU1GTGs5L1hXtTQuP5VuF6HEFIdbauXniQndp7.TML.CHTa', 'guru', 60000.00),
(7, 'PT Jaya Sentosa', 'jaya', '$2y$10$83WtQx5Wi9T1Zy2.x8Ilx.FI.TT3w07YtKUI4qAaWB5jqmEjpp1B2', 'pengepul', 550000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sampah`
--
ALTER TABLE `sampah`
  ADD PRIMARY KEY (`id_sampah`);

--
-- Indexes for table `tarik_saldo`
--
ALTER TABLE `tarik_saldo`
  ADD PRIMARY KEY (`id_tarik`),
  ADD KEY `fk_tarik_user` (`id_user`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `fk_transaksi_user` (`id_user`),
  ADD KEY `fk_transaksi_sampah` (`id_sampah`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sampah`
--
ALTER TABLE `sampah`
  MODIFY `id_sampah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tarik_saldo`
--
ALTER TABLE `tarik_saldo`
  MODIFY `id_tarik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tarik_saldo`
--
ALTER TABLE `tarik_saldo`
  ADD CONSTRAINT `fk_tarik_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_sampah` FOREIGN KEY (`id_sampah`) REFERENCES `sampah` (`id_sampah`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
