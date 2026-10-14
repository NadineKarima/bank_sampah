<?php
/**
 * Isi halaman Pengaturan (dipakai admin, anggota, pengepul).
 * Butuh: $id_user
 */
?>
<div class="page-intro mb-3">
    <h4 class="mb-1">Pengaturan</h4>
    <p class="text-muted mb-0">Atur tampilan dan keamanan akun kamu.</p>
</div>
<?php show_flash(); ?>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card card-stat p-4 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><?= icon('moon') ?></div>
                <div>
                    <h6 class="mb-1">Mode Gelap</h6>
                    <small class="text-muted">Pilihan tersimpan khusus untuk akun ini di perangkat ini.</small>
                </div>
            </div>
            <div class="form-check form-switch mt-4 theme-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="darkModeSwitch">
                <label class="form-check-label fw-semibold" for="darkModeSwitch">
                    <span class="switch-state" id="darkModeState">Nonaktif</span>
                </label>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card card-stat p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon"><?= icon('lock') ?></div>
                <div>
                    <h6 class="mb-1">Ganti Password</h6>
                    <small class="text-muted">Minimal 6 karakter.</small>
                </div>
            </div>
            <form method="POST" id="formPassword" novalidate>
                <input type="hidden" name="action" value="ganti_password">

                <div class="mb-3">
                    <label class="form-label" for="pwLama">Password Lama</label>
                    <div class="pw-field">
                        <input type="password" id="pwLama" name="password_lama" class="form-control" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Tampilkan password"><?= icon('eye') ?><?= icon('eye-off') ?></button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="pwBaru">Password Baru</label>
                    <div class="pw-field">
                        <input type="password" id="pwBaru" name="password_baru" class="form-control" minlength="6" required autocomplete="new-password">
                        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Tampilkan password"><?= icon('eye') ?><?= icon('eye-off') ?></button>
                    </div>
                    <div class="pw-meter mt-2" id="pwMeter" aria-hidden="true"><span></span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="pwKonfirmasi">Konfirmasi Password Baru</label>
                    <div class="pw-field">
                        <input type="password" id="pwKonfirmasi" name="konfirmasi_password" class="form-control" minlength="6" required autocomplete="new-password">
                        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Tampilkan password"><?= icon('eye') ?><?= icon('eye-off') ?></button>
                    </div>
                    <div class="form-text" id="pwMatchHint"></div>
                </div>

                <button type="submit" class="btn btn-success">Simpan Password</button>
            </form>
        </div>
    </div>
</div>
