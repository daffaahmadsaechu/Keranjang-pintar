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
    <script type="text/javascript" src="assets/js/jquery-3.4.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.4.1/jspdf.debug.js"></script>
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="SB-Mid-client-Ll_hNUyXQfVw6LFT"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">



</head>     

<style type="text/css">

#listBelanjaan1 li {
    display: flex;
}

#listBelanjaan1 li span {
    min-width: 150px; /* Sesuaikan lebar minimum sesuai kebutuhan */
}

.struk {
    border: 1px solid #000;
    padding: 20px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    width: 300px;
}

.info {
    margin-bottom: 10px;
}

table {
    width: 100%;
}

th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.total {
    text-align: right;
}

.terima-kasih {
    text-align: center;
    margin-top: 20px;
}

	
	@media all and (max-width:40em){
		body{
			font-size:0.8em;
		}
		div.testGroup{
			display:block;
			margin: 0 auto;
		}
	}
    
</style>
<script type="text/javascript"> 
   $(document).ready(function(){
    $("#btnBelanjaUser1").click(function(){
        $("#cardBelanjaUser1").show();

        $.ajax({
            url: "get_belanjaan_user1.php",
            type: "GET",
            dataType: "json",
            success: function(response){
                var belanjaan = response;
                var belanjaanHTML = "<ul id='listBelanjaan1' style='padding-left: 20px;'>";
                var totalBelanja = 0;

                $.each(belanjaan, function(index, item){
                    belanjaanHTML += "<li style='color: black;'><span>" + item.nama_barang + "</span>&emsp;x " + item.jumlah_barang + "&emsp;Rp " + item.harga_barang + "</li>";
                    totalBelanja += (parseInt(item.jumlah_barang) * parseFloat(item.harga_barang));
                });
                belanjaanHTML += "</ul>";

                $("#listBelanjaan1").html(belanjaanHTML);
                $("#totalBelanjaan").text("Total: Rp " + totalBelanja.toFixed(2)).css("color", "black");

                $("#btnBayar").click(function() {
                    var bayar = parseFloat($("#inputBayar").val());
                    var kembali = bayar - totalBelanja;

                    Swal.fire({
                        title: 'Pilih Metode Pembayaran',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Cash',
                        denyButtonText: 'Cashless',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var belanjaanJSON = JSON.stringify(belanjaan);
                            // var totalBelanja = $("#totalBelanjaan").text().replace("Total: Rp ", "");
                            Swal.fire({
                                title: 'Pembayaran dengan Cash berhasil!',
                                text: 'Kembali: Rp ' + kembali.toFixed(2),
                                icon: 'success'
                            }).then(() => {
                                tampilkanStruk(belanjaan, totalBelanja, bayar, kembali);
                                $("#divStruk").show();
                                updateStock(belanjaan);
                                simpanLaporan(belanjaanJSON, totalBelanja);
                                resetBelanjaan();
                            });
                        } else if (result.isDenied) {
                            var itemDetails = belanjaan.map(item => ({
                                name: item.nama_barang,
                                price: item.harga_barang,
                                quantity: item.jumlah_barang
                            }));

                            var transactionDetails = {
                                gross_amount: totalBelanja
                            };

                            var requestBody = {
                                item_details: itemDetails,
                                transaction_details: transactionDetails
                            };

                            console.log("Request Body: ", requestBody);

                            fetch('http://127.0.0.1:3000/process-cashless-payment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(requestBody)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Terjadi kesalahan dalam proses pembayaran');
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log("Response Data: ", data);
                                if (data.token) {
                                    // Menggunakan Snap.js untuk mengarahkan pengguna ke halaman pembayaran Midtrans
                                    snap.pay(data.token, {
                                        onSuccess: function(result) {
                                            console.log('Payment success:', result);
                                            Swal.fire('Pembayaran berhasil!', '', 'success')
                                            .then(() => {
                                                tampilkanStruk(belanjaan, totalBelanja, bayar, kembali);
                                                $("#divStruk").show();
                                                updateStock(belanjaan);
                                                resetBelanjaan();
                                                simpanLaporan(belanjaanJSON, totalBelanja);
                                            });
                                        },
                                        onPending: function(result) {
                                            console.log('Payment pending:', result);
                                            Swal.fire('Pembayaran pending', '', 'info');
                                        },
                                        onError: function(result) {
                                            console.log('Payment error:', result);
                                            Swal.fire('Pembayaran gagal', '', 'error');
                                        },
                                        onClose: function() {
                                            console.log('Payment popup closed');
                                        }
                                    });
                                } else {
                                    throw new Error('Token transaksi tidak ditemukan dalam respons');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                Swal.fire('Oops...', error.message, 'error');
                            });
                        }
                    });
                });
            }
        });
    });

    function tampilkanStruk(detailBelanja, total, bayar, kembali) {
    var today = new Date();
    var tanggal = today.getDate() + '/' + (today.getMonth() + 1) + '/' + today.getFullYear();
    $("#tanggal").text("Tanggal: " + tanggal);

    var detailHTML = "";
    $.each(detailBelanja, function(index, item){
        detailHTML += "<tr>";
        detailHTML += "<td>" + item.nama_barang + "</td>";
        detailHTML += "<td>x " + item.jumlah_barang + "</td>";
        var hargaBarang = typeof item.harga_barang === 'number' ? item.harga_barang.toFixed(2) : item.harga_barang;
        detailHTML += "<td>Rp " + hargaBarang + "</td>";
        detailHTML += "</tr>";
    });
    $("#detailStruk").html(detailHTML);

    // Periksa apakah total adalah angka sebelum menggunakan toFixed()
    if (typeof total === 'number') {
        $("#total").text("Total: Rp " + total.toFixed(2));
    } else {
        $("#total").text("Total: Rp " + total);
    }

    $("#bayar").text("Bayar: Rp " + bayar.toFixed(2));
    $("#kembali2").text("Kembali: Rp " + kembali.toFixed(2));
}

