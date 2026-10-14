<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// ---- TAMBAH TRANSAKSI SETOR ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $id_user = (int) ($_POST['id_user'] ?? 0);
    $id_sampah = (int) ($_POST['id_sampah'] ?? 0);
    $berat_kg = (float) ($_POST['berat_kg'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');

    if ($id_user <= 0 || $id_sampah <= 0 || $berat_kg <= 0 || $tanggal === '') {
        set_flash('error', 'Semua field wajib diisi dengan benar.');
        redirect('transaksi.php');
    }

    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ? AND role IN ('guru','siswa')");
    $stmt->execute([$id_user]);
    if (!$stmt->fetch()) {
        set_flash('error', 'Nasabah tidak valid.');
        redirect('transaksi.php');
    }

    $stmt = $pdo->prepare("SELECT harga_per_kg FROM sampah WHERE id_sampah = ?");
    $stmt->execute([$id_sampah]);
    $sampah = $stmt->fetch();
    if (!$sampah) {
        set_flash('error', 'Jenis sampah tidak ditemukan.');
        redirect('transaksi.php');
    }

    $total_rp = $berat_kg * (float)$sampah['harga_per_kg'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, tanggal, total_rp) VALUES (?, ?, 'setor', ?, ?, ?)");
        $stmt->execute([$id_user, $id_sampah, $berat_kg, $tanggal . ' ' . date('H:i:s'), $total_rp]);
        $stmt = $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg + ? WHERE id_sampah = ?");
        $stmt->execute([$berat_kg, $id_sampah]);
        $stmt = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id_user = ?");
        $stmt->execute([$total_rp, $id_user]);
        $pdo->commit();
        set_flash('success', 'Transaksi berhasil dicatat. Saldo nasabah bertambah ' . rupiah($total_rp) . '.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', 'Gagal menyimpan transaksi.');
    }
    redirect('transaksi.php');
}

// ---- EDIT TRANSAKSI ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id_transaksi = (int) ($_POST['id_transaksi'] ?? 0);
    $id_user_baru = (int) ($_POST['id_user'] ?? 0);
    $id_sampah_baru = (int) ($_POST['id_sampah'] ?? 0);
    $berat_baru = (float) ($_POST['berat_kg'] ?? 0);
    $tanggal_baru = trim($_POST['tanggal'] ?? '');

    if ($id_transaksi <= 0 || $id_user_baru <= 0 || $id_sampah_baru <= 0 || $berat_baru <= 0 || $tanggal_baru === '') {
        set_flash('error', 'Data transaksi tidak valid.');
        redirect('transaksi.php');
    }

    try {
        $pdo->beginTransaction();

        // Ambil transaksi lama dengan lock agar stok/saldo konsisten.
        $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id_transaksi = ? FOR UPDATE");
        $stmt->execute([$id_transaksi]);
        $lama = $stmt->fetch();
        if (!$lama || $lama['tipe'] !== 'setor') throw new RuntimeException('Transaksi tidak ditemukan.');

        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ? AND role IN ('guru','siswa')");
        $stmt->execute([$id_user_baru]);
        if (!$stmt->fetch()) throw new RuntimeException('Nasabah tidak valid.');

        $stmt = $pdo->prepare("SELECT id_sampah, harga_per_kg FROM sampah WHERE id_sampah = ? FOR UPDATE");
        $stmt->execute([$id_sampah_baru]);
        $sampah_baru = $stmt->fetch();
        if (!$sampah_baru) throw new RuntimeException('Jenis sampah tidak ditemukan.');

        // Kembalikan efek transaksi lama.
        $stmt = $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg - ? WHERE id_sampah = ? AND stok_kg >= ?");
        $stmt->execute([(float)$lama['berat_kg'], (int)$lama['id_sampah'], (float)$lama['berat_kg']]);
        if ($stmt->rowCount() === 0) throw new RuntimeException('Stok lama tidak mencukupi untuk koreksi transaksi.');

        $stmt = $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id_user = ? AND saldo >= ?");
        $stmt->execute([(float)$lama['total_rp'], (int)$lama['id_user'], (float)$lama['total_rp']]);
        if ($stmt->rowCount() === 0) throw new RuntimeException('Saldo lama tidak mencukupi untuk koreksi transaksi.');

        $total_baru = $berat_baru * (float)$sampah_baru['harga_per_kg'];

        $stmt = $pdo->prepare("UPDATE transaksi SET id_user=?, id_sampah=?, berat_kg=?, tanggal=?, total_rp=? WHERE id_transaksi=?");
        $stmt->execute([$id_user_baru, $id_sampah_baru, $berat_baru, $tanggal_baru . ' ' . date('H:i:s'), $total_baru, $id_transaksi]);

        $stmt = $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg + ? WHERE id_sampah = ?");
        $stmt->execute([$berat_baru, $id_sampah_baru]);

        $stmt = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id_user = ?");
        $stmt->execute([$total_baru, $id_user_baru]);

        $pdo->commit();
        set_flash('success', 'Transaksi berhasil diperbarui. Stok dan saldo ikut disesuaikan.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', 'Edit transaksi gagal: ' . $e->getMessage());
    }
    redirect('transaksi.php');
}

