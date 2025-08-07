<?php
include 'koneksi.php';
$pasien = $_POST['pasien'];
$jumlah = $_POST['jumlah'];
$keterangan = $_POST['keterangan'];
$tanggal = date("Y-m-d");

mysqli_query($conn, "INSERT INTO pembayaran (pasien, jumlah, keterangan, tanggal) VALUES ('$pasien', '$jumlah', '$keterangan', '$tanggal')");
header("Location: admin_pembayaran.php");
?>