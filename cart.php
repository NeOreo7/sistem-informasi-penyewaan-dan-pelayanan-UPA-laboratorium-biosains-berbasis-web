<?php
session_start();
if (!isset($_SESSION['id_user'])) {
  header("Location: login/signin.php");
  exit;
}

require_once 'login/database.php';
$db = new Database();
$conn = $db->getConnection();

$id_user  = $_SESSION['id_user']['id_user'];
$id_role  = $_SESSION['id_user']['id_role'];

// --- HANDLE ACTIONS ---
// Add to cart
if (isset($_GET['add'])) {
  $pid = (int)$_GET['add'];
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
  if (isset($_SESSION['cart'][$pid])) {
    $_SESSION['cart'][$pid]++;
  } else {
    $_SESSION['cart'][$pid] = 1;
  }
  header("Location: cart.php");
  exit;
}

// Remove from cart
if (isset($_GET['remove'])) {
  $pid = (int)$_GET['remove'];
  unset($_SESSION['cart'][$pid]);
  header("Location: cart.php");
  exit;
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
  foreach ($_POST['qty'] as $pid => $qty) {
    $pid = (int)$pid;
    $qty = max(1, (int)$qty);
    if (isset($_SESSION['cart'][$pid])) {
      $_SESSION['cart'][$pid] = $qty;
    }
  }
  header("Location: cart.php");
  exit;
}

