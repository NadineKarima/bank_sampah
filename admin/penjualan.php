<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$list = $pdo->query("SELECT t.*, u.nama, s.jenis_sampah FROM transaksi t JOIN users u ON u.id_user = t.id_user JOIN sampah s ON s.id_sampah = t.id_sampah WHERE t.tipe = 'penjualan' ORDER BY t.id_transaksi DESC")->fetchAll();
$tot = $pdo->query("SELECT COALESCE(SUM(berat_kg),0) kg, COALESCE(SUM(total_rp),0) rp FROM transaksi WHERE tipe='penjualan'")->fetch();

$page_title = 'Penjualan Pengepul';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Penjualan ke Pengepul</h4>
        <p class="text-muted mb-0">Pembelian stok oleh Pengepul. Stok dan saldo sudah otomatis terpotong saat transaksi.</p>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-6"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('deposit') ?></div><h5 class="mt-2 mb-0"><?= number_format((float)$tot['kg'], 1, ',', '.') ?> kg</h5><small class="text-muted">Total Terjual</small></div></div>
        <div class="col-6"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('cash') ?></div><h5 class="mt-2 mb-0"><?= rupiah($tot['rp']) ?></h5><small class="text-muted">Total Pendapatan</small></div></div>
    </div>
    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Tanggal</th><th>Pengepul</th><th>Jenis</th><th>Berat (kg)</th><th>Total</th></tr></thead>
                <tbody>
                <?php if (empty($list)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penjualan</td></tr>
                <?php else: foreach ($list as $t): ?>
                    <tr><td><?= (int)$t['id_transaksi'] ?></td><td><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td><td><?= clean($t['nama']) ?></td><td><?= clean($t['jenis_sampah']) ?></td><td><?= number_format((float)$t['berat_kg'], 2, ',', '.') ?></td><td><?= rupiah($t['total_rp']) ?></td></tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
