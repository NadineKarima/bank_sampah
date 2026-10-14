<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('pengepul');
$id_user = (int) $_SESSION['id_user'];

$data = $pdo->query("SELECT * FROM sampah ORDER BY jenis_sampah")->fetchAll();

$page_title = 'Stok & Harga Jual';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_pengepul.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Stok &amp; Harga Jual</h4>
        <p class="text-muted mb-0">Stok yang tersedia di Bank Sampah. Harga diatur oleh Admin.</p>
    </div>
    <?php show_flash(); ?>
    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Jenis Sampah</th><th>Harga Jual/kg</th><th>Stok (kg)</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
                <?php else: foreach ($data as $s): $ada = (float)$s['stok_kg'] > 0; ?>
                    <tr>
                        <td><?= clean($s['jenis_sampah']) ?></td>
                        <td><?= rupiah($s['harga_jual']) ?></td>
                        <td><?= $ada ? number_format((float)$s['stok_kg'], 2, ',', '.') : '<span class="badge bg-secondary">Kosong</span>' ?></td>
                        <td class="text-end"><?php if ($ada): ?><a href="beli.php?jenis=<?= (int)$s['id_sampah'] ?>" class="btn btn-sm btn-outline-success">Beli</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
