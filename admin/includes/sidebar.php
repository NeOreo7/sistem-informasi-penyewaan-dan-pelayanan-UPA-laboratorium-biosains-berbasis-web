<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0" style="width: 260px;">
        <div class="sidebar-heading text-center">
            <img src="../images/logo-bioscience.png" alt="Bioscience Labs" style="height: 80px; margin: 10px 0;">
        </div>
        <div class="list-group list-group-flush mt-3 flex-grow-1">
            <a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie me-3"></i> Dashboard
            </a>
            <a href="sewa.php" class="<?= $currentPage == 'sewa.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-box-open me-3"></i> Katalog Sewa
            </a>
            <a href="pengujian.php" class="<?= $currentPage == 'pengujian.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-microscope me-3"></i> Katalog Pengujian
            </a>
            <a href="pesanan.php" class="<?= $currentPage == 'pesanan.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-clipboard-list me-3"></i> Daftar Pesanan
            </a>
            <a href="../index.php" class="mt-4" target="_blank">
                <i class="fa-solid fa-globe me-3"></i> Lihat Website
            </a>
            <a href="../login/logout.php" class="text-danger mt-auto mb-4 border-top border-secondary pt-4" style="border-color: rgba(255,255,255,0.1)!important;">
                <i class="fa-solid fa-sign-out-alt me-3"></i> Logout
            </a>
        </div>
    </div>
    <!-- End Sidebar -->
    <!-- Main Content wrapper -->
    <div class="main-content">
