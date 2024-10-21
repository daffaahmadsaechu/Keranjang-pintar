<?php
// Mulai session
session_start();

// Periksa apakah session 'username' sudah diset
if (!isset($_SESSION['username'])) {
    // Jika tidak, redirect pengguna ke halaman login
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Hapus barang dari database
    $delete_sql = "DELETE FROM laporan WHERE NO = $id";
    if (mysqli_query($koneksi, $delete_sql)) {
        // Perbarui nomor setelah penghapusan
        $update_sql = "SET @num := 0; UPDATE laporan SET NO = @num := (@num+1);";
        mysqli_multi_query($koneksi, $update_sql);

        // Kembalikan pesan sukses
        echo "Barang berhasil dihapus dan nomor diperbarui.";
    } else {
        // Kembalikan pesan error
        echo "Error: " . $delete_sql . "<br>" . mysqli_error($koneksi);
    }
}

mysqli_close($koneksi);
?>
