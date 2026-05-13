<?php
require_once 'includes/auth.php';
/** @var mysqli $conn */

// Menangani form submission untuk tambah/edit item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $nama_produk   = $_POST['nama_produk'];
    $sub_kategori  = $_POST['sub_kategori'] ?? 'Pengujian Kimia';
    $kategori      = 'Pengujian'; // tetap "Pengujian" di kolom kategori
    $deskripsi     = $_POST['deskripsi'];
    $min_duration = $_POST['min_duration'];
    $duration_unit = $_POST['duration_unit'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Harga per role
    $harga_mahasiswa = $_POST['harga_3'] ?? 0;
    $harga_dosen = $_POST['harga_4'] ?? 0;
    $harga_peneliti = $_POST['harga_5'] ?? 0;

    if ($action == 'add') {
        // Upload foto
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $dir = '../images/';
            $foto = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $foto);
        }

        $stmt = $conn->prepare("INSERT INTO products (nama_produk, kategori, sub_kategori, foto, deskripsi, min_duration, duration_unit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisi", $nama_produk, $kategori, $sub_kategori, $foto, $deskripsi, $min_duration, $duration_unit, $is_active);
        $stmt->execute();
        $id_product = $stmt->insert_id;

        // Insert prices
        $stmt_price = $conn->prepare("INSERT INTO prices (id_product, id_role, nominal_harga) VALUES (?, ?, ?)");
        $role = 3;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_mahasiswa);
        $stmt_price->execute();
        $role = 4;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_dosen);
        $stmt_price->execute();
        $role = 5;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_peneliti);
        $stmt_price->execute();

        header("Location: pengujian.php?success=add");
        exit;
    } elseif ($action == 'edit') {
        $id_product = $_POST['id_product'];

        $update_foto = "";
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $dir = '../images/';
            $foto = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $foto);
            $update_foto = ", foto='$foto'";
        }

        $stmt = $conn->prepare("UPDATE products SET nama_produk=?, sub_kategori=?, deskripsi=?, min_duration=?, duration_unit=?, is_active=? $update_foto WHERE id_product=?");
        $stmt->bind_param("sssisii", $nama_produk, $sub_kategori, $deskripsi, $min_duration, $duration_unit, $is_active, $id_product);
        $stmt->execute();

        // Update prices
        $conn->query("DELETE FROM prices WHERE id_product=$id_product");
        $stmt_price = $conn->prepare("INSERT INTO prices (id_product, id_role, nominal_harga) VALUES (?, ?, ?)");
        $role = 3;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_mahasiswa);
        $stmt_price->execute();
        $role = 4;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_dosen);
        $stmt_price->execute();
        $role = 5;
        $stmt_price->bind_param("iid", $id_product, $role, $harga_peneliti);
        $stmt_price->execute();

        header("Location: pengujian.php?success=edit");
        exit;
    } elseif ($action == 'delete') {
        $id_product = $_POST['id_product'];
        try {
            $conn->query("DELETE FROM products WHERE id_product=$id_product");
            header("Location: pengujian.php?success=delete");
        } catch (mysqli_sql_exception $e) {
            header("Location: pengujian.php?error=delete_constraint");
        }
        exit;
    }
}

// Ambil data item
$query = "SELECT p.*, 
          MAX(CASE WHEN pr.id_role = 3 THEN pr.nominal_harga END) AS harga_mahasiswa,
          MAX(CASE WHEN pr.id_role = 4 THEN pr.nominal_harga END) AS harga_dosen,
          MAX(CASE WHEN pr.id_role = 5 THEN pr.nominal_harga END) AS harga_peneliti
          FROM products p
          LEFT JOIN prices pr ON p.id_product = pr.id_product
          WHERE p.kategori = 'Pengujian'
          GROUP BY p.id_product
          ORDER BY p.sub_kategori, p.created_at DESC";
