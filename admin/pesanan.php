<?php
require_once 'includes/auth.php';
/** @var mysqli $conn */

// Handle konfirmasi / tolak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['id_order'])) {
    $id_order = (int)$_POST['id_order'];
    $action   = $_POST['action'];

    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE orders SET status_order='Confirmed' WHERE id_order=?");
        $stmt->bind_param("i", $id_order);
        $stmt->execute();
        header("Location: pesanan.php?success=confirm");
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE orders SET status_order='Canceled' WHERE id_order=?");
        $stmt->bind_param("i", $id_order);
        $stmt->execute();
        header("Location: pesanan.php?success=reject");
    }
    exit;
}

// --- Filter periode pesanan ---
$periode_pesanan = $_GET['periode_pesanan'] ?? 'semua';
$status_filter   = $_GET['status'] ?? 'semua';

// Build WHERE clause berdasarkan periode
$where_periode = '';
$periode_label = 'Semua Waktu';
switch ($periode_pesanan) {
    case 'harian':
        $where_periode = "AND DATE(o.created_at) = CURDATE()";
        $periode_label = 'Hari Ini (' . date('d M Y') . ')';
        break;
    case 'mingguan':
        $where_periode = "AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $periode_label = '7 Hari Terakhir';
        break;
    case 'bulanan':
        $where_periode = "AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
        $periode_label = 'Bulan Ini (' . date('F Y') . ')';
        break;
    case 'tahunan':
        $where_periode = "AND YEAR(o.created_at) = YEAR(CURDATE())";
        $periode_label = 'Tahun Ini (' . date('Y') . ')';
        break;
    default: // semua
        $where_periode = '';
        $periode_label = 'Semua Waktu';
}

// Build WHERE clause berdasarkan status
$where_status = '';
if ($status_filter !== 'semua') {
    $safe_status = $conn->real_escape_string($status_filter);
    $where_status = "AND o.status_order = '$safe_status'";
}

// Ambil daftar pesanan — urut dari yang pesan lebih awal (ASC)
$query = "SELECT o.*,
    u.nama_lengkap, u.email, u.no_telpon,
    GROUP_CONCAT(p.nama_produk ORDER BY p.nama_produk SEPARATOR ', ') AS item_details,
    GROUP_CONCAT(CONCAT(p.nama_produk,' x',oi.qty) ORDER BY p.nama_produk SEPARATOR ', ') AS item_qty_details
  FROM orders o
  JOIN users u ON o.id_user = u.id_user
  LEFT JOIN order_items oi ON o.id_order = oi.id_order
  LEFT JOIN products p ON oi.id_product = p.id_product
  WHERE 1=1 $where_periode $where_status
  GROUP BY o.id_order
  ORDER BY o.created_at ASC";
