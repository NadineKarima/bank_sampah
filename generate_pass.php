<?php
function generate_random_password($panjang = 10) {
    $huruf_besar = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $huruf_kecil = 'abcdefghijkmnopqrstuvwxyz';
    $angka       = '23456789';
    $simbol      = '!@#$%&*';
    $semua       = $huruf_besar . $huruf_kecil . $angka . $simbol;

    $pass  = $huruf_besar[random_int(0, strlen($huruf_besar) - 1)];
    $pass .= $huruf_kecil[random_int(0, strlen($huruf_kecil) - 1)];
    $pass .= $angka[random_int(0, strlen($angka) - 1)];
    $pass .= $simbol[random_int(0, strlen($simbol) - 1)];

    for ($i = strlen($pass); $i < $panjang; $i++) {
        $pass .= $semua[random_int(0, strlen($semua) - 1)];
    }

    return str_shuffle($pass);
}

$hasil = null;   // hasil generate: password, hash, sql
$pesan = null;   // pesan sukses/gagal simpan ke database
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role     = $_POST['role'] ?? 'siswa';
    $saldo    = (float) ($_POST['saldo'] ?? 0);
    $password = trim($_POST['password'] ?? '');
    $simpan_db = isset($_POST['simpan_db']);

    if (!in_array($role, ['admin', 'guru', 'siswa', 'pengepul'], true)) {
        $role = 'siswa';
    }
    if ($role === 'pengepul' || $role === 'admin') {
        $saldo = 0; // pengepul & admin tidak punya saldo tabungan
    }

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } else {
        // Kalau password dikosongkan, buatkan password acak otomatis
        if ($password === '') {
            $password = generate_random_password(10);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (nama, username, password, saldo, role) VALUES (\n"
             . "    " . $pdo->quote($nama) . ",\n"
             . "    " . $pdo->quote($username) . ",\n"
             . "    " . $pdo->quote($hash) . ",\n"
             . "    " . $saldo . ",\n"
             . "    " . $pdo->quote($role) . "\n"
             . ");";

        $hasil = [
            'nama' => $nama,
            'username' => $username,
            'role' => $role,
            'saldo' => $saldo,
            'password' => $password,
            'hash' => $hash,
            'sql' => $sql,
        ];

        if ($simpan_db) {
            $cek = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
            $cek->execute([$username]);
            if ($cek->fetch()) {
                $error = "Username '{$username}' sudah dipakai. Password & hash tetap dibuat di bawah, tapi TIDAK disimpan ke database.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, saldo, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nama, $username, $hash, $saldo, $role]);
                $pesan = "Akun '{$username}' ({$role}) berhasil dibuat & disimpan ke database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generate Password / Akun - Bank Sampah</title>
<style>
body{font-family:system-ui,sans-serif;background:#f6f8f6;padding:2.5rem 1rem;color:#1c2b22}
.box{max-width:640px;margin:0 auto;background:#fff;border-radius:14px;padding:2rem;box-shadow:0 8px 24px rgba(0,0,0,.08)}
h1{font-size:1.3rem;margin-top:0}
label{display:block;font-size:.85rem;font-weight:600;margin:.9rem 0 .3rem}
input[type=text],input[type=number],select{width:100%;padding:.55rem .7rem;border:1px solid #d7ddd8;border-radius:8px;font-size:.95rem;box-sizing:border-box}
.row2{display:flex;gap:.8rem}
.row2 > div{flex:1}
.chk{display:flex;align-items:center;gap:.5rem;margin-top:1rem;font-size:.9rem}
.chk input{width:auto}
button{margin-top:1.4rem;background:#16a34a;color:#fff;border:0;padding:.7rem 1.2rem;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer;width:100%}
button:hover{background:#128a3e}
code{background:#ecfdf3;padding:2px 6px;border-radius:6px;word-break:break-all}
.result{margin-top:1.5rem;background:#f0fdf4;border:1px solid #bbf0cc;border-radius:10px;padding:1.1rem 1.2rem}
.result dt{font-size:.78rem;color:#4b5c52;margin-top:.7rem}
.result dd{margin:.15rem 0 0;font-family:'Courier New',monospace;font-size:.92rem}
.result dd.sql{white-space:pre-wrap;background:#0f2417;color:#d9f7e4;padding:.7rem;border-radius:8px;font-size:.82rem}
.msg-success{background:#e9fbe9;color:#1c6b2b;padding:.7rem 1rem;border-radius:8px;font-size:.88rem;margin-top:1rem}
.msg-error{background:#fdeceb;color:#a12a20;padding:.7rem 1rem;border-radius:8px;font-size:.88rem;margin-top:1rem}
.warn{background:#fffbeb;color:#92400e;padding:.8rem 1rem;border-radius:10px;margin-top:1.4rem;font-size:.85rem}
.copy-btn{background:#eef3ef;color:#1c2b22;border:1px solid #d7ddd8;padding:.25rem .6rem;border-radius:6px;font-size:.75rem;cursor:pointer;margin-left:.4rem}
</style>
</head>
<body>
<div class="box">
    <h1>🔑 Generate Password / Akun</h1>
    <p style="color:#4b5c52;font-size:.9rem;margin-top:-.5rem">
        Buat akun baru secara manual: isi datanya, password boleh dikosongkan supaya dibuatkan otomatis.
        Centang "Simpan ke database" kalau ingin langsung membuat akunnya, atau biarkan tidak dicentang
        untuk sekadar mengambil hash / query SQL-nya.
    </p>

    <?php if ($error): ?><div class="msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($pesan): ?><div class="msg-success"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>

    <form method="POST">
        <div class="row2">
            <div>
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row2">
            <div>
                <label>Role</label>
                <?php $role_sel = $_POST['role'] ?? 'siswa'; ?>
                <select name="role">
                    <option value="siswa" <?= $role_sel === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                    <option value="guru" <?= $role_sel === 'guru' ? 'selected' : '' ?>>Guru</option>
                    <option value="pengepul" <?= $role_sel === 'pengepul' ? 'selected' : '' ?>>Pengepul</option>
                    <option value="admin" <?= $role_sel === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div>
                <label>Saldo Awal (khusus guru/siswa)</label>
                <input type="number" step="0.01" name="saldo" value="<?= htmlspecialchars($_POST['saldo'] ?? '0') ?>">
            </div>
        </div>

        <label>Password (kosongkan untuk dibuatkan otomatis)</label>
        <input type="text" name="password" placeholder="Kosongkan untuk generate acak">

        <label class="chk">
            <input type="checkbox" name="simpan_db" <?= isset($_POST['simpan_db']) ? 'checked' : '' ?>>
            Simpan langsung ke database (bukan cuma preview)
        </label>

        <button type="submit">Generate</button>
    </form>

    <?php if ($hasil): ?>
    <div class="result">
        <dl class="mb-0">
            <dt>Nama / Username / Role</dt>
            <dd><?= htmlspecialchars($hasil['nama']) ?> — <?= htmlspecialchars($hasil['username']) ?> — <?= htmlspecialchars($hasil['role']) ?></dd>

            <dt>Password (berikan ini ke pemilik akun, tidak disimpan mentah di database)</dt>
            <dd id="plainPass"><?= htmlspecialchars($hasil['password']) ?>
                <button type="button" class="copy-btn" onclick="copyText('plainPass')">Salin</button>
            </dd>

            <dt>Hash (password_hash / bcrypt)</dt>
            <dd id="hashPass"><code><?= htmlspecialchars($hasil['hash']) ?></code>
                <button type="button" class="copy-btn" onclick="copyText('hashPass')">Salin</button>
            </dd>

            <dt>Query SQL siap pakai (kalau tidak dicentang "Simpan ke database")</dt>
            <dd class="sql" id="sqlBox"><?= htmlspecialchars($hasil['sql']) ?></dd>
            <button type="button" class="copy-btn" onclick="copyText('sqlBox')">Salin SQL</button>
        </dl>
    </div>
    <?php endif; ?>

    <div class="warn">⚠️ Ini adalah tool developer. Setelah selesai membuat akun-akun yang dibutuhkan, hapus atau amankan file <code>generate_pass.php</code> ini seperti halnya <code>seed.php</code>, agar tidak sembarang orang bisa membuat akun baru.</div>

    <p style="margin-top:1.2rem"><a href="auth/login.php">→ Ke halaman Login</a> &nbsp;|&nbsp; <a href="seed.php">→ Seed akun contoh</a></p>
</div>
<script>
function copyText(id){
    const el = document.getElementById(id);
    const text = el.innerText.trim();
    navigator.clipboard.writeText(text).catch(()=>{});
}
</script>
</body>
</html>
