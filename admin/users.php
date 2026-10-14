<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// ---- TAMBAH PENGGUNA BARU ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'siswa';

    if (!in_array($role, ['guru', 'siswa', 'pengepul'], true)) $role = 'siswa';

    if ($nama === '' || $username === '' || strlen($password) < 6) {
        set_flash('error', 'Nama, username, dan password minimal 6 karakter <b>wajib diisi</b>.');
    } else {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            set_flash('error', 'Username sudah digunakan.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, saldo, role) VALUES (?, ?, ?, 0, ?)");
            $stmt->execute([$nama, $username, $hash, $role]);
            set_flash('success', 'Akun ' . ucfirst($role) . ' berhasil ditambahkan.');
        }
    }
    redirect('users.php');
}

// ---- EDIT PENGGUNA ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int) ($_POST['id_user'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'siswa';
    $password = $_POST['password'] ?? '';

    if ($id <= 0 || $nama === '' || $username === '' || !in_array($role, ['guru', 'siswa', 'pengepul'], true)) {
        set_flash('error', 'Data pengguna tidak valid.');
        redirect('users.php');
    }

    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = ? AND id_user <> ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetch()) {
        set_flash('error', 'Username sudah digunakan oleh pengguna lain.');
        redirect('users.php');
    }

    if ($password !== '' && strlen($password) < 6) {
        set_flash('error', 'Password baru minimal 6 karakter.');
        redirect('users.php');
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=?, password=? WHERE id_user=? AND role <> 'admin'");
        $stmt->execute([$nama, $username, $role, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id_user=? AND role <> 'admin'");
        $stmt->execute([$nama, $username, $role, $id]);
    }

    set_flash('success', 'Data pengguna berhasil diperbarui.');
    redirect('users.php');
}

// ---- HAPUS PENGGUNA ----
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ? AND role != 'admin'");
        $stmt->execute([$id]);
        set_flash('success', 'Akun berhasil dihapus.');
    } catch (PDOException $e) {
        set_flash('error', 'Tidak bisa menghapus, akun ini masih punya riwayat transaksi/penarikan.');
    }
    redirect('users.php');
}

$filter = $_GET['role'] ?? '';
if (in_array($filter, ['guru', 'siswa', 'pengepul'], true)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY id_user DESC");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id_user DESC");
}
$data_users = $stmt->fetchAll();

$page_title = 'Data Pengguna';
$asset_path = '../';
$nav_prefix = '../';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>
<main class="app-main">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0">Data Pengguna</h4>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdd">Tambah Pengguna</button>
    </div>
    <?php show_flash(); ?>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link <?= $filter === '' ? 'active' : '' ?>" href="users.php">Semua</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 'siswa' ? 'active' : '' ?>" href="?role=siswa">Siswa</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 'guru' ? 'active' : '' ?>" href="?role=guru">Guru</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 'pengepul' ? 'active' : '' ?>" href="?role=pengepul">Pengepul</a></li>
    </ul>

    <div class="card card-stat p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Saldo</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($data_users)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                <?php else: $badge = ['guru' => 'success', 'siswa' => 'warning', 'pengepul' => 'danger']; foreach ($data_users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id_user'] ?></td>
                        <td><?= clean($u['nama']) ?></td>
                        <td><?= clean($u['username']) ?></td>
                        <td><span class="badge bg-<?= $badge[$u['role']] ?? 'secondary' ?> text-capitalize"><?= clean($u['role']) ?></span></td>
                        <td><?= rupiah($u['saldo']) ?></td>
                        <td class="text-nowrap">
                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#modalEdit<?= (int)$u['id_user'] ?>"><?= icon('edit') ?></button>
                            <a href="?delete=<?= (int)$u['id_user'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Yakin hapus akun ini?')"><?= icon('trash') ?></a>
                        </td>
                    </tr>
<?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if (!empty($data_users)): foreach ($data_users as $u): ?>
<div class="modal fade" id="modalEdit<?= (int)$u['id_user'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_user" value="<?= (int)$u['id_user'] ?>">
            <div class="modal-header app-modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon"><?= icon('edit') ?></div>
                    <div><h5 class="modal-title mb-0">Edit Pengguna</h5><p class="modal-subtitle mb-0">Perbarui informasi akun pengguna.</p></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body app-modal-body">
                <div class="modal-section-label">Informasi akun</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="siswa" <?= $u['role']==='siswa'?'selected':'' ?>>Siswa</option><option value="guru" <?= $u['role']==='guru'?'selected':'' ?>>Guru</option><option value="pengepul" <?= $u['role']==='pengepul'?'selected':'' ?>>Pengepul</option></select></div>
                    <div class="col-md-6"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= clean($u['nama']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= clean($u['username']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Password Baru <span class="text-muted fw-normal">(opsional)</span></label><input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password"></div>
                </div>
                <div class="modal-help">Kosongkan password jika tidak ingin mengubah password pengguna.</div>
            </div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
<?php endforeach; endif; ?>

<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered app-modal-dialog">
        <form method="POST" class="modal-content app-modal">
            <input type="hidden" name="action" value="add">
            <div class="modal-header app-modal-header"><div class="modal-title-wrap"><div class="modal-icon">+</div><div><h5 class="modal-title mb-0">Tambah Pengguna</h5><p class="modal-subtitle mb-0">Buat akun baru untuk guru, siswa, atau pengepul.</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body app-modal-body">
                <div class="modal-section-label">Informasi akun</div>
                <div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="siswa">Siswa</option><option value="guru">Guru</option><option value="pengepul">Pengepul</option></select></div>
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password"></div>
            </div>
            <div class="modal-footer app-modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan Pengguna</button></div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
