<?php
session_start();
if ($_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pasien</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('rumah sakit.jpg'); /* Ganti dengan lokasi gambar */
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
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .menu a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h1>
    <div class="menu">
        <a href="pasien_booking.php">Booking Janji</a>
        <a href="pasien_riwayat.php">Riwayat Booking</a>
        <a href="logout.php" style="background-color: #dc3545;">Logout</a>
    </div>
</div>

</body>
</html>
