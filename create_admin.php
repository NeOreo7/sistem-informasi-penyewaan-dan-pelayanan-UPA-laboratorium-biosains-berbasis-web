<?php
require_once 'login/database.php';
$db = new Database();
$conn = $db->getConnection();

$email = 'admin@biosains.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
$role = 1;
$nama = 'Administrator Utama';

// Check if exists
$res = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($res->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, id_role, is_verified) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $nama, $email, $hashed, $role);
    if($stmt->execute()) {
        echo "SUCCESS";
    } else {
        echo "ERROR";
    }
} else {
    // If exists, force update the password and role to ensure it's admin
    $stmt = $conn->prepare("UPDATE users SET password=?, id_role=? WHERE email=?");
    $stmt->bind_param("sis", $hashed, $role, $email);
    $stmt->execute();
    echo "EXISTS_UPDATED";
}
?>
