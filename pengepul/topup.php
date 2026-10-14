<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('pengepul');
$id_user = (int) $_SESSION['id_user'];

$metode_list = ['M-Banking', 'E-Money', 'Alfamart', 'Indomaret'];
$MIN = 10000; $MAX = 5000000;
$struk_id = (int) ($_GET['struk'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal = round((float) ($_POST['nominal'] ?? 0));
    $metode = trim($_POST['metode'] ?? '');

    if (!consume_form_token('topup')) {
        set_flash('error', 'Permintaan sudah diproses atau kedaluwarsa. Silakan ulangi.');
    } elseif (!in_array($metode, $metode_list, true)) {
        set_flash('error', 'Pilih metode top up.');
    } elseif ($nominal < $MIN || $nominal > $MAX) {
        set_flash('error', 'Nominal top up harus antara ' . rupiah($MIN) . ' dan ' . rupiah($MAX) . '.');
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id_user = ?");
            $stmt->execute([$nominal, $id_user]);

            $stmt = $pdo->prepare("
                INSERT INTO transaksi
                    (id_user, id_sampah, tipe, berat_kg, total_rp, metode_topup, tanggal)
                VALUES (?, NULL, 'topup', 0, ?, ?, NOW())
            ");
            $stmt->execute([$id_user, $nominal, $metode]);
            $new_id = (int) $pdo->lastInsertId();

            $pdo->commit();
            redirect('topup.php?struk=' . $new_id);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash('error', 'Top up gagal diproses.');
            redirect('topup.php');
        }
    }
    redirect('topup.php');
}

$saldo = saldo_user($pdo, $id_user);

$struk = null;
if ($struk_id > 0) {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama, u.username
        FROM transaksi t
        JOIN users u ON u.id_user = t.id_user
        WHERE t.id_transaksi = ? AND t.id_user = ? AND t.tipe = 'topup'
    ");
    $stmt->execute([$struk_id, $id_user]);
    $struk = $stmt->fetch();
}

$page_title = 'Top Up Saldo';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_pengepul.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <h4 class="mb-1">Top Up Saldo</h4>
        <p class="text-muted mb-0">Tambah saldo Pengepul dan simpan bukti top up secara digital.</p>
    </div>
    <?php show_flash(); ?>

    <?php if ($struk): ?>
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card card-stat p-4 topup-receipt">
                <div class="text-center">
                    <img src="<?= $asset_path ?>assets/img/bank_sampah_icon.png" height="48" class="mb-2" alt="Bank Sampah">
                    <h5 class="fw-bold mb-0">Bank Sampah</h5>
                    <small class="text-muted">Bukti Top Up Saldo</small>
                </div>
                <hr class="border-dashed">
                <div class="text-center mb-3">
                    <div class="receipt-success-mark">✓</div>
                    <div class="fw-bold text-success mt-2">Top Up Berhasil</div>
                    <div class="display-6 fw-bold mt-1"><?= rupiah($struk['total_rp']) ?></div>
                </div>
                <table class="table table-borderless table-sm mb-3">
                    <tr><td class="text-muted">Nama</td><td class="text-end fw-semibold"><?= clean($struk['nama']) ?></td></tr>
                    <tr><td class="text-muted">Metode</td><td class="text-end"><?= clean($struk['metode_topup'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Tanggal</td><td class="text-end"><?= date('d/m/Y H:i', strtotime($struk['tanggal'])) ?></td></tr>
                    <tr><td class="text-muted">Kode Transaksi</td><td class="text-end fw-semibold">TOPUP-<?= str_pad((int)$struk['id_transaksi'], 6, '0', STR_PAD_LEFT) ?></td></tr>
                </table>
                <div class="receipt-total">
                    <span>Saldo setelah top up</span>
                    <strong><?= rupiah($saldo) ?></strong>
                </div>
                <div class="d-flex gap-2 mt-3 no-print">
                    <button type="button" class="btn btn-success flex-fill" onclick="window.print()">Cetak / Simpan PDF</button>
                    <a href="topup.php" class="btn btn-outline-success">Top Up Lagi</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card card-stat p-4">
                <small class="text-muted">Saldo saat ini</small>
                <h3 class="mb-3"><?= rupiah($saldo) ?></h3>
                <form method="POST" id="formTopup">
                    <?= form_token_field('topup') ?>
                    <label class="form-label">Metode</label>
                    <div class="row g-2 mb-3">
                        <?php foreach ($metode_list as $i => $m): ?>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="metode" id="m<?= $i ?>" value="<?= clean($m) ?>" required>
                            <label class="btn btn-outline-success w-100" for="m<?= $i ?>"><?= clean($m) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <label class="form-label" for="nominal">Nominal</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal" id="nominal" class="form-control" min="<?= $MIN ?>" max="<?= $MAX ?>" step="1000" required>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ([20000, 50000, 100000, 200000, 500000] as $n): ?>
                            <button type="button" class="btn btn-sm btn-light" data-nominal="<?= $n ?>"><?= rupiah($n) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text mb-3">Minimal <?= rupiah($MIN) ?>, maksimal <?= rupiah($MAX) ?>.</div>
                    <button type="submit" class="btn btn-success w-100" id="btnTopup">Top Up</button>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="card card-stat p-4 h-100">
                <h6 class="mb-2">Cara Top Up</h6>
                <p class="text-muted small">Untuk simulasi aplikasi sekolah, saldo langsung ditambahkan setelah top up berhasil.</p>
                <div class="row g-2">
                    <?php foreach ($metode_list as $m): ?>
                    <div class="col-6">
                        <div class="topup-method-card">
                            <strong><?= clean($m) ?></strong>
                            <small class="text-muted">Metode pembayaran</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-nominal]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('nominal').value = b.dataset.nominal;
        });
    });
    var form = document.getElementById('formTopup');
    if (form) form.addEventListener('submit', function () {
        document.getElementById('btnTopup').disabled = true;
    });
});
</script>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
