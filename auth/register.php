<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../' . dashboard_url_for($_SESSION['role']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        set_flash('error', 'Semua field wajib diisi.');
    } elseif ($password !== $confirm) {
        set_flash('error', 'Konfirmasi password tidak cocok.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password minimal 6 karakter.');
    } else {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            set_flash('error', 'Username sudah digunakan, silakan pilih yang lain.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, saldo, role) VALUES (?, ?, ?, 0, 'siswa')");
            $stmt->execute([$nama, $username, $hash]);
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme-key="bs_theme_guest">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Bank Sampah</title>
    <link rel="icon" type="image/png" href="../assets/img/bank_sampah_icon.png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script>
        // ikut pilihan tema (terang/gelap) dari menu Pengaturan
        document.documentElement.setAttribute('data-theme', localStorage.getItem('bs_theme_guest') || 'light');
    </script>
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card p-4">
        <div class="card-body">
            <div class="login-logo-box text-center mb-4">
                <img src="../assets/img/bank_sampah_icon.png" alt="Logo Bank Sampah" height="56" class="mb-2">
                <h5 class="fw-bold mb-0" style="color: var(--bs-green-dark);">Daftar Akun Siswa</h5>
                <p class="text-muted small mb-0 mt-1">Guru &amp; Pengepul dibuatkan akun oleh Admin</p>
            </div>
            <?php show_flash(); ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-icon">
                        <?= icon('user') ?>
                        <input type="text" name="nama" class="form-control" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-icon">
                        <?= icon('user') ?>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <?= icon('lock') ?>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-icon">
                        <?= icon('lock') ?>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2">Daftar</button>
            </form>
            <p class="text-center small text-muted mt-3 mb-0">
                Sudah punya akun? <a href="login.php">Masuk</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