// Checkout - simpan order ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
  $nama_pemesan = trim($_POST['nama'] ?? '');
  $bukti_file   = '';

  // Terima waktu per kategori
  // Sewa Alat: tanggal + slot jam (08:00/11:00/14:00)
  $waktu_alat     = '';
  $alat_date      = trim($_POST['waktu_alat_date'] ?? '');
  $alat_slot      = trim($_POST['waktu_alat_slot'] ?? '');
  if ($alat_date && $alat_slot) {
    $waktu_alat = $alat_date . ' ' . $alat_slot . ':00'; // '2026-05-16 08:00:00'
  }
  // Sewa Ruangan: bulan → simpan sebagai hari pertama bulan
  $waktu_ruangan  = '';
  $ruangan_month  = trim($_POST['waktu_ruangan_month'] ?? '');
  if ($ruangan_month) {
    $waktu_ruangan = $ruangan_month . '-01 00:00:00';
  }
  // Pengujian: tanggal saja
  $waktu_pengujian = '';
  $pengujian_date  = trim($_POST['waktu_pengujian_date'] ?? '');
  if ($pengujian_date) {
    $waktu_pengujian = $pengujian_date . ' 00:00:00';
  }

  // Waktu utama order (prioritas: alat > ruangan > pengujian)
  $waktu = $waktu_alat ?: $waktu_ruangan ?: $waktu_pengujian;

  // Deteksi kategori dari cart
  $has_alat     = false;
  $has_ruangan  = false;
  $has_pengujian = false;
  if (!empty($_SESSION['cart'])) {
    $chk_ids = array_keys($_SESSION['cart']);
    $chk_pl  = implode(',', array_fill(0, count($chk_ids), '?'));
    $chk_st  = $conn->prepare("SELECT id_product, kategori FROM products WHERE id_product IN ($chk_pl)");
    $chk_st->bind_param(str_repeat('i', count($chk_ids)), ...$chk_ids);
    $chk_st->execute();
    $chk_res = $chk_st->get_result();
    $product_kategori = []; // id_product => kategori
    while ($cr = $chk_res->fetch_assoc()) {
      $product_kategori[$cr['id_product']] = $cr['kategori'];
      if ($cr['kategori'] === 'Sewa Alat')    $has_alat      = true;
      if ($cr['kategori'] === 'Sewa Ruangan') $has_ruangan   = true;
      if ($cr['kategori'] === 'Pengujian')    $has_pengujian = true;
    }
  }

  // Validasi: waktu wajib sesuai kategori yang ada
  $checkout_error = null;
  if ($has_alat && empty($waktu_alat))         $checkout_error = "Pilih tanggal dan slot jam untuk Sewa Alat.";
  if ($has_ruangan && empty($waktu_ruangan))   $checkout_error = "Pilih bulan untuk Sewa Ruangan.";
  if ($has_pengujian && empty($waktu_pengujian)) $checkout_error = "Pilih tanggal untuk Pengujian.";

  // Validasi tanggal tidak lampau (untuk alat dan pengujian)
  if (!$checkout_error && $waktu_alat && strtotime($waktu_alat) <= time())
    $checkout_error = "Slot Sewa Alat sudah lampau.";
  if (!$checkout_error && $waktu_pengujian && strtotime($waktu_pengujian) < strtotime('today'))
    $checkout_error = "Tanggal Pengujian sudah lampau.";

  if (!$checkout_error) {
    // Upload bukti transfer
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] == 0) {
      $dir = 'images/';
      $ext = pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION);
      $bukti_file = 'bukti_' . time() . '_' . $id_user . '.' . $ext;
      move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $dir . $bukti_file);
    }

    if (empty($_SESSION['cart']) || empty($nama_pemesan) || empty($waktu) || empty($bukti_file)) {
      $checkout_error = "Lengkapi semua data dan upload bukti transfer.";
    } else {
      // Hitung total
      $cart_ids = array_keys($_SESSION['cart']);
      $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
      $types = str_repeat('i', count($cart_ids));

      $stmt_prod = $conn->prepare("SELECT p.id_product, p.nama_produk, p.kategori,
            MAX(CASE WHEN pr.id_role = ? THEN pr.nominal_harga END) AS harga
            FROM products p
            LEFT JOIN prices pr ON p.id_product = pr.id_product
            WHERE p.id_product IN ($placeholders)
            GROUP BY p.id_product");
      $params = array_merge([$id_role], $cart_ids);
      $stmt_prod->bind_param('i' . $types, ...$params);
      $stmt_prod->execute();
      $prod_res = $stmt_prod->get_result();

      $grand_total = 0;
      $items_data  = [];
      while ($p = $prod_res->fetch_assoc()) {
        $qty      = $_SESSION['cart'][$p['id_product']];
        $harga    = $p['harga'] ?? 0;
        $subtotal = $harga * $qty;
        $grand_total += $subtotal;
        // Tentukan waktu_item per kategori produk
        $kat = $p['kategori'];
        if ($kat === 'Sewa Alat')    $wi = $waktu_alat;
        elseif ($kat === 'Sewa Ruangan') $wi = $waktu_ruangan;
        else                             $wi = $waktu_pengujian;
        $items_data[] = [
          'id_product'  => $p['id_product'],
          'nama'        => $p['nama_produk'],
          'qty'         => $qty,
          'harga'       => $harga,
          'subtotal'    => $subtotal,
          'waktu_item'  => $wi ?: $waktu,
        ];
      }

      // Generate invoice
      $inv_num = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

      // Insert order
      $stmt_ord = $conn->prepare(
        "INSERT INTO orders (id_user, grand_total, status_order, invoice_number, waktu_pelaksanaan, nama_pemesan, bukti_transfer, created_at)
             VALUES (?, ?, 'Pending', ?, ?, ?, ?, NOW())"
      );
      $stmt_ord->bind_param("idssss", $id_user, $grand_total, $inv_num, $waktu, $nama_pemesan, $bukti_file);
      $stmt_ord->execute();
      $id_order = $stmt_ord->insert_id;

      // Insert order_items dengan waktu_item per item
      $stmt_item = $conn->prepare(
        "INSERT INTO order_items (id_order, id_product, qty, harga_satuan, subtotal, waktu_item) VALUES (?, ?, ?, ?, ?, ?)"
      );
      foreach ($items_data as $it) {
        $stmt_item->bind_param("iiidds", $id_order, $it['id_product'], $it['qty'], $it['harga'], $it['subtotal'], $it['waktu_item']);
        $stmt_item->execute();
      }

      $_SESSION['cart'] = [];
      header("Location: pesanan.php?order=success");
      exit;
    }
  }
}


// --- DETEKSI KATEGORI UNIK DI CART ---
$cart_has_alat     = false;
$cart_has_ruangan  = false;
$cart_has_pengujian = false;

// --- AMBIL DATA PRODUK DI CART ---
$cart_items = [];
$grand_total = 0;
if (!empty($_SESSION['cart'])) {
  $cart_ids = array_keys($_SESSION['cart']);
  $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
  $types = str_repeat('i', count($cart_ids));

  $stmt = $conn->prepare("SELECT p.id_product, p.nama_produk, p.foto, p.duration_unit, p.kategori,
        MAX(CASE WHEN pr.id_role = ? THEN pr.nominal_harga END) AS harga
        FROM products p
        LEFT JOIN prices pr ON p.id_product = pr.id_product
        WHERE p.id_product IN ($placeholders)
        GROUP BY p.id_product");
  $params = array_merge([$id_role], $cart_ids);
  $stmt->bind_param('i' . $types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['id_product']];
    $row['qty']     = $qty;
    $row['subtotal'] = ($row['harga'] ?? 0) * $qty;
    $grand_total   += $row['subtotal'];
    $cart_items[]   = $row;
    // Deteksi kategori
    if ($row['kategori'] === 'Sewa Alat')    $cart_has_alat     = true;
    if ($row['kategori'] === 'Sewa Ruangan') $cart_has_ruangan  = true;
    if ($row['kategori'] === 'Pengujian')    $cart_has_pengujian = true;
  }
}

