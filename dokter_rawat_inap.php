<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Kirim permintaan rawat inap
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pasien = $_POST['pasien'];
    $dokter = $_SESSION['username'];
    $alasan = $_POST['alasan'];

    $query = "INSERT INTO rawat_inap (pasien, dokter, alasan) VALUES ('$pasien', '$dokter', '$alasan')";
    mysqli_query($conn, $query);
    echo "<script>alert('Permintaan rawat inap telah dikirim');</script>";
}
?>

<h2>Form Permintaan Rawat Inap</h2>
<form method="POST">
    <label>Nama Pasien:</label><br>
    <input type="text" name="pasien" required><br><br>

    <label>Alasan Rawat Inap:</label><br>
    <textarea name="alasan" rows="4" cols="50" required></textarea><br><br>

    <button type="submit">Kirim Permintaan</button>
</form>

<a href="dokter_dashboard.php">Kembali ke Dashboard</a>
