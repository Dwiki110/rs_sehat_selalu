<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';
$username = $_SESSION['username'];
?>

<h2>Daftar Booking Pasien</h2>
<table border="1">
<tr><th>Pasien</th><th>Hari</th><th>Jam</th></tr>
<?php
$data = mysqli_query($conn, "SELECT * FROM booking WHERE dokter='$username'");
while ($row = mysqli_fetch_assoc($data)) {
    echo "<tr><td>{$row['pasien']}</td><td>{$row['hari']}</td><td>{$row['jam']}</td></tr>";
}
?>
</table>
<a href="dokter_dashboard.php">Kembali</a>
