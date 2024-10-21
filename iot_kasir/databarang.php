<?php
// Mulai session
session_start();

// Periksa apakah session 'username' sudah diset
if(!isset($_SESSION['username'])) {
    // Jika tidak, redirect pengguna ke halaman login
    header("Location: login.php");
    exit(); // Pastikan keluar dari skrip
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Smart Shopping</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link rel="stylesheet" href="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">

</head>

<script>
$(document).ready(function () {
    // Fungsi untuk menambah barang
    function tambahBarang() {
        var kodebarang = $('#kodebarang').val();
        var namabarang = $('#namabarang').val();
        var hargabarang = $('#hargabarang').val();
        var jumlahbarang = $('#jumlahbarang').val();
        var inputwaktu = $('#inputwaktu').val();

        // Kirim data ke server menggunakan AJAX
        $.ajax({
            url: 'tambah_barang.php',
            method: 'POST',
            data: {
                kodebarang: kodebarang,
                namabarang: namabarang,
                hargabarang: hargabarang,
                jumlahbarang: jumlahbarang,
                inputwaktu: inputwaktu
            },
            success: function (response) {
                // Tampilkan pesan sukses atau error
                Swal.fire({
                    title: 'Response',
                    text: response,
                    icon: 'success' // atau 'error' tergantung respons Anda
                }).then(() => {
                    // Refresh halaman untuk memperbarui data tabel
                    location.reload();
                });
            }
        });
    }

    // Fungsi untuk mengedit barang
    function editBarang(id) {
        var kodebarang = $('#kodebarang').val();
        var namabarang = $('#namabarang').val();
        var hargabarang = $('#hargabarang').val();
        var jumlahbarang = $('#jumlahbarang').val();
        var inputwaktu = $('#inputwaktu').val();

        // Kirim data ke server menggunakan AJAX
        $.ajax({
            url: 'edit_barang.php',
            method: 'POST',
            data: {
                kodebarang: kodebarang,
                namabarang: namabarang,
                hargabarang: hargabarang,
                jumlahbarang: jumlahbarang,
                inputwaktu: inputwaktu,
                id: id // Sertakan ID barang untuk operasi edit
            },
            success: function (response) {
                // Tampilkan pesan sukses atau error
                Swal.fire({
                    title: 'Response',
                    text: response,
                    icon: 'success' // atau 'error' tergantung respons Anda
                }).then(() => {
                    // Refresh halaman untuk memperbarui data tabel
                    location.reload();
                });
            }
        });
    }

    // Event listener untuk tombol "Tambah" atau "Simpan"
    $('#btnTambah').click(function () {
        var id = $(this).attr('data-id'); // Dapatkan ID barang dari atribut data-id tombol
        if (id !== undefined) {
            // Jika ID ada (mode edit), panggil fungsi editBarang
            editBarang(id);
        } else {
            // Jika ID tidak ada (mode tambah), panggil fungsi tambahBarang
            tambahBarang();
        }
    });

    // Event listener untuk tombol "Edit"
    $(document).on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        // Kirim request AJAX untuk mendapatkan data barang berdasarkan ID
        $.ajax({
            url: 'get_barang.php',
            method: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (response) {
                // Set nilai form berdasarkan data yang diterima
                $('#kodebarang').val(response.Kode_Barang);
                $('#namabarang').val(response.Nama_Barang);
                $('#hargabarang').val(response.Harga_Barang);
                $('#jumlahbarang').val(response.Jumlah_Barang);
                $('#inputwaktu').val(response.Tanggal_Input);
                // Ubah teks tombol "Tambah" menjadi "Simpan"
                $('#btnTambah').text('Simpan');
                // Set atribut data-id pada tombol "Tambah" untuk mengetahui bahwa ini adalah mode edit
                $('#btnTambah').attr('data-id', id);
            }
        });
    });

    // Event listener untuk tombol "Hapus"
    $(document).on('click', '.btn-hapus', function () {
        // Konfirmasi pengguna sebelum menghapus barang
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                var id = $(this).data('id');
                // Kirim request AJAX untuk menghapus data barang berdasarkan ID
                $.ajax({
                    url: 'hapus_barang.php',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        // Tampilkan pesan sukses atau error
                        Swal.fire({
                            title: 'Response',
                            text: response,
                            icon: 'success' // atau 'error' tergantung respons Anda
                        }).then(() => {
                            // Refresh halaman untuk memperbarui data tabel
                            location.reload();
                        });
                    }
                });
            }
        });
    });

});
</script>


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Daffa<sup>Mart</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Kasir</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="databarang.php">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Barang</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="laporan.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <hr class="sidebar-divider">

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo $_SESSION['username']; ?>
                                </span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="login.php" data-toggle="modal"
                                    data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-12 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-shopping-cart"></i> Tambah Barang
                                    </h5>

                                    <div class="dropdown no-arrow">
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="kodebarang" style="font-weight: bold;">Kode Barang</label>
                                            <input type="text" class="form-control" id="kodebarang" name="kodebarang" required>
                                        </div>
                                        <div class="col-sm-4 offset-sm-2 mb-4">
                                            <label for="namabarang" style="font-weight: bold;">Nama Barang</label>
                                            <input type="text" class="form-control" id="namabarang" name="namabarang" required>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="hargabarang" style="font-weight: bold;">Harga Barang</label>
                                            <input type="text" class="form-control" id="hargabarang" name="hargabarang" required>
                                        </div>
                                        <div class="col-sm-4 offset-sm-2 mb-4">
                                            <label for="inputwaktu" style="font-weight: bold;">Tanggal Input</label>
                                            <input type="datetime-local" class="form-control" id="inputwaktu"name="inputwaktu"
                                                placeholder="Masukkan tanggal input" style="width: 290px;">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="jumlahbarang" style="font-weight: bold;">Jumlah Barang</label>
                                            <input type="text" class="form-control" id="jumlahbarang" name="jumlahbarang" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <button type="button" id="btnTambah" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Tambah Barang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-xl-12 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h5 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-fw fa-table"></i> Data Barang
                                    </h5>

                                    <div class="dropdown no-arrow">
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-purple" id="dataTable" width="100%"
                                            cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Harga Barang</th>
                                                    <th>Jumlah Barang</th>
                                                    <th>Tanggal Input</th>
                                                    <th>Opsi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                include 'koneksi.php';
                                                // Lakukan query untuk mengambil data barang
                                                $sql = "SELECT * FROM barang";
                                                $result = mysqli_query($koneksi, $sql);
                                                // Periksa apakah ada data yang ditemukan
                                                if (mysqli_num_rows($result) > 0) {
                                                    // Jika ada, tampilkan data dalam tabel
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>";
                                                        echo "<td>" . $row['NO'] . "</td>";
                                                        echo "<td>" . $row['Kode_Barang'] . "</td>";
                                                        echo "<td>" . $row['Nama_Barang'] . "</td>";
                                                        echo "<td>" . $row['Harga_Barang'] . "</td>";
                                                        echo "<td>" . $row['Jumlah_Barang'] . "</td>";
                                                        echo "<td>" . $row['Tanggal_Input'] . "</td>";
                                                        echo "<td>";
                                                        echo "<button class='btn btn-primary btn-edit' data-id='" . $row['NO'] . "'>Edit</button>";
                                                        echo "<button class='btn btn-danger btn-hapus' data-id='" . $row['NO'] . "'>Hapus</button>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    // Jika tidak ada data, tampilkan pesan
                                                    echo "<tr><td colspan='5'>Tidak ada data barang.</td></tr>";
                                                }
                                                // Tutup koneksi
                                                mysqli_close($koneksi);
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- End of Main Content -->
                    
                    <!-- Footer -->
                    <footer class="sticky-footer bg-white">
                        <div class="container my-auto">
                            <div class="copyright text-center my-auto">
                                <span>Dipersembahkan &copy; Politeknik Negeri Malang</span>
                            </div>
                        </div>
                    </footer>
                    <!-- End of Footer -->

                </div>
                <!-- End of Content Wrapper -->

            </div>
            <!-- End of Page Wrapper -->

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <!-- Logout Modal-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Select "Logout" below if you are ready to end your current session.
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" href="login.php">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap core JavaScript-->
            <script src="vendor/jquery/jquery.min.js"></script>
            <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="js/sb-admin-2.min.js"></script>

            <!-- Page level plugins -->
            <script src="vendor/chart.js/Chart.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="js/demo/chart-area-demo.js"></script>
            <script src="js/demo/chart-pie-demo.js"></script>

            <!-- DataTables JavaScript -->
            <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

            <script>
                $(document).ready(function () {
                    $('#dataTable').DataTable();
                });
            </script>

</body>

</html>
