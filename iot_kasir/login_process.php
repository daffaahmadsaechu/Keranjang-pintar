<?php

session_start();

include 'koneksi.php';


// Periksa koneksi
if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

// Ambil nilai yang diposting dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// Lakukan escapte karakter khusus untuk mencegah SQL Injection
$username = mysqli_real_escape_string($koneksi, $username);
$password = mysqli_real_escape_string($koneksi, $password);

// Buat dan jalankan query untuk memeriksa username dan password
$sql = "SELECT * FROM user WHERE username='$username' AND password='$password'";
$result = $koneksi->query($sql);

// Periksa apakah hasil query mengembalikan baris yang sesuai
if ($result->num_rows > 0) {
    // Login berhasil
    $_SESSION['username'] = $username;
    header("Location: index.php");
    exit();
} else {
    // Login gagal
    header("Location: login.php?login=failed");
    exit();
}

mysqli_close($koneksi);
?>
