<?php
// Lakukan koneksi ke database
include 'koneksi.php';

// Ambil data dari request POST
$belanjaanJSON = $_POST['belanjaan'];
$totalBelanja = $_POST['total'];

// Konversi JSON belanjaan menjadi array PHP
$belanjaan = json_decode($belanjaanJSON, true);

// Buat query SQL untuk menyimpan data ke tabel laporan
$query = "INSERT INTO laporan (Kode_Barang, Nama_Barang, Jumlah_Barang, Harga_Barang, `Sub-Total`) VALUES ";

// Loop untuk menambahkan setiap item belanjaan ke dalam query
foreach ($belanjaan as $item) {
    $kode_barang = $item['kode_barang'];
    $nama_barang = $item['nama_barang'];
    $jumlah_barang = $item['jumlah_barang'];
    $harga_barang = $item['harga_barang'];

    // Hitung sub-total
    $subtotal = $jumlah_barang * $harga_barang;

    // Tambahkan setiap item belanjaan ke dalam query
    $query .= "('$kode_barang', '$nama_barang', $jumlah_barang, $harga_barang, $subtotal), ";
}

// Hapus koma dan spasi ekstra di akhir query
$query = rtrim($query, ", ");

// Check if the table is empty
$checkQuery = "SELECT COUNT(*) as total FROM laporan";
$result = mysqli_query($koneksi, $checkQuery);
$row = mysqli_fetch_assoc($result);
$totalRows = $row['total'];

// Jalankan query
if ($totalRows == 0) {
    // Setel nilai auto_increment ke nomor 1 jika tabel kosong
    $nextNo = 1;
    $queryResetAutoIncrement = "ALTER TABLE laporan AUTO_INCREMENT = $nextNo";
    mysqli_query($koneksi, $queryResetAutoIncrement);
}

if (mysqli_query($koneksi, $query)) {
    echo "Laporan berhasil disimpan";
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($koneksi);
}

// Tutup koneksi database
mysqli_close($koneksi);
?>
