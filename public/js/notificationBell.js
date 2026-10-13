/**
 * notificationBell.js
 * Handles the notification bell icon in the navbar.
 * Fetches from /api/workouts/notifications every 30 seconds.
 */

class NotificationBell {
    constructor() {
        this.pollInterval = 30000;
        this.pollTimer = null;
        this.isOpen = false;
    }

    async init() {
        this.attachEvents();
        await this.refresh();
        this.startPolling();
    }

    attachEvents() {
        const bell = document.getElementById('notificationBell');
        const panel = document.getElementById('notificationPanel');
        const markAll = document.getElementById('markAllRead');

        if (!panel) return;

        if (bell) {
            bell.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });
        }

        if (markAll) {
            markAll.addEventListener('click', async (e) => {
                e.stopPropagation();
                await this.markAllRead();
            });
        }

        document.addEventListener('click', (e) => {
            if (!panel.contains(e.target) && (!bell || !bell.contains(e.target))) {
                panel.style.display = 'none';
                this.isOpen = false;
            }
        });
    }

    toggle() {
        const panel = document.getElementById('notificationPanel');
        if (!panel) return;
        if (this.isOpen) {
            panel.style.display = 'none';
            this.isOpen = false;
        } else {
            panel.style.display = 'block';
            this.isOpen = true;
            this.refresh();
        }
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    async fetchJson(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...(options.headers || {}),
            },
            credentials: 'same-origin',
            ...options,
        });
        if (!res.ok) throw new Error(`API ${res.status}`);
        return res.json();
    }

    async refresh() {
        try {
            const data = await this.fetchJson('/api/workouts/notifications');
            this.renderBadge(data.unread_count);
            this.renderList(data.unread);
        } catch (err) {
            console.error('Notification fetch failed:', err);
        }
    }

    renderBadge(count) {
        const badge = document.getElementById('notificationCount');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        const sideBadge = document.getElementById('sidebarNotifCount');
        if (sideBadge) {
            if (count > 0) {
                sideBadge.textContent = count > 99 ? '99+' : count;
                sideBadge.style.display = 'inline-block';
            } else {
                sideBadge.style.display = 'none';
            }
        }
    }

    renderList(notifications) {
        const listEl = document.getElementById('notificationList');
        if (!listEl) return;

        if (!notifications || notifications.length === 0) {
            listEl.innerHTML = `
                <p class="text-center text-muted my-4">
                    <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                    You're all caught up!
                </p>`;
            return;
        }

        listEl.innerHTML = notifications.map(n => {
            const title = n.data.title ?? 'Notification';
            const message = n.data.message ?? '';
            const icon = n.data.icon ?? 'bi-info-circle-fill';
            const color = n.data.color ?? 'primary';
            const url = n.data.url ?? '#';
            const when = new Date(n.created_at).toLocaleString();

            return `
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-start">
                        <i class="bi ${icon} text-${color} fs-4 me-3"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">${title}</strong>
                            <small class="text-muted d-block mb-2">${message}</small>
                            <small class="text-secondary d-block mb-2">
                                <i class="bi bi-clock"></i> ${when}
                            </small>
                            <a href="${url}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-right"></i> View
                            </a>
                        </div>
                    </div>
                </div>`;
        }).join('');
    }

    async markAllRead() {
        try {
            await this.fetchJson('/api/workouts/notifications/read', { method: 'POST' });
            await this.refresh();
        } catch (err) {
            console.error('Mark read failed:', err);
        }
    }

    startPolling() {
        this.pollTimer = setInterval(() => {
            if (!this.isOpen) this.refresh();
        }, this.pollInterval);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.notificationBell = new NotificationBell();
    window.notificationBell.init();
});