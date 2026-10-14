document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    const root = document.documentElement;

    // -----------------------------------------------------
    // SIDEBAR
    // Desktop : collapse / expand
    // Mobile  : buka / tutup off-canvas
    // -----------------------------------------------------
    function isMobile() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function closeAppSidebar() {
        if (!sidebar) return;
        if (isMobile()) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        } else {
            root.classList.add('sidebar-collapsed');
            localStorage.setItem('bs_sidebar', 'collapsed');
        }
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    function openAppSidebar() {
        if (!sidebar) return;
        if (isMobile()) {
            sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
        } else {
            root.classList.remove('sidebar-collapsed');
            localStorage.setItem('bs_sidebar', 'expanded');
        }
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
    }

    function toggleAppSidebar() {
        if (!sidebar) return;
        if (isMobile()) {
            sidebar.classList.contains('show') ? closeAppSidebar() : openAppSidebar();
        } else {
            root.classList.contains('sidebar-collapsed') ? openAppSidebar() : closeAppSidebar();
        }
    }

    window.closeAppSidebar = closeAppSidebar;
    window.openAppSidebar = openAppSidebar;

    if (sidebar && !isMobile() && localStorage.getItem('bs_sidebar') === 'collapsed') {
        root.classList.add('sidebar-collapsed');
    }

    if (toggle && sidebar) {
        toggle.addEventListener('click', toggleAppSidebar);
    }

    if (overlay) overlay.addEventListener('click', closeAppSidebar);

    if (sidebar) {
        sidebar.querySelectorAll('a.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) closeAppSidebar();
            });
        });
    }

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            if (overlay) overlay.classList.remove('show');
            if (sidebar) sidebar.classList.remove('show');
            if (localStorage.getItem('bs_sidebar') === 'collapsed') {
                root.classList.add('sidebar-collapsed');
            }
        }
    });

    // -----------------------------------------------------
    // DARK MODE
    // -----------------------------------------------------
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = root.getAttribute('data-theme') === 'dark';
            const next = isDark ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            const themeKey = root.getAttribute('data-theme-key') || 'bs_theme_guest';
            localStorage.setItem(themeKey, next);

            const settingsSwitch = document.getElementById('darkModeSwitch');
            if (settingsSwitch) settingsSwitch.checked = next === 'dark';
        });
    }

    // -----------------------------------------------------
    // SWITCH DARK MODE DI PENGATURAN (sinkron dengan tombol topbar)
    // -----------------------------------------------------
    const themeKey = () => root.getAttribute('data-theme-key') || 'bs_theme_guest';
    const darkSwitch = document.getElementById('darkModeSwitch');
    const darkState = document.getElementById('darkModeState');
    function syncSwitch() {
        const dark = root.getAttribute('data-theme') === 'dark';
        if (darkSwitch) darkSwitch.checked = dark;
        if (darkState) darkState.textContent = dark ? 'Aktif' : 'Nonaktif';
    }
    function setTheme(t) {
        root.setAttribute('data-theme', t);
        localStorage.setItem(themeKey(), t);
        localStorage.setItem('bs_theme_guest', t); // dibawa ke halaman login
        syncSwitch();
    }
    if (themeBtn) {
        // ganti handler lama dengan yang menyimpan ke dua key
        const fresh = themeBtn.cloneNode(true);
        themeBtn.replaceWith(fresh);
        fresh.addEventListener('click', () => setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
    }
    if (darkSwitch) darkSwitch.addEventListener('change', () => setTheme(darkSwitch.checked ? 'dark' : 'light'));
    syncSwitch();

    // -----------------------------------------------------
    // LIHAT / SEMBUNYIKAN PASSWORD
    // -----------------------------------------------------
    document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-on', show);
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    // -----------------------------------------------------
    // FORM GANTI PASSWORD: kekuatan + kecocokan
    // -----------------------------------------------------
    const pwBaru = document.getElementById('pwBaru');
    const pwKonf = document.getElementById('pwKonfirmasi');
    const meter = document.getElementById('pwMeter');
    const hint = document.getElementById('pwMatchHint');
    function score(v) {
        let s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return v ? Math.max(1, s) : 0;
    }
    function checkPw() {
        if (meter && pwBaru) meter.dataset.level = score(pwBaru.value);
        if (hint && pwKonf) {
            if (!pwKonf.value) { hint.textContent = ''; hint.className = 'form-text'; return; }
            const ok = pwKonf.value === pwBaru.value;
            hint.textContent = ok ? 'Password cocok.' : 'Password belum sama.';
            hint.className = 'form-text ' + (ok ? 'text-success' : 'text-danger');
        }
    }
    if (pwBaru) pwBaru.addEventListener('input', checkPw);
    if (pwKonf) pwKonf.addEventListener('input', checkPw);
    const formPw = document.getElementById('formPassword');
    if (formPw) formPw.addEventListener('submit', e => {
        if (pwBaru.value.length < 6 || pwBaru.value !== pwKonf.value) {
            e.preventDefault();
            checkPw();
            (pwBaru.value.length < 6 ? pwBaru : pwKonf).focus();
        }
    });
});
