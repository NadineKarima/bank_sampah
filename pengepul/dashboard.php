<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('pengepul');
$id_user = (int) $_SESSION['id_user'];

$saldo = saldo_user($pdo, $id_user);
$total_stok = (float) $pdo->query("SELECT COALESCE(SUM(stok_kg),0) FROM sampah")->fetchColumn();
$total_jenis = (int) $pdo->query("SELECT COUNT(*) FROM sampah WHERE stok_kg > 0")->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(berat_kg),0) kg, COALESCE(SUM(total_rp),0) rp, COUNT(*) n FROM transaksi WHERE id_user = ? AND tipe = 'penjualan'");
$stmt->execute([$id_user]);
$ringkas = $stmt->fetch();

$stmt = $pdo->prepare("SELECT t.*, s.jenis_sampah FROM transaksi t JOIN sampah s ON s.id_sampah = t.id_sampah WHERE t.id_user = ? AND t.tipe = 'penjualan' ORDER BY t.id_transaksi DESC LIMIT 5");
$stmt->execute([$id_user]);
$terbaru = $stmt->fetchAll();

$page_title = 'Dashboard Pengepul';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_pengepul.php';
?>
<main class="app-main">
    <div class="dashboard-head">
        <div>
            <h4 class="mb-1">Dashboard Pengepul</h4>
            <p class="text-muted mb-0">Halo, <strong><?= clean($_SESSION['nama']) ?></strong></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="beli.php" class="btn btn-success btn-sm"><?= icon('deposit') ?> Beli Sampah</a>
            <div class="dashboard-date"><?= date('d-m-Y') ?></div>
        </div>
    </div>
    <?php show_flash(); ?>

    <section class="member-saldo-card mb-4">
        <div class="saldo-card-top">
            <div>
                <div class="saldo-card-title">Saldo Pengepul</div>
                <div class="saldo-card-amount"><?= rupiah($saldo) ?></div>
            </div>
            <div class="saldo-card-icon"><?= icon('wallet') ?></div>
        </div>
        <div class="saldo-card-bottom">
            <span class="saldo-card-note">Dipakai untuk membeli stok sampah</span>
            <a href="topup.php" class="btn btn-light btn-sm fw-semibold">Top Up</a>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('history') ?></div><h5 class="mt-2 mb-0"><?= number_format($total_stok, 1, ',', '.') ?> kg</h5><small class="text-muted">Stok Tersedia</small></div></div>
        <div class="col-6 col-lg-3"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('tag') ?></div><h5 class="mt-2 mb-0"><?= $total_jenis ?></h5><small class="text-muted">Jenis Tersedia</small></div></div>
        <div class="col-6 col-lg-3"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('deposit') ?></div><h5 class="mt-2 mb-0"><?= number_format((float)$ringkas['kg'], 1, ',', '.') ?> kg</h5><small class="text-muted">Total Dibeli</small></div></div>
        <div class="col-6 col-lg-3"><div class="card card-stat p-3 text-center"><div class="stat-icon"><?= icon('cash') ?></div><h5 class="mt-2 mb-0"><?= rupiah($ringkas['rp']) ?></h5><small class="text-muted">Total Pengeluaran</small></div></div>
    </div>

    <section class="card card-stat dashboard-history-card">
        <div class="history-header">
            <div><h6 class="mb-1">Pembelian Terakhir</h6><small class="text-muted"><?= (int)$ringkas['n'] ?> pembelian sejauh ini</small></div>
            <a href="transaksi.php" class="btn btn-outline-success btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Jenis</th><th>Berat</th><th>Total Rp</th></tr></thead>
                <tbody>
                <?php if (empty($terbaru)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pembelian</td></tr>
                <?php else: foreach ($terbaru as $r): ?>
                    <tr><td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td><td><?= clean($r['jenis_sampah']) ?></td><td><?= number_format((float)$r['berat_kg'], 2, ',', '.') ?> kg</td><td class="fw-semibold"><?= rupiah($r['total_rp']) ?></td></tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
