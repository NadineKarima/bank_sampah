<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// ---------------------------------------------------------
// Format angka ke Rupiah
// ---------------------------------------------------------
function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

// ---------------------------------------------------------
// Redirect helper
// ---------------------------------------------------------
function redirect($url) {
    header("Location: $url");
    exit;
}

// ---------------------------------------------------------
// Flash message (pesan sukses/error sekali tampil)
// ---------------------------------------------------------
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function show_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// ---------------------------------------------------------
// Auth helpers
// ---------------------------------------------------------
function is_logged_in() {
    return isset($_SESSION['id_user']);
}

function current_user() {
    return $_SESSION ?? null;
}

// $depth = jumlah folder dari root proyek (auth/admin/pengepul/anggota = 1 level)
function require_login($depth = 1) {
    if (!is_logged_in()) {
        redirect(str_repeat('../', $depth) . 'auth/login.php');
    }
}

function require_role($roles, $depth = 1) {
    require_login($depth);
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'], $allowed, true)) {
        redirect(str_repeat('../', $depth) . 'auth/login.php');
    }
}

// Redirect ke dashboard sesuai role setelah login
function dashboard_url_for($role) {
    return match ($role) {
        'admin'    => 'admin/dashboard.php',
        'pengepul' => 'pengepul/dashboard.php',
        default    => 'anggota/dashboard.php', // guru & siswa
    };
}

// ---------------------------------------------------------
// Sanitasi input sederhana
// ---------------------------------------------------------
function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------------------
// Ikon inline (SVG) — tanpa CDN, biar tetap tampil walau
// server tidak terhubung ke internet.
// ---------------------------------------------------------
function icon($name, $class = 'icon') {
    $paths = [
        'menu'      => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'home'      => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9a1 1 0 0 0 1 1H10v-6h4v6h3.5a1 1 0 0 0 1-1v-9"/>',
        'deposit'   => '<path d="M21 8.5 16 3l-3 3 5 5.5z"/><path d="M13 6 4 15v5h5l9-9"/><path d="M3 21h18"/>',
        'cash'      => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/>',
        'history'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'tag'       => '<path d="M20.6 12.6 12.6 20.6a2 2 0 0 1-2.8 0L3 13.8V3h10.8l6.8 6.8a2 2 0 0 1 0 2.8Z"/><circle cx="8" cy="8" r="1.5"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 13a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V19a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H4a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H10a1.7 1.7 0 0 0 1-1.5V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V10a1.7 1.7 0 0 0 1.5 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon'      => '<path d="M20.5 14.5A8.5 8.5 0 1 1 9.5 3.5a7 7 0 0 0 11 11Z"/>',
        'lock'      => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'chevron'   => '<path d="m9 6 6 6-6 6"/>',
        'wallet'    => '<path d="M3 7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M16 13.5h.01"/>',
        'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'trash'     => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'close-x'   => '<path d="M18 6 6 18"/><path d="M6 6l12 12"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'eye'       => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'   => '<path d="M17.9 17.9A10.4 10.4 0 0 1 12 19C5.6 19 2 12 2 12a18 18 0 0 1 4.1-5.1"/><path d="M9.9 5.2A9.7 9.7 0 0 1 12 5c6.4 0 10 7 10 7a18 18 0 0 1-2.2 3.2"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="M3 3l18 18"/>',
    ];
    $body = $paths[$name] ?? $paths['info'];
    return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
}


// ---------------------------------------------------------
// Ganti password (dipakai semua role lewat pengaturan)
// Mengembalikan [tipe_flash, pesan].
// ---------------------------------------------------------
function ganti_password(PDO $pdo, int $id_user, string $lama, string $baru, string $konfirmasi): array {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $hash_lama = $stmt->fetchColumn();

    if (!$hash_lama || !password_verify($lama, $hash_lama)) {
        return ['error', 'Password lama salah.'];
    }
    if (strlen($baru) < 6) {
        return ['error', 'Password baru minimal 6 karakter.'];
    }
    if ($baru !== $konfirmasi) {
        return ['error', 'Konfirmasi password baru tidak cocok.'];
    }
    if (hash_equals($lama, $baru)) {
        return ['error', 'Password baru tidak boleh sama dengan password lama.'];
    }

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
    $stmt->execute([password_hash($baru, PASSWORD_DEFAULT), $id_user]);
    session_regenerate_id(true);
    return ['success', 'Password berhasil diubah.'];
}


// ---------------------------------------------------------
// Token form sekali pakai: mencegah transaksi terkirim dua kali
// (klik ganda / refresh / tombol back).
// ---------------------------------------------------------
function form_token_field(string $name = 'default'): string {
    $token = bin2hex(random_bytes(16));
    $_SESSION['form_tokens'][$name] = $token;
    return '<input type="hidden" name="_token" value="' . $token . '">';
}

function consume_form_token(string $name = 'default'): bool {
    $sent = $_POST['_token'] ?? '';
    $saved = $_SESSION['form_tokens'][$name] ?? '';
    unset($_SESSION['form_tokens'][$name]);
    return $saved !== '' && hash_equals($saved, $sent);
}

function saldo_user(PDO $pdo, int $id_user): float {
    $stmt = $pdo->prepare("SELECT saldo FROM users WHERE id_user = ?");
    $stmt->execute([$id_user]);
    return (float) $stmt->fetchColumn();
}
