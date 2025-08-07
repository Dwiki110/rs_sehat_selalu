<?php
session_start();
include 'koneksi.php';

$pasien = $_SESSION['username']; // Asumsikan username pasien disimpan di session
$dokter = $_POST['dokter'];
$hari = $_POST['hari'];
$jam = $_POST['jam'];
$keluhan = $_POST['keluhan'];

// Simpan ke database
$query = "INSERT INTO booking (pasien, dokter, hari, jam, keluhan) 
          VALUES ('$pasien', '$dokter', '$hari', '$jam', '$keluhan')";

if (mysqli_query($conn, $query)) {
    echo "Booking berhasil!";
    header("Location: pasien_dashboard.php"); // arahkan sesuai kebutuhan Anda
} else {
    echo "Terjadi kesalahan: " . mysqli_error($conn);
}
?>
