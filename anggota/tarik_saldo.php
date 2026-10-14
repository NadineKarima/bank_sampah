<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['guru', 'siswa']);

$id_user = (int) $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT saldo FROM users WHERE id_user = ?");
$stmt->execute([$id_user]);
$saldo = (float) $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal = (float) ($_POST['nominal'] ?? 0);

    if ($nominal < 1) {
        set_flash('error', 'Minimal penarikan adalah Rp 10.');
        redirect('tarik_saldo.php');
    }

    if ($nominal > $saldo) {
        set_flash('error', 'Nominal penarikan melebihi saldo Anda.');
        redirect('tarik_saldo.php');
    }

    try {
        // Penarikan masuk sebagai permintaan menunggu.
        // Saldo baru dipotong setelah admin menyelesaikannya.
        $stmt = $pdo->prepare("
            INSERT INTO tarik_saldo (id_user, nominal, tanggal, status)
            VALUES (?, ?, NOW(), 'menunggu')
        ");
        $stmt->execute([$id_user, $nominal]);

        set_flash('success', 'Permintaan penarikan berhasil dikirim. Tunggu admin memprosesnya.');
    } catch (Throwable $e) {
        set_flash('error', 'Gagal mengirim permintaan penarikan.');
    }

    redirect('tarik_saldo.php');
}

$stmt = $pdo->prepare("SELECT * FROM tarik_saldo WHERE id_user = ? ORDER BY id_tarik DESC");
$stmt->execute([$id_user]);
$riwayat_tarik = $stmt->fetchAll();

$page_title = 'Tarik Saldo';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_anggota.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <div class="eyebrow">Keuangan</div>
        <h4 class="mb-1">Tarik Saldo</h4>
        <p class="text-muted mb-0">Ajukan penarikan saldo dan lihat status pemrosesannya.</p>
    </div>

    <?php show_flash(); ?>

    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <div class="card card-stat p-3">
                <p class="text-muted mb-1">Saldo Anda saat ini</p>
                <h4 class="text-success mb-3"><?= rupiah($saldo) ?></h4>

                <form method="POST" onsubmit="return confirm('Kirim permintaan penarikan sekarang?')" data-enter-submit>
                    <div class="mb-3">
                        <label class="form-label">Nominal Penarikan (Rp)</label>
                        <input type="number" step="10" min="10" max="<?= $saldo ?>" name="nominal"
                               class="form-control" required <?= $saldo < 10 ? 'disabled' : '' ?>>
                    </div>
                    <button type="submit" class="btn btn-success w-100" <?= $saldo < 10 ? 'disabled' : '' ?>>
                        Ajukan Penarikan
                    </button>
                </form>
                <small class="text-muted d-block mt-2">
                    Saldo belum berkurang saat pengajuan. Admin akan memproses permintaan terlebih dahulu.
                </small>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-stat p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Riwayat Penarikan</h6>
                    <span class="badge bg-secondary"><?= count($riwayat_tarik) ?> transaksi</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>Tanggal</th><th>Nominal</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($riwayat_tarik)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada riwayat penarikan</td></tr>
                        <?php else: foreach ($riwayat_tarik as $r): ?>
                            <?php $badge = ['selesai' => 'success', 'menunggu' => 'warning', 'ditolak' => 'danger']; ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                                <td><?= rupiah($r['nominal']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $badge[$r['status']] ?? 'secondary' ?> text-capitalize">
                                        <?= clean($r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['status'] !== 'ditolak'): ?>
                                        <a href="struk.php?type=tarik&id=<?= (int) $r['id_tarik'] ?>" class="small">Struk</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
