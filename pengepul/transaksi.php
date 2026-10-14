<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('pengepul');
$id_user = (int) $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT t.*, s.jenis_sampah FROM transaksi t LEFT JOIN sampah s ON s.id_sampah = t.id_sampah WHERE t.id_user = ? AND t.tipe IN ('penjualan','topup') ORDER BY t.id_transaksi DESC");
$stmt->execute([$id_user]);
$list = $stmt->fetchAll();

$page_title = 'Riwayat';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_pengepul.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Riwayat Transaksi</h4>
        <p class="text-muted mb-0">Pembelian sampah dan top up saldo kamu.</p>
    </div>
    <?php show_flash(); ?>
    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th>Berat</th><th class="text-end">Nominal</th></tr></thead>
                <tbody>
                <?php if (empty($list)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td></tr>
                <?php else: foreach ($list as $t): $topup = $t['tipe'] === 'topup'; ?>
                    <tr>
                        <td><?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?></td>
                        <td><span class="badge bg-<?= $topup ? 'success' : 'primary' ?>"><?= $topup ? 'Top Up' : 'Pembelian' ?></span></td>
                        <td><?= $topup ? 'Isi saldo' : clean($t['jenis_sampah']) ?></td>
                        <td><?= $topup ? '—' : number_format((float)$t['berat_kg'], 2, ',', '.') . ' kg' ?></td>
                        <td class="text-end fw-semibold <?= $topup ? 'text-success' : '' ?>"><?= $topup ? '+' : '−' ?> <?= rupiah($t['total_rp']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
