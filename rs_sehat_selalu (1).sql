-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Bulan Mei 2025 pada 13.03
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rs_sehat_selalu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `pasien` varchar(50) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam` varchar(20) DEFAULT NULL,
  `keluhan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id`, `pasien`, `dokter`, `hari`, `jam`, `keluhan`) VALUES
(4, 'dwiki', 'dr.rovi', 'kamis', '14:00-18:00', 'panas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id`, `username`, `password`, `nama`) VALUES
(2, 'dwiki', 'dwiki123', 'dokter dwiki'),
(3, 'wahyu', 'wahyu123', 'dokter wahyu'),
(4, 'rovi', 'rovi123', 'dokter rovi'),
(5, 'dimas', 'dimas123', 'dokter dimas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter_jadwal`
--

CREATE TABLE `dokter_jadwal` (
  `id` int(11) NOT NULL,
  `dokter` varchar(50) DEFAULT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter_jadwal`
--

INSERT INTO `dokter_jadwal` (`id`, `dokter`, `hari`, `jam`) VALUES
(1, 'dr.dwiki', 'Senin', '09:00-13:00'),
(2, 'dr.wahyu', 'Rabu', '13:00-15:30'),
(3, 'dr.dimas', 'Selasa', '10:00-14:00'),
(4, 'dr.rovi', 'Kamis', '14:00-18:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `pasien` varchar(50) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pasien`, `jumlah`, `keterangan`, `tanggal`) VALUES
(1, '', 0, '', '2025-05-15'),
(2, '', 0, '', '2025-05-15'),
(3, '', 0, '', '2025-05-15'),
(4, '', 0, '', '2025-05-15'),
(5, '', 0, '', '2025-05-15'),
(6, '', 0, '', '2025-05-15'),
(7, '', 0, '', '2025-05-15'),
(8, 'dimas', 5000000, 'cacar elemen', '2025-05-17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan`
--

CREATE TABLE `pemeriksaan` (
  `id` int(11) NOT NULL,
  `pasien` varchar(100) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `diagnosa` text DEFAULT NULL,
  `obat` text DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemeriksaan`
--

INSERT INTO `pemeriksaan` (`id`, `pasien`, `dokter`, `diagnosa`, `obat`, `tanggal`) VALUES
(1, 'dimas', 'dwiki', 'cacar batu', 'paracetamol', '2025-05-17'),
(2, 'dwiki', 'dimas', 'demam', 'paracetamol', '2025-05-17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rawat_inap`
--

CREATE TABLE `rawat_inap` (
  `id` int(11) NOT NULL,
  `pasien` varchar(100) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Menunggu',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rawat_inap`
--

INSERT INTO `rawat_inap` (`id`, `pasien`, `dokter`, `alasan`, `status`, `tanggal`) VALUES
(1, 'farras', 'dwiki', 'penyakitfarras sudah parah harus ditangani rawat inap', 'Disetujui', '2025-05-17 10:52:37'),
(2, 'deni', 'dimas', 'sakit nya udah oarah', 'Ditolak', '2025-05-17 10:55:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat`
--

CREATE TABLE `riwayat` (
  `id` int(11) NOT NULL,
  `pasien` varchar(50) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `diagnosa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','pasien') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin1', '0192023a7bbd73250516f069df18b500', 'admin'),
(2, 'pasien1', '43b39eea8ff4885aa49ec46c39a08178', 'pasien'),
(3, 'dwiki', 'e91f39dd30ee8d28c0bf895810db1ad3', 'pasien'),
(4, 'dimas', '51947e3cf64ee746b6f2c73d174d525a', 'pasien');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dokter_jadwal`
--
ALTER TABLE `dokter_jadwal`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rawat_inap`
--
ALTER TABLE `rawat_inap`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `dokter_jadwal`
--
ALTER TABLE `dokter_jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `rawat_inap`
--
ALTER TABLE `rawat_inap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
