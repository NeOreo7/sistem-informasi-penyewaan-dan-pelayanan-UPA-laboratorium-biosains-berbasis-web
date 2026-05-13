<?php
require 'login/database.php';
$db = new Database();
$conn = $db->getConnection();

// Tambah kolom sub_kategori jika belum ada
$r = $conn->query("SHOW COLUMNS FROM products LIKE 'sub_kategori'");
if ($r->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN sub_kategori VARCHAR(100) DEFAULT 'Pengujian Kimia' AFTER kategori");
    echo "ADDED sub_kategori column\n";
} else {
    echo "sub_kategori ALREADY EXISTS\n";
}

// Cek kolom orders untuk memastikan kolom yang diperlukan ada
$cols_needed = [
    ['table' => 'orders', 'col' => 'invoice_number', 'def' => "ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(50) UNIQUE AFTER id_order"],
    ['table' => 'orders', 'col' => 'waktu_pelaksanaan', 'def' => "ALTER TABLE orders ADD COLUMN waktu_pelaksanaan DATETIME NULL AFTER grand_total"],
    ['table' => 'orders', 'col' => 'nama_pemesan', 'def' => "ALTER TABLE orders ADD COLUMN nama_pemesan VARCHAR(255) NULL AFTER waktu_pelaksanaan"],
    ['table' => 'orders', 'col' => 'bukti_transfer', 'def' => "ALTER TABLE orders ADD COLUMN bukti_transfer VARCHAR(255) NULL AFTER nama_pemesan"],
    ['table' => 'orders', 'col' => 'status_order', 'def' => "ALTER TABLE orders ADD COLUMN status_order ENUM('Pending','Confirmed','Rejected','Completed') DEFAULT 'Pending' AFTER bukti_transfer"],
];

foreach ($cols_needed as $item) {
    $r2 = $conn->query("SHOW COLUMNS FROM `{$item['table']}` LIKE '{$item['col']}'");
    if ($r2->num_rows == 0) {
        if ($conn->query($item['def'])) {
            echo "ADDED {$item['table']}.{$item['col']}\n";
        } else {
            echo "FAILED {$item['table']}.{$item['col']}: " . $conn->error . "\n";
        }
    } else {
        echo "EXISTS {$item['table']}.{$item['col']}\n";
    }
}

// Cek tabel order_items
$r3 = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($r3->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS order_items (
        id_item INT AUTO_INCREMENT PRIMARY KEY,
        id_order INT NOT NULL,
        id_product INT NOT NULL,
        qty INT DEFAULT 1,
        harga_satuan DECIMAL(15,2) DEFAULT 0,
        subtotal DECIMAL(15,2) DEFAULT 0,
        FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE,
        FOREIGN KEY (id_product) REFERENCES products(id_product)
    )");
    echo "CREATED order_items table\n";
} else {
    echo "order_items EXISTS\n";
}

// Pastikan tabel orders ada kolom created_at
$r4 = $conn->query("SHOW COLUMNS FROM orders LIKE 'created_at'");
if ($r4->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "ADDED orders.created_at\n";
} else {
    echo "orders.created_at EXISTS\n";
}

echo "DONE\n";
?>
