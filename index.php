<?php
session_start();
// if (!isset($_SESSION['id_user'])) {
//     header("Location: login/signin.php");
//     exit;
// }
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

<?php if (isset($_SESSION['id_user']) && ($_SESSION['id_user']['id_role'] ?? 0) == 1): ?>
<a href="admin/index.php" style="
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    background: #1a202c; color: #fff;
    padding: 12px 20px; border-radius: 50px;
    text-decoration: none; font-size: 14px; font-weight: 600;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    display: flex; align-items: center; gap: 8px;
    transition: background 0.2s;" 
    onmouseover="this.style.background='#4299e1'" 
    onmouseout="this.style.background='#1a202c'">
    &#8592; Kembali ke Dashboard Admin
</a>
<?php endif; ?>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="index.php"><img src="images/logo-bioscience.png" alt="Bioscience Labs" style="height: 40px;"></a>
	      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>

	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item active"><a href="index.php" class="nav-link">Home</a></li>
	          <li class="nav-item"><a href="shop.php" class="nav-link">Catalog</a></li>
	          <li class="nav-item"><a href="pesanan.php" class="nav-link">Pesanan</a></li>
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

    <section id="home-section" class="hero">
		  <div class="home-slider owl-carousel">
	      <div class="slider-item js-fullheight">
	      	<div class="overlay"></div>
	        <div class="container-fluid p-0">
	          <div class="row d-md-flex no-gutters slider-text align-items-center justify-content-end" data-scrollax-parent="true">
	          	<img class="one-second order-md-last img-fluid" src="images/bg_index1.jpg" alt="">
		          <div class="one-forth d-flex align-items-center ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
		          	<div class="text">
		          		<div class="horizontal">
				            <h1 class="mb-4 mt-3">UPA LABORATORIUM BIOSAINS</h1>
				            <p class="mb-4">Laboratorium Biosains Politeknik Negeri Jember (POLIJE) adalah pusat riset dan layanan unggulan yang berdedikasi untuk kemajuan ilmu hayati terapan. Berada di bawah naungan institusi pendidikan vokasi terkemuka, kami menggabungkan kekuatan akademik dengan kebutuhan industri untuk menghadirkan solusi inovatif dan akurat.</p>
				            <p><a href="shop.php" class="btn-custom">Selengkapnya</a></p>
				          </div>
		            </div>
		          </div>
	        	</div>
	        </div>
	      </div>
	    </div>
    </section>

    <section class="ftco-section ftco-no-pt ftco-no-pb">
			<div class="container">
				<div class="row no-gutters ftco-services">
          <div class="col-lg-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services p-4 py-md-5">
              <div class="icon d-flex justify-content-center align-items-center mb-4">
            		<span class="icon-calendar"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">8+ Tahun pengalaman</h3>
              </div>
            </div>      
          </div>
          <div class="col-lg-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services p-4 py-md-5">
              <div class="icon d-flex justify-content-center align-items-center mb-4">
            		<span class="icon-flask tube"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">100+ Uji sampel perbulan</h3>
              </div>
            </div>    
          </div>
          <div class="col-lg-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services p-4 py-md-5">
              <div class="icon d-flex justify-content-center align-items-center mb-4">
            		<span class="icon-done"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">3000+ Sampel telah di uji</h3>
              </div>
            </div>      
          </div>
		  <div class="col-lg-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services p-4 py-md-5">
              <div class="icon d-flex justify-content-center align-items-center mb-4">
            		<span class="icon-thumbs-up"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">99% Akurasi analisa</h3>
              </div>
            </div>    
          </div>
        </div>
			</div>
		</section>

    <section class="ftco-gallery">
    	<div class="container">
    		<div class="row justify-content-center">
    			<div class="col-md-8 heading-section text-center mb-4 ftco-animate">
            <h2 class="mb-4">Butuh Paket Kustom?</h2>
            <p>Hubungi kami untuk mendapatkan penawaran khusus untuk penyewaan jangka panjang atau paket bundling dengan peralatan</p>
			<p><a href="https://api.whatsapp.com/send/?phone=6285233397889&text=Halo+saya+ingin+konsultasi+tentang+paket+kustom&type=phone_number&app_absent=0" class="btn-custom" target="_blank">Konsultasi Gratis</a></p>
          </div>
    		</div>
    	</div>

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
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">Jl. Mastrip PO BOX 164, Jember - Jawa Timur- Indonesia</span>
                  </li>
                  <li class="d-flex align-items-center mb-3">
                    <div style="margin-right: 15px;">
                      <span class="icon ion-logo-whatsapp" style="color: #fff; font-size: 18px;"></span>
                    </div>
                    <span class="text" style="color: rgba(255,255,255,0.9); font-size: 14px;">+62 852-3339-7889</span>
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
