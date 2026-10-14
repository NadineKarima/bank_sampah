/**
 * Keyboard shortcut global:
 * ESC = tutup modal / sidebar
 * ENTER = submit form saat fokus berada pada input/select/button form
 */
document.addEventListener('keydown', function (e) {
    const active = document.activeElement;
    const tag = active ? active.tagName : '';

    // ESC
    if (e.key === 'Escape') {
        const modalEl = document.querySelector('.modal.show');
        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
        if (typeof window.closeAppSidebar === 'function') window.closeAppSidebar();
        return;
    }

    // ENTER: jangan ganggu textarea, tombol, link, atau select.
    if (e.key === 'Enter' && active && active.form) {
        if (tag === 'TEXTAREA' || tag === 'BUTTON' || tag === 'A' || tag === 'SELECT') return;

        if (tag === 'INPUT') {
            e.preventDefault();
            if (typeof active.form.requestSubmit === 'function') {
                active.form.requestSubmit();
            } else {
                active.form.submit();
            }
        }
    }
});
