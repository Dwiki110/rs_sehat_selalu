<?php
session_start();
if ($_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

$pasien = $_SESSION['username'];
$booking = mysqli_query($conn, "SELECT * FROM booking WHERE pasien = '$pasien' ORDER BY id DESC");
?>

<h2>Riwayat Booking Janji Temu</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Dokter</th>
        <th>Hari</th>
        <th>Jam</th>
        <th>Keluhan</th>
    </tr>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($booking)) {
        echo "<tr>
                <td>{$no}</td>
                <td>{$row['dokter']}</td>
                <td>{$row['hari']}</td>
                <td>{$row['jam']}</td>
                <td>{$row['keluhan']}</td>
              </tr>";
        $no++;
    }
    ?>
</table>

<br>
<a href="pasien_dashboard.php">Kembali</a>
