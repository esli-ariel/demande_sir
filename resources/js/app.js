import '../css/admin.css';
import '../css/auth.css';
import './bootstrap';

import Alpine from 'alpinejs';

Alpine.data('adminNavigation', () => ({
    open: false,
    unread: 0,
    notificationTimer: null,
    init() {
        this.unread = Number(this.$el.dataset.notificationCount || 0);
        this.notificationTimer = setInterval(() => this.refreshNotifications(), 30000);
    },
    destroy() { clearInterval(this.notificationTimer); },
    async refreshNotifications() {
        if (document.hidden || !this.$el.dataset.notificationSummary) return;
        try {
            const response = await fetch(this.$el.dataset.notificationSummary, {
                headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store',
            });
            if (response.status === 401 || response.redirected) {
                clearInterval(this.notificationTimer);
                return;
            }
            if (response.ok) {
                const data = await response.json();
                if (Number.isInteger(data.unread) && data.unread >= 0) this.unread = data.unread;
            }
        } catch (_) {}
    },
    dark: document.documentElement.dataset.adminTheme === 'dark',
    toggleTheme() {
        this.dark = !this.dark;
        const theme = this.dark ? 'dark' : 'light';
        document.documentElement.dataset.adminTheme = theme;
        document.documentElement.style.colorScheme = theme;
        try { localStorage.setItem('dma-theme', theme); } catch (_) {}
    },
}));

window.Alpine = Alpine;

Alpine.data('authPage', () => ({
    dark: document.documentElement.dataset.adminTheme === 'dark',
    submitting: false,
    init() {
        const summary = this.$el.querySelector('[data-auth-errors]');
        if (summary) requestAnimationFrame(() => summary.focus());
    },
    toggleTheme() {
        this.dark = !this.dark;
        const theme = this.dark ? 'dark' : 'light';
        document.documentElement.dataset.adminTheme = theme;
        document.documentElement.style.colorScheme = theme;
        try { localStorage.setItem('dma-theme', theme); } catch (_) {}
    },
}));

Alpine.start();
