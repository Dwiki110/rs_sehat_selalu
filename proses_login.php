<?php
include 'koneksi.php';
session_start();

$username = $_POST['username'];
$password = md5($_POST['password']);

$query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if ($data) {
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];

    if ($data['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: pasien_dashboard.php");
    }
} else {
    echo "Login gagal. <a href='index.html'>Coba lagi</a>";
}
?>