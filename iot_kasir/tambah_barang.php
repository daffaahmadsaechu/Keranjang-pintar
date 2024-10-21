<?php
// Mulai session
session_start();

// Periksa apakah session 'username' sudah diset
if (!isset($_SESSION['username'])) {
    // Jika tidak, redirect pengguna ke halaman login
    header("Location: login.php");
    exit(); // Pastikan keluar dari skrip
}

// Pastikan kode barang, nama barang, harga barang, dan tanggal input tersedia
if (isset($_POST['kodebarang']) && isset($_POST['namabarang']) && isset($_POST['hargabarang']) && isset($_POST['jumlahbarang']) && isset($_POST['inputwaktu'])) {
    // Koneksikan ke database
    include 'koneksi.php';

    // Ambil data yang dikirimkan melalui AJAX
    $kodebarang = $_POST['kodebarang'];
    $namabarang = $_POST['namabarang'];
    $hargabarang = $_POST['hargabarang'];
    $jumlahbarang = $_POST['jumlahbarang'];
    $inputwaktu = $_POST['inputwaktu'];

    // Dapatkan nomor terakhir dan tambahkan 1 untuk nomor barang baru
    $result = mysqli_query($koneksi, "SELECT MAX(NO) as max_no FROM barang");
    $row = mysqli_fetch_assoc($result);
    $no = $row['max_no'] + 1;

    // Periksa apakah kode barang sudah ada dalam database
    $check_query = "SELECT * FROM barang WHERE Kode_Barang = '$kodebarang'";
    $check_result = mysqli_query($koneksi, $check_query);

    // Jika kode barang sudah ada, kirim pesan error
    if (mysqli_num_rows($check_result) > 0) {
        echo "Kode Barang sudah ada dalam database.";
    } else {
        // Jika kode barang belum ada, tambahkan barang baru ke dalam database
        $insert_query = "INSERT INTO barang (NO, Kode_Barang, Nama_Barang, Harga_Barang, Jumlah_Barang, Tanggal_Input) VALUES ('$no', '$kodebarang', '$namabarang', '$hargabarang', '$jumlahbarang', '$inputwaktu')";
        if (mysqli_query($koneksi, $insert_query)) {
            echo "Barang berhasil ditambahkan.";
        } else {
            echo "Error: " . $insert_query . "<br>" . mysqli_error($koneksi);
        }
    }

    // Tutup koneksi
    mysqli_close($koneksi);
} else {
    // Jika tidak ada data yang dikirimkan melalui AJAX
    echo "Data tidak lengkap.";
}
?>
