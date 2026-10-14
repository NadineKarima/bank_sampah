<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$id_user = (int) $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT saldo FROM users WHERE id_user = ?");
$stmt->execute([$id_user]);
$saldo = (float) $stmt->fetchColumn();

$riwayat = $pdo->prepare("
    SELECT t.*, s.jenis_sampah
    FROM transaksi t
    JOIN sampah s ON s.id_sampah = t.id_sampah
    WHERE t.id_user = ?
    ORDER BY t.id_transaksi DESC
    LIMIT 5
");
$riwayat->execute([$id_user]);
$riwayat = $riwayat->fetchAll();

$page_title = 'Dashboard Nasabah';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <div class="dashboard-head">
        <div>
            <h4 class="mb-1">Dashboard Nasabah</h4>
            <p class="text-muted mb-0">Halo, <strong><?= clean($_SESSION['nama']) ?></strong></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="setor.php" class="btn btn-success btn-sm"><?= icon('deposit') ?> Setor Sampah</a>
            <div class="dashboard-date">
                <?= date('d-m-Y') ?>
            </div>
        </div>
    </div>

    <?php show_flash(); ?>

    <!-- Kartu saldo sesuai rancangan -->
    <section class="member-saldo-card mb-4">
        <div class="saldo-card-top">
            <div>
                <div class="saldo-card-title">Kartu Saldo Anda</div>
                <div class="saldo-card-amount"><?= rupiah($saldo) ?></div>
            </div>
            <div class="saldo-card-icon"><?= icon('wallet') ?></div>
        </div>
        <div class="saldo-card-bottom">
            <span class="saldo-card-note">Saldo dari setoran sampah</span>
            <a href="tarik_saldo.php" class="btn btn-light btn-sm fw-semibold">Ajukan Tarik</a>
        </div>
    </section>

    <!-- Tabel riwayat seperti rancangan -->
    <section class="card card-stat dashboard-history-card">
        <div class="history-header">
            <div>
                <h6 class="mb-1">Tabel Riwayat Setoran Terakhir</h6>
                <small class="text-muted">5 transaksi terakhir akun Anda</small>
            </div>
            <a href="riwayat.php" class="btn btn-outline-success btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Berat</th>
                        <th>Total Rp</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat setor</td>
                    </tr>
                <?php else: foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= clean($r['jenis_sampah']) ?></td>
                        <td><?= number_format((float)$r['berat_kg'], 2, ',', '.') ?> kg</td>
                        <td class="fw-semibold"><?= rupiah($r['total_rp']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
