<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('rumah sakit.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            font-family: Arial, sans-serif;
        }

        .dashboard-container {
            background-color: rgba(255, 255, 255, 0.9);
            width: 80%;
            max-width: 600px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu a {
            display: block;
            padding: 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .menu a:hover {
            background-color: #1e7e34;
        }

        .logout {
            background-color: #dc3545 !important;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Selamat Datang Admin, <?php echo $_SESSION['username']; ?>!</h1>
    <div class="menu">
        <a href="admin_kelola_booking.php">Kelola Booking</a>
        <a href="admin_pembayaran.php">Pembayaran</a>
        <a href="admin_rawat_inap.php">Rawat Inap</a>
        <a href="admin_riwayat_diagnosa.php">Riwayat Diagnosa</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

</body>
</html>
