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
          <li class="nav-item active"><a href="shop.php" class="nav-link">Catalog</a></li>
          <li class="nav-item"><a href="pesanan.php" class="nav-link">Pesanan</a></li>
          <?php if (isset($_SESSION['id_user'])): ?>
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

  <?php
  // Koneksi database
  require_once 'login/database.php';
  $db = new Database();
  $dbConn = $db->getConnection();

  // Deteksi role user yang sedang login
  $id_role = null;
  $role_label = null;
  if (isset($_SESSION['id_user'])) {
    $id_role = $_SESSION['id_user']['id_role'];
    $role_names = [3 => 'Mahasiswa', 4 => 'Dosen', 5 => 'Peneliti Eksternal'];
    $role_label = $role_names[$id_role] ?? null;
  }

  // Ambil semua produk aktif beserta harga per role
  $sql = "SELECT p.*,
    MAX(CASE WHEN pr.id_role = 3 THEN pr.nominal_harga END) AS harga_mahasiswa,
    MAX(CASE WHEN pr.id_role = 4 THEN pr.nominal_harga END) AS harga_dosen,
    MAX(CASE WHEN pr.id_role = 5 THEN pr.nominal_harga END) AS harga_peneliti
FROM products p
LEFT JOIN prices pr ON p.id_product = pr.id_product
WHERE p.is_active = 1
GROUP BY p.id_product
ORDER BY p.kategori, p.nama_produk";
  $products = $dbConn->query($sql);

  // Ambil data waktu booking (raw datetime string untuk validasi per kategori)
  $booked_times_res = $dbConn->query(
    "SELECT oi.id_product, o.waktu_pelaksanaan
     FROM orders o
     JOIN order_items oi ON o.id_order = oi.id_order
     WHERE o.status_order IN ('Pending','Confirmed')
       AND o.waktu_pelaksanaan IS NOT NULL
     ORDER BY o.waktu_pelaksanaan ASC"
  );
  $bookings_raw = []; // id_product => ['2026-05-06 08:00:00', ...]
  if ($booked_times_res) {
    while ($b = $booked_times_res->fetch_assoc()) {
      $bookings_raw[$b['id_product']][] = $b['waktu_pelaksanaan'];
    }
  }

  // Mapping kategori ke data-category attribute untuk filter sidebar
  $kategori_map = [
    'Sewa Alat'    => 'sewa-alat',
    'Sewa Ruangan' => 'sewa-ruangan',
    'Pengujian'    => 'pengujian',
  ];

  // Mapping sub_kategori pengujian ke slug untuk filter
  $sub_kategori_map = [
    'Pengujian Kimia'              => 'pengujian-kimia',
    'Pengujian Mikrobiologi'       => 'pengujian-mikrobiologi',
    'Pengujian GCMS, LCMS, ELISA'  => 'pengujian-gcms-lcms-elisa',
  ];
  ?>

  <section class="ftco-section bg-light">
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-lg-10 order-md-last">

          <!-- Search Bar -->
          <div class="d-flex align-items-center mb-4" id="search-bar-wrapper">
            <div class="search-box flex-grow-1">
              <div class="input-group">
                <input type="text" id="product-search" class="form-control search-input" placeholder="Search..." autocomplete="off">
                <div class="input-group-append">
                  <button class="btn btn-search" id="btn-search-go" type="button">
                    <i class="ion-ios-search"></i>
                  </button>
                </div>
              </div>
            </div>
            <div id="note-pengujian" class="search-note ml-3" style="display:none;">
              <span class="note-badge">Note: lama pengerjaan 14 Hari</span>
            </div>
          </div>
          <!-- /Search Bar -->

          <div class="row" id="product-grid">
            <?php if ($products && $products->num_rows > 0):
              while ($item = $products->fetch_assoc()):
                $cat_slug = $kategori_map[$item['kategori']] ?? strtolower(str_replace(' ', '-', $item['kategori']));
                // Untuk kategori Pengujian, gunakan sub_kategori sebagai slug filter
                if ($item['kategori'] === 'Pengujian' && !empty($item['sub_kategori'])) {
                  $sub_slug = $sub_kategori_map[$item['sub_kategori']] ?? strtolower(str_replace([' ', ','], ['-', ''], $item['sub_kategori']));
                } else {
                  $sub_slug = '';
                }
                $foto_src = $item['foto'] ? 'images/' . htmlspecialchars($item['foto']) : 'images/no-image.png';

                // Tentukan harga yang akan ditampilkan berdasarkan role
                if ($id_role == 3) {
                  $harga_tampil = $item['harga_mahasiswa'];
                  $label_harga  = 'Harga Mahasiswa';
                } elseif ($id_role == 4) {
                  $harga_tampil = $item['harga_dosen'];
                  $label_harga  = 'Harga Dosen';
                } elseif ($id_role == 5) {
                  $harga_tampil = $item['harga_peneliti'];
                  $label_harga  = 'Harga Peneliti Eksternal';
                } else {
                  // Belum login atau role lain: tampilkan semua harga
                  $harga_tampil = null;
                  $label_harga  = null;
                }
            ?>
                <div class="col-sm-12 col-md-12 col-lg-4 d-flex product-item" data-category="<?= $cat_slug ?>" data-subcategory="<?= $sub_slug ?>">
                  <div class="product d-flex flex-column">
                    <a href="#" class="img-prod">
                      <img class="img-fluid" src="<?= $foto_src ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                      <div class="overlay"></div>
                    </a>
                    <div class="text py-3 pb-4 px-3">
                      <div class="d-flex">
                        <div class="cat">
                          <span><?= htmlspecialchars($item['kategori']) ?></span>
                        </div>
                        <div class="cat text-right">
                          <span>Satuan: Per <?= htmlspecialchars($item['duration_unit'] ?? 'sampel') ?></span>
                        </div>
                      </div>
                      <h3><a href="#"><?= htmlspecialchars($item['nama_produk']) ?></a></h3>

                      <p class="bottom-area d-flex px-3 mt-auto">
                        <a href="cart.php?add=<?= $item['id_product'] ?>" class="add-to-cart text-center py-2 mr-1">
                          <span>Add to cart <i class="ion-ios-add ml-1"></i></span>
                        </a>
                        <a href="#" class="buy-now booking-button text-center py-2"
                          data-product-id="<?= $item['id_product'] ?>"
                          data-product-name="<?= htmlspecialchars($item['nama_produk']) ?>"
                          data-category="<?= $cat_slug ?>">
                          Jadwal<span><i class="ion-ios-calendar ml-1"></i></span>
                        </a>
                      </p>

                      <div class="pricing mt-3 border-top pt-2">
                        <?php if ($harga_tampil !== null): ?>
                          <!-- User sudah login: tampilkan harga sesuai role -->
                          <div class="text-dark fw-bold" style="font-size:1.05rem;">
                            <?= $label_harga ?>: <strong class="float-right">Rp <?= number_format($harga_tampil, 0, ',', '.') ?></strong>
                          </div>
                        <?php else: ?>
                          <!-- Belum login: tampilkan semua harga -->
                          <div class="small text-dark" style="line-height: 1.6;">
                            <div class="d-flex justify-content-between"><span>Mahasiswa:</span> <strong>Rp <?= number_format($item['harga_mahasiswa'] ?? 0, 0, ',', '.') ?></strong></div>
                            <div class="d-flex justify-content-between"><span>Dosen:</span> <strong>Rp <?= number_format($item['harga_dosen'] ?? 0, 0, ',', '.') ?></strong></div>
                            <div class="d-flex justify-content-between"><span>Peneliti Ekst:</span> <strong>Rp <?= number_format($item['harga_peneliti'] ?? 0, 0, ',', '.') ?></strong></div>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endwhile;
            else: ?>
              <div class="col-12 text-center py-5">
                <i class="ion-ios-flask" style="font-size:3rem; color:#ccc;"></i>
                <p class="mt-3 text-muted">Belum ada item katalog yang tersedia. Hubungi admin untuk menambahkan item.</p>
              </div>
            <?php endif; ?>
          </div><!-- /#product-grid -->

          <!-- No results message -->
          <div id="no-results-msg" class="col-12 text-center py-5">
            <i class="ion-ios-search" style="font-size:3rem; color:#ccc;"></i>
            <p class="mt-3 text-muted">Tidak ada item yang cocok dengan pencarian Anda.</p>
          </div>

          <div class="row mt-5">
            <div class="col text-center">
              <div class="block-27"></div>
            </div>
          </div>
        </div><!-- /.col -->

        <div class="col-md-4 col-lg-2">
          <div class="sidebar">
            <div class="sidebar-box-2">
              <h2 class="heading">Kategori</h2>
              <div class="fancy-collapse-panel">
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                  <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingAll">
                      <h4 class="panel-title">
                        <a href="#" class="category-link" data-target="all" style="color: #007bff;">
                          Semua Produk
                        </a>
                      </h4>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingOne">
                      <h4 class="panel-title">
                        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                          Penyewaan
                        </a>
                      </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">
                      <div class="panel-body">
                        <ul>
                          <li><a href="#" class="category-link" data-target="sewa-alat">Sewa Alat</a></li>
                          <li><a href="#" class="category-link" data-target="sewa-ruangan">Sewa Ruangan</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingTwo">
                      <h4 class="panel-title">
                        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                          Pengujian
                        </a>
                      </h4>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" data-parent="#accordion">
                      <div class="panel-body">
                        <ul>
                          <li><a href="#" class="category-link" data-target="pengujian-kimia" data-subcategory="true">Pengujian Kimia</a></li>
                          <li><a href="#" class="category-link" data-target="pengujian-mikrobiologi" data-subcategory="true">Pengujian Mikrobiologi</a></li>
                          <li><a href="#" class="category-link" data-target="pengujian-gcms-lcms-elisa" data-subcategory="true">Pengujian GCMS, LCMS, ELISA</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.row -->
    </div><!-- /.container -->
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
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
      <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
      <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
    </svg></div>

  <style>
    #bookingModal .modal-body {
      overflow: visible;
      position: relative;
      z-index: 1;
    }

    #bookingModal .form-group {
      position: relative;
    }

    .timepicker-dropdown {
      overflow: visible !important;
    }

    /* Katalog layout consistency */
    .product-item {
      margin-bottom: 30px;
    }

    .product .img-prod img {
      height: 250px;
      object-fit: cover;
      width: 100%;
    }

    .product {
      width: 100%;
      height: 100%;
    }

    .product .text {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .product .text h3 {
      line-height: 1.5;
      height: 6em; /* 4 baris x 1.5 line-height */
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      margin-bottom: 15px;
    }

    .product .bottom-area {
      margin-top: auto;
    }

    /* Search Bar Styles */
    #search-bar-wrapper {
      gap: 0;
    }

    .search-box {
      max-width: 480px;
    }

    .search-input {
      border: 2px solid #dee2e6;
      border-right: none;
      border-radius: 50px 0 0 50px !important;
      padding: 10px 20px;
      font-size: 15px;
      box-shadow: none !important;
      transition: border-color 0.2s;
    }

    .search-input:focus {
      border-color: #4a90e2;
    }

    .btn-search {
      background: #4a90e2;
      color: #fff;
      border: 2px solid #4a90e2;
      border-radius: 0 50px 50px 0 !important;
      padding: 10px 18px;
      font-size: 16px;
      transition: background 0.2s;
    }

    .btn-search:hover {
      background: #357abd;
      border-color: #357abd;
      color: #fff;
    }

    .note-badge {
      display: inline-block;
      background: #e8f0fe;
      color: #2563eb;
      border: 1.5px solid #93c5fd;
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
    }

    #no-results-msg {
      display: none;
      text-align: center;
      padding: 40px 0;
      color: #999;
    }
  </style>

  <style>
    /* â”€â”€ Slot buttons (Sewa Alat) â”€â”€ */
    .slot-time-btn { min-width: 80px; font-weight: 600; border-radius: 8px; }
    .slot-time-btn.available  { background:#28a745; color:#fff; border-color:#28a745; }
    .slot-time-btn.taken      { background:#dc3545; color:#fff; border-color:#dc3545; cursor:not-allowed; opacity:.75; }
    .slot-time-btn.selected   { box-shadow:0 0 0 3px #4a90e2; }
    #capacity-bar { height:10px; border-radius:5px; background:#e9ecef; overflow:hidden; }
    #capacity-bar-fill { height:100%; border-radius:5px; transition:width .4s; }
  </style>

  <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingModalLabel">Jadwal Sewa</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Layanan: <strong id="booking-product-name">-</strong></p>

          <!-- â•â•â• SEWA ALAT: slot jam 08:00 / 11:00 / 14:00 â•â•â• -->
          <div id="section-sewa-alat" style="display:none;">
            <div class="form-group mb-3">
              <label><strong>Pilih Tanggal</strong></label>
              <input type="date" class="form-control" id="booking-date-alat">
            </div>
            <div class="form-group mb-3">
              <label><strong>Pilih Slot Jam</strong></label>
              <div class="d-flex flex-wrap" style="gap:10px;" id="slot-buttons">
                <button type="button" class="btn slot-time-btn" data-time="08:00">08:00</button>
                <button type="button" class="btn slot-time-btn" data-time="11:00">11:00</button>
                <button type="button" class="btn slot-time-btn" data-time="14:00">14:00</button>
              </div>
              <small class="text-muted d-block mt-1"><i class="ion-ios-information-circle mr-1"></i>Jeda minimal 3 jam antar-pesanan</small>
            </div>
            <div id="status-alat" class="mt-2"></div>
          </div>

          <!-- â•â•â• SEWA RUANGAN: kapasitas per bulan â•â•â• -->
          <div id="section-sewa-ruangan" style="display:none;">
            <div class="form-group mb-3">
              <label><strong>Pilih Bulan</strong></label>
              <input type="month" class="form-control" id="booking-month">
            </div>
            <div id="capacity-info" class="mb-3" style="display:none;">
              <div class="d-flex justify-content-between mb-1">
                <span>Sisa kapasitas:</span>
                <strong id="capacity-text">-</strong>
              </div>
              <div id="capacity-bar"><div id="capacity-bar-fill"></div></div>
            </div>
            <div id="status-ruangan" class="mt-2"></div>
          </div>

          <!-- â•â•â• PENGUJIAN: jeda 3 hari â•â•â• -->
          <div id="section-pengujian" style="display:none;">
            <div class="form-group mb-3">
              <label><strong>Pilih Tanggal Pengujian</strong></label>
              <input type="date" class="form-control" id="booking-date-pengujian">
            </div>
            <div id="status-pengujian" class="mt-2"></div>
            <div class="mt-3">
              <h6 class="text-muted" style="font-size:.85rem;">Tanggal Terblokir (jeda 3 hari)</h6>
              <ul class="list-group list-group-flush" id="booking-list-pengujian" style="max-height:160px;overflow-y:auto;"></ul>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary" id="check-jadwal">Cek Jadwal</button>
        </div>
      </div>
    </div>
  </div>

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
  <script src="js/jquery.timepicker.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>

  <script>
    // â”€â”€ Data dari PHP â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const bookingsRaw = <?= json_encode($bookings_raw) ?>; // pid => ['YYYY-MM-DD HH:MM:SS', ...]
    const RUANGAN_CAPACITY = 25;

    // â”€â”€ Utilitas â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var activeCategoryFilter = 'all';
    var isSubcategoryFilter  = false;
    var pengujianSubSlugs    = ['pengujian-kimia','pengujian-mikrobiologi','pengujian-gcms-lcms-elisa'];

    function padZ(n){ return String(n).padStart(2,'0'); }
    function todayStr(){
      var d = new Date();
      return d.getFullYear()+'-'+padZ(d.getMonth()+1)+'-'+padZ(d.getDate());
    }

    // â”€â”€ Pengujian note â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function updatePengujianNote(){
      var hasPengujian=false, hasPenyewaan=false;
      $('.product-item:not(.d-none)').each(function(){
        var c=$(this).data('category');
        if(c==='pengujian') hasPengujian=true;
        if(c==='sewa-alat'||c==='sewa-ruangan') hasPenyewaan=true;
      });
      hasPengujian&&!hasPenyewaan ? $('#note-pengujian').fadeIn(200) : $('#note-pengujian').fadeOut(200);
    }

    // â”€â”€ Search & filter â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function applySearch(){
      var q=$('#product-search').val().toLowerCase().trim(), anyVisible=false;
      $('.product-item').each(function(){
        var cat=$(this).data('category'), sub=$(this).data('subcategory')||'';
        var name=$(this).find('h3 a').text().toLowerCase();
        var catLabel=$(this).find('.cat span').first().text().toLowerCase();
        var matchCat = activeCategoryFilter==='all' ? true :
                       isSubcategoryFilter ? sub===activeCategoryFilter :
                       cat===activeCategoryFilter;
        var matchSearch = q===''||name.includes(q)||catLabel.includes(q);
        if(matchCat&&matchSearch){ $(this).removeClass('d-none').addClass('d-flex'); anyVisible=true; }
        else { $(this).removeClass('d-flex').addClass('d-none'); }
      });
      anyVisible ? $('#no-results-msg').hide() : $('#no-results-msg').show();
      updatePengujianNote();
    }

    // â”€â”€ SEWA ALAT helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var _sewaAlatPid = null, _selectedSlot = null;

    function bookedDatesForAlat(pid){
      // Returns Set of 'YYYY-MM-DD HH:00' strings that are booked
      var set = new Set();
      (bookingsRaw[pid]||[]).forEach(function(dt){
        // dt = 'YYYY-MM-DD HH:MM:SS'
        set.add(dt.substring(0,13).replace(' ','T').substring(0,13)); // 'YYYY-MM-DDTHH'
      });
      return set;
    }

    function updateSlotButtons(pid, dateStr){
      // dateStr = 'YYYY-MM-DD'
      var booked = bookingsRaw[pid]||[];
      var today = todayStr();
      $('.slot-time-btn').each(function(){
        var slot = $(this).data('time'); // '08:00'
        var hour = parseInt(slot.split(':')[0]);
        // Build comparable key: 'YYYY-MM-DD HH'
        var key = dateStr + ' ' + padZ(hour);
        var isTaken = booked.some(function(dt){ return dt.startsWith(key); });
        var isPast  = dateStr < today || (dateStr===today && hour <= new Date().getHours());
        $(this).removeClass('available taken selected');
        if(isTaken || isPast){
          $(this).addClass('taken').prop('disabled',true);
          if(isTaken) $(this).attr('title','Slot ini sudah dipesan');
          else $(this).attr('title','Waktu sudah lewat');
        } else {
          $(this).addClass('available').prop('disabled',false).attr('title','');
        }
        if($(this).data('time')===_selectedSlot) $(this).addClass('selected');
      });
    }

    // â”€â”€ SEWA RUANGAN helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function countRuanganInMonth(pid, monthStr){
      // monthStr = 'YYYY-MM'
      return (bookingsRaw[pid]||[]).filter(function(dt){ return dt.startsWith(monthStr); }).length;
    }

    function renderCapacity(pid, monthStr){
      var used = countRuanganInMonth(pid, monthStr);
      var sisa = Math.max(0, RUANGAN_CAPACITY - used);
      var pct  = Math.round((used/RUANGAN_CAPACITY)*100);
      var color= sisa===0 ? '#dc3545' : sisa<=5 ? '#fd7e14' : '#28a745';
      $('#capacity-info').show();
      $('#capacity-text').text(sisa + ' / ' + RUANGAN_CAPACITY + ' orang');
      $('#capacity-bar-fill').css({width:pct+'%', background:color});
      if(sisa===0){
        $('#status-ruangan').html('<div class="alert alert-danger mb-0"><strong>Kapasitas Penuh</strong> â€” Tidak dapat memesan pada bulan ini.</div>');
      } else {
        $('#status-ruangan').html('<div class="alert alert-info mb-0">Sisa <strong>'+sisa+'</strong> slot tersedia. <a href="cart.php?add='+_sewaRuanganPid+'" class="btn btn-sm btn-success ml-2">Pesan Sekarang</a></div>');
      }
    }

    var _sewaRuanganPid = null;

    // â”€â”€ PENGUJIAN helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function getBlockedRanges(pid){
      // Returns array of {start:'YYYY-MM-DD', end:'YYYY-MM-DD'} (blocked 3 days incl start)
      return (bookingsRaw[pid]||[]).map(function(dt){
        var start = new Date(dt.substring(0,10));
        var end   = new Date(start); end.setDate(end.getDate()+2);
        var fmt   = function(d){ return d.getFullYear()+'-'+padZ(d.getMonth()+1)+'-'+padZ(d.getDate()); };
        return {start:fmt(start), end:fmt(end)};
      });
    }

    function isDateBlocked(dateStr, ranges){
      return ranges.some(function(r){ return dateStr>=r.start && dateStr<=r.end; });
    }

    // â”€â”€ Main: document ready â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $(document).ready(function(){
      updatePengujianNote();
      $('#product-search').on('keyup input', applySearch);
      $('#btn-search-go').on('click', applySearch);

      // Category filter
      $('.category-link').on('click', function(e){
        e.preventDefault();
        activeCategoryFilter = $(this).data('target');
        isSubcategoryFilter  = $(this).data('subcategory')===true;
        $('.category-link').css({'font-weight':'normal','color':''});
        $(this).css('color','#007bff');
        applySearch();
      });

      // â”€â”€ Booking button â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $('.booking-button').on('click', function(e){
        e.preventDefault();
        <?php if (!isset($_SESSION['id_user'])): ?>
          alert('Silakan login terlebih dahulu untuk melakukan booking.');
          window.location.href = 'login/signin.php';
          return;
        <?php endif; ?>

        var pid   = $(this).data('product-id');
        var pname = $(this).data('product-name');
        var pcat  = $(this).data('category') || '';

        // Reset semua section
        $('#section-sewa-alat, #section-sewa-ruangan, #section-pengujian').hide();
        $('#status-alat, #status-ruangan, #status-pengujian').html('');
        $('#capacity-info').hide();
        _selectedSlot = null;

        $('#booking-product-name').text(pname);

        // â”€â”€ SEWA ALAT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        if(pcat === 'sewa-alat'){
          $('#bookingModalLabel').text('Jadwal Sewa Alat');
          $('#section-sewa-alat').show();
          _sewaAlatPid = pid;

          // Set min date = hari ini
          $('#booking-date-alat').attr('min', todayStr()).val('');
          $('.slot-time-btn').removeClass('available taken selected').prop('disabled',true);

          $('#booking-date-alat').off('change.alat').on('change.alat', function(){
            var d = $(this).val();
            if(!d) return;
            _selectedSlot = null;
            $('.slot-time-btn').removeClass('selected');
            updateSlotButtons(pid, d);
          });

          $('.slot-time-btn').off('click.alat').on('click.alat', function(){
            if($(this).hasClass('taken')) return;
            _selectedSlot = $(this).data('time');
            $('.slot-time-btn').removeClass('selected');
            $(this).addClass('selected');
          });

          $('#check-jadwal').off('click').on('click', function(){
            var d = $('#booking-date-alat').val();
            if(!d){ alert('Pilih tanggal terlebih dahulu.'); return; }
            if(!_selectedSlot){ alert('Pilih slot jam terlebih dahulu.'); return; }
            var hour = parseInt(_selectedSlot.split(':')[0]);
            var key  = d + ' ' + padZ(hour);
            var isTaken = (bookingsRaw[pid]||[]).some(function(dt){ return dt.startsWith(key); });
            if(isTaken){
              $('#status-alat').html('<div class="alert alert-danger mb-0"><i class="ion-ios-close-circle mr-1"></i>Slot <strong>'+d+' '+_selectedSlot+'</strong> sudah dipesan.</div>');
            } else {
              $('#status-alat').html('<div class="alert alert-success mb-0" style="font-size:14px;">Slot <strong>'+d+' '+_selectedSlot+'</strong> tersedia!<br><br><a href="cart.php?add='+pid+'" class="btn btn-sm btn-success">Lanjutkan Transaksi</a></div>');
            }
          });

        // â”€â”€ SEWA RUANGAN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        } else if(pcat === 'sewa-ruangan'){
          $('#bookingModalLabel').text('Jadwal Sewa Ruangan');
          $('#section-sewa-ruangan').show();
          _sewaRuanganPid = pid;

          // Set min month = bulan ini
          var now = new Date();
          $('#booking-month').attr('min', now.getFullYear()+'-'+padZ(now.getMonth()+1)).val('');

          $('#booking-month').off('change.ruangan').on('change.ruangan', function(){
            var m = $(this).val();
            if(!m) return;
            renderCapacity(pid, m);
          });

          $('#check-jadwal').off('click').on('click', function(){
            var m = $('#booking-month').val();
            if(!m){ alert('Pilih bulan terlebih dahulu.'); return; }
            renderCapacity(pid, m);
          });

        // â”€â”€ PENGUJIAN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        } else if(pcat === 'pengujian'){
          $('#bookingModalLabel').text('Jadwal Pengujian');
          $('#section-pengujian').show();

          // Tampilkan daftar tanggal terblokir
          var ranges = getBlockedRanges(pid);
          var listEl = $('#booking-list-pengujian').empty();
          if(ranges.length===0){
            listEl.append('<li class="list-group-item text-success py-2"><i class="ion-ios-checkmark-circle mr-1"></i>Belum ada jadwal aktif.</li>');
          } else {
            ranges.forEach(function(r){
              listEl.append('<li class="list-group-item list-group-item-danger py-1 small"><i class="ion-ios-lock mr-1"></i>Diblokir: <strong>'+r.start+'</strong> s/d <strong>'+r.end+'</strong></li>');
            });
          }

          $('#booking-date-pengujian').attr('min', todayStr()).val('');

          $('#booking-date-pengujian').off('change.pengujian').on('change.pengujian', function(){
            var d = $(this).val();
            if(!d){ $('#status-pengujian').html(''); return; }
            if(isDateBlocked(d, ranges)){
              $('#status-pengujian').html('<div class="alert alert-danger mb-0"><i class="ion-ios-lock mr-1"></i>Tanggal ini masih dalam jeda 3 hari dari pesanan sebelumnya.</div>');
            } else if(d < todayStr()){
              $('#status-pengujian').html('<div class="alert alert-warning mb-0">Tanggal sudah lampau.</div>');
            } else {
              $('#status-pengujian').html('');
            }
          });

          $('#check-jadwal').off('click').on('click', function(){
            var d = $('#booking-date-pengujian').val();
            if(!d){ alert('Pilih tanggal terlebih dahulu.'); return; }
            if(d < todayStr()){ alert('Tanggal sudah lampau.'); return; }
            if(isDateBlocked(d, ranges)){
              $('#status-pengujian').html('<div class="alert alert-danger mb-0"><i class="ion-ios-lock mr-1"></i>Tanggal ini diblokir (jeda 3 hari dari pesanan sebelumnya).</div>');
            } else {
              $('#status-pengujian').html('<div class="alert alert-success mb-0" style="font-size:14px;">Tanggal <strong>'+d+'</strong> tersedia!<br><br><a href="cart.php?add='+pid+'" class="btn btn-sm btn-success">Lanjutkan Transaksi</a></div>');
            }
          });
        }

        $('#bookingModal').modal('show');
      }); // end booking-button click

      // â”€â”€ Add to cart login check â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $('.add-to-cart').on('click', function(e){
        <?php if (!isset($_SESSION['id_user'])): ?>
          e.preventDefault();
          alert('Silakan login terlebih dahulu untuk memasukkan item ke keranjang.');
          window.location.href = 'login/signin.php';
        <?php endif; ?>
      });
    }); // end document ready
  </script>


</body>

</html>
