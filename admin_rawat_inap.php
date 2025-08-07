<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Proses setujui/tolak permintaan rawat inap
if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi === 'setujui') {
        $status = 'Disetujui';
    } elseif ($aksi === 'tolak') {
        $status = 'Ditolak';
    }

    mysqli_query($conn, "UPDATE rawat_inap SET status = '$status' WHERE id = $id");
    header("Location: admin_rawat_inap.php");
    exit();
}

// Tampilkan semua permintaan rawat inap
$result = mysqli_query($conn, "SELECT * FROM rawat_inap ORDER BY tanggal DESC");
?>

<h2>Daftar Permintaan Rawat Inap</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Pasien</th>
        <th>Dokter</th>
        <th>Alasan</th>
        <th>Status</th>
        <th>Tanggal</th>
        <th>Aksi</th>
    </tr>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$no}</td>
                <td>{$row['pasien']}</td>
                <td>{$row['dokter']}</td>
                <td>{$row['alasan']}</td>
                <td>{$row['status']}</td>
                <td>{$row['tanggal']}</td>
                <td>";

        if ($row['status'] === 'Menunggu') {
            echo "<a href='admin_rawat_inap.php?id={$row['id']}&aksi=setujui'>Setujui</a> |
                  <a href='admin_rawat_inap.php?id={$row['id']}&aksi=tolak' onclick=\"return confirm('Yakin ingin menolak?')\">Tolak</a>";
        } else {
            echo "-";
        }

        echo "</td></tr>";
        $no++;
    }
    ?>
</table>

<br>
<a href="admin_dashboard.php">Kembali ke Dashboard</a>
