<?php
require 'login/database.php';
$db = new Database();
$conn = $db->getConnection();
$res = $conn->query("SHOW CREATE TABLE order_items");
if ($res) print_r($res->fetch_assoc());
else echo "Error: " . $conn->error . "\n";

$res2 = $conn->query("SHOW CREATE TABLE orders");
if ($res2) print_r($res2->fetch_assoc());
else echo "Error: " . $conn->error . "\n";
