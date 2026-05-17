<?php
require_once 'includes/auth.php';
/** @var mysqli $conn */

// --- Filter periode grafik ---
$periode = $_GET['periode'] ?? 'harian';

switch ($periode) {
    case 'mingguan':
        $queryRevenue = "SELECT DATE_FORMAT(MIN(created_at), '%Y-W%u') as tgl,
                         SUM(grand_total) as total
                         FROM orders
                         WHERE status_order IN ('Confirmed','Completed')
                         GROUP BY DATE_FORMAT(created_at, '%Y-%u')
                         ORDER BY MIN(created_at) ASC
                         LIMIT 20";
        $labelFormat = 'Minggu ke-';
        break;
    case 'bulanan':
        $queryRevenue = "SELECT DATE_FORMAT(MIN(created_at), '%b %Y') as tgl,
                         SUM(grand_total) as total
                         FROM orders
                         WHERE status_order IN ('Confirmed','Completed')
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                         ORDER BY MIN(created_at) ASC
                         LIMIT 24";
        $labelFormat = '';
        break;
    case 'tahunan':
        $queryRevenue = "SELECT YEAR(MIN(created_at)) as tgl,
                         SUM(grand_total) as total
                         FROM orders
                         WHERE status_order IN ('Confirmed','Completed')
                         GROUP BY YEAR(created_at)
                         ORDER BY MIN(created_at) ASC";
        $labelFormat = '';
        break;
    default: // harian
        $queryRevenue = "SELECT DATE_FORMAT(MIN(created_at), '%d %b %Y') as tgl,
                         SUM(grand_total) as total
                         FROM orders
                         WHERE status_order IN ('Confirmed','Completed')
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                         ORDER BY MIN(created_at) ASC
                         LIMIT 30";
        $labelFormat = '';
}

// Fetch users count
$queryUsers = "SELECT u.nama_lengkap, u.no_telpon, u.email, r.nama_role 
               FROM users u 
               JOIN roles r ON u.id_role = r.id_role 
               ORDER BY u.created_at DESC";
$resUsers  = $conn->query($queryUsers);
$countUsers = $resUsers->num_rows;

// Fetch revenue data
$resRevenue = $conn->query($queryRevenue);
$chartLabels = [];
$chartData   = [];
$totalPendapatan = 0;
while ($row = $resRevenue->fetch_assoc()) {
    $chartLabels[]    = $labelFormat . $row['tgl'];
    $chartData[]      = (float)$row['total'];
    $totalPendapatan += (float)$row['total'];
}

// Fetch pending orders count
$resPending = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status_order='Pending'");
$countPending = $resPending->fetch_assoc()['cnt'];

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 navbar-custom">
    <h3 class="m-0 fw-bold text-dark">Dashboard Overview</h3>
    <div class="user-profile d-flex align-items-center">
        <div class="me-3 text-end">
            <span class="d-block fw-bold"><?= htmlspecialchars($_SESSION['id_user']['nama_lengkap'] ?? 'Admin') ?></span>
            <small class="text-muted">Administrator</small>
        </div>
        <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" class="rounded-circle shadow-sm" width="45" alt="Admin">
    </div>
</div>

