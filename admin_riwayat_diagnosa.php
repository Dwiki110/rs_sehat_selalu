<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM pemeriksaan ORDER BY tanggal DESC");
?>

<h2>Riwayat Diagnosa Dokter</h2>
<table border="1">
    <tr>
        <th>No</th>
        <th>Pasien</th>
        <th>Dokter</th>
        <th>Diagnosa</th>
        <th>Obat</th>
        <th>Tanggal</th>
    </tr>
    <?php
    $no = 1;
    while ($data = mysqli_fetch_assoc($query)) {
        echo "<tr>
                <td>$no</td>
                <td>{$data['pasien']}</td>
                <td>{$data['dokter']}</td>
                <td>{$data['diagnosa']}</td>
                <td>{$data['obat']}</td>
                <td>{$data['tanggal']}</td>
              </tr>";
        $no++;
    }
    ?>
</table>

<br><a href="admin_dashboard.php">Kembali ke Dashboard</a>
