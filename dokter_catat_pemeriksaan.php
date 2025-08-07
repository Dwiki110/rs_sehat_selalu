<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pasien = $_POST['pasien'];
    $diagnosa = $_POST['diagnosa'];
    $obat = $_POST['obat'];
    $dokter = $_SESSION['username'];
    $tgl = date("Y-m-d");
    mysqli_query($conn, "INSERT INTO pemeriksaan (pasien, dokter, diagnosa, obat, tanggal) VALUES ('$pasien', '$dokter', '$diagnosa', '$obat', '$tgl')");
    echo "Berhasil dicatat.";
}
?>

<h2>Catat Pemeriksaan</h2>
<form method="POST">
    Nama Pasien: <input type="text" name="pasien" required><br>
    Diagnosa: <input type="text" name="diagnosa" required><br>
    Obat: <input type="text" name="obat" required><br>
    <button type="submit">Simpan</button>
</form>
<a href="dokter_dashboard.php">Kembali</a>
