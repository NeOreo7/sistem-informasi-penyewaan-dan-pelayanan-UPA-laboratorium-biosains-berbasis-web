<?php
require_once 'login/database.php';
$db = new Database();
$conn = $db->getConnection();

$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    $res2 = $conn->query("SHOW CREATE TABLE `$table`");
    if ($row2 = $res2->fetch_row()) {
        echo $row2[1] . ";\n\n";
    }
}