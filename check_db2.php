<?php
require 'login/database.php';
$db = new Database();
$conn = $db->getConnection();
echo "=== PRICES PER PRODUCT FOR ROLE 3 (Mahasiswa) ===\n";
$r = $conn->query("SELECT id_product, nominal_harga FROM prices WHERE id_role=3 ORDER BY id_product");
if ($r) foreach ($r->fetch_all(MYSQLI_ASSOC) as $row) echo "product {$row['id_product']} => {$row['nominal_harga']}\n";

echo "\n=== PRICES PER PRODUCT FOR ROLE 4 (Dosen) ===\n";
$r = $conn->query("SELECT id_product, nominal_harga FROM prices WHERE id_role=4 ORDER BY id_product");
if ($r) foreach ($r->fetch_all(MYSQLI_ASSOC) as $row) echo "product {$row['id_product']} => {$row['nominal_harga']}\n";

echo "\n=== PRICES PER PRODUCT FOR ROLE 5 (Peneliti) ===\n";
$r = $conn->query("SELECT id_product, nominal_harga FROM prices WHERE id_role=5 ORDER BY id_product");
if ($r) foreach ($r->fetch_all(MYSQLI_ASSOC) as $row) echo "product {$row['id_product']} => {$row['nominal_harga']}\n";
