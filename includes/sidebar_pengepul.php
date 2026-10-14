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
            <a href="sampah.php" class="nav-link <?= $current === 'sampah.php' ? 'active' : '' ?>">
                <?= icon('tag') ?><span>Stok & Harga Jual</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="beli.php" class="nav-link <?= $current === 'beli.php' ? 'active' : '' ?>">
                <?= icon('deposit') ?><span>Beli Sampah</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="topup.php" class="nav-link <?= $current === 'topup.php' ? 'active' : '' ?>">
                <?= icon('wallet') ?><span>Top Up Saldo</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="transaksi.php" class="nav-link <?= $current === 'transaksi.php' ? 'active' : '' ?>">
                <?= icon('history') ?><span>Riwayat</span>
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
