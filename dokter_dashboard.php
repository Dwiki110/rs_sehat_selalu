<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
?>

<h2>Dashboard Dokter</h2>
<ul>
    <li><a href="dokter_lihat_booking.php">Lihat Booking Pasien</a></li>
    <li><a href="dokter_catat_pemeriksaan.php">Catat Pemeriksaan</a></li>
    <li><a href="dokter_rawat_inap.php">rawat inap</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>
