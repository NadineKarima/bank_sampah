<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

/* =========================================================
   KELOLA PENARIKAN GURU & SISWA
   - menunggu  -> admin dapat menyelesaikan / menolak
   - selesai   -> saldo sudah dipotong
   - ditolak   -> saldo tidak dipotong
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_tarik = (int) ($_POST['id_tarik'] ?? 0);

    if ($id_tarik <= 0) {
        set_flash('error', 'Data penarikan tidak valid.');
        redirect('penarikan.php');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT ts.*, u.nama, u.username, u.role, u.saldo
            FROM tarik_saldo ts
            JOIN users u ON u.id_user = ts.id_user
            WHERE ts.id_tarik = ?
            FOR UPDATE
        ");
        $stmt->execute([$id_tarik]);
        $tarik = $stmt->fetch();

        if (!$tarik || !in_array($tarik['role'], ['guru', 'siswa'], true)) {
            throw new RuntimeException('Data penarikan tidak ditemukan.');
        }

        if ($tarik['status'] !== 'menunggu') {
            throw new RuntimeException('Penarikan ini sudah diproses.');
        }

        if ($action === 'selesaikan') {
            // Potong saldo hanya saat admin menyelesaikan penarikan.
            $stmt = $pdo->prepare("
                UPDATE users
                SET saldo = saldo - ?
                WHERE id_user = ? AND saldo >= ?
            ");
            $stmt->execute([$tarik['nominal'], $tarik['id_user'], $tarik['nominal']]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('Saldo pengguna tidak mencukupi untuk menyelesaikan penarikan.');
            }

            $stmt = $pdo->prepare("UPDATE tarik_saldo SET status = 'selesai' WHERE id_tarik = ?");
            $stmt->execute([$id_tarik]);

            $pdo->commit();
            set_flash('success', 'Penarikan ' . $tarik['nama'] . ' berhasil diselesaikan.');
        } elseif ($action === 'tolak') {
            $stmt = $pdo->prepare("UPDATE tarik_saldo SET status = 'ditolak' WHERE id_tarik = ?");
            $stmt->execute([$id_tarik]);

            $pdo->commit();
            set_flash('success', 'Penarikan ' . $tarik['nama'] . ' ditolak. Saldo pengguna tidak berubah.');
        } else {
            throw new RuntimeException('Aksi tidak dikenal.');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        set_flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Gagal memproses penarikan.');
    }

    redirect('penarikan.php');
}

$riwayat = $pdo->query("
    SELECT ts.*, u.nama, u.username, u.role, u.saldo
    FROM tarik_saldo ts
    JOIN users u ON u.id_user = ts.id_user
    WHERE u.role IN ('guru', 'siswa')
    ORDER BY
        CASE WHEN ts.status = 'menunggu' THEN 0 ELSE 1 END,
        ts.id_tarik DESC
    LIMIT 300
")->fetchAll();

$total = $pdo->query("
    SELECT COALESCE(SUM(nominal),0)
    FROM tarik_saldo ts
    JOIN users u ON u.id_user = ts.id_user
    WHERE ts.status = 'selesai' AND u.role IN ('guru','siswa')
")->fetchColumn();

$menunggu = $pdo->query("
    SELECT COUNT(*)
    FROM tarik_saldo ts
    JOIN users u ON u.id_user = ts.id_user
    WHERE ts.status = 'menunggu' AND u.role IN ('guru','siswa')
")->fetchColumn();

$page_title = 'Penarikan Saldo';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="page-intro mb-3">
        <div class="eyebrow">Manajemen Keuangan</div>
        <h4 class="mb-1">Penarikan Saldo</h4>
        <p class="text-muted mb-0">Kelola permintaan penarikan saldo dari Guru dan Siswa.</p>
    </div>

    <?php show_flash(); ?>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <small class="text-muted">Menunggu Diproses</small>
                <h4 class="mb-0 mt-1"><?= (int) $menunggu ?></h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <small class="text-muted">Total Penarikan Selesai</small>
                <h4 class="mb-0 mt-1"><?= rupiah($total) ?></h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <small class="text-muted">Data Penarikan</small>
                <h4 class="mb-0 mt-1"><?= count($riwayat) ?> transaksi</h4>
            </div>
        </div>
    </div>

    <div class="card card-stat p-3">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h6 class="mb-1">Daftar Penarikan Guru & Siswa</h6>
                <small class="text-muted">Penarikan berstatus menunggu perlu diproses oleh admin.</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Saldo Saat Ini</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$riwayat): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada penarikan dari Guru atau Siswa.</td>
                    </tr>
                <?php else: foreach ($riwayat as $r): ?>
                    <?php
                        $status = $r['status'];
                        $badge = match ($status) {
                            'selesai' => 'success',
                            'menunggu' => 'warning',
                            'ditolak' => 'danger',
                            'disetujui' => 'info',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td><?= (int) $r['id_tarik'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                        <td>
                            <strong><?= clean($r['nama']) ?></strong>
                            <small class="d-block text-muted">@<?= clean($r['username']) ?></small>
                        </td>
                        <td><span class="role-badge"><?= clean(ucfirst($r['role'])) ?></span></td>
                        <td><?= rupiah($r['saldo']) ?></td>
                        <td><strong><?= rupiah($r['nominal']) ?></strong></td>
                        <td>
                            <span class="badge bg-<?= $badge ?> text-capitalize"><?= clean($status) ?></span>
                        </td>
                        <td>
                            <?php if ($status === 'menunggu'): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Selesaikan penarikan <?= clean($r['nama']) ?> sebesar <?= rupiah($r['nominal']) ?>?')">
                                        <input type="hidden" name="action" value="selesaikan">
                                        <input type="hidden" name="id_tarik" value="<?= (int) $r['id_tarik'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Selesaikan</button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Tolak penarikan <?= clean($r['nama']) ?>?')">
                                        <input type="hidden" name="action" value="tolak">
                                        <input type="hidden" name="id_tarik" value="<?= (int) $r['id_tarik'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Tolak</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Sudah diproses</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