$result = $conn->query($query);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 navbar-custom">
    <h3 class="m-0 fw-bold text-dark">Data Item Pengujian</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
        <i class="fa-solid fa-plus me-2"></i>Tambah Pengujian
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Berhasil memproses data item pengujian!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] == 'delete_constraint'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Gagal menghapus item: Item sedang digunakan dalam transaksi pesanan.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th>
                        <th>Sub Kategori</th>
                        <th>Nama Pengujian</th>
                        <th>Estimasi Waktu</th>
                        <th>Harga (Mhs / Dsn / Pnl)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($row['foto']): ?>
                                    <img src="../images/<?= htmlspecialchars($row['foto']) ?>" width="50" height="50" class="rounded object-fit-cover" alt="Foto">
                                <?php else: ?>
                                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                        <i class="fa-solid fa-microscope"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($row['sub_kategori'] ?? 'Pengujian Kimia') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['nama_produk']) ?></div>
                                <small class="text-muted text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($row['deskripsi'] ?? '') ?></small>
                            </td>
                            <td><?= $row['min_duration'] ?> <?= htmlspecialchars($row['duration_unit']) ?></td>
                            <td class="small">
                                <div>M: Rp <?= number_format($row['harga_mahasiswa'] ?: 0, 0, ',', '.') ?></div>
                                <div>D: Rp <?= number_format($row['harga_dosen'] ?: 0, 0, ',', '.') ?></div>
                                <div>P: Rp <?= number_format($row['harga_peneliti'] ?: 0, 0, ',', '.') ?></div>
                            </td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 btn-edit"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit"
                                    data-id="<?= $row['id_product'] ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_produk']) ?>"
                                    data-subkat="<?= htmlspecialchars($row['sub_kategori'] ?? 'Pengujian Kimia') ?>"
                                    data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? '') ?>"
                                    data-min="<?= $row['min_duration'] ?>"
                                    data-unit="<?= htmlspecialchars($row['duration_unit']) ?>"
                                    data-active="<?= $row['is_active'] ?>"
                                    data-hm="<?= $row['harga_mahasiswa'] ?>"
                                    data-hd="<?= $row['harga_dosen'] ?>"
                                    data-hp="<?= $row['harga_peneliti'] ?>">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengujian ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_product" value="<?= $row['id_product'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-plus-circle me-2"></i>Tambah Item Pengujian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Pengujian</label>
                        <input type="text" class="form-control" name="nama_produk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sub Kategori</label>
                        <select class="form-select" name="sub_kategori" required>
                            <option value="Pengujian Kimia">Pengujian Kimia</option>
                            <option value="Pengujian Mikrobiologi">Pengujian Mikrobiologi</option>
                            <option value="Pengujian GCMS, LCMS, ELISA">Pengujian GCMS, LCMS, ELISA</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Foto Referensi</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Estimasi Durasi</label>
                            <input type="number" class="form-control" name="min_duration" value="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Satuan Waktu</label>
                            <select class="form-select" name="duration_unit" required>
                                <option value="hour">Jam (Hour)</option>
                                <option value="day">Hari (Day)</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-primary mb-3">Pengaturan Biaya</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Mahasiswa</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_3" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Dosen</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_4" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Peneliti Eks.</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_5" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Tersedia untuk Diuji</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-edit me-2"></i>Edit Pengujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_product" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Pengujian</label>
                        <input type="text" class="form-control" name="nama_produk" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sub Kategori</label>
                        <select class="form-select" name="sub_kategori" id="edit_subkat" required>
                            <option value="Pengujian Kimia">Pengujian Kimia</option>
                            <option value="Pengujian Mikrobiologi">Pengujian Mikrobiologi</option>
                            <option value="Pengujian GCMS, LCMS, ELISA">Pengujian GCMS, LCMS, ELISA</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Update Foto <small>(Opsional)</small></label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Estimasi Durasi</label>
                            <input type="number" class="form-control" name="min_duration" id="edit_min" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Satuan Waktu</label>
                            <select class="form-select" name="duration_unit" id="edit_unit" required>
                                <option value="hour">Jam (Hour)</option>
                                <option value="day">Hari (Day)</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-primary mb-3">Pengaturan Biaya</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Mahasiswa</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_3" id="edit_hm" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Dosen</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_4" id="edit_hd" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Biaya Peneliti Eks.</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga_5" id="edit_hp" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_active">
                        <label class="form-check-label" for="edit_active">Tersedia untuk Diuji</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_subkat').value = this.dataset.subkat;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
            document.getElementById('edit_min').value = this.dataset.min;
            document.getElementById('edit_unit').value = this.dataset.unit;

            document.getElementById('edit_hm').value = this.dataset.hm;
            document.getElementById('edit_hd').value = this.dataset.hd;
            document.getElementById('edit_hp').value = this.dataset.hp;

            document.getElementById('edit_active').checked = this.dataset.active == "1";
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>