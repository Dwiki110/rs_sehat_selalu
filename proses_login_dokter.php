<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$login = mysqli_query($conn, "SELECT * FROM dokter WHERE username='$username' AND password='$password'");
$data = mysqli_fetch_assoc($login);

if ($data) {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'dokter';
    header("Location: dokter_dashboard.php");
} else {
    echo "Login gagal. <a href='dokter_login.html'>Coba lagi</a>";
}
?>
