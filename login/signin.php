<?php
require_once 'database.php';
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();
$auth = new Auth($conn);

// Proses login ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Panggil method login
        if ($auth->login($email, $password)) {
            // Redirect jika berhasil
            if (isset($_SESSION['id_user']) && in_array($_SESSION['id_user']['id_role'], [1, 2])) {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
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

    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        min-height: 100vh;
        overflow-x: hidden;
        position: relative;
      }

      /* Animated background elements */
      body::before {
        content: '';
        position: fixed;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        top: -100px;
        left: -100px;
        animation: float 6s ease-in-out infinite;
        z-index: 0;
      }

      body::after {
        content: '';
        position: fixed;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        bottom: -50px;
        right: -50px;
        animation: float 8s ease-in-out infinite reverse;
        z-index: 0;
      }

      @keyframes float {
        0%, 100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(30px);
        }
      }

      .signin-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        z-index: 1;
      }

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
      }

      .signin-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 450px;
        width: 100%;
        overflow: hidden;
        animation: slideIn 0.5s ease-out;
        position: relative;
      }

      @keyframes slideIn {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .signin-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        padding: 40px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
      }

      .signin-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
      }

      .logo-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
      }

      .logo-container img {
        animation: bounce 2s ease-in-out infinite;
      }

      .logo-container img:last-child {
        animation-delay: 0.2s;
      }

      @keyframes bounce {
        0%, 100% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(-5px);
        }
      }

      .signin-header h1 {
        color: white;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
      }

      .signin-header p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        margin: 8px 0 0 0;
        position: relative;
        z-index: 1;
      }

      .signin-body {
        padding: 40px;
      }

      .form-group {
        margin-bottom: 25px;
      }

      .form-label {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 8px;
        display: block;
      }

      .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
      }

      .form-control:focus {
        border-color: #007bff;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25);
        outline: none;
      }

      .form-control::placeholder {
        color: #999;
      }

      .form-label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .forgot-password {
        color: #007bff;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s ease;
      }

      .forgot-password:hover {
        color: #0056b3;
        text-decoration: underline;
      }

      .checkbox-group {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
      }

      .form-check-input {
        width: 18px;
        height: 18px;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        cursor: pointer;
        accent-color: #007bff;
        transition: all 0.3s ease;
      }

      .form-check-input:hover {
        border-color: #007bff;
      }

      .form-check-label {
        margin-left: 8px;
        cursor: pointer;
        color: #666;
        font-size: 13px;
        font-weight: 500;
      }

      .signin-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        letter-spacing: 0.5px;
      }

      .signin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
      }

      .signin-btn:active {
        transform: translateY(0);
      }

      .signup-link {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e0e0e0;
        font-size: 14px;
        color: #666;
      }

      .signup-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
      }

      .signup-link a:hover {
        color: #0056b3;
        text-decoration: underline;
      }

      .invalid-feedback {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
        animation: shake 0.5s ease;
      }

      .invalid-feedback.show {
        display: block;
      }

      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
      }

      .form-control.is-invalid {
        border-color: #dc3545;
      }

      @media (max-width: 480px) {
        .signin-body {
          padding: 30px;
        }

        .signin-header {
          padding: 30px 20px;
        }

        .signin-header h1 {
          font-size: 24px;
        }
      }
    </style>

</head>

<body>
  <a href="/minishop-master/index.php" class="btn back-btn">
    <span class="ion-ios-arrow-back mr-2"></span> Kembali
  </a>

  <div class="signin-container">
    <div class="signin-card">
      <div class="signin-header">
        <div class="logo-container">
          <a href="/minishop-master/index.php">
          </a>
        </div>
        <h1>Masuk Akun</h1>
        <p>Akses ke toko online Anda</p>
      </div>

      <div class="signin-body">
        <?php if (!empty($error)): ?>
          <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
          <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center;">
            Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.
          </div>
        <?php endif; ?>
        <form class="signin-form" id="signinForm" action="signin.php" method="POST" novalidate>
          <div class="form-group">
            <label for="email" class="form-label">
              <span class="ion-ios-mail-outline"></span> Email
            </label>
            <input 
              id="email" 
              name="email"
              type="email" 
              class="form-control" 
              placeholder="nama@contoh.com" 
              required 
              autofocus>
            <div class="invalid-feedback">Masukkan email yang valid</div>
          </div>

          <div class="form-group">
            <label class="form-label form-label-row">
              <span><span class="ion-ios-lock-outline"></span> Password</span>
            
            </label>
            <input 
              id="password" 
              name="password"
              type="password" 
              class="form-control" 
              placeholder="••••••" 
              required 
              minlength="6">
            <div class="invalid-feedback">Password minimal 6 karakter</div>
          </div>

          <div class="checkbox-group">
            <input id="remember" class="form-check-input" type="checkbox">
            <label class="form-check-label" for="remember">Ingat saya</label>
          </div>

          <button class="signin-btn" type="submit">Masuk</button>
        </form>

        <div class="signup-link">
          Belum punya akun? <a href="signup.php">Daftar di sini</a>
        </div>
      </div>
    </div>
  </div>



  <!-- Bootstrap JS -->
  <script src="./assets/js/main.js" type="module"></script>

  <script>
    (function () {
      'use strict'
      
      // Enhanced form validation
      const form = document.getElementById('signinForm');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const emailFeedback = emailInput.nextElementSibling;
      const passwordFeedback = passwordInput.nextElementSibling;

      form.addEventListener('submit', function (event) {
        let isValid = true;
        
        // Email validation
        if (!emailInput.value || !emailInput.validity.valid) {
          emailInput.classList.add('is-invalid');
          emailFeedback.classList.add('show');
          isValid = false;
        } else {
          emailInput.classList.remove('is-invalid');
          emailFeedback.classList.remove('show');
        }
        
        // Password validation
        if (!passwordInput.value || passwordInput.value.length < 6) {
          passwordInput.classList.add('is-invalid');
          passwordFeedback.classList.add('show');
          isValid = false;
        } else {
          passwordInput.classList.remove('is-invalid');
          passwordFeedback.classList.remove('show');
        }
        
        if (!isValid) {
          event.preventDefault();
        }
      });

      // Remove error styling on focus
      emailInput.addEventListener('focus', function() {
        this.classList.remove('is-invalid');
        emailFeedback.classList.remove('show');
      });

      passwordInput.addEventListener('focus', function() {
        this.classList.remove('is-invalid');
        passwordFeedback.classList.remove('show');
      });
    })()
  </script>

</body>

</html>