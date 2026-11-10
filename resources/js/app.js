import '../css/admin.css';
import './bootstrap';

import Alpine from 'alpinejs';

Alpine.data('adminNavigation', () => ({
    open: false,
    dark: document.documentElement.dataset.adminTheme === 'dark',
    toggleTheme() {
        this.dark = !this.dark;
        const theme = this.dark ? 'dark' : 'light';
        document.documentElement.dataset.adminTheme = theme;
        document.documentElement.style.colorScheme = theme;
        try { localStorage.setItem('dma-admin-theme', theme); } catch (_) {}
    },
}));

window.Alpine = Alpine;

Alpine.start();