<!-- Stat Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-3 border-0 bg-primary text-white bg-gradient">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-uppercase mb-1" style="font-size:0.8rem; letter-spacing:1px; opacity:0.8;">Pengguna Terdaftar</div>
                        <div class="h3 mb-0 fw-bold"><?= $countUsers ?></div>
                    </div>
                    <div class="col-auto"><i class="fa-solid fa-users stat-icon"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-3 border-0 bg-success text-white bg-gradient">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-uppercase mb-1" style="font-size:0.8rem; letter-spacing:1px; opacity:0.8;">Total Pendapatan (Confirmed)</div>
                        <div class="h3 mb-0 fw-bold">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto"><i class="fa-solid fa-wallet stat-icon"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100 py-3 border-0 bg-warning text-dark bg-gradient">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-uppercase mb-1" style="font-size:0.8rem; letter-spacing:1px; opacity:0.8;">Pesanan Menunggu</div>
                        <div class="h3 mb-0 fw-bold"><?= $countPending ?></div>
                    </div>
                    <div class="col-auto"><i class="fa-solid fa-clock stat-icon"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Column -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 fw-bold text-primary">Grafik Pendapatan</h6>
                <!-- Filter Periode -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="index.php?periode=harian"
                        class="btn <?= $periode === 'harian'   ? 'btn-primary' : 'btn-outline-primary' ?>">Harian</a>
                    <a href="index.php?periode=mingguan"
                        class="btn <?= $periode === 'mingguan' ? 'btn-primary' : 'btn-outline-primary' ?>">Mingguan</a>
                    <a href="index.php?periode=bulanan"
                        class="btn <?= $periode === 'bulanan'  ? 'btn-primary' : 'btn-outline-primary' ?>">Bulanan</a>
                    <a href="index.php?periode=tahunan"
                        class="btn <?= $periode === 'tahunan'  ? 'btn-primary' : 'btn-outline-primary' ?>">Tahunan</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($chartData)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-chart-bar fa-3x mb-3 opacity-25"></i>
                        <p>Belum ada data pendapatan.<br>Konfirmasi pesanan untuk melihat grafik.</p>
                    </div>
                <?php else: ?>
                    <canvas id="revenueChart" style="height:320px; width:100%;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Users Table Column -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm mb-4 h-100">
            <div class="card-header py-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="m-0 fw-bold text-primary">Data Pengguna</h6>
                    <span class="badge bg-primary rounded-pill" id="user-count-badge"><?= $countUsers ?></span>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" id="user-search-input"
                        placeholder="Cari nama, email, atau telepon..." autocomplete="off">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                    <table class="table table-hover mb-0 align-middle" id="user-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Info Pengguna</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            <?php if ($countUsers > 0): ?>
                                <?php foreach ($resUsers as $u): ?>
                                    <tr class="user-row"
                                        data-name="<?= strtolower(htmlspecialchars($u['nama_lengkap'])) ?>"
                                        data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>"
                                        data-phone="<?= strtolower(htmlspecialchars($u['no_telpon'] ?? '')) ?>"
                                        data-role="<?= strtolower(htmlspecialchars($u['nama_role'])) ?>">
                                        <td class="px-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                                            <div class="small text-muted"><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($u['email']) ?></div>
                                            <?php if (!empty($u['no_telpon'])): ?>
                                                <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($u['no_telpon']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($u['nama_role']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4">Belum ada pengguna terdaftar</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <!-- No search results message -->
                    <div id="user-no-results" class="text-center py-4 d-none">
                        <i class="fa-solid fa-user-slash fa-2x mb-2 text-muted opacity-50"></i>
                        <p class="text-muted small mb-0">Tidak ada pengguna yang cocok.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($chartData)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("revenueChart");
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: "Pendapatan",
                        backgroundColor: "rgba(78,115,223,0.8)",
                        hoverBackgroundColor: "#2e59d9",
                        borderColor: "#4e73df",
                        data: <?= json_encode($chartData) ?>,
                        borderRadius: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 50000,
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + 'Jt';
                                    return 'Rp' + (value / 1000).toFixed(0) + 'rb';
                                }
                            },
                            grid: {
                                color: "rgb(234,236,244)",
                                borderDash: [2]
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "#fff",
                            bodyColor: "#555",
                            titleColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            callbacks: {
                                label: function(ctx) {
                                    return ' Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<!-- User Search Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('user-search-input');
        const userRows = document.querySelectorAll('.user-row');
        const noResults = document.getElementById('user-no-results');
        const countBadge = document.getElementById('user-count-badge');
        const totalUsers = <?= $countUsers ?>;

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            userRows.forEach(function(row) {
                const name = row.dataset.name || '';
                const email = row.dataset.email || '';
                const phone = row.dataset.phone || '';
                const role = row.dataset.role || '';

                const matches = query === '' ||
                    name.includes(query) ||
                    email.includes(query) ||
                    phone.includes(query) ||
                    role.includes(query);

                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && query !== '') {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }

            // Update badge counter
            if (query === '') {
                countBadge.textContent = totalUsers;
                countBadge.className = 'badge bg-primary rounded-pill';
            } else {
                countBadge.textContent = visibleCount + '/' + totalUsers;
                countBadge.className = 'badge bg-success rounded-pill';
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>