// ---- HAPUS TRANSAKSI ----
if (isset($_GET['delete'])) {
    $id_transaksi = (int) $_GET['delete'];
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id_transaksi = ? FOR UPDATE");
        $stmt->execute([$id_transaksi]);
        $lama = $stmt->fetch();
        if (!$lama || $lama['tipe'] !== 'setor') throw new RuntimeException('Transaksi tidak ditemukan.');

        // Kembalikan efek transaksi ke stok & saldo sebelum baris dihapus.
        $stmt = $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg - ? WHERE id_sampah = ? AND stok_kg >= ?");
        $stmt->execute([(float)$lama['berat_kg'], (int)$lama['id_sampah'], (float)$lama['berat_kg']]);
        if ($stmt->rowCount() === 0) throw new RuntimeException('Stok tidak mencukupi untuk membatalkan transaksi ini.');

        $stmt = $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id_user = ? AND saldo >= ?");
        $stmt->execute([(float)$lama['total_rp'], (int)$lama['id_user'], (float)$lama['total_rp']]);
        if ($stmt->rowCount() === 0) throw new RuntimeException('Saldo nasabah tidak mencukupi untuk membatalkan transaksi ini.');

        $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id_transaksi = ?");
        $stmt->execute([$id_transaksi]);

        $pdo->commit();
        set_flash('success', 'Transaksi berhasil dihapus. Stok dan saldo sudah dikoreksi.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
    }
    redirect('transaksi.php');
}

$nasabah_list = $pdo->query("SELECT id_user, nama FROM users WHERE role IN ('guru','siswa') ORDER BY nama")->fetchAll();
$sampah_list = $pdo->query("SELECT * FROM sampah ORDER BY jenis_sampah")->fetchAll();
$transaksi_list = $pdo->query("SELECT t.*, u.nama, s.jenis_sampah FROM transaksi t JOIN users u ON u.id_user=t.id_user JOIN sampah s ON s.id_sampah=t.id_sampah WHERE t.tipe='setor' ORDER BY t.id_transaksi DESC")->fetchAll();

