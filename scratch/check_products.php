<?php
require_once 'login/database.php';
$db = new Database();
$conn = $db->getConnection();
$res = $conn->query('SELECT id_product, nama_produk, kategori, is_active FROM products');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
