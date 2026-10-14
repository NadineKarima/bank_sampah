<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$id_user = $_SESSION['id_user'];
$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

$data = null;

if ($type === 'setor') {
    $stmt = $pdo->prepare("
        SELECT t.*, s.jenis_sampah, s.harga_per_kg, u.nama
        FROM transaksi t
        JOIN sampah s ON s.id_sampah = t.id_sampah
        JOIN users u ON u.id_user = t.id_user
        WHERE t.id_transaksi = ? AND t.id_user = ?
    ");
    $stmt->execute([$id, $id_user]);
    $data = $stmt->fetch();
} elseif ($type === 'tarik') {
    $stmt = $pdo->prepare("
        SELECT ts.*, u.nama
        FROM tarik_saldo ts
        JOIN users u ON u.id_user = ts.id_user
        WHERE ts.id_tarik = ? AND ts.id_user = ?
    ");
    $stmt->execute([$id, $id_user]);
    $data = $stmt->fetch();
}

if (!$data) {
    set_flash('error', 'Struk tidak ditemukan.');
    redirect('dashboard.php');
}

$kode_struk = strtoupper($type) . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);

$page_title = 'Struk';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="mb-0">Struk Transaksi</h4>
        <div>
            <button onclick="window.print()" class="btn btn-success btn-sm">Cetak / Simpan PDF</button>
            <a href="dashboard.php" class="btn btn-outline-success btn-sm">Kembali</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-stat p-4 struk-box">
                <div class="text-center mb-3">
                    <img src="<?= $asset_path ?>assets/img/bank_sampah_icon.png" height="44" class="mb-2">
                    <h5 class="fw-bold mb-0">Bank Sampah</h5>
                    <small class="text-muted">Bukti Transaksi Digital</small>
                </div>
                <hr class="border-dashed">

                <?php if ($type === 'setor'): ?>
                    <p class="text-center text-muted mb-1">Setor Sampah Berhasil</p>
                    <h4 class="text-center text-success mb-3">+ <?= rupiah($data['total_rp']) ?></h4>
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted">Nama</td><td class="text-end"><?= clean($data['nama']) ?></td></tr>
                        <tr><td class="text-muted">Jenis Sampah</td><td class="text-end"><?= clean($data['jenis_sampah']) ?></td></tr>
                        <tr><td class="text-muted">Berat</td><td class="text-end"><?= number_format($data['berat_kg'], 2) ?> kg</td></tr>
                        <tr><td class="text-muted">Harga/kg</td><td class="text-end"><?= rupiah($data['harga_per_kg']) ?></td></tr>
                        <tr><td class="text-muted">Tanggal</td><td class="text-end"><?= date('d-m-Y', strtotime($data['tanggal'])) ?></td></tr>
                    </table>

                <?php elseif ($type === 'tarik'): ?>
                    <p class="text-center text-muted mb-1">
                        Penarikan Saldo <?= $data['status'] === 'menunggu' ? '(Menunggu Diproses)' : ($data['status'] === 'ditolak' ? '(Ditolak)' : 'Berhasil') ?>
                    </p>
                    <h4 class="text-center text-danger mb-3">- <?= rupiah($data['nominal']) ?></h4>
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted">Nama</td><td class="text-end"><?= clean($data['nama']) ?></td></tr>
                        <tr><td class="text-muted">Metode</td><td class="text-end">Tunai</td></tr>
                        <tr><td class="text-muted">Status</td><td class="text-end text-capitalize"><?= clean($data['status']) ?></td></tr>
                        <tr><td class="text-muted">Tanggal</td><td class="text-end"><?= date('d-m-Y', strtotime($data['tanggal'])) ?></td></tr>
                    </table>

                <?php endif; ?>

                <hr class="border-dashed">
                <p class="text-center small text-muted mb-0">Kode Struk: <?= $kode_struk ?></p>
                <p class="text-center small text-muted">Terima kasih telah peduli lingkungan ♻</p>
            </div>
        </div>
    </div>
</main>
<style>
.border-dashed { border-top: 1.5px dashed #cbd5cd; }
.struk-box { font-family: 'Courier New', monospace; }
</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