function resetBelanjaan() {
    $.ajax({
        url: 'hapus_belanjaan_user1.php',
        method: 'POST',
        success: function(response) {
            console.log('Belanjaan berhasil direset:', response);
            $("#listBelanjaan1").html(''); // Hapus tampilan belanjaan
            $("#totalBelanjaan").text("Total: Rp 0.00"); // Reset total belanjaan
            $("#inputBayar").val(''); // Kosongkan input pembayaran
        },
        error: function(xhr, status, error) {
            console.error('Gagal mereset belanjaan. Status:', status, 'Error:', error);
        }
    });
}


    function updateStock(belanjaan) {
        console.log("Memperbarui stok barang.");
        $.ajax({
            url: 'update_stock.php',
            method: 'POST',
            data: { belanjaan},
            success: function(response) {
                console.log('Stok berhasil diperbarui:', response);
            },
            error: function(xhr, status, error) {
                console.error('Gagal memperbarui stok. Status:', status, 'Error:', error);
            }
            
        });
    }

    function simpanLaporan(belanjaanJSON, totalBelanja) {
    $.ajax({
        url: "simpan_laporan.php",
        type: "POST",
        data: {
            belanjaan: belanjaanJSON,
            total: totalBelanja
        },
        success: function(response) {
            console.log("Laporan disimpan:", response);
        },
        error: function(xhr, status, error) {
            console.error('Gagal menyimpan laporan. Status:', status, 'Error:', error);
        }
    });
}


    $("#btnCetakPDF").click(function(){
        var content = $("#strukPembayaran").html();

        var printWindow = window.open('', '_blank', 'width=600,height=600');

        printWindow.document.write('<html><head><title>Struk Pembayaran</title>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    });

    $("#btnBackBelanjaUser1").click(function() {
        $("#cardBelanjaUser1").hide();
        $("#divStruk").hide();
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
            <li class="nav-item">
       

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                <i class="fas fa-money-bill-wave"></i>
                    <span>Kasir</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

             <li class="nav-item">
                <a class="nav-link" href="databarang.php">
                <i class="fas fa-shopping-basket"></i>
                    <span>Barang</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="laporan.php">
                <i class="fas fa-file-alt"></i>
                    <span>Laporan</span></a>
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
                                <a class="dropdown-item" href="login.php" data-toggle="modal" data-target="#logoutModal">
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
                                    <h5 class="m-0 font-weight-bold text-primary">Monitoring</h5>
                                    <div class="dropdown no-arrow">
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body d-flex justify-content-center">
                                <div class="mb-4">
                                    <img src="img/icon_cowok.png" style="width: 150px; margin-right: 20px;">
                                    <button id="btnBelanjaUser1" class="btn btn-primary mt-2"><i class="fas fa-shopping-cart"></i> Belanja User 1</button>
                                </div>
                                <div>   
                                    <img src="img/icon_cewek.png" style="width: 150px; margin-left: 20px;">
                                    <button class="btn btn-primary mt-2"><i class="fas fa-shopping-cart"></i> Belanja User 2</button>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body "Belanja User 1" -->
                        <div class="row" id="cardBelanjaUser1" style="display: none;">
                        <div class="col-xl-5 col-lg-5">
    <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h5 class="m-0 font-weight-bold text-primary">Belanja User 1</h5>
            <button id="btnBackBelanjaUser1" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <!-- Card Body -->
        <div class="card-body">
            <div id="listBelanjaan1"></div>
            <hr>
            <div id="totalBelanjaan">Total: Rp 0.00</div> <!-- Menampilkan total belanja -->
            <div class="form-group">
                <label for="inputBayar" style="color:black">Bayar:</label>
                <input type="text" class="form-control" id="inputBayar" placeholder="Masukkan jumlah pembayaran">
            </div>
            <button id="btnBayar" class="btn btn-primary mt-3">Bayar</button> 
        </div>
    </div>
</div>

<!-- Struk -->
<div class="col-xl-5 col-lg-5">
    <div class="card shadow mb-4" id="divStruk" style="display: none; max-width: 400px;">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Struk Pembayaran</h6>
            <button id="btnCetakPDF" class="btn btn-primary btn-sm">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>
        <div class="card-body">
            <div id="strukPembayaran" class="struk">
                <h2>DAFFA MART</h2>
                <p>Jalan Semanggi Barat No 18 B</p>
                <p>Telp 0895331108251</p>
                <hr>
                <div class="info">
                    <p>Kasir: ADMIN</p>
                    <p><span id="tanggal"></span></p>
                </div>
                <hr>
                <table class="table table-bordered" id="detailStruk">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Detail barang akan ditampilkan di sini -->
                    </tbody>
                </table>
                <hr>
                <div class="total">
                    <p id="total"></p>
                    <p id="bayar"></p>
                    <p id="kembali2"></p>
                </div>
                <hr>
                <p class="terima-kasih">Terimakasih Telah Berbelanja</p>
            </div>
        </div>
    </div>
</div>


</div>




                    

            </div>
            <!-- End of Main Content -->
            <br><br><br><br><br><br><br><br><br><br>
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
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

</body>

</html>