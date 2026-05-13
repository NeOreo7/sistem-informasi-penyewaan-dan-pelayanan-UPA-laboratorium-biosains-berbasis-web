<?php
session_start();
// Cek apakah user sudah login dan role-nya adalah 1 (Admin) atau 2 (Ketua Lab)
if (!isset($_SESSION['id_user']) || !in_array($_SESSION['id_user']['id_role'], [1, 2])) {
    header("Location: ../login/signin.php");
    exit();
}

require_once __DIR__ . '/../../login/database.php';
$db = new Database();
$conn = $db->getConnection();
?>
