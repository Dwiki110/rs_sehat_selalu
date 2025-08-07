<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Hapus booking jika diminta
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM booking WHERE id = $id");
    header("Location: admin_kelola_booking.php");
    exit();
}
?>

<h2>Kelola Booking Pasien</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Nama Pasien</th>
        <th>Dokter</th>
        <th>Hari</th>
        <th>Jam</th>
        <th>Keluhan</th>
        <th>Aksi</th>
    </tr>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM booking");
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$no}</td>
                <td>{$row['pasien']}</td>
                <td>{$row['dokter']}</td>
                <td>{$row['hari']}</td>
                <td>{$row['jam']}</td>
                <td>{$row['keluhan']}</td>
                <td><a href='admin_kelola_booking.php?hapus={$row['id']}' onclick=\"return confirm('Yakin ingin hapus booking ini?')\">Hapus</a></td>
              </tr>";
        $no++;
    }
    ?>
</table>

<br>
<a href="admin_dashboard.php">Kembali ke Dashboard</a>
