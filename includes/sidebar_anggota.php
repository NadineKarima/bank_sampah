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
                <?= icon('home') ?><span>Beranda</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="setor.php" class="nav-link <?= $current === 'setor.php' ? 'active' : '' ?>">
                <?= icon('deposit') ?><span>Setor Sampah</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="harga_sampah.php" class="nav-link <?= $current === 'harga_sampah.php' ? 'active' : '' ?>">
                <?= icon('tag') ?><span>Harga Sampah</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="riwayat.php" class="nav-link <?= $current === 'riwayat.php' ? 'active' : '' ?>">
                <?= icon('history') ?><span>Riwayat Setor</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="tarik_saldo.php" class="nav-link <?= $current === 'tarik_saldo.php' ? 'active' : '' ?>">
                <?= icon('wallet') ?><span>Tarik Saldo</span>
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
