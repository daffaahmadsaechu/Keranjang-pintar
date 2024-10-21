<?php
// Koneksi ke database
include 'koneksi.php';

// Pastikan parameter yang diperlukan telah terkirim
if (isset($_POST['kodebarang']) && isset($_POST['namabarang']) && isset($_POST['hargabarang']) && isset($_POST['jumlahbarang']) && isset($_POST['inputwaktu'])) {
    // Escape input untuk mencegah SQL Injection
    $kodebarang = mysqli_real_escape_string($koneksi, $_POST['kodebarang']);
    $namabarang = mysqli_real_escape_string($koneksi, $_POST['namabarang']);
    $hargabarang = mysqli_real_escape_string($koneksi, $_POST['hargabarang']);
    $jumlahbarang = mysqli_real_escape_string($koneksi, $_POST['jumlahbarang']);
    $inputwaktu = mysqli_real_escape_string($koneksi, $_POST['inputwaktu']);
    
    // Mendapatkan ID barang dari data POST
    $id = $_POST['id'];

    // Query untuk mengupdate data barang berdasarkan ID
    $sql = "UPDATE barang SET Kode_Barang='$kodebarang', Nama_Barang='$namabarang', Harga_Barang='$hargabarang', Jumlah_Barang='$jumlahbarang', Tanggal_Input='$inputwaktu' WHERE NO='$id'";

    if (mysqli_query($koneksi, $sql)) {
        // Jika update berhasil, kirimkan pesan sukses
        echo "Data barang berhasil diupdate";
    } else {
        // Jika terjadi kesalahan, kirimkan pesan error
        echo "Error: " . $sql . "<br>" . mysqli_error($koneksi);
    }
} else {
    // Jika parameter yang diperlukan tidak terkirim, kirimkan pesan error
    echo "Parameter yang diperlukan tidak terkirim";
}

// Tutup koneksi
mysqli_close($koneksi);
?>
