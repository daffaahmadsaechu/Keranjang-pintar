<?php
// Mulai session
session_start();

include 'koneksi.php';

// Ambil data yang dikirimkan melalui AJAX
$belanjaan = isset($_POST['belanjaan']) ? $_POST['belanjaan'] : [];

if (!empty($belanjaan)) {
    foreach ($belanjaan as $item) {
        $kodebarang = $item['kode_barang'];
        $jumlahbeli = $item['jumlah_barang'];

        // Kurangi stok barang
        $update_query = "UPDATE barang SET Jumlah_Barang = Jumlah_Barang - $jumlahbeli WHERE Kode_Barang = '$kodebarang'";
        if (!mysqli_query($koneksi, $update_query)) {
            echo "Error updating record: " . mysqli_error($koneksi);
        }
    }
    echo "Stock updated successfully.";
} else {
    echo "Cart is empty.";
}

// Tutup koneksi
mysqli_close($koneksi);

?>
