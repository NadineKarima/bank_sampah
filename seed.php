<?php
// =========================================================
// SEED AKUN CONTOH — jalankan SEKALI lewat browser:
//   http://localhost/bank-sampah/seed.php
// Setelah berhasil, HAPUS file ini (untuk keamanan).
// =========================================================
require_once __DIR__ . '/config/database.php';

$akun = [
    ['nama' => 'Admin Sekolah',    'username' => 'admin',    'role' => 'admin',    'saldo' => 0, 'password' => 'admin123'],
    ['nama' => 'Pak Pengepul',     'username' => 'pengepul', 'role' => 'pengepul', 'saldo' => 0, 'password' => 'pengepul123'],
    ['nama' => 'Bu Sari, S.Pd.',   'username' => 'guru',     'role' => 'guru',     'saldo' => 15000, 'password' => 'guru123'],
    ['nama' => 'Nadine Karima',    'username' => 'nadine',   'role' => 'siswa',    'saldo' => 45000, 'password' => 'nadine123'],
];
$hasil = [];
$id_by_username = [];

foreach ($akun as $a) {
    $cek = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
    $cek->execute([$a['username']]);
    $existing = $cek->fetch();
    if ($existing) {
        $id_by_username[$a['username']] = $existing['id_user'];
        $hasil[] = "Akun '{$a['username']}' sudah ada, dilewati.";
        continue;
    }
    $hash = password_hash($a['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, saldo, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$a['nama'], $a['username'], $hash, $a['saldo'], $a['role']]);
    $id_by_username[$a['username']] = $pdo->lastInsertId();
    $hasil[] = "Akun '{$a['username']}' ({$a['role']}) berhasil dibuat.";
}

// ---- Contoh riwayat setoran & penarikan untuk akun siswa 'nadine' ----
if (isset($id_by_username['nadine'])) {
    $id_nadine = $id_by_username['nadine'];
    $ada_transaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
    if ($ada_transaksi == 0) {
        $sampah = $pdo->query("SELECT id_sampah, harga_per_kg FROM sampah ORDER BY id_sampah LIMIT 2")->fetchAll();
        if (count($sampah) >= 2) {
            $stmt = $pdo->prepare("INSERT INTO transaksi (id_user, id_sampah, tipe, berat_kg, tanggal, total_rp) VALUES (?, ?, 'setor', ?, ?, ?)");
            $stmt->execute([$id_nadine, $sampah[0]['id_sampah'], 2,  '2026-09-01', 2  * $sampah[0]['harga_per_kg']]);
            $stmt->execute([$id_nadine, $sampah[1]['id_sampah'], 6,  '2026-08-28', 6  * $sampah[1]['harga_per_kg']]);
            $hasil[] = 'Contoh riwayat setoran untuk akun siswa dibuat.';
        }
    }
    $ada_tarik = $pdo->query("SELECT COUNT(*) FROM tarik_saldo")->fetchColumn();
    if ($ada_tarik == 0) {
        $stmt = $pdo->prepare("INSERT INTO tarik_saldo (id_user, nominal, tanggal, status) VALUES (?, ?, NOW(), 'menunggu')");
        $stmt->execute([$id_nadine, 10000]);
        $hasil[] = 'Contoh permintaan tarik saldo (menunggu) dibuat.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Seed Akun - Bank Sampah</title>
<style>
body{font-family:system-ui,sans-serif;background:#f6f8f6;padding:2.5rem;color:#1c2b22}
.box{max-width:560px;margin:0 auto;background:#fff;border-radius:14px;padding:2rem;box-shadow:0 8px 24px rgba(0,0,0,.08)}
h1{font-size:1.3rem;margin-top:0}
ul{line-height:1.9}
code{background:#ecfdf3;padding:2px 6px;border-radius:6px}
.warn{background:#fffbeb;color:#92400e;padding:.8rem 1rem;border-radius:10px;margin-top:1.2rem;font-size:.9rem}
</style>
</head>
<body>
<div class="box">
    <h1>♻️ Seed Akun Bank Sampah Sekolah</h1>
    <ul>
        <?php foreach ($hasil as $h): ?>
            <li><?= htmlspecialchars($h) ?></li>
        <?php endforeach; ?>
    </ul>
    <p>Login dengan salah satu akun berikut:</p>
    <ul>
        <li>Admin &nbsp;&nbsp;→ <code>admin</code> / <code>admin123</code></li>
        <li>Pengepul → <code>pengepul</code> / <code>pengepul123</code></li>
        <li>Guru &nbsp;&nbsp;→ <code>guru</code> / <code>guru123</code></li>
        <li>Siswa &nbsp;→ <code>nadine</code> / <code>nadine123</code></li>
    </ul>
    <p><a href="auth/login.php">→ Ke halaman Login</a></p>
    <div class="warn">⚠️ Demi keamanan, hapus file <code>seed.php</code> ini setelah selesai dipakai.</div>
</div>
</body>
</html>
