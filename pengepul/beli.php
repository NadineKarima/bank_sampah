<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('pengepul');
$id_user = (int) $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sampah = (int) ($_POST['id_sampah'] ?? 0);
    $berat = round((float) ($_POST['berat_kg'] ?? 0), 2);

    if (!consume_form_token('beli')) {
        set_flash('error', 'Permintaan sudah diproses atau kedaluwarsa. Silakan ulangi dari halaman ini.');
    } elseif ($id_sampah <= 0 || $berat <= 0) {
        set_flash('error', 'Pilih jenis sampah dan isi berat dengan benar.');
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM sampah WHERE id_sampah = ? FOR UPDATE");
            $stmt->execute([$id_sampah]);
            $s = $stmt->fetch();
            if (!$s) throw new RuntimeException('Jenis sampah tidak ditemukan.');
            if ((float)$s['stok_kg'] < $berat) {
                throw new RuntimeException('Stok ' . $s['jenis_sampah'] . ' tidak cukup. Tersedia ' . number_format((float)$s['stok_kg'], 2, ',', '.') . ' kg.');
            }

            $total = round($berat * (float)$s['harga_jual'], 2);

            $stmt = $pdo->prepare("SELECT saldo FROM users WHERE id_user = ? FOR UPDATE");
            $stmt->execute([$id_user]);
            $saldo = (float) $stmt->fetchColumn();
            if ($saldo < $total) {
                throw new RuntimeException('Saldo tidak cukup. Butuh ' . rupiah($total) . ', saldo kamu ' . rupiah($saldo) . '. Silakan top up dulu.');
            }

            $pdo->prepare("UPDATE sampah SET stok_kg = stok_kg - ? WHERE id_sampah = ?")->execute([$berat, $id_sampah]);
            $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id_user = ?")->execute([$total, $id_user]);
            $pdo->prepare("INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, total_rp, tanggal) VALUES (?, ?, 'penjualan', ?, ?, NOW())")
                ->execute([$id_user, $id_sampah, $berat, $total]);

            $pdo->commit();
            set_flash('success', 'Pembelian berhasil: ' . number_format($berat, 2, ',', '.') . ' kg ' . $s['jenis_sampah'] . ' senilai ' . rupiah($total) . '.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Pembelian gagal diproses.');
        }
    }
    redirect('beli.php');
}

$saldo = saldo_user($pdo, $id_user);
$list = $pdo->query("SELECT * FROM sampah WHERE stok_kg > 0 ORDER BY jenis_sampah")->fetchAll();
$pilih = (int) ($_GET['jenis'] ?? 0);

$page_title = 'Beli Sampah';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_pengepul.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Beli Sampah</h4>
        <p class="text-muted mb-0">Pembelian memotong saldo kamu dan mengurangi stok Bank Sampah.</p>
    </div>
    <?php show_flash(); ?>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card card-stat p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Form Pembelian</h6>
                    <span class="badge bg-success-subtle text-success-emphasis">Saldo <?= rupiah($saldo) ?></span>
                </div>
                <?php if (empty($list)): ?>
                    <div class="alert alert-warning mb-0">Belum ada stok sampah yang bisa dibeli.</div>
                <?php else: ?>
                <form method="POST" id="formBeli">
                    <?= form_token_field('beli') ?>
                    <div class="mb-3">
                        <label class="form-label" for="selJenis">Jenis Sampah</label>
                        <select name="id_sampah" id="selJenis" class="form-select" required>
                            <option value="">-- Pilih jenis sampah --</option>
                            <?php foreach ($list as $s): ?>
                                <option value="<?= (int)$s['id_sampah'] ?>" data-harga="<?= (float)$s['harga_jual'] ?>" data-stok="<?= (float)$s['stok_kg'] ?>" <?= $pilih === (int)$s['id_sampah'] ? 'selected' : '' ?>>
                                    <?= clean($s['jenis_sampah']) ?> — <?= rupiah($s['harga_jual']) ?>/kg
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="infoStok"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inpBerat">Berat (kg)</label>
                        <input type="number" step="0.01" min="0.01" name="berat_kg" id="inpBerat" class="form-control" required>
                    </div>
                    <div class="setor-total-box mb-3">
                        <small class="text-muted d-block">Total bayar</small>
                        <strong class="fs-5" id="totalBayar">Rp 0</strong>
                        <small class="d-block mt-1" id="infoSaldo"></small>
                    </div>
                    <button type="submit" class="btn btn-success w-100" id="btnBeli">Beli Sekarang</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="card card-stat p-3">
                <h6 class="mb-3 px-1">Stok Tersedia</h6>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Jenis</th><th>Harga Jual/kg</th><th>Stok (kg)</th></tr></thead>
                        <tbody>
                        <?php if (empty($list)): ?><tr><td colspan="3" class="text-center text-muted">Stok kosong</td></tr>
                        <?php else: foreach ($list as $s): ?>
                            <tr><td><?= clean($s['jenis_sampah']) ?></td><td><?= rupiah($s['harga_jual']) ?></td><td><?= number_format((float)$s['stok_kg'], 2, ',', '.') ?></td></tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('selJenis'), berat = document.getElementById('inpBerat');
    if (!sel) return;
    var saldo = <?= json_encode($saldo) ?>;
    var fmt = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };
    function hitung() {
        var o = sel.options[sel.selectedIndex], harga = parseFloat(o.dataset.harga || 0), stok = parseFloat(o.dataset.stok || 0);
        var kg = parseFloat(berat.value || 0), total = harga * kg;
        document.getElementById('infoStok').textContent = stok ? 'Stok tersedia: ' + stok.toLocaleString('id-ID') + ' kg' : '';
        document.getElementById('totalBayar').textContent = fmt(total);
        var info = document.getElementById('infoSaldo'), ok = total <= saldo && (!stok || kg <= stok);
        info.textContent = total && total > saldo ? 'Saldo tidak cukup, silakan top up.' : (stok && kg > stok ? 'Melebihi stok tersedia.' : '');
        info.className = 'd-block mt-1 text-danger';
        document.getElementById('btnBeli').disabled = !!(kg && !ok);
        if (stok) berat.max = stok;
    }
    sel.addEventListener('change', hitung); berat.addEventListener('input', hitung); hitung();
    document.getElementById('formBeli').addEventListener('submit', function () { document.getElementById('btnBeli').disabled = true; });
});
</script>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
