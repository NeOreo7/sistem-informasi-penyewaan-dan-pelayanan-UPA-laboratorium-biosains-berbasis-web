<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Minishop - Free Bootstrap 4 Template by Colorlib</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">

    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">

    
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body class="goto-here">
		<div class="py-1 bg-black">
    	<div class="container">
    		<div class="row no-gutters d-flex align-items-start align-items-center px-md-0">
	    		<div class="col-lg-12 d-block">
		    		<div class="row d-flex">
		    			<div class="col-md pr-4 d-flex topper align-items-center">
					    	<div class="icon mr-2 d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
						    <span class="text">+ 1235 2355 98</span>
					    </div>
					    <div class="col-md pr-4 d-flex topper align-items-center">
					    	<div class="icon mr-2 d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
						    <span class="text">youremail@email.com</span>
					    </div>
					    <div class="col-md-5 pr-4 d-flex topper align-items-center text-lg-right">
						    <span class="text">3-5 Business days delivery &amp; Free Returns</span>
					    </div>
				    </div>
			    </div>
		    </div>
		  </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="index.php"><img src="images/logo-bioscience.png" alt="Bioscience Labs" style="height: 40px;"></a>
	      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>

	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
	          <li class="nav-item"><a href="shop.php" class="nav-link">Catalog</a></li>
	          <li class="nav-item active"><a href="pesanan.php" class="nav-link">Pesanan</a></li>
	          <?php if(isset($_SESSION['id_user'])): ?>
	            <li class="nav-item"><a href="login/logout.php" class="nav-link" onclick="return confirm('Apakah Anda yakin ingin logout?');">Logout</a></li>
	          <?php else: ?>
	            <li class="nav-item"><a href="login/signin.php" class="nav-link">Login</a></li>
	          <?php endif; ?>
	          <li class="nav-item cta cta-colored"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>[<?= count($_SESSION['cart'] ?? []) ?>]</a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->


    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_6.jpg');">
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.php">Home</a></span> <span>Pesanan</span></p>
            <h1 class="mb-0 bread">Status Pesanan Saya</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section ftco-cart">
			<div class="container">
				<div class="row justify-content-center">
    			<div class="col-md-8 ftco-animate">
    				<div class="cart-list bg-light p-5" style="border-radius: 10px;">
                        <h2 class="mb-4">Invoice #ORD-002</h2>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Status:</span>
                            <span class="badge badge-success" style="padding: 10px; font-size: 14px;">Terkonfirmasi (Transaksi Selesai)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Tanggal Pemesanan:</span>
                            <span>12 Oktober 2026</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Waktu / Tanggal Pelaksanaan:</span>
                            <span>15 November 2026, 13:00 WIB</span>
                        </div>
                        
                        <hr>
                        <h4 class="mt-4 mb-3">Layanan yang Dipesan</h4>
	    				<table class="table">
						    <thead class="thead-primary">
						      <tr class="text-center">
						        <th>Layanan</th>
						        <th>Harga</th>
						        <th>Jumlah</th>
						        <th>Subtotal</th>
						      </tr>
						    </thead>
						    <tbody>
						      <tr class="text-center">
						        <td class="product-name">
						        	Real Time PCR
						        </td>
						        <td class="price">Rp 1.200.000</td>
						        <td class="quantity">1</td>
						        <td class="total">Rp 1.200.000</td>
						      </tr>
						    </tbody>
						  </table>
                          
                        <hr>
                        <div class="d-flex justify-content-between mt-4">
                            <h4 class="font-weight-bold">Total Pembayaran:</h4>
                            <h4 class="font-weight-bold text-success">Rp 1.200.000</h4>
                        </div>
                        <p class="text-success mt-3 font-weight-bold">Pembayaran Lunas dan Transaksi Selesai.</p>
                        
                        <div class="mt-5 text-center">
                            <button class="btn btn-primary py-3 px-4 mr-2" onclick="window.print()">Cetak Invoice</button>
                            <a href="pesanan.php" class="btn btn-secondary py-3 px-4">Kembali ke Pesanan</a>
                        </div>
					  </div>
    			</div>
    		</div>
			</div>
		</section>

    <footer class="ftco-footer ftco-section" style="background: #111827; padding: 50px 0;">
      <div class="container">
        <div class="row">
          <div class="col-md-8">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2" style="color: #fff; font-weight: 700; font-size: 22px; margin-bottom: 15px;">UPA Laboratorium Biosains</h2>
              <p style="color: rgba(255,255,255,0.7); line-height: 1.6; font-size: 14px; max-width: 450px;">
                Penyewaan peralatan laboratorium, ruangan, dan layanan pengujian biosains terpercaya.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2" style="color: #fff; font-weight: 700; font-size: 18px; margin-bottom: 20px;">Kontak</h2>
              <div class="block-23">
                <ul style="list-style: none; padding: 0;">
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-ios-pin" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">Jl.ssssss</span>
                  </li>
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-logo-whatsapp" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">085757575757</span>
                  </li>
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-ios-mail" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">biosains.polije@gmail.com</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    
  

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/jquery.animateNumber.min.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>
    
  </body>
