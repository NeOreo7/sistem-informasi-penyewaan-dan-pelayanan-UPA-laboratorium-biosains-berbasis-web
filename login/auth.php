<?php
class Auth
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($email, $password)
    {
        $email = trim($email);
        $password = trim($password);
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['id_user'] = $user;
                return true;
            } else {
                throw new Exception("Email atau password salah, harap mengisi dengan benar.");
            }
        }
        throw new Exception("Email tidak terdaftar. Harap periksa kembali email Anda.");
    }

    /**
     * Register user baru dengan no_telpon dan id_role yang benar
     * Role: mahasiswa=2, dosen=3, peneliti eksternal=4
     */
    public function register($name, $phone, $email, $password, $id_role)
    {
        $name     = trim($name);
        $phone    = trim($phone);
        $email    = trim($email);
        $password = trim($password);
        $id_role  = (int) $id_role;

        // Pastikan role valid (3=Mahasiswa, 4=Dosen, 5=Peneliti Eksternal untuk user biasa)
        if (!in_array($id_role, [3, 4, 5])) {
            $id_role = 3; // default mahasiswa
        }

        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            throw new Exception("Harap lengkapi semua data diri dengan benar.");
        }

        // Cek apakah email sudah terdaftar
        $stmt = $this->conn->prepare("SELECT id_user FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            throw new Exception("Email sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            "INSERT INTO users (nama_lengkap, no_telpon, email, password, id_role) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssi", $name, $phone, $email, $hashed_password, $id_role);
        if ($stmt->execute()) {
            return true;
        }
        throw new Exception("Terjadi kesalahan pada server saat mendaftar. Silakan coba lagi.");
    }
}
