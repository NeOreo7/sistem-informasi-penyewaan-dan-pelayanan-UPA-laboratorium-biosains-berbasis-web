<?php
require_once 'database.php';
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();
$auth = new Auth($conn);

// Proses register ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['name'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_val = $_POST['role'] ?? '';

    // Mapping role string ke id_role di database
    $role_map = [
        'mahasiswa internal'  => 3,
        'dosen internal'      => 4,
        'peneliti eksternal'  => 5,
    ];
    $id_role = $role_map[strtolower($role_val)] ?? 3;

    try {
        // Panggil method register dengan semua parameter
        if ($auth->register($name, $phone, $email, $password, $id_role)) {
            header("Location: signin.php?registered=true");
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Minishop - Free Bootstrap 4 Template by Colorlib</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="apple-touch-icon" sizes="180x180" href="./assets/images/favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="./assets/images/favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="./assets/images/favicon_io/favicon-16x16.png">
  <link rel="manifest" href="./assets/images/favicon_io/site.webmanifest">

<link rel="stylesheet" href="../css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="../css/animate.css">
    
    <link rel="stylesheet" href="../css/owl.carousel.min.css">
    <link rel="stylesheet" href="../css/owl.theme.default.min.css">
    <link rel="stylesheet" href="../css/magnific-popup.css">

    <link rel="stylesheet" href="../css/aos.css">

    <link rel="stylesheet" href="../css/ionicons.min.css">

    <link rel="stylesheet" href="../css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="../css/jquery.timepicker.css">

    
    <link rel="stylesheet" href="../css/flaticon.css">
    <link rel="stylesheet" href="../css/icomoon.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

  <style>
    /* ========== CSS TAMBAHAN - WARNA BIRU SEPERTI HALAMAN LAIN ========== */
    /* HTML tidak diubah sedikitpun */

    /* Background abu-abu muda seperti gambar 1 */
    body {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
    }

    .min-vh-100 {
     background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* Card putih dengan bayangan */
    .card {
      border: none !important;
      border-radius: 12px !important;
      background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%) !important;
      box-shadow: 0 1px 10px rgba(0, 0, 0, 0.08) !important;
      animation: slideUp 0.5s ease-out !important;
    }

    @keyframes slideUp {
      from {
      opacity: 0;
      transform: translateY(30px);
      }
      to {
      opacity: 1;
      transform: translateY(0);
      }
    }

    .card-body {
      padding: 2rem !important;
    }

    /* Judul */
    .card-title {
      font-size: 1.5rem !important;
      font-weight: 600 !important;
      color: #1a1a2e !important;
      margin-bottom: 0 !important;
      text-align: center !important;
    }

    /* Hilangkan pseudo-element */
    .text-center.mb-3::after {
      display: none !important;
    }

    /* Label form */
    .form-label {
      font-size: 0.8rem !important;
      font-weight: 600 !important;
      color: #333 !important;
      margin-bottom: 0.25rem !important;
    }

    /* Input field */
    .form-control {
      border-radius: 8px !important;
      border: 1px solid #ddd !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.9rem !important;
      background: #ffffff !important;
    }

    .form-control:focus {
      border-color: #007bff !important;
      outline: none !important;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
    }

    /* TOMBOL BIRU seperti halaman lain */
    .btn-primary {
      background-color: #007bff !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0.6rem !important;
      font-weight: 500 !important;
      font-size: 0.9rem !important;
      color: white !important;
    }

    .btn-primary:hover {
      background-color: #0056b3 !important;
    }

    /* Checkbox biru */
    .form-check-input {
      border-radius: 4px !important;
      border: 1px solid #ddd !important;
      width: 1rem;
      height: 1rem;
      margin-top: 0.2rem;
    }

    .form-check-input:checked {
      background-color: #007bff !important;
      border-color: #007bff !important;
    }

    .form-check-label {
      font-size: 0.8rem !important;
      color: #333 !important;
    }

    .form-check-label a {
      color: #007bff !important;
      text-decoration: none !important;
    }

    .form-check-label a:hover {
      text-decoration: underline !important;
    }

    /* Link biru seperti halaman lain */
    .text-center.mt-3 a,
    .link-primary {
      color: #007bff !important;
      text-decoration: none !important;
      font-weight: 500 !important;
    }

    .text-center.mt-3 a:hover {
      text-decoration: underline !important;
    }

    .small.text-muted {
      color: #888 !important;
      font-size: 0.8rem !important;
    }

    /* Tombol Kembali */
    .back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 102;
      background: rgba(255, 255, 255, 0.9) !important;
      color: #007bff !important;
      border: none !important;
      border-radius: 50px;
      padding: 8px 16px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .back-btn:hover {
      background: white !important;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
      color: #0056b3 !important;
    }

    /* Spasi */
    .mb-3 {
      margin-bottom: 1rem !important;
    }
  </style>
</head>

<body>
  <a href="signin.php" class="btn back-btn">
    <span class="ion-ios-arrow-back mr-2"></span> Kembali
  </a>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="card " style="max-width:420px; width:100%;">
    <div class="card-body p-5">
      <div class="text-center mb-3">
     <a href="project-smt2/index.php" class="mb-4 d-inline-block"><img src="./assets/images/logo-icon.svg" alt="" width="36">
      <span class="ms-2"> <img src="./assets/images/logo.svg" alt=""></span>
      </a>
        <h1 class="card-title mb-5 h5">Daftar Akun</h1>
      </div>

      <?php if (!empty($error)): ?>
        <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form class="needs-validation mt-3" action="signup.php" method="POST" novalidate>
        <div class="mb-3">
          <label for="fullName" class="form-label">Nama Lengkap</label>
          <input id="fullName" name="name" type="text" class="form-control" placeholder="ujang kedu" required>
          <div class="invalid-feedback">Masukkan nama lengkap anda.</div>
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">No Telepon / WhatsApp</label>
          <input id="phone" name="phone" type="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control" placeholder="0812xxxxxxxx" required>
          <div class="invalid-feedback">Masukkan nomor telepon yang valid (hanya angka).</div>
        </div>

        <div class="mb-3">
          <label for="role" class="form-label">Role</label>
          <select id="role" name="role" class="form-control" required>
            <option value="" disabled selected>Pilih Role Anda</option>
            <option value="mahasiswa internal">Mahasiswa Internal</option>
            <option value="dosen internal">Dosen Internal</option>
            <option value="peneliti eksternal">Peneliti Eksternal</option>
          </select>
          <div class="invalid-feedback">Silakan pilih role Anda.</div>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Alamat Email</label>
          <input id="email" name="email" type="email" class="form-control" placeholder="name@contoh.com" required>
          <div id="email-note" class="form-text text-muted" style="font-size: 0.8rem; margin-top: 4px; display: none;">Gunakan email kampus (contoh: nama@polije.ac.id).</div>
          <div class="invalid-feedback" id="email-feedback">Sertakan email yang valid.</div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input id="password" name="password" type="password" class="form-control" placeholder="Buat password" required minlength="6">
          <div class="invalid-feedback">Masukkan password (min 6 karakter).</div>
        </div>

        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
          <input id="confirmPassword" type="password" class="form-control" placeholder="Ulangi password" required
            oninput="this.setCustomValidity(document.getElementById('password').value !== this.value ? 'Passwords do not match.' : '')">
          <div class="invalid-feedback">Password harus sesuai.</div>
        </div>

        <div class="mb-3 form-check">
          <input id="terms" class="form-check-input" type="checkbox" required>
          <label class="form-check-label small" for="terms">Saya setuju dengan <a href="#" class="text-decoration-none">syarat dan ketentuan privasi</a></label>
          <div class="invalid-feedback">Kamu harus setuju dengan syarat dan ketentuan sebelum melanjutkan.</div>
        </div>

        <button class="btn btn-primary w-100" type="submit">Daftar</button>
      </form>

      <div class="text-center mt-3 small text-muted">
        Sudah memiliki akun? <a href="signin.php" class="link-primary">Masuk</a>
      </div>
    </div>
  </div>
</div>

  <!-- Bootstrap JS -->
  <script src="./assets/js/main.js" type="module"></script>

  <script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      
      const roleSelect = document.getElementById('role');
      const emailInput = document.getElementById('email');
      const emailNote = document.getElementById('email-note');
      const emailFeedback = document.getElementById('email-feedback');

      function validateEmailRole() {
         const role = roleSelect.value;
         const email = emailInput.value;
         if (role === 'dosen internal' || role === 'mahasiswa internal') {
             emailNote.style.display = 'block';
             if (email.length > 0 && !email.toLowerCase().includes('polije')) {
                 emailInput.setCustomValidity('Gunakan email kampus (polije).');
                 emailFeedback.textContent = 'Anda harus menggunakan email kampus (polije).';
             } else {
                 emailInput.setCustomValidity('');
                 emailFeedback.textContent = 'Sertakan email yang valid.';
             }
         } else {
             emailNote.style.display = 'none';
             emailInput.setCustomValidity('');
             emailFeedback.textContent = 'Sertakan email yang valid.';
         }
      }

      if (roleSelect && emailInput) {
          roleSelect.addEventListener('change', validateEmailRole);
          emailInput.addEventListener('input', validateEmailRole);
      }

      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (roleSelect && emailInput) validateEmailRole();
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            alert('Peringatan: Anda harus melengkapi semua data pendaftaran dengan benar!')
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>

</body>

</html>