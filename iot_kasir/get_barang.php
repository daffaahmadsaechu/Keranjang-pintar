<?php
// Koneksi ke database
include 'koneksi.php';

// Periksa apakah parameter ID terkirim
if (isset($_POST['id'])) {
    // Escape input untuk mencegah SQL Injection
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);

    // Query untuk mengambil data barang berdasarkan ID
    $sql = "SELECT * FROM barang WHERE NO = '$id'";
    $result = mysqli_query($koneksi, $sql);

    // Periksa apakah ada data yang ditemukan
    if (mysqli_num_rows($result) > 0) {
        // Ambil data barang
        $barang = mysqli_fetch_assoc($result);
        // Mengembalikan data dalam format JSON
        echo json_encode($barang);
    } else {
        // Jika tidak ada data ditemukan, kembalikan pesan error
        echo json_encode(array('error' => 'Data barang tidak ditemukan'));
    }
} else {
    // Jika parameter ID tidak terkirim, kembalikan pesan error
    echo json_encode(array('error' => 'Parameter ID tidak ditemukan'));
}

// Tutup koneksi
mysqli_close($koneksi);
?>
