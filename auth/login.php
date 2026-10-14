<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('../' . dashboard_url_for($_SESSION['role']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        set_flash('error', 'Username dan password wajib diisi.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            redirect('../' . dashboard_url_for($user['role']));
        } else {
            set_flash('error', 'Username atau password salah.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme-key="bs_theme_login">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Sampah</title>
    <link rel="icon" type="image/png" href="../assets/img/bank_sampah_icon.png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script>
        // Halaman login selalu menggunakan tema terang.
        // Pengaturan dark mode tiap pengguna tidak memengaruhi halaman login.
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card p-4">
        <div class="card-body">
            <div class="login-logo-box text-center mb-4">
                <img src="../assets/img/bank_sampah_icon.png" alt="Logo Bank Sampah" height="56" class="mb-2">
                <h5 class="fw-bold mb-1" style="color: var(--bs-green-dark);">Bank Sampah</h5>
                <p class="text-muted small mb-0">Sistem Informasi Bank Sampah</p>
            </div>
            <?php show_flash(); ?>
            <form method="POST" data-enter-submit>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-icon">
                        <?= icon('user') ?>
                        <input type="text" name="username" class="form-control" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <?= icon('lock') ?>
                        <input type="password" name="password" class="form-control" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2">Masuk</button>
            </form>
            <p class="text-center small text-muted mt-3 mb-0">
                Siswa belum punya akun? <a href="register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