// Ambil bookings yang sudah ada (untuk info booking modal di shop)
$booked_times = $conn->query("SELECT id_product, waktu_pelaksanaan FROM orders o JOIN order_items oi ON o.id_order = oi.id_order WHERE o.status_order IN ('Pending','Confirmed') ORDER BY waktu_pelaksanaan ASC");
$bookings_by_product = [];
if ($booked_times) {
  while ($b = $booked_times->fetch_assoc()) {
    $bookings_by_product[$b['id_product']][] = $b['waktu_pelaksanaan'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Keranjang - UPA Lab Biosains</title>
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
              <span class="text">+62 819-4662-8655</span>
            </div>
            <div class="col-md pr-4 d-flex topper align-items-center">
              <div class="icon mr-2 d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
              <span class="text">@lab_biosain_polije</span>
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
          <li class="nav-item"><a href="pesanan.php" class="nav-link">Pesanan</a></li>
          <?php if (isset($_SESSION['id_user'])): ?>
            <li class="nav-item"><a href="login/logout.php" class="nav-link" onclick="return confirm('Logout?');">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a href="login/signin.php" class="nav-link">Login</a></li>
          <?php endif; ?>
          <li class="nav-item cta cta-colored active"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>[<?= count($_SESSION['cart'] ?? []) ?>]</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="ftco-section ftco-cart">
    <div class="container">
      <?php if (isset($checkout_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($checkout_error) ?></div>
      <?php endif; ?>

      <div class="row">
        <div class="col-md-12 ftco-animate">
          <div class="cart-list">
            <form method="POST" id="qty-form">
              <table class="table">
                <thead class="thead-primary">
                  <tr class="text-center">
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>Layanan / Alat</th>
                    <th>Harga Satuan</th>
                    <th>Kuantitas</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($cart_items)): ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">Keranjang Anda kosong. <a href="shop.php">Lihat Katalog</a></td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                      <tr class="text-center">
                        <td class="product-remove">
                          <a href="cart.php?remove=<?= $item['id_product'] ?>"><span class="ion-ios-close"></span></a>
                        </td>
                        <td class="image-prod">
                          <?php if ($item['foto']): ?>
                            <div class="img" style="background-image:url(images/<?= htmlspecialchars($item['foto']) ?>);"></div>
                          <?php else: ?>
                            <div class="img" style="background:#eee; display:flex; align-items:center; justify-content:center;"><span class="ion-ios-flask" style="font-size:2rem; color:#999;"></span></div>
                          <?php endif; ?>
                        </td>
                        <td class="product-name">
                          <h3><?= htmlspecialchars($item['nama_produk']) ?></h3>
                          <small class="text-muted">Per <?= htmlspecialchars($item['duration_unit'] ?? 'sampel') ?></small>
                        </td>
                        <td class="price">Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></td>
                        <td class="quantity">
                          <div class="input-group mb-3" style="width:100px; margin:auto;">
                            <input type="number" name="qty[<?= $item['id_product'] ?>]" class="quantity form-control input-number text-center"
                              value="<?= $item['qty'] ?>" min="1" max="100"
                              onchange="document.getElementById('qty-form').submit();">
                          </div>
                        </td>
                        <td class="total">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
              <input type="hidden" name="update_qty" value="1">
            </form>
          </div>
        </div>
      </div>

      <?php if (!empty($cart_items)): ?>
        <div class="row justify-content-center">
          <!-- Detail Checkout -->
          <div class="col col-lg-7 col-md-6 mt-5 cart-wrap ftco-animate">
            <form id="checkout-form" action="cart.php" method="POST" enctype="multipart/form-data" class="billing-form bg-light p-4" style="border-radius:5px;">
              <input type="hidden" name="checkout" value="1">
              <h3 class="mb-4 billing-heading">Detail Checkout</h3>
              <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" class="form-control" required
                  placeholder="Masukkan nama lengkap Anda"
                  value="<?= htmlspecialchars($_SESSION['id_user']['nama_lengkap'] ?? '') ?>">
              </div>
              <?php
              // --- BLOK WAKTU: Sewa Alat ---
              if ($cart_has_alat): ?>
                <div class="form-group" id="section-cart-alat">
                  <label class="font-weight-bold"><span class="ion-ios-flask mr-1"></span>Jadwal Sewa Alat</label>
                  <input type="date" name="waktu_alat_date" id="waktu_alat_date" class="form-control mb-2"
                    min="<?= date('Y-m-d') ?>" required placeholder="Pilih tanggal">
                  <div class="d-flex flex-wrap" style="gap:8px;" id="cart-slot-buttons">
                    <button type="button" class="btn btn-sm cart-slot-btn" data-slot="08:00">08:00</button>
                    <button type="button" class="btn btn-sm cart-slot-btn" data-slot="11:00">11:00</button>
                    <button type="button" class="btn btn-sm cart-slot-btn" data-slot="14:00">14:00</button>
                  </div>
                  <input type="hidden" name="waktu_alat_slot" id="waktu_alat_slot" value="">
                  <small class="text-muted d-block mt-1">Pilih tanggal lalu pilih slot jam yang tersedia</small>
                  <div id="alat-slot-error" class="alert alert-warning mt-2 py-2" style="display:none;font-size:.9rem;">
                    <span class="ion-ios-warning mr-1"></span> Pilih tanggal dan slot jam.
                  </div>
                </div>
              <?php endif; ?>

              <?php
              // --- BLOK WAKTU: Sewa Ruangan ---
              if ($cart_has_ruangan): ?>
                <div class="form-group" id="section-cart-ruangan">
                  <label class="font-weight-bold"><span class="ion-ios-home mr-1"></span>Jadwal Sewa Ruangan</label>
                  <input type="month" name="waktu_ruangan_month" id="waktu_ruangan_month" class="form-control"
                    min="<?= date('Y-m') ?>" required>
                  <small class="text-muted">Kapasitas: maks. 25 orang/bulan</small>
                </div>
              <?php endif; ?>

              <?php
              // --- BLOK WAKTU: Pengujian ---
              if ($cart_has_pengujian): ?>
                <div class="form-group" id="section-cart-pengujian">
                  <label class="font-weight-bold"><span class="ion-ios-analytics mr-1"></span>Tanggal Pengujian</label>
                  <input type="date" name="waktu_pengujian_date" id="waktu_pengujian_date" class="form-control"
                    min="<?= date('Y-m-d') ?>" required>
                  <small class="text-muted">Jeda 3 hari antar-pesanan diterapkan otomatis</small>
                  <div id="pengujian-past-error" class="alert alert-warning mt-2 py-2" style="display:none;font-size:.9rem;">
                    <span class="ion-ios-warning mr-1"></span> Pemesanan hanya untuk jadwal mendatang.
                  </div>
                </div>
              <?php endif; ?>
              <div class="form-group">
                <label for="bukti_transfer">Upload Bukti Transfer</label>
                <div class="alert alert-info py-2 px-3 mb-2" style="font-size:0.9rem;">
                  <i class="ion-ios-card mr-1"></i>
                  <strong>No. Rekening:</strong> 54326785365 (BRI a.n. UPA Lab Biosains POLIJE)
                </div>
                <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control"
                  style="height:auto; padding:10px;" accept="image/*,application/pdf" required>
              </div>
            </form>
          </div>

          <!-- Cart Totals -->
          <div class="col col-lg-5 col-md-6 mt-5 cart-wrap ftco-animate">
            <div class="cart-total mb-3">
              <h3>Ringkasan Pesanan</h3>
              <?php foreach ($cart_items as $item): ?>
                <p class="d-flex justify-content-between" style="font-size:0.9rem;">
                  <span><?= htmlspecialchars($item['nama_produk']) ?> x<?= $item['qty'] ?></span>
                  <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </p>
              <?php endforeach; ?>
              <hr>
              <p class="d-flex total-price">
                <span>Total</span>
                <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
              </p>
            </div>
            <p class="text-center">
              <button type="submit" form="checkout-form" class="btn btn-primary py-3 px-4" style="width:100%; border-radius:5px;">
                <span class="ion-ios-card mr-2"></span> Checkout Sekarang
              </button>
            </p>
            <p class="text-center mt-2">
              <a href="shop.php" class="btn btn-outline-secondary btn-sm">← Lanjut Belanja</a>
            </p>
          </div>
        </div>
      <?php endif; ?>
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
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
      <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
      <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
    </svg></div>

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
  <script src="js/main.js"></script>

  <style>
    .cart-slot-btn {
      min-width: 72px;
      font-weight: 600;
      border-radius: 6px;
      border: 2px solid #adb5bd;
      background: #fff;
      color: #495057;
    }

    .cart-slot-btn.available {
      border-color: #28a745;
      color: #28a745;
    }

    .cart-slot-btn.taken {
      border-color: #dc3545;
      background: #dc3545;
      color: #fff;
      opacity: .7;
      cursor: not-allowed;
    }

    .cart-slot-btn.selected {
      background: #28a745;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(40, 167, 69, .35);
    }
  </style>
  <script>
    (function() {
      // Booking data dari server (raw datetime strings)
      var bookingsByProduct = <?= json_encode($bookings_by_product) ?>;

      function padZ(n) {
        return String(n).padStart(2, '0');
      }

      // ── Sewa Alat: slot buttons ──────────────────────────────────
      var selectedSlot = null;
      var alatPids = <?= json_encode(
                        array_values(array_map(
                          fn($i) => $i['id_product'],
                          array_filter($cart_items, fn($i) => $i['kategori'] === 'Sewa Alat')
                        ))
                      ) ?>;

      function getAlatBookings() {
        var set = new Set();
        alatPids.forEach(function(pid) {
          (bookingsByProduct[pid] || []).forEach(function(dt) {
            set.add(dt.substring(0, 13)); // 'YYYY-MM-DD HH'
          });
        });
        return set;
      }

      var dateInput = document.getElementById('waktu_alat_date');
      var slotInput = document.getElementById('waktu_alat_slot');
      var slotBtns = document.querySelectorAll('.cart-slot-btn');

      function updateSlots(dateStr) {
        if (!slotBtns.length || !dateStr) return;
        var booked = getAlatBookings();
        var today = new Date().toISOString().substring(0, 10);
        var nowH = new Date().getHours();
        slotBtns.forEach(function(btn) {
          var slot = btn.dataset.slot;
          var hour = parseInt(slot.split(':')[0]);
          var key = dateStr + ' ' + padZ(hour);
          var taken = booked.has(key);
          var past = (dateStr < today) || (dateStr === today && hour <= nowH);
          btn.classList.remove('available', 'taken', 'selected');
          if (taken || past) {
            btn.classList.add('taken');
            btn.disabled = true;
            btn.title = taken ? 'Sudah dipesan' : 'Waktu lampau';
          } else {
            btn.classList.add('available');
            btn.disabled = false;
            btn.title = '';
          }
          if (slotInput && btn.dataset.slot === selectedSlot) btn.classList.add('selected');
        });
      }

      if (dateInput) {
        dateInput.addEventListener('change', function() {
          selectedSlot = null;
          if (slotInput) slotInput.value = '';
          slotBtns.forEach(function(b) {
            b.classList.remove('selected');
          });
          updateSlots(this.value);
        });
      }

      slotBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
          if (this.classList.contains('taken')) return;
          selectedSlot = this.dataset.slot;
          if (slotInput) slotInput.value = selectedSlot;
          slotBtns.forEach(function(b) {
            b.classList.remove('selected');
          });
          this.classList.add('selected');
        });
      });

      // ── Pengujian: validasi tanggal lampau ──────────────────────
      var pengujianInput = document.getElementById('waktu_pengujian_date');
      var pengujianErr = document.getElementById('pengujian-past-error');
      if (pengujianInput && pengujianErr) {
        pengujianInput.addEventListener('change', function() {
          var today = new Date().toISOString().substring(0, 10);
          if (this.value < today) {
            pengujianErr.style.display = 'block';
            this.style.borderColor = '#f0ad4e';
          } else {
            pengujianErr.style.display = 'none';
            this.style.borderColor = '';
          }
        });
      }

      // ── Form submit validation ───────────────────────────────────
      var form = document.getElementById('checkout-form');
      if (form) {
        form.addEventListener('submit', function(e) {
          // Validasi slot alat
          if (slotBtns.length) {
            var dateVal = dateInput ? dateInput.value : '';
            var slotVal = slotInput ? slotInput.value : '';
            if (!dateVal || !slotVal) {
              e.preventDefault();
              var errEl = document.getElementById('alat-slot-error');
              if (errEl) errEl.style.display = 'block';
              if (dateInput) dateInput.focus();
              return;
            }
          }
          // Validasi tanggal pengujian lampau
          if (pengujianInput && pengujianInput.value) {
            var today = new Date().toISOString().substring(0, 10);
            if (pengujianInput.value < today) {
              e.preventDefault();
              if (pengujianErr) pengujianErr.style.display = 'block';
              pengujianInput.focus();
            }
          }
        });
      }
    })();
  </script>
</body>

</html>