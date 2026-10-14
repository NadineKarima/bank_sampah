<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$id_user = $_SESSION['id_user'];
$stmt = $pdo->prepare("
    SELECT t.*, s.jenis_sampah
    FROM transaksi t JOIN sampah s ON s.id_sampah = t.id_sampah
    WHERE t.id_user = ? ORDER BY t.id_transaksi DESC
");
$stmt->execute([$id_user]);
$riwayat = $stmt->fetchAll();

$page_title = 'Riwayat Setor';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <h4 class="mb-3">Riwayat Setor Sampah</h4>
    <?php show_flash(); ?>
    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>#</th><th>Tanggal</th><th>Jenis Sampah</th><th>Berat (kg)</th><th>Total</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Belum ada riwayat setor</td></tr>
                <?php else: foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= $r['id_transaksi'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= clean($r['jenis_sampah']) ?></td>
                        <td><?= number_format($r['berat_kg'], 1) ?></td>
                        <td><?= rupiah($r['total_rp']) ?></td>
                        <td><a href="struk.php?type=setor&id=<?= $r['id_transaksi'] ?>" class="small">Struk</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
