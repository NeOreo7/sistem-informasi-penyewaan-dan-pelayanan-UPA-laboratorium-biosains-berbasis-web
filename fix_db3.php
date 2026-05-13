<?php
require 'login/database.php';
$db = new Database();
$conn = $db->getConnection();

$conn->query("SET FOREIGN_KEY_CHECKS=0");

// Drop existing order_items table
$conn->query("DROP TABLE IF EXISTS order_items");

// Recreate order_items table with correct schema
$sql = "CREATE TABLE order_items (
    id_order_item INT AUTO_INCREMENT PRIMARY KEY,
    id_order INT NOT NULL,
    id_product INT NOT NULL,
    qty INT DEFAULT 1,
    harga_satuan DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products(id_product) ON DELETE CASCADE
)";
if ($conn->query($sql)) {
    echo "order_items table recreated successfully.\n";
} else {
    echo "Error creating order_items: " . $conn->error . "\n";
}

$conn->query("SET FOREIGN_KEY_CHECKS=1");
echo "Done.";
