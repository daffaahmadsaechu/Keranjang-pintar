<?php
// Informasi untuk koneksi ke database
$servername = "localhost"; // Nama server database
$username = "root"; // Username database
$password = ""; // Password database
$dbname = "iot_kasir"; // Nama database

// Buat koneksi
$koneksi = mysqli_connect($servername, $username, $password, $dbname);

// Periksa koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
