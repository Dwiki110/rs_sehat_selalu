<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';
?>

<h2>Pencatatan Pembayaran</h2>
<form action="tambah_pembayaran.php" method="POST">
    Nama Pasien: <input type="text" name="pasien" required><br>
    Jumlah (Rp): <input type="number" name="jumlah" required><br>
    Keterangan: <input type="text" name="keterangan"><br>
    <button type="submit">Catat</button>
</form>

<h3>Riwayat Pembayaran</h3>
<table border="1">
<tr><th>Pasien</th><th>Jumlah</th><th>Keterangan</th><th>Tanggal</th></tr>
<?php
$data = mysqli_query($conn, "SELECT * FROM pembayaran");
while ($r = mysqli_fetch_assoc($data)) {
    echo "<tr><td>{$r['pasien']}</td><td>{$r['jumlah']}</td><td>{$r['keterangan']}</td><td>{$r['tanggal']}</td></tr>";
}
?>
</table>
<a href="admin_dashboard.php">Kembali</a>