$result = $conn->query($query);
$total_filtered = $result ? $result->num_rows : 0;

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Helper function to build URL with filters
function buildFilterUrl($params) {
    $current = $_GET;
    $merged  = array_merge($current, $params);
    return 'pesanan.php?' . http_build_query($merged);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 navbar-custom">
    <h3 class="m-0 fw-bold text-dark">Daftar Pesanan</h3>
    <small class="text-muted"><?= $periode_label ?> &mdash; <?= $total_filtered ?> pesanan</small>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_GET['success'] === 'confirm' ? 'Pesanan berhasil <strong>dikonfirmasi</strong>!' : 'Pesanan berhasil <strong>ditolak</strong>.' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h6 class="m-0 fw-bold text-primary me-3"><i class="fa-solid fa-filter me-1"></i>Periode:</h6>
            <div class="btn-group btn-group-sm" role="group">
                <a href="<?= buildFilterUrl(['periode_pesanan' => 'semua']) ?>"
                    class="btn <?= $periode_pesanan === 'semua' ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
                <a href="<?= buildFilterUrl(['periode_pesanan' => 'harian']) ?>"
                    class="btn <?= $periode_pesanan === 'harian' ? 'btn-primary' : 'btn-outline-primary' ?>">Harian</a>
                <a href="<?= buildFilterUrl(['periode_pesanan' => 'mingguan']) ?>"
                    class="btn <?= $periode_pesanan === 'mingguan' ? 'btn-primary' : 'btn-outline-primary' ?>">Mingguan</a>
                <a href="<?= buildFilterUrl(['periode_pesanan' => 'bulanan']) ?>"
                    class="btn <?= $periode_pesanan === 'bulanan' ? 'btn-primary' : 'btn-outline-primary' ?>">Bulanan</a>
                <a href="<?= buildFilterUrl(['periode_pesanan' => 'tahunan']) ?>"
                    class="btn <?= $periode_pesanan === 'tahunan' ? 'btn-primary' : 'btn-outline-primary' ?>">Tahunan</a>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted fw-bold me-1">Status:</small>
            <select class="form-select form-select-sm" style="width:auto; min-width:160px;"
                    onchange="window.location.href=this.value;">
                <option value="<?= buildFilterUrl(['status' => 'semua']) ?>" <?= $status_filter === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                <option value="<?= buildFilterUrl(['status' => 'Pending']) ?>" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                <option value="<?= buildFilterUrl(['status' => 'Confirmed']) ?>" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Terkonfirmasi</option>
                <option value="<?= buildFilterUrl(['status' => 'Canceled']) ?>" <?= $status_filter === 'Canceled' ? 'selected' : '' ?>>Tidak Terkonfirmasi</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Pemesan</th>
                        <th>Item Dipesan</th>
                        <th>Waktu Pelaksanaan</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted small"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-primary">#<?= htmlspecialchars($row['invoice_number'] ?? $row['id_order']) ?></div>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                <small class="text-muted d-block"><?= htmlspecialchars($row['email']) ?></small>
                                <?php if ($row['no_telpon']): ?>
                                  <small class="text-muted d-block"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($row['no_telpon']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width:220px;">
                                    <?= htmlspecialchars($row['item_qty_details'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['waktu_pelaksanaan']): ?>
                                  <span><?= date('d M Y', strtotime($row['waktu_pelaksanaan'])) ?></span><br>
                                  <small class="text-muted"><?= date('H:i', strtotime($row['waktu_pelaksanaan'])) ?> WIB</small>
                                <?php else: ?>
                                  <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                  $s = $row['status_order'] ?? 'Pending';
                                  $cls = match($s) {
                                    'Pending'   => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-success',
                                    'Canceled'  => 'bg-danger',
                                    default     => 'bg-secondary',
                                  };
                                  $lbl = match($s) {
                                    'Pending'   => 'Menunggu Konfirmasi',
                                    'Confirmed' => 'Terkonfirmasi',
                                    'Canceled'  => 'Tidak Terkonfirmasi',
                                    default     => $s,
                                  };
                                ?>
                                <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                            </td>
                            <td>
                                <!-- Tombol Detail -->
                                <button class="btn btn-sm btn-info text-white me-1 btn-detail"
                                    data-bs-toggle="modal" data-bs-target="#modalDetail"
                                    data-invoice="<?= htmlspecialchars($row['invoice_number'] ?? $row['id_order']) ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_pemesan'] ?? $row['nama_lengkap']) ?>"
                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                    data-telp="<?= htmlspecialchars($row['no_telpon'] ?? '-') ?>"
                                    data-items="<?= htmlspecialchars($row['item_qty_details'] ?? '-') ?>"
                                    data-total="Rp <?= number_format($row['grand_total'], 0, ',', '.') ?>"
                                    data-waktu="<?= $row['waktu_pelaksanaan'] ? date('d M Y H:i', strtotime($row['waktu_pelaksanaan'])) . ' WIB' : '-' ?>"
                                    data-bukti="<?= htmlspecialchars($row['bukti_transfer'] ?? '') ?>">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>

                                <?php if ($row['status_order'] === 'Pending'): ?>
                                    <!-- Konfirmasi -->
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi pesanan ini?');">
                                        <input type="hidden" name="action" value="confirm">
                                        <input type="hidden" name="id_order" value="<?= $row['id_order'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success me-1">
                                            <i class="fa-solid fa-check"></i> Konfirmasi
                                        </button>
                                    </form>
                                    <!-- Tolak -->
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Tolak pesanan ini?');">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="id_order" value="<?= $row['id_order'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-times"></i> Tolak
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($total_filtered === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-muted opacity-25"></i>
                                <p class="text-muted mb-1">Tidak ada pesanan pada periode <strong><?= $periode_label ?></strong>.</p>
                                <a href="pesanan.php" class="text-primary small">Lihat semua pesanan →</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail + Bukti Transfer -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title"><i class="fa-solid fa-receipt me-2"></i>Detail Pesanan #<span id="det_invoice"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Nama Pemesan</small>
                        <span class="fw-bold" id="det_nama"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email / Telepon</small>
                        <span id="det_email"></span><br>
                        <span class="text-muted" id="det_telp"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Waktu Pelaksanaan</small>
                    <span class="fw-bold" id="det_waktu"></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Item yang Dipesan</small>
                    <span id="det_items"></span>
                </div>
                <div class="mb-4">
                    <small class="text-muted d-block">Total Pembayaran</small>
                    <span class="fw-bold fs-5 text-success" id="det_total"></span>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted d-block mb-2"><strong>Bukti Transfer:</strong></small>
                    <div id="det_bukti_container" class="text-center bg-light p-3 rounded border">
                        <!-- Diisi oleh JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('det_invoice').textContent = this.dataset.invoice;
        document.getElementById('det_nama').textContent    = this.dataset.nama;
        document.getElementById('det_email').textContent   = this.dataset.email;
        document.getElementById('det_telp').textContent    = this.dataset.telp;
        document.getElementById('det_waktu').textContent   = this.dataset.waktu;
        document.getElementById('det_items').textContent   = this.dataset.items;
        document.getElementById('det_total').textContent   = this.dataset.total;

        const buktiPath = this.dataset.bukti;
        const container = document.getElementById('det_bukti_container');
        if (buktiPath && buktiPath !== '') {
            container.innerHTML = `<a href="../images/${buktiPath}" target="_blank">
                <img src="../images/${buktiPath}" class="img-fluid rounded shadow-sm" style="max-height:280px;" alt="Bukti Transfer">
            </a>
            <small class="d-block mt-2 text-muted">Klik untuk memperbesar</small>`;
        } else {
            container.innerHTML = `<div class="text-muted py-4"><i class="fa-solid fa-image fa-2x mb-2"></i><br>Belum ada bukti transfer diupload.</div>`;
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>