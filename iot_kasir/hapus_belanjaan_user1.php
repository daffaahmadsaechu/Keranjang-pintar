<?php
// Mulai session
session_start();

// Periksa apakah session 'username' sudah diset
if (!isset($_SESSION['username'])) {
    // Jika tidak, redirect pengguna ke halaman login
    header("Location: login.php");
    exit(); // Pastikan keluar dari skrip
}

include 'koneksi.php';

// Periksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Hapus semua data belanjaan user 1
$sql = "DELETE FROM belanjaan_user1";

if ($koneksi->query($sql) === TRUE) {
    echo "Belanjaan berhasil dihapus";
} else {
    echo "Error: " . $koneksi->error;
}

$koneksi->close();
?>
