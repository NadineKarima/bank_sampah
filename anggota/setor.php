<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$id_user = (int) $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sampah = (int) ($_POST['id_sampah'] ?? 0);
    $berat_kg = (float) ($_POST['berat_kg'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));

    if ($id_sampah <= 0 || $berat_kg <= 0 || $tanggal === '') {
        set_flash('error', 'Jenis sampah, berat, dan tanggal wajib diisi dengan benar.');
        redirect('setor.php');
    }

    $stmt = $pdo->prepare("SELECT id_sampah, jenis_sampah, harga_per_kg FROM sampah WHERE id_sampah = ?");
    $stmt->execute([$id_sampah]);
    $sampah = $stmt->fetch();

    if (!$sampah) {
        set_flash('error', 'Jenis sampah tidak ditemukan.');
        redirect('setor.php');
    }

    $total_rp = $berat_kg * (float) $sampah['harga_per_kg'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, tanggal, total_rp)
                               VALUES (?, ?, 'setor', ?, ?, ?)");
        $stmt->execute([$id_user, $id_sampah, $berat_kg, $tanggal . ' ' . date('H:i:s'), $total_rp]);

        $stmt = $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg + ? WHERE id_sampah = ?");
        $stmt->execute([$berat_kg, $id_sampah]);

        $stmt = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id_user = ? AND role IN ('guru','siswa')");
        $stmt->execute([$total_rp, $id_user]);

        $pdo->commit();
        set_flash('success', 'Setoran berhasil dicatat. Saldo Anda bertambah ' . rupiah($total_rp) . '.');
        redirect('setor.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        set_flash('error', 'Setoran gagal disimpan. Silakan coba lagi.');
        redirect('setor.php');
    }
}

$sampah_list = $pdo->query("SELECT * FROM sampah ORDER BY jenis_sampah ASC")->fetchAll();

$stmt = $pdo->prepare("SELECT saldo FROM users WHERE id_user = ?");
$stmt->execute([$id_user]);
$saldo = (float) $stmt->fetchColumn();

$page_title = 'Setor Sampah';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <div class="eyebrow">Transaksi Nasabah</div>
        <h1>Setor Sampah</h1>
        <p class="text-muted mb-0">Catat sampah yang kamu setorkan dan saldo akan bertambah otomatis.</p>
    </div>

    <?php show_flash(); ?>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card card-stat p-4">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h6 class="mb-1">Form Setoran</h6>
                        <small class="text-muted">Pastikan berat sesuai hasil penimbangan.</small>
                    </div>
                    <div class="stat-icon m-0"><?= icon('deposit') ?></div>
                </div>

                <?php if (empty($sampah_list)): ?>
                    <div class="alert alert-warning mb-0">Belum ada jenis sampah. Hubungi admin untuk menambahkan data sampah.</div>
                <?php else: ?>
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Jenis Sampah</label>
                            <select name="id_sampah" id="setorJenis" class="form-select" required>
                                <option value="">-- Pilih Jenis Sampah --</option>
                                <?php foreach ($sampah_list as $s): ?>
                                    <option value="<?= (int)$s['id_sampah'] ?>" data-harga="<?= (float)$s['harga_per_kg'] ?>">
                                        <?= clean($s['jenis_sampah']) ?> — <?= rupiah($s['harga_per_kg']) ?>/kg
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Berat Sampah (kg)</label>
                                <input type="number" name="berat_kg" id="setorBerat" class="form-control" min="0.01" step="0.01" placeholder="Contoh: 2.5" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Setor</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="setor-total-box mt-4">
                            <span>Perkiraan saldo bertambah</span>
                            <strong id="setorTotal">Rp 0</strong>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-success" <?= empty($sampah_list) ? 'disabled' : '' ?>>Simpan Setoran</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card card-stat p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon m-0"><?= icon('wallet') ?></div>
                    <div>
                        <h6 class="mb-1">Saldo Saat Ini</h6>
                        <small class="text-muted">Saldo akan diperbarui setelah setoran berhasil.</small>
                    </div>
                </div>
                <div class="setor-balance-number"><?= rupiah($saldo) ?></div>
                <hr>
                <div class="small text-muted">
                    <div class="mb-2"><strong>Alur setoran:</strong></div>
                    <div>1. Pilih jenis sampah.</div>
                    <div>2. Masukkan berat hasil penimbangan.</div>
                    <div>3. Sistem menghitung total berdasarkan harga/kg.</div>
                    <div>4. Transaksi, stok, dan saldo diperbarui otomatis.</div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenis = document.getElementById('setorJenis');
    const berat = document.getElementById('setorBerat');
    const total = document.getElementById('setorTotal');
    function hitung() {
        const option = jenis ? jenis.options[jenis.selectedIndex] : null;
        const harga = option ? parseFloat(option.dataset.harga || 0) : 0;
        const kg = parseFloat(berat?.value || 0);
        const nilai = harga * kg;
        total.textContent = 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(nilai);
    }
    jenis?.addEventListener('change', hitung);
    berat?.addEventListener('input', hitung);
    hitung();
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
