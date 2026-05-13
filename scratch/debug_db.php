<?php
$conn = new mysqli('localhost', 'root', '', 'db_lab_biosains');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$res = $conn->query("SELECT id_product, nama_produk, kategori, is_active FROM products");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo "ID: " . $row['id_product'] . " | Name: " . $row['nama_produk'] . " | Category: [" . $row['kategori'] . "] | Active: " . $row['is_active'] . "\n";
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