</html>SESSION['cart'] ?? []) ?>]</a></li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->


    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_6.jpg');">
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.php">Home</a></span> <span>Pesanan</span></p>
            <h1 class="mb-0 bread">Status Pesanan Saya</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section ftco-cart">
			<div class="container">
				<div class="row justify-content-center">
    			<div class="col-md-8 ftco-animate">
    				<div class="cart-list bg-light p-5" style="border-radius: 10px;">
                        <h2 class="mb-4">Invoice #ORD-002</h2>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Status:</span>
                            <span class="badge badge-success" style="padding: 10px; font-size: 14px;">Terkonfirmasi (Transaksi Selesai)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Tanggal Pemesanan:</span>
                            <span>12 Oktober 2026</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="font-weight-bold">Waktu / Tanggal Pelaksanaan:</span>
                            <span>15 November 2026, 13:00 WIB</span>
                        </div>
                        
                        <hr>
                        <h4 class="mt-4 mb-3">Layanan yang Dipesan</h4>
	    				<table class="table">
						    <thead class="thead-primary">
						      <tr class="text-center">
						        <th>Layanan</th>
						        <th>Harga</th>
						        <th>Jumlah</th>
						        <th>Subtotal</th>
						      </tr>
						    </thead>
						    <tbody>
						      <tr class="text-center">
						        <td class="product-name">
						        	Real Time PCR
						        </td>
						        <td class="price">Rp 1.200.000</td>
						        <td class="quantity">1</td>
						        <td class="total">Rp 1.200.000</td>
						      </tr>
						    </tbody>
						  </table>
                          
                        <hr>
                        <div class="d-flex justify-content-between mt-4">
                            <h4 class="font-weight-bold">Total Pembayaran:</h4>
                            <h4 class="font-weight-bold text-success">Rp 1.200.000</h4>
                        </div>
                        <p class="text-success mt-3 font-weight-bold">Pembayaran Lunas dan Transaksi Selesai.</p>
                        
                        <div class="mt-5 text-center">
                            <button class="btn btn-primary py-3 px-4 mr-2" onclick="window.print()">Cetak Invoice</button>
                            <a href="pesanan.php" class="btn btn-secondary py-3 px-4">Kembali ke Pesanan</a>
                        </div>
					  </div>
    			</div>
    		</div>
			</div>
		</section>

    <footer class="ftco-footer ftco-section" style="background: #111827; padding: 50px 0;">
      <div class="container">
        <div class="row">
          <div class="col-md-8">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2" style="color: #fff; font-weight: 700; font-size: 22px; margin-bottom: 15px;">UPA Laboratorium Biosains</h2>
              <p style="color: rgba(255,255,255,0.7); line-height: 1.6; font-size: 14px; max-width: 450px;">
                Penyewaan peralatan laboratorium, ruangan, dan layanan pengujian biosains terpercaya.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2" style="color: #fff; font-weight: 700; font-size: 18px; margin-bottom: 20px;">Kontak</h2>
              <div class="block-23">
                <ul style="list-style: none; padding: 0;">
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-ios-pin" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">Jl.ssssss</span>
                  </li>
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-logo-whatsapp" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">085757575757</span>
                  </li>
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-ios-mail" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">biosains.polije@gmail.com</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
    
  

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/jquery.animateNumber.min.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>
    
  </body>
</html>
