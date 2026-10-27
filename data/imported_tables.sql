-- Database export for tables from XAMPP
-- Created for wira_harapan_vote / MySQL phpMyAdmin

CREATE DATABASE IF NOT EXISTS `wira_harapan_vote` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `wira_harapan_vote`;

-- --------------------------------------------------------
-- Table structure for `user`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `user`
INSERT INTO `user` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'itsMeNat79', 'agusnatawiryawan@gmail.com', '123456789101', '2026-09-11 23:05:41'),
(2, 'K22090017', 'shehehe@gmail.com', '25052010', '2026-09-11 23:24:30'),
(3, 'yuhuhu', 'mclarenih@gmail.com', '1234567', '2026-09-11 23:26:08'),
(4, 'ItsNat', 'admin123@gmail.com', 'admin123', '2026-09-12 09:47:40'),
(8, 'kingsphp', 'agus@gmail.com', '2222222222', '2026-09-23 21:52:45');

-- --------------------------------------------------------
-- Table structure for `siswa`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL AUTO_INCREMENT,
  `foto_siswa` varchar(255) DEFAULT NULL,
  `nis` varchar(20) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nama_siswa` varchar(255) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `jurusan` enum('RPL','ULW','DKV','Kuliner','Perhotelan') NOT NULL,
  `alamat` text DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `nis` (`nis`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `siswa`
INSERT INTO `siswa` (`id_siswa`, `foto_siswa`, `nis`, `nisn`, `nama_siswa`, `jenis_kelamin`, `jurusan`, `alamat`, `tanggal_lahir`) VALUES
(11, 'Screenshot 2026-07-22 133408.png', '6767', '098765432123', 'Nata Anjay', 'Laki-laki', 'RPL', 'nn', '2011-12-04');

