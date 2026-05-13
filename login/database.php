<?php
class Database {
    // Konfigurasi database
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "db_lab_biosains";

    // Properti untuk koneksi database
    public $conn;

    public function __construct() {
        // Membuat koneksi ke database dengan error handling
        try {
            $this->conn = new mysqli(
                $this->host, $this->user, $this->pass, $this->db
            );
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>