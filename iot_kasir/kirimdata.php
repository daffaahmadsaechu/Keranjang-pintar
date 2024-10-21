<?php
include 'koneksi.php';

$response = ["status" => "error", "message" => "Unknown error occurred"];

if (isset($_GET['kode_barang']) && isset($_GET['rfid'])) {
    $kode_barang = $_GET['kode_barang'];
    $rfid = $_GET['rfid'];

    // Ambil nama dan harga barang dari tabel barang
    $sql = "SELECT nama_barang, harga_barang FROM barang WHERE kode_barang = ?";
    $stmt = $koneksi->prepare($sql);
    if ($stmt === false) {
        die("Error: " . $koneksi->error);
    }
    $stmt->bind_param("s", $kode_barang);
    $stmt->execute();
    $stmt->bind_result($nama_barang, $harga_barang);
    $stmt->fetch();
    $stmt->close();

    if ($nama_barang) {
        // Cek apakah barang sudah ada di tabel belanjaan_user1
        $sql = "SELECT jumlah_barang FROM belanjaan_user1 WHERE kode_barang = ?";
        $stmt = $koneksi->prepare($sql);
        if ($stmt === false) {
            die("Error: " . $koneksi->error);
        }
        $stmt->bind_param("s", $kode_barang);
        $stmt->execute();
        $stmt->bind_result($jumlah_barang);
        $stmt->fetch();
        $stmt->close();

        if ($jumlah_barang) {
            // Barang sudah ada, update jumlah
            $jumlah_barang += 1;

            $sql = "UPDATE belanjaan_user1 SET jumlah_barang = ? WHERE kode_barang = ?";
            $stmt = $koneksi->prepare($sql);
            if ($stmt === false) {
                die("Error: " . $koneksi->error);
            }
            $stmt->bind_param("is", $jumlah_barang, $kode_barang);
        } else {
            // Barang belum ada, insert data baru
            $jumlah_barang = 1;

            $sql = "INSERT INTO belanjaan_user1 (kode_barang, nama_barang, harga_barang, jumlah_barang) VALUES (?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            if ($stmt === false) {
                die("Error: " . $koneksi->error);
            }
            $stmt->bind_param("ssdi", $kode_barang, $nama_barang, $harga_barang, $jumlah_barang);
        }

        if ($stmt->execute()) {
            $response = ["status" => "success", "message" => "Data berhasil disimpan", "rfid" => $rfid];
        } else {
            $response = ["status" => "error", "message" => "Gagal menyimpan data: " . $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ["status" => "error", "message" => "Nama barang tidak ditemukan"];
    }
} else {
    $response = ["status" => "error", "message" => "Kode barang atau RFID tidak diterima"];
}

$koneksi->close();

echo json_encode($response);
?>
