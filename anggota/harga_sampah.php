<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$data_sampah = $pdo->query("SELECT * FROM sampah ORDER BY jenis_sampah")->fetchAll();

$page_title = 'Harga Sampah';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <h4 class="mb-3">Daftar Harga Sampah</h4>
    <?php show_flash(); ?>
    <div class="row g-3">
        <?php foreach ($data_sampah as $s): ?>
        <div class="col-md-4">
            <div class="card card-stat p-3">
                <h6 class="mb-1"><?= clean($s['jenis_sampah']) ?></h6>
                <p class="text-success fw-bold mb-0"><?= rupiah($s['harga_per_kg']) ?> / kg</p>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($data_sampah)): ?>
            <p class="text-muted">Belum ada data harga sampah.</p>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
