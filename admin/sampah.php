<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// ---- TAMBAH ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $jenis = trim($_POST['jenis_sampah'] ?? '');
    $harga = (float) ($_POST['harga_per_kg'] ?? 0);
    $harga_jual = (float) ($_POST['harga_jual'] ?? 0);
    $stok = (float) ($_POST['stok_kg'] ?? 0);
    if ($jenis === '' || $harga <= 0 || $harga_jual < $harga || $stok < 0) {
        set_flash('error', 'Jenis sampah, harga beli (>0), harga jual (minimal sama dengan harga beli), dan stok (tidak boleh minus) wajib diisi dengan benar.');
        redirect('sampah.php');
    }

    $stmt = $pdo->prepare("SELECT id_sampah FROM sampah WHERE jenis_sampah = ?");
    $stmt->execute([$jenis]);
    if ($stmt->fetch()) {
        set_flash('error', 'Jenis sampah dengan nama tersebut sudah ada.');
        redirect('sampah.php');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO sampah (jenis_sampah, harga_per_kg, harga_jual, stok_kg) VALUES (?, ?, ?, ?)");
        $stmt->execute([$jenis, $harga, $harga_jual, $stok]);
        set_flash('success', 'Jenis sampah berhasil ditambahkan.');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal menambahkan data sampah.');
    }
    redirect('sampah.php');
}

// ---- EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int) ($_POST['id_sampah'] ?? 0);
    $jenis = trim($_POST['jenis_sampah'] ?? '');
    $harga = (float) ($_POST['harga_per_kg'] ?? 0);
    $harga_jual = (float) ($_POST['harga_jual'] ?? 0);
    $stok = (float) ($_POST['stok_kg'] ?? 0);

    if ($id <= 0 || $jenis === '' || $harga <= 0 || $harga_jual < $harga || $stok < 0) {
        set_flash('error', 'Jenis sampah, harga beli (>0), harga jual (minimal sama dengan harga beli), dan stok (tidak boleh minus) wajib diisi dengan benar.');
        redirect('sampah.php');
    }

    $stmt = $pdo->prepare("SELECT id_sampah FROM sampah WHERE jenis_sampah = ? AND id_sampah <> ?");
    $stmt->execute([$jenis, $id]);
    if ($stmt->fetch()) {
        set_flash('error', 'Jenis sampah dengan nama tersebut sudah ada.');
        redirect('sampah.php');
    }

    try {
        $stmt = $pdo->prepare("UPDATE sampah SET jenis_sampah=?, harga_per_kg=?, harga_jual=?, stok_kg=? WHERE id_sampah=?");
        $stmt->execute([$jenis, $harga, $harga_jual, $stok, $id]);
        set_flash('success', 'Data sampah berhasil diperbarui.');
    } catch (PDOException $e) {
        set_flash('error', 'Gagal memperbarui data sampah.');
    }
    redirect('sampah.php');
}

// ---- HAPUS ----
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM sampah WHERE id_sampah=?");
        $stmt->execute([$id]);
        set_flash('success', 'Data sampah berhasil dihapus.');
    } catch (PDOException $e) {
        set_flash('error', 'Tidak bisa menghapus, data ini masih dipakai di transaksi.');
    }
    redirect('sampah.php');
}

$data_sampah = $pdo->query("SELECT * FROM sampah ORDER BY id_sampah DESC")->fetchAll();

$page_title = 'Data Sampah';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div class="page-intro mb-0"><div class="eyebrow">Bank Sampah</div><h1>Data Jenis Sampah</h1></div>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdd">
            Tambah Jenis Sampah
        </button>
    </div>
    <?php show_flash(); ?>

    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>#</th><th>Jenis Sampah</th><th>Harga Beli/kg</th><th>Harga Jual/kg</th><th>Stok (kg)</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php if (empty($data_sampah)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                <?php else: foreach ($data_sampah as $s): ?>
                    <tr>
                        <td><?= $s['id_sampah'] ?></td>
                        <td><?= clean($s['jenis_sampah']) ?></td>
                        <td><?= rupiah($s['harga_per_kg']) ?></td>
                        <td><?= rupiah($s['harga_jual']) ?></td>
                        <td><?= number_format($s['stok_kg'], 1) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Edit"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $s['id_sampah'] ?>">
                                <?= icon('edit') ?>
                            </button>
                            <a href="?delete=<?= $s['id_sampah'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus"
                               onclick="return confirm('Yakin hapus data ini?')">
                                <?= icon('trash') ?>
                            </a>
                        </td>
                    </tr>
<?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if (!empty($data_sampah)): foreach ($data_sampah as $s): ?>
<div class="modal fade" id="modalEdit<?= $s['id_sampah'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="edit"><input type="hidden" name="id_sampah" value="<?= $s['id_sampah'] ?>">
            <div class="modal-header app-modal-header"><div class="modal-title-wrap"><div class="modal-icon">♻</div><div><h5 class="modal-title mb-0">Edit Jenis Sampah</h5><p class="modal-subtitle mb-0">Perbarui harga dan stok jenis sampah.</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body app-modal-body"><div class="modal-section-label">Data sampah</div><div class="row g-3"><div class="col-12"><label class="form-label">Jenis Sampah</label><input type="text" name="jenis_sampah" class="form-control" value="<?= clean($s['jenis_sampah']) ?>" required></div><div class="col-md-6"><label class="form-label">Harga beli/kg (Rp)</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" step="0.01" min="0.01" name="harga_per_kg" class="form-control" value="<?= $s['harga_per_kg'] ?>" required></div></div><div class="col-md-6"><label class="form-label">Harga jual/kg (Rp)</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" step="0.01" min="0.01" name="harga_jual" class="form-control" value="<?= $s['harga_jual'] ?>" required></div></div><div class="col-md-6"><label class="form-label">Stok (kg)</label><input type="number" step="0.01" name="stok_kg" class="form-control" value="<?= $s['stok_kg'] ?>" required></div></div></div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
<?php endforeach; endif; ?>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="add">
            <div class="modal-header app-modal-header"><div class="modal-title-wrap"><div class="modal-icon">+</div><div><h5 class="modal-title mb-0">Tambah Jenis Sampah</h5><p class="modal-subtitle mb-0">Tambahkan jenis sampah beserta harga dan stok awal.</p></div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body app-modal-body"><div class="modal-section-label">Data sampah</div>
                <div class="mb-3">
                    <label class="form-label">Jenis Sampah</label>
                    <input type="text" name="jenis_sampah" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga beli per kg (Rp) <small class="text-muted">— dibayar ke Guru/Siswa</small></label>
                    <input type="number" step="0.01" min="0.01" name="harga_per_kg" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga jual per kg (Rp) <small class="text-muted">— dibayar Pengepul</small></label>
                    <input type="number" step="0.01" min="0.01" name="harga_jual" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stok Awal (kg)</label>
                    <input type="number" step="0.01" name="stok_kg" class="form-control" value="0" required>
                </div>
            </div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Data</button></div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
