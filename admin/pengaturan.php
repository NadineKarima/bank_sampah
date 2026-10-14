<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$id_user = (int) $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ganti_password') {
    [$tipe, $pesan] = ganti_password(
        $pdo, $id_user,
        $_POST['password_lama'] ?? '',
        $_POST['password_baru'] ?? '',
        $_POST['konfirmasi_password'] ?? ''
    );
    set_flash($tipe, $pesan);
    redirect('pengaturan.php');
}

$page_title = 'Pengaturan';
$asset_path = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <?php include __DIR__ . '/../includes/pengaturan_content.php'; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
