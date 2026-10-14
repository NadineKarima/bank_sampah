<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$page_title = 'Tentang';
$asset_path = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Tentang</h4>
        <p class="text-muted mb-0">Informasi aplikasi Bank Sampah.</p>
    </div>
    <div class="card card-stat p-4" style="max-width: 700px;">
        <div class="text-center py-3">
            <img src="../assets/img/bank_sampah_icon.png" alt="Logo Bank Sampah" width="76" height="76" class="mb-3">
            <h5 class="fw-bold mb-1">Bank Sampah Sekolah</h5>
            <p class="text-muted mb-3">Sistem informasi untuk pencatatan setoran sampah, saldo, penarikan, dan pengelolaan data bank sampah.</p>
            <span class="badge bg-primary">Versi 1.0.0</span>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
