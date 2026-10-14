<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

/* Ringkasan pengguna & saldo */
$total_siswa = $pdo->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn();
$total_guru = $pdo->query("SELECT COUNT(*) FROM users WHERE role='guru'")->fetchColumn();
$total_pengepul = $pdo->query("SELECT COUNT(*) FROM users WHERE role='pengepul'")->fetchColumn();
$total_saldo_beredar = $pdo->query("SELECT COALESCE(SUM(saldo),0) FROM users WHERE role IN ('guru','siswa')")->fetchColumn();
$total_ditarik = $pdo->query("SELECT COALESCE(SUM(nominal),0) FROM tarik_saldo WHERE status='selesai'")->fetchColumn();
$pending_tarik = $pdo->query("SELECT COUNT(*) FROM tarik_saldo WHERE status='menunggu'")->fetchColumn();

/* Statistik hari ini */
$stat_hari = $pdo->query("
    SELECT
        COALESCE(SUM(CASE WHEN tipe='setor' THEN berat_kg ELSE 0 END),0) AS setor_kg,
        COALESCE(SUM(CASE WHEN tipe='setor' THEN total_rp ELSE 0 END),0) AS setor_rp,
        COALESCE(SUM(CASE WHEN tipe='penjualan' THEN berat_kg ELSE 0 END),0) AS jual_kg,
        COALESCE(SUM(CASE WHEN tipe='penjualan' THEN total_rp ELSE 0 END),0) AS jual_rp,
        COUNT(CASE WHEN tipe='setor' THEN 1 END) AS setor_n,
        COUNT(CASE WHEN tipe='penjualan' THEN 1 END) AS jual_n
    FROM transaksi
    WHERE DATE(tanggal) = CURDATE()
")->fetch();

/* Statistik bulan berjalan */
$stat_bulan = $pdo->query("
    SELECT
        COALESCE(SUM(CASE WHEN tipe='setor' THEN berat_kg ELSE 0 END),0) AS setor_kg,
        COALESCE(SUM(CASE WHEN tipe='setor' THEN total_rp ELSE 0 END),0) AS setor_rp,
        COALESCE(SUM(CASE WHEN tipe='penjualan' THEN berat_kg ELSE 0 END),0) AS jual_kg,
        COALESCE(SUM(CASE WHEN tipe='penjualan' THEN total_rp ELSE 0 END),0) AS jual_rp,
        COUNT(CASE WHEN tipe='setor' THEN 1 END) AS setor_n,
        COUNT(CASE WHEN tipe='penjualan' THEN 1 END) AS jual_n
    FROM transaksi
    WHERE YEAR(tanggal)=YEAR(CURDATE()) AND MONTH(tanggal)=MONTH(CURDATE())
")->fetch();

$total_stok = $pdo->query("SELECT COALESCE(SUM(stok_kg),0) FROM sampah")->fetchColumn();

/* Grafik garis transaksi */
$periode = $_GET['periode'] ?? '7_hari';
$allowed_periode = ['hari_ini', '7_hari', 'bulan_ini'];
if (!in_array($periode, $allowed_periode, true)) {
    $periode = '7_hari';
}

$chart_labels = [];
$chart_setor = [];
$chart_jual = [];

if ($periode === 'hari_ini') {
    for ($h = 0; $h <= 23; $h++) {
        $chart_labels[] = str_pad((string)$h, 2, '0', STR_PAD_LEFT) . ':00';
        $chart_setor[] = 0;
        $chart_jual[] = 0;
    }

    $stmt_chart = $pdo->query("
        SELECT HOUR(tanggal) AS jam,
               COALESCE(SUM(CASE WHEN tipe='setor' THEN berat_kg ELSE 0 END),0) AS setor_kg,
               COALESCE(SUM(CASE WHEN tipe='penjualan' THEN berat_kg ELSE 0 END),0) AS jual_kg
        FROM transaksi
        WHERE DATE(tanggal)=CURDATE()
        GROUP BY HOUR(tanggal)
        ORDER BY HOUR(tanggal)
    ");
    foreach ($stmt_chart as $r) {
        $jam = (int)$r['jam'];
        $chart_setor[$jam] = (float)$r['setor_kg'];
        $chart_jual[$jam] = (float)$r['jual_kg'];
    }
} elseif ($periode === 'bulan_ini') {
    $hari_dalam_bulan = (int)date('t');
    for ($d = 1; $d <= $hari_dalam_bulan; $d++) {
        $chart_labels[] = (string)$d;
        $chart_setor[] = 0;
        $chart_jual[] = 0;
    }

    $stmt_chart = $pdo->query("
        SELECT DAY(tanggal) AS hari,
               COALESCE(SUM(CASE WHEN tipe='setor' THEN berat_kg ELSE 0 END),0) AS setor_kg,
               COALESCE(SUM(CASE WHEN tipe='penjualan' THEN berat_kg ELSE 0 END),0) AS jual_kg
        FROM transaksi
        WHERE YEAR(tanggal)=YEAR(CURDATE()) AND MONTH(tanggal)=MONTH(CURDATE())
        GROUP BY DAY(tanggal)
        ORDER BY DAY(tanggal)
    ");
    foreach ($stmt_chart as $r) {
        $i = (int)$r['hari'] - 1;
        if ($i >= 0 && $i < count($chart_labels)) {
            $chart_setor[$i] = (float)$r['setor_kg'];
            $chart_jual[$i] = (float)$r['jual_kg'];
        }
    }
} else {
    for ($i = 6; $i >= 0; $i--) {
        $date = new DateTimeImmutable('-' . $i . ' days');
        $chart_labels[] = $date->format('d/m');
        $chart_setor[] = 0;
        $chart_jual[] = 0;
    }

    $stmt_chart = $pdo->query("
        SELECT DATE(tanggal) AS tgl,
               COALESCE(SUM(CASE WHEN tipe='setor' THEN berat_kg ELSE 0 END),0) AS setor_kg,
               COALESCE(SUM(CASE WHEN tipe='penjualan' THEN berat_kg ELSE 0 END),0) AS jual_kg
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND tanggal < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        GROUP BY DATE(tanggal)
        ORDER BY DATE(tanggal)
    ");
    $map = [];
    foreach ($stmt_chart as $r) {
        $map[$r['tgl']] = [(float)$r['setor_kg'], (float)$r['jual_kg']];
    }
    foreach ($chart_labels as $i => $label) {
        $date = (new DateTimeImmutable('-' . (6 - $i) . ' days'))->format('Y-m-d');
        if (isset($map[$date])) {
            $chart_setor[$i] = $map[$date][0];
            $chart_jual[$i] = $map[$date][1];
        }
    }
}

$chart_max = max(1, ...$chart_setor, ...$chart_jual);
$chart_width = 760;
$chart_height = 250;
$chart_pad_left = 48;
$chart_pad_right = 18;
$chart_pad_top = 18;
$chart_pad_bottom = 40;
$chart_inner_w = $chart_width - $chart_pad_left - $chart_pad_right;
$chart_inner_h = $chart_height - $chart_pad_top - $chart_pad_bottom;
$chart_count = max(1, count($chart_labels));
$chart_step = $chart_count > 1 ? $chart_inner_w / ($chart_count - 1) : $chart_inner_w;

$chart_points = function(array $values) use ($chart_pad_left, $chart_pad_top, $chart_inner_h, $chart_step, $chart_max) {
    $points = [];
    foreach ($values as $i => $value) {
        $x = $chart_pad_left + ($i * $chart_step);
        $y = $chart_pad_top + $chart_inner_h - (($value / $chart_max) * $chart_inner_h);
        $points[] = round($x, 2) . ',' . round($y, 2);
    }
    return implode(' ', $points);
};
$setor_points = $chart_points($chart_setor);
$jual_points = $chart_points($chart_jual);

/* Jenis sampah teratas bulan ini */
$jenis_bulan = $pdo->query("
    SELECT s.jenis_sampah,
           COALESCE(SUM(CASE WHEN t.tipe='setor' THEN t.berat_kg ELSE 0 END),0) AS setor_kg,
           COALESCE(SUM(CASE WHEN t.tipe='penjualan' THEN t.berat_kg ELSE 0 END),0) AS jual_kg
    FROM sampah s
    LEFT JOIN transaksi t
      ON t.id_sampah=s.id_sampah
     AND YEAR(t.tanggal)=YEAR(CURDATE())
     AND MONTH(t.tanggal)=MONTH(CURDATE())
    GROUP BY s.id_sampah, s.jenis_sampah
    ORDER BY (
        COALESCE(SUM(CASE WHEN t.tipe='setor' THEN t.berat_kg ELSE 0 END),0) +
        COALESCE(SUM(CASE WHEN t.tipe='penjualan' THEN t.berat_kg ELSE 0 END),0)
    ) DESC, s.jenis_sampah
    LIMIT 5
")->fetchAll();

$max_aktivitas = 1;
foreach ($jenis_bulan as $r) {
    $max_aktivitas = max($max_aktivitas, (float)$r['setor_kg'], (float)$r['jual_kg']);
}

$permintaan_terbaru = $pdo->query("
    SELECT ts.*, u.nama, u.role
    FROM tarik_saldo ts
    JOIN users u ON u.id_user = ts.id_user
    ORDER BY ts.id_tarik DESC LIMIT 5
")->fetchAll();

$page_title = 'Dashboard';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="dashboard-head mb-3">
        <div>
            <h4 class="mb-1">Dashboard Admin</h4>
            <p class="text-muted mb-0">Pantau aktivitas Bank Sampah hari ini dan bulan ini.</p>
        </div>
        <div class="dashboard-date"><?= date('d-m-Y') ?></div>
    </div>
    <?php show_flash(); ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-stat p-3 text-center">
                <div class="stat-icon"><?= icon('user') ?></div>
                <h5 class="mt-2 mb-0"><?= $total_siswa ?></h5><small class="text-muted">Siswa</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stat p-3 text-center">
                <div class="stat-icon"><?= icon('user') ?></div>
                <h5 class="mt-2 mb-0"><?= $total_guru ?></h5><small class="text-muted">Guru</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stat p-3 text-center">
                <div class="stat-icon"><?= icon('deposit') ?></div>
                <h5 class="mt-2 mb-0"><?= $total_pengepul ?></h5><small class="text-muted">Pengepul</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-stat p-3 text-center">
                <div class="stat-icon"><?= icon('history') ?></div>
                <h5 class="mt-2 mb-0"><?= number_format((float)$total_stok, 1, ',', '.') ?> kg</h5><small class="text-muted">Stok Saat Ini</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card card-stat p-4 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <span class="eyebrow">Statistik</span>
                        <h5 class="mb-0">Pergerakan Transaksi</h5>
                        <small class="text-muted">Grafik berat sampah yang masuk dan terjual.</small>
                    </div>
                    <form method="get" class="d-flex align-items-center gap-2">
                        <label for="periodeGrafik" class="visually-hidden">Periode grafik</label>
                        <select id="periodeGrafik" name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="hari_ini" <?= $periode === 'hari_ini' ? 'selected' : '' ?>>Hari ini</option>
                            <option value="7_hari" <?= $periode === '7_hari' ? 'selected' : '' ?>>7 hari terakhir</option>
                            <option value="bulan_ini" <?= $periode === 'bulan_ini' ? 'selected' : '' ?>>Bulan ini</option>
                        </select>
                    </form>
                </div>

                <div class="d-flex flex-wrap gap-3 small mb-2">
                    <span><span class="d-inline-block rounded-circle me-1" style="width:9px;height:9px;background:var(--bs-primary)"></span> Setoran</span>
                    <span><span class="d-inline-block rounded-circle me-1" style="width:9px;height:9px;background:var(--bs-success)"></span> Penjualan</span>
                </div>

                <div class="line-chart-wrap">
                    <svg class="line-chart" viewBox="0 0 <?= $chart_width ?> <?= $chart_height ?>" role="img" aria-label="Grafik garis setoran dan penjualan">
                        <?php for ($g = 0; $g <= 4; $g++):
                            $gy = $chart_pad_top + ($chart_inner_h * $g / 4);
                            $gv = $chart_max * (1 - $g / 4);
                        ?>
                            <line x1="<?= $chart_pad_left ?>" y1="<?= round($gy,2) ?>" x2="<?= $chart_width - $chart_pad_right ?>" y2="<?= round($gy,2) ?>" class="chart-grid-line" />
                            <text x="<?= $chart_pad_left - 8 ?>" y="<?= round($gy + 4,2) ?>" text-anchor="end" class="chart-axis-text"><?= number_format($gv, 1, ',', '.') ?></text>
                        <?php endfor; ?>
                        <polyline points="<?= clean($setor_points) ?>" class="chart-line chart-line-setor" fill="none" />
                        <polyline points="<?= clean($jual_points) ?>" class="chart-line chart-line-jual" fill="none" />
                        <?php foreach ($chart_setor as $i => $value):
                            $x = $chart_pad_left + ($i * $chart_step);
                            $y = $chart_pad_top + $chart_inner_h - (($value / $chart_max) * $chart_inner_h);
                        ?>
                            <circle cx="<?= round($x,2) ?>" cy="<?= round($y,2) ?>" r="3.5" class="chart-dot-setor"><title><?= clean($chart_labels[$i]) ?>: <?= number_format($value,1,',','.') ?> kg setoran</title></circle>
                        <?php endforeach; ?>
                        <?php foreach ($chart_jual as $i => $value):
                            $x = $chart_pad_left + ($i * $chart_step);
                            $y = $chart_pad_top + $chart_inner_h - (($value / $chart_max) * $chart_inner_h);
                        ?>
                            <circle cx="<?= round($x,2) ?>" cy="<?= round($y,2) ?>" r="3.5" class="chart-dot-jual"><title><?= clean($chart_labels[$i]) ?>: <?= number_format($value,1,',','.') ?> kg penjualan</title></circle>
                        <?php endforeach; ?>
                        <?php foreach ($chart_labels as $i => $label):
                            $x = $chart_pad_left + ($i * $chart_step);
                            $show = ($chart_count <= 8) || $i === 0 || $i === $chart_count - 1 || $i % max(1, (int)ceil($chart_count / 8)) === 0;
                            if ($show):
                        ?>
                            <text x="<?= round($x,2) ?>" y="<?= $chart_height - 12 ?>" text-anchor="middle" class="chart-axis-text"><?= clean($label) ?></text>
                        <?php endif; endforeach; ?>
                    </svg>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card card-stat p-4 h-100">
                <span class="eyebrow">Ringkasan</span>
                <h5 class="mb-3">Aktivitas <?= $periode === 'hari_ini' ? 'Hari Ini' : ($periode === 'bulan_ini' ? 'Bulan Ini' : '7 Hari Terakhir') ?></h5>
                <div class="mini-stat mb-3"><small>Total Setoran</small><strong><?= number_format(array_sum($chart_setor),1,',','.') ?> kg</strong><span><?= rupiah($periode === 'hari_ini' ? $stat_hari['setor_rp'] : ($periode === 'bulan_ini' ? $stat_bulan['setor_rp'] : 0)) ?></span></div>
                <div class="mini-stat mb-3"><small>Total Penjualan</small><strong><?= number_format(array_sum($chart_jual),1,',','.') ?> kg</strong><span><?= rupiah($periode === 'hari_ini' ? $stat_hari['jual_rp'] : ($periode === 'bulan_ini' ? $stat_bulan['jual_rp'] : 0)) ?></span></div>
                <div class="mini-stat"><small>Stok Saat Ini</small><strong><?= number_format((float)$total_stok,1,',','.') ?> kg</strong><span>Seluruh jenis sampah</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card card-stat p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><span class="eyebrow">Bulan Ini</span><h6 class="mb-0">Aktivitas per Jenis Sampah</h6></div>
                    <small class="text-muted">kg</small>
                </div>
                <?php if (empty($jenis_bulan)): ?>
                    <p class="text-muted mb-0">Belum ada data transaksi.</p>
                <?php else: foreach ($jenis_bulan as $r): ?>
                    <div class="chart-item mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold"><?= clean($r['jenis_sampah']) ?></span>
                            <span class="text-muted"><?= number_format((float)$r['setor_kg'],1,',','.') ?> / <?= number_format((float)$r['jual_kg'],1,',','.') ?></span>
                        </div>
                        <div class="chart-track"><span style="width:<?= min(100, ((float)$r['setor_kg']/$max_aktivitas)*100) ?>%"></span></div>
                        <div class="chart-track chart-track-secondary mt-1"><span style="width:<?= min(100, ((float)$r['jual_kg']/$max_aktivitas)*100) ?>%"></span></div>
                    </div>
                <?php endforeach; endif; ?>
                <div class="small text-muted mt-2">Bar atas = setoran, bar bawah = penjualan.</div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card card-stat p-4 h-100">
                <h6 class="mb-3">Ringkasan Saldo</h6>
                <div class="mini-stat mb-3"><small>Saldo Guru & Siswa</small><strong><?= rupiah($total_saldo_beredar) ?></strong></div>
                <div class="mini-stat mb-3"><small>Total pencairan selesai</small><strong><?= rupiah($total_ditarik) ?></strong></div>
                <?php if ($pending_tarik > 0): ?>
                    <a href="penarikan.php" class="btn btn-warning w-100">Proses <?= $pending_tarik ?> Penarikan</a>
                <?php else: ?>
                    <div class="alert alert-success mb-0">Tidak ada penarikan yang menunggu.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card card-stat p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Permintaan Tarik Saldo Terbaru</h6>
            <a href="penarikan.php" class="small">Kelola semua &raquo;</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Tanggal</th><th>Nama</th><th>Role</th><th>Nominal</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (empty($permintaan_terbaru)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada permintaan tarik saldo</td></tr>
                <?php else: $badge = ['selesai' => 'success', 'disetujui' => 'info', 'menunggu' => 'warning', 'ditolak' => 'danger'];
                    foreach ($permintaan_terbaru as $t): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                        <td><?= clean($t['nama']) ?></td>
                        <td class="text-capitalize"><?= clean($t['role']) ?></td>
                        <td><?= rupiah($t['nominal']) ?></td>
                        <td><span class="badge bg-<?= $badge[$t['status']] ?? 'secondary' ?> text-capitalize"><?= clean($t['status']) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<style>
.line-chart-wrap{width:100%;overflow-x:auto;overflow-y:hidden;border-radius:14px}
.line-chart{display:block;width:100%;min-width:560px;height:auto;overflow:visible}
.chart-grid-line{stroke:var(--border,#e6eaf0);stroke-width:1}
.chart-axis-text{fill:var(--muted,#7b8492);font-size:11px}
.chart-line{fill:none;stroke-width:2.75;stroke-linecap:round;stroke-linejoin:round}
.chart-line-setor{stroke:var(--bs-primary)}
.chart-line-jual{stroke:var(--bs-success)}
.chart-dot-setor,.chart-dot-jual{stroke:var(--surface,#fff);stroke-width:2;transition:r .15s ease}
.chart-dot-setor{fill:var(--bs-primary)}
.chart-dot-jual{fill:var(--bs-success)}
.chart-dot-setor:hover,.chart-dot-jual:hover{r:5.5}
@media (max-width:575.98px){.line-chart{min-width:620px}.line-chart-wrap{margin-left:-4px;margin-right:-4px}}
</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
