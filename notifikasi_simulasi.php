<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}
?>

<h2>Simulasi Notifikasi Email</h2>
<p>Halo <?php echo $_SESSION['username']; ?>,</p>
<p>Berikut notifikasi yang masuk ke sistem Anda:</p>
<ul>
    <li>Janji temu Anda sudah dijadwalkan.</li>
    <li>Hasil pemeriksaan terakhir sudah tersedia.</li>
    <li>Tagihan pembayaran sudah diterbitkan.</li>
</ul>
<a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'pasien_dashboard.php'; ?>">Kembali ke Dashboard</a>