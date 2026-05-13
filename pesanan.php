<?php
session_start();
require_once 'login/database.php';
$db   = new Database();
$conn = $db->getConnection();

$is_logged = isset($_SESSION['id_user']);
$id_user   = $is_logged ? $_SESSION['id_user']['id_user'] : null;

// Ambil pesanan user yang login (order by created_at ASC - urut dari yang paling awal)
$orders = [];
if ($is_logged) {
    $stmt = $conn->prepare(
        "SELECT o.*,
            GROUP_CONCAT(p.nama_produk ORDER BY p.nama_produk SEPARATOR ', ') AS item_names,
            GROUP_CONCAT(oi.qty ORDER BY p.nama_produk SEPARATOR ', ') AS item_qtys
         FROM orders o
         LEFT JOIN order_items oi ON o.id_order = oi.id_order
         LEFT JOIN products p ON oi.id_product = p.id_product
         WHERE o.id_user = ?
         GROUP BY o.id_order
         ORDER BY o.created_at ASC"
    );
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Status Pesanan - UPA Lab Biosains</title>
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
      <div class="row no-gutters d-flex align-items-center px-md-0">
        <div class="col-lg-12 d-block">
          <div class="row d-flex">
            <div class="col-md pr-4 d-flex topper align-items-center">
              <div class="icon mr-2 d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
              <span class="text">+62 819-4662-8655</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
      <a class="navbar-brand" href="index.php">BIOSCIENCE LABS</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
        <span class="oi oi-menu"></span> Menu
      </button>
      <div class="collapse navbar-collapse" id="ftco-nav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="shop.php" class="nav-link">Catalog</a></li>
          <li class="nav-item active"><a href="pesanan.php" class="nav-link">Pesanan</a></li>
          <?php if ($is_logged): ?>
            <li class="nav-item"><a href="login/logout.php" class="nav-link" onclick="return confirm('Logout?');">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a href="login/signin.php" class="nav-link">Login</a></li>
          <?php endif; ?>
          <li class="nav-item cta cta-colored"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>[<?= count($_SESSION['cart'] ?? []) ?>]</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="row no-gutters slider-text align-items-center justify-content-center mb-4">
      <div class="col-md-9 ftco-animate text-center">
        <h1 class="mb-0 bread">Status Pesanan Saya</h1>
      </div>
    </div>
  </div>

  <section class="ftco-section ftco-cart">
    <div class="container">
      <?php if (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
        <div class="alert alert-success"><i class="ion-ios-checkmark-circle mr-2"></i>Pesanan berhasil dikirim! Silakan tunggu konfirmasi dari admin.</div>
      <?php endif; ?>

      <?php if (!$is_logged): ?>
        <div class="alert alert-warning text-center">
          Silakan <a href="login/signin.php" class="alert-link">login</a> untuk melihat pesanan Anda.
        </div>
      <?php elseif (empty($orders)): ?>
        <div class="text-center py-5 text-muted">
          <i class="ion-ios-cart" style="font-size:4rem; color:#ddd;"></i>
          <p class="mt-3">Belum ada pesanan. <a href="shop.php">Lihat Katalog</a></p>
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-md-12 ftco-animate">
            <div class="cart-list">
              <table class="table">
                <thead class="thead-primary">
                  <tr class="text-center">
                    <th>Order ID</th>
                    <th>Layanan Dipesan</th>
                    <th>Waktu Pelaksanaan</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $ord): ?>
                    <?php
                      $status = $ord['status_order'] ?? 'Pending';
                      $badge  = match($status) {
                          'Pending'   => 'badge-warning',
                          'Confirmed' => 'badge-success',
                          'Canceled'  => 'badge-danger',
                          default     => 'badge-secondary',
                      };
                      $label  = match($status) {
                          'Pending'   => 'Menunggu Konfirmasi',
                          'Confirmed' => 'Terkonfirmasi',
                          'Canceled'  => 'Tidak Terkonfirmasi',
                          default     => $status,
                      };
                    ?>
                    <tr class="text-center text-dark">
                      <td class="align-middle">
                        <span class="font-weight-bold" style="font-size:18px;">#<?= htmlspecialchars($ord['invoice_number'] ?? $ord['id_order']) ?></span>
                        <br><small class="text-muted"><?= date('d M Y', strtotime($ord['created_at'])) ?></small>
                      </td>
                      <td class="align-middle text-left">
                        <small><?= htmlspecialchars($ord['item_names'] ?? '-') ?></small>
                      </td>
                      <td class="align-middle">
                        <?php if ($ord['waktu_pelaksanaan']): ?>
                          <?= date('d M Y', strtotime($ord['waktu_pelaksanaan'])) ?><br>
                          <small class="text-muted"><?= date('H:i', strtotime($ord['waktu_pelaksanaan'])) ?> WIB</small>
                        <?php else: ?>-<?php endif; ?>
                      </td>
                      <td class="align-middle font-weight-bold">Rp <?= number_format($ord['grand_total'], 0, ',', '.') ?></td>
                      <td class="align-middle">
                        <span class="badge <?= $badge ?>" style="padding:8px 12px; font-size:13px;"><?= $label ?></span>
                      </td>
                      <td class="align-middle">
                        <?php if ($status === 'Confirmed'): ?>
                          <!-- Terkonfirmasi: tampilkan tombol Invoice -->
                          <button type="button" class="btn btn-success py-2 px-3 btn-invoice"
                            data-invoice="<?= htmlspecialchars($ord['invoice_number'] ?? $ord['id_order']) ?>"
                            data-order="<?= $ord['id_order'] ?>"
                            data-nama="<?= htmlspecialchars($ord['nama_pemesan'] ?? '') ?>"
                            data-items="<?= htmlspecialchars($ord['item_names'] ?? '-') ?>"
                            data-qtys="<?= htmlspecialchars($ord['item_qtys'] ?? '-') ?>"
                            data-total="<?= number_format($ord['grand_total'], 0, ',', '.') ?>"
                            data-tanggal="<?= date('d M Y', strtotime($ord['created_at'])) ?>"
                            data-waktu="<?= $ord['waktu_pelaksanaan'] ? date('d M Y, H:i', strtotime($ord['waktu_pelaksanaan'])) : '-' ?>"
                            data-toggle="modal" data-target="#modalInvoice">
                            <i class="ion-ios-document mr-1"></i> Invoice
                          </button>
                        <?php elseif ($status === 'Rejected'): ?>
                          <!-- Tidak terkonfirmasi: tampilkan detail alasan -->
                          <button type="button" class="btn btn-danger py-2 px-3 btn-detail"
                            data-invoice="<?= htmlspecialchars($ord['invoice_number'] ?? $ord['id_order']) ?>"
                            data-items="<?= htmlspecialchars($ord['item_names'] ?? '-') ?>"
                            data-total="<?= number_format($ord['grand_total'], 0, ',', '.') ?>"
                            data-tanggal="<?= date('d M Y', strtotime($ord['created_at'])) ?>"
                            data-waktu="<?= $ord['waktu_pelaksanaan'] ? date('d M Y, H:i', strtotime($ord['waktu_pelaksanaan'])) : '-' ?>"
                            data-toggle="modal" data-target="#modalDetail">
                            <i class="ion-ios-information-circle mr-1"></i> Detail
                          </button>
                        <?php else: ?>
                          <!-- Pending: tampilkan detail biasa -->
                          <button type="button" class="btn btn-primary py-2 px-3 btn-detail"
                            data-invoice="<?= htmlspecialchars($ord['invoice_number'] ?? $ord['id_order']) ?>"
                            data-items="<?= htmlspecialchars($ord['item_names'] ?? '-') ?>"
                            data-total="<?= number_format($ord['grand_total'], 0, ',', '.') ?>"
                            data-tanggal="<?= date('d M Y', strtotime($ord['created_at'])) ?>"
                            data-waktu="<?= $ord['waktu_pelaksanaan'] ? date('d M Y, H:i', strtotime($ord['waktu_pelaksanaan'])) : '-' ?>"
                            data-status="<?= $label ?>"
                            data-toggle="modal" data-target="#modalDetail">
                            Detail
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal Detail (Pending / Rejected) -->
  <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="ion-ios-document mr-2"></i>Detail Transaksi <span id="det_invoice"></span></h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-5">
          <div class="row mb-4">
            <div class="col-sm-6">
              <h6 class="mb-1 text-muted">Tanggal Pemesanan:</h6>
              <p class="font-weight-bold" id="det_tanggal"></p>
              <h6 class="mb-1 text-muted">Waktu Pelaksanaan:</h6>
              <p class="font-weight-bold" id="det_waktu"></p>
            </div>
            <div class="col-sm-6 text-sm-right">
              <h6 class="mb-1 text-muted">Status:</h6>
              <span class="badge badge-warning px-3 py-2" style="font-size:14px;" id="det_status"></span>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="thead-light text-center">
                <tr><th>Layanan Dipesan</th><th>Total</th></tr>
              </thead>
              <tbody id="det_items_tbody"></tbody>
            </table>
          </div>
          <div class="row align-items-center bg-light p-3 rounded mt-4 mx-0">
            <div class="col-sm-7 mb-2 mb-sm-0">
              <p class="text-muted mb-0 small" id="det_info"></p>
            </div>
            <div class="col-sm-5 text-sm-right text-center">
              <span class="text-muted">Total:</span>
              <h4 class="font-weight-bold text-primary d-inline-block mb-0 ml-2">Rp <span id="det_total"></span></h4>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Invoice (Confirmed) -->
  <div class="modal fade" id="modalInvoice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="ion-ios-checkmark-circle mr-2"></i>Invoice <span id="inv_invoice"></span></h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body p-5" id="printInvoiceArea">
          <div class="row mb-5">
            <div class="col-sm-6">
              <img src="images/logo polije.png" alt="Logo" style="height:50px;" class="mb-3">
              <h5 class="font-weight-bold mb-0">UPA Laboratorium Biosain</h5>
              <p class="text-muted small">Politeknik Negeri Jember</p>
            </div>
            <div class="col-sm-6 text-sm-right">
              <h4 class="font-weight-bold text-uppercase text-success mb-1">INVOICE</h4>
              <p class="text-muted mb-0">#<span id="inv_num"></span></p>
              <span class="badge badge-success px-3 py-2 mt-2" style="font-size:14px;">Terkonfirmasi Lunas</span>
            </div>
          </div>
          <div class="row mb-4">
            <div class="col-sm-6">
              <h6 class="mb-1 text-muted">Nama Pemesan:</h6>
              <p class="font-weight-bold" id="inv_nama"></p>
              <h6 class="mb-1 text-muted">Tanggal Pemesanan:</h6>
              <p class="font-weight-bold" id="inv_tanggal"></p>
            </div>
            <div class="col-sm-6 text-sm-right">
              <h6 class="mb-1 text-muted">Waktu Pelaksanaan:</h6>
              <p class="font-weight-bold" id="inv_waktu"></p>
            </div>
          </div>
          <div class="table-responsive mb-4">
            <table class="table table-bordered">
              <thead class="bg-light text-center">
                <tr><th>Layanan Dipesan</th><th>Total</th></tr>
              </thead>
              <tbody id="inv_items_tbody"></tbody>
            </table>
          </div>
          <div class="row align-items-center bg-light p-3 rounded mt-4 mx-0">
            <div class="col-12 text-right">
              <h6 class="mb-0 text-muted">Total Pembayaran:</h6>
              <h4 class="mb-0 text-success font-weight-bold">Rp <span id="inv_total"></span></h4>
            </div>
          </div>
          <div class="text-center mt-5 pt-3 border-top">
            <p class="text-muted mb-0">Terima kasih telah menggunakan layanan UPA Laboratorium Biosain Politeknik Negeri Jember.</p>
          </div>
        </div>
        <div class="modal-footer bg-light d-flex justify-content-between">
          <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-success px-4" onclick="printInvoice()">
            <i class="ion-ios-print mr-2"></i>Cetak Invoice
          </button>
        </div>
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
  <script>
    // Modal Detail
    document.querySelectorAll('.btn-detail').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('det_invoice').textContent = '#' + this.dataset.invoice;
        document.getElementById('det_tanggal').textContent = this.dataset.tanggal;
        document.getElementById('det_waktu').textContent   = this.dataset.waktu;
        document.getElementById('det_total').textContent   = this.dataset.total;

        const status = this.dataset.status || 'Tidak Terkonfirmasi';
        const statusEl = document.getElementById('det_status');
        statusEl.textContent = status;
        statusEl.className = status.includes('Tidak') ? 'badge badge-danger px-3 py-2' :
                             (status.includes('Menunggu') ? 'badge badge-warning px-3 py-2' : 'badge badge-secondary px-3 py-2');

        const infoEl = document.getElementById('det_info');
        if (status.includes('Tidak')) {
          infoEl.innerHTML = '<i class="ion-ios-close-circle mr-1 text-danger"></i> Pesanan Anda tidak terkonfirmasi. Silakan hubungi admin untuk informasi lebih lanjut.';
        } else {
          infoEl.innerHTML = '<i class="ion-ios-information-circle mr-1"></i> Pesanan Anda sedang menunggu konfirmasi dari admin.';
        }

        const items = this.dataset.items ? this.dataset.items.split(', ') : ['-'];
        let rows = '';
        items.forEach(item => {
          rows += `<tr class="text-center"><td class="text-left">${item}</td><td>-</td></tr>`;
        });
        document.getElementById('det_items_tbody').innerHTML = rows;
      });
    });

    // Modal Invoice
    document.querySelectorAll('.btn-invoice').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('inv_invoice').textContent = '#' + this.dataset.invoice;
        document.getElementById('inv_num').textContent     = this.dataset.invoice;
        document.getElementById('inv_nama').textContent    = this.dataset.nama;
        document.getElementById('inv_tanggal').textContent = this.dataset.tanggal;
        document.getElementById('inv_waktu').textContent   = this.dataset.waktu;
        document.getElementById('inv_total').textContent   = this.dataset.total;

        const items = this.dataset.items ? this.dataset.items.split(', ') : ['-'];
        let rows = '';
        items.forEach(item => {
          rows += `<tr class="text-center"><td class="text-left">${item}</td><td>-</td></tr>`;
        });
        document.getElementById('inv_items_tbody').innerHTML = rows;
      });
    });

    function printInvoice() {
      var printContents = document.getElementById('printInvoiceArea').innerHTML;
      var originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      window.location.reload();
    }
  </script>
</body>
</html>