$page_title = 'Transaksi Setor';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div class="page-intro mb-0"><div class="eyebrow">Bank Sampah</div><h1>Transaksi Setor Sampah</h1></div>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdd" <?= empty($nasabah_list) || empty($sampah_list) ? 'disabled' : '' ?>>Catat Transaksi</button>
    </div>
    <?php show_flash(); ?>

    <?php if (empty($nasabah_list)): ?><div class="alert alert-warning">Belum ada nasabah terdaftar.</div><?php endif; ?>
    <?php if (empty($sampah_list)): ?><div class="alert alert-warning">Belum ada data jenis sampah. Tambahkan dulu di menu Data Sampah.</div><?php endif; ?>

    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>#</th><th>Tanggal</th><th>Nasabah</th><th>Jenis Sampah</th><th>Berat (kg)</th><th>Total</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($transaksi_list)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Belum ada transaksi</td></tr>
                <?php else: foreach ($transaksi_list as $t): ?>
                    <tr>
                        <td><?= (int)$t['id_transaksi'] ?></td>
                        <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                        <td><?= clean($t['nama']) ?></td>
                        <td><?= clean($t['jenis_sampah']) ?></td>
                        <td><?= number_format((float)$t['berat_kg'], 2, ',', '.') ?></td>
                        <td><?= rupiah($t['total_rp']) ?></td>
                        <td class="text-nowrap">
                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#modalEdit<?= (int)$t['id_transaksi'] ?>"><?= icon('edit') ?></button>
                            <a href="?delete=<?= (int)$t['id_transaksi'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin hapus transaksi ini? Stok dan saldo nasabah akan dikoreksi.')"><?= icon('trash') ?></a>
                        </td>
                    </tr>
<?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if (!empty($transaksi_list)): foreach ($transaksi_list as $t): ?>
<div class="modal fade" id="modalEdit<?= (int)$t['id_transaksi'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog app-modal-wide">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="edit"><input type="hidden" name="id_transaksi" value="<?= (int)$t['id_transaksi'] ?>">
            <div class="modal-header app-modal-header"><div class="modal-title-wrap"><div class="modal-icon"><?= icon('edit') ?></div><div><h5 class="modal-title mb-0">Edit Transaksi Setor</h5><p class="modal-subtitle mb-0">Koreksi data transaksi dan sesuaikan saldo serta stok.</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body app-modal-body"><div class="alert alert-info modal-note">Perubahan akan otomatis mengoreksi saldo nasabah dan stok sampah berdasarkan transaksi lama.</div><div class="modal-section-label">Detail transaksi</div><div class="row g-3"><div class="col-md-6"><label class="form-label">Nasabah</label><select name="id_user" class="form-select" required><?php foreach ($nasabah_list as $n): ?><option value="<?= (int)$n['id_user'] ?>" <?= (int)$n['id_user']===(int)$t['id_user']?'selected':'' ?>><?= clean($n['nama']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Jenis Sampah</label><select name="id_sampah" class="form-select" required><?php foreach ($sampah_list as $s): ?><option value="<?= (int)$s['id_sampah'] ?>" <?= (int)$s['id_sampah']===(int)$t['id_sampah']?'selected':'' ?>><?= clean($s['jenis_sampah']) ?> — <?= rupiah($s['harga_per_kg']) ?>/kg</option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Berat (kg)</label><input type="number" step="0.01" min="0.01" name="berat_kg" class="form-control" value="<?= (float)$t['berat_kg'] ?>" required></div><div class="col-md-6"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d', strtotime($t['tanggal'])) ?>" max="<?= date('Y-m-d') ?>" required></div></div></div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
<?php endforeach; endif; ?>

<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog app-modal-wide">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="add">
            <div class="modal-header app-modal-header"><div class="modal-title-wrap"><div class="modal-icon">+</div><div><h5 class="modal-title mb-0">Catat Transaksi Setor</h5><p class="modal-subtitle mb-0">Masukkan data setor guru atau siswa.</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body app-modal-body"><div class="modal-section-label">Detail transaksi</div>
                <div class="mb-3"><label class="form-label">Nasabah</label><select name="id_user" class="form-select" required><option value="">-- Pilih Nasabah --</option><?php foreach ($nasabah_list as $n): ?><option value="<?= (int)$n['id_user'] ?>"><?= clean($n['nama']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Jenis Sampah</label><select name="id_sampah" class="form-select" required><option value="">-- Pilih Jenis Sampah --</option><?php foreach ($sampah_list as $s): ?><option value="<?= (int)$s['id_sampah'] ?>"><?= clean($s['jenis_sampah']) ?> (<?= rupiah($s['harga_per_kg']) ?>/kg)</option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Berat (kg)</label><input type="number" step="0.01" min="0.01" name="berat_kg" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Transaksi</button></div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
