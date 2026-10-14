<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= $asset_path ?? '' ?>assets/img/bank_sampah_icon.png" alt="Logo">
        <span>Bank Sampah</span>
    </div>
    <div class="sidebar-section">Menu</div>
    <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>">
                <?= icon('home') ?><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="transaksi.php" class="nav-link <?= $current === 'transaksi.php' ? 'active' : '' ?>">
                <?= icon('deposit') ?><span>Catat Setoran</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="sampah.php" class="nav-link <?= $current === 'sampah.php' ? 'active' : '' ?>">
                <?= icon('tag') ?><span>Data Sampah</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="penjualan.php" class="nav-link <?= $current === 'penjualan.php' ? 'active' : '' ?>">
                <?= icon('cash') ?><span>Penjualan Pengepul</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="penarikan.php" class="nav-link <?= $current === 'penarikan.php' ? 'active' : '' ?>">
                <?= icon('wallet') ?><span>Penarikan Saldo</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link <?= $current === 'users.php' ? 'active' : '' ?>">
                <?= icon('user') ?><span>Data Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="pengaturan.php" class="nav-link <?= $current === 'pengaturan.php' ? 'active' : '' ?>">
                <?= icon('settings') ?><span>Pengaturan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="tentang.php" class="nav-link <?= $current === 'tentang.php' ? 'active' : '' ?>">
                <?= icon('info') ?><span>Tentang</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-bottom">
        <a href="../auth/logout.php" class="nav-link logout-link">
            <?= icon('logout') ?><span>Keluar</span>
        </a>
    </div>
</aside>
