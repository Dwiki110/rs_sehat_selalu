<?php
include 'koneksi.php';

$username = $_POST['username'];
$password = md5($_POST['password']);
$role = 'pasien';

// Cek apakah username sudah digunakan
$cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
if (mysqli_num_rows($cek) > 0) {
    echo "Username sudah digunakan. <a href='daftar.html'>Coba lagi</a>";
} else {
    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        echo "Pendaftaran berhasil. <a href='index.html'>Login sekarang</a>";
    } else {
        echo "Pendaftaran gagal.";
    }
}
?>