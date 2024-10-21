<?php
include 'koneksi.php';

// Query untuk mengambil daftar belanjaan user 1 dari database
$sql = "SELECT * FROM belanjaan_user1";
$result = mysqli_query($koneksi, $sql);

// Siapkan array untuk menampung hasil query
$belanjaan = [];

// Periksa apakah ada data
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        // Tambahkan data belanjaan ke dalam array
        $belanjaan[] = $row;
    }
}

// Tutup koneksi
mysqli_close($koneksi);

// Keluarkan data belanjaan dalam format JSON
echo json_encode($belanjaan);
?>
