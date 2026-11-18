/**
 * Horticulture CRM - Toolbar
 * 1. Global search across every module the user may view
 * 2. Real "needs attention" notifications (loaded on demand)
 */
(function () {
    'use strict';

    // Escape untrusted server data before injecting as HTML
    function esc(value) {
        const div = document.createElement('div');
        div.textContent = value === null || value === undefined ? '' : String(value);
        return div.innerHTML;
    }

    /* ─────────────────────────── GLOBAL SEARCH ─────────────────────────── */
    const input = document.getElementById('global-search-input');
    const panel = document.getElementById('search-panel');

    if (input && panel) {
        let debounceTimer = null;
        let activeIndex = -1;
        let requestId = 0;

        const isOpen = () => !panel.hidden;

        function closePanel() {
            panel.hidden = true;
            panel.innerHTML = '';
            activeIndex = -1;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function openPanel() {
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function items() {
            return Array.from(panel.querySelectorAll('.search-item'));
        }

        function highlight(index) {
            const list = items();
            if (!list.length) return;
            activeIndex = (index + list.length) % list.length;
            list.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
            list[activeIndex].scrollIntoView({ block: 'nearest' });
            input.setAttribute('aria-activedescendant', list[activeIndex].id);
        }

        function render(query, results) {
            if (!results.length) {
                panel.innerHTML =
                    '<div class="search-empty">' +
                    '<i class="bi bi-search"></i>' +
                    '<div>No matches for <strong>' + esc(query) + '</strong></div>' +
                    '<small>Try a name, code (LEAD-0001), phone or city.</small>' +
                    '</div>';
                openPanel();
                return;
            }

            let html = '';
            let lastType = null;

            results.forEach((r, i) => {
                if (r.type !== lastType) {
                    html += '<div class="search-group">' + esc(r.type) + '</div>';
                    lastType = r.type;
                }
                html +=
                    '<a class="search-item" id="search-opt-' + i + '" role="option" href="' + esc(r.url) + '">' +
                    '<span class="search-item-icon"><i class="bi ' + esc(r.icon) + '"></i></span>' +
                    '<span class="search-item-text">' +
                    '<span class="search-item-title">' + esc(r.title) + '</span>' +
                    '<span class="search-item-sub">' + esc(r.subtitle) + '</span>' +
                    '</span>' +
                    '<i class="bi bi-arrow-up-right search-item-go"></i>' +
                    '</a>';
            });

            panel.innerHTML = html;
            openPanel();
            activeIndex = -1;
        }

        async function run(query) {
            const id = ++requestId;
            try {
                const data = await ajaxRequest('/search?q=' + encodeURIComponent(query));
                if (id !== requestId) return; // a newer keystroke superseded this one
                render(query, (data && data.results) || []);
            } catch (error) {
                if (id === requestId) closePanel();
            }
        }

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = input.value.trim();

            if (query.length < 2) {
                requestId++;
                closePanel();
                return;
            }
            debounceTimer = setTimeout(() => run(query), 220);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePanel();
                input.blur();
                return;
            }
            if (!isOpen()) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlight(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(activeIndex - 1);
            } else if (event.key === 'Enter') {
                const list = items();
                const target = list[activeIndex] || list[0];
                if (target) {
                    event.preventDefault();
                    window.location.href = target.href;
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.global-search')) closePanel();
        });

        // "/" focuses search (unless already typing somewhere else)
        document.addEventListener('keydown', (event) => {
            const tag = (event.target.tagName || '').toLowerCase();
            const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || event.target.isContentEditable;

            if (event.key === '/' && !typing && !event.metaKey && !event.ctrlKey) {
                event.preventDefault();
                input.focus();
                input.select();
            }
        });
    }

    /* ──────────────────────── REAL NOTIFICATIONS ──────────────────────── */
    const notifButton = document.getElementById('notif-btn');
    const notifList = document.getElementById('notif-list');
    const notifBadge = document.getElementById('notif-badge');
    const notifCount = document.getElementById('notif-count');
    const notifMarkAllBtn = document.getElementById('notif-mark-all-btn');

    if (notifButton && notifList) {
        const SEVERITY_ICON = { danger: 'text-danger', warning: 'text-warning', info: 'text-info' };
        let lastLoaded = 0;
        let loading = false;

        function updateBadge(count) {
            const badgeNum = document.getElementById('notif-badge-num');
            if (notifBadge) {
                notifBadge.hidden = count === 0;
                if (badgeNum) badgeNum.textContent = String(count);
            }
            if (notifCount) notifCount.textContent = count === 0 ? 'All clear' : count + ' open';
            if (notifMarkAllBtn) notifMarkAllBtn.hidden = count === 0;
        }

        function showEmptyState() {
            notifList.innerHTML =
                '<div class="notif-empty p-3 text-center text-muted">' +
                '<i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>' +
                '<div><strong>Nothing needs attention</strong></div>' +
                '<small>All caught up! No pending notifications.</small>' +
                '</div>';
            if (notifMarkAllBtn) notifMarkAllBtn.hidden = true;
        }

        async function markAsRead(key, elementToFade) {
            if (elementToFade) {
                elementToFade.style.opacity = '0.4';
                elementToFade.style.pointerEvents = 'none';
            }
            try {
                const response = await ajaxRequest('/notifications/mark-read', {
                    method: 'POST',
                    body: { key: key }
                });

                if (elementToFade) {
                    elementToFade.remove();
                }

                const remaining = response && typeof response.count === 'number' ? response.count : 0;
                updateBadge(remaining);

                if (!notifList.querySelector('.notif-item')) {
                    showEmptyState();
                }
            } catch (err) {
                console.error('Failed to mark notification as read:', err);
                if (elementToFade) {
                    elementToFade.style.opacity = '1';
                    elementToFade.style.pointerEvents = '';
                }
            }
        }

        async function markAllAsRead() {
            if (!notifList.querySelector('.notif-item')) return;
            const originalText = notifMarkAllBtn ? notifMarkAllBtn.textContent : '';
            if (notifMarkAllBtn) notifMarkAllBtn.textContent = 'Clearing…';

            try {
                await ajaxRequest('/notifications/mark-read', {
                    method: 'POST',
                    body: { all: true }
                });

                updateBadge(0);
                showEmptyState();
            } catch (err) {
                console.error('Failed to mark all as read:', err);
            } finally {
                if (notifMarkAllBtn) notifMarkAllBtn.textContent = originalText;
            }
        }

        async function loadNotifications(force) {
            if (loading) return;
            if (!force && Date.now() - lastLoaded < 60000) return; // cache for 60s
            loading = true;

            try {
                const data = await ajaxRequest('/notifications');
                lastLoaded = Date.now();

                const items = (data && data.items) || [];
                const count = (data && data.count) || 0;

                updateBadge(count);

                if (!items.length) {
                    showEmptyState();
                    return;
                }

                notifList.innerHTML = items.map((item) =>
                    '<div class="list-group-item list-group-item-action notif-item p-2 d-flex align-items-center justify-content-between gap-2" data-key="' + esc(item.key) + '">' +
                    '<a href="' + esc(item.url) + '" class="d-flex align-items-center gap-2 text-decoration-none flex-grow-1 overflow-hidden notif-link" data-key="' + esc(item.key) + '">' +
                    '<span class="notif-dot ' + esc(SEVERITY_ICON[item.severity] || 'text-info') + '">' +
                    '<i class="bi ' + esc(item.icon) + '"></i></span>' +
                    '<span class="flex-grow-1 overflow-hidden">' +
                    '<span class="d-block fw-semibold text-dark text-truncate">' + esc(item.title) + '</span>' +
                    '<span class="d-block text-muted text-truncate" style="font-size:0.75rem">' + esc(item.label) + (item.meta ? ' · ' + esc(item.meta) : '') + '</span>' +
                    '</span>' +
                    '</a>' +
                    '<button type="button" class="btn btn-sm btn-light border text-success notif-read-btn flex-shrink-0" title="Mark as read" data-key="' + esc(item.key) + '" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 6px;">' +
                    '<i class="bi bi-check2 me-1"></i>Mark read' +
                    '</button>' +
                    '</div>'
                ).join('');
            } catch (error) {
                notifList.innerHTML = '<div class="p-3 text-muted small mb-0">Could not load notifications.</div>';
            } finally {
                loading = false;
            }
        }

        notifList.addEventListener('click', (e) => {
            const readBtn = e.target.closest('.notif-read-btn');
            if (readBtn) {
                e.preventDefault();
                e.stopPropagation();
                const key = readBtn.getAttribute('data-key');
                const row = readBtn.closest('.notif-item');
                markAsRead(key, row);
                return;
            }

            const link = e.target.closest('.notif-link');
            if (link) {
                const key = link.getAttribute('data-key');
                if (key) {
                    ajaxRequest('/notifications/mark-read', {
                        method: 'POST',
                        body: { key: key }
                    }).catch(() => {});
                }
            }
        });

        if (notifMarkAllBtn) {
            notifMarkAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                markAllAsRead();
            });
        }

        notifButton.addEventListener('click', () => loadNotifications(false));
        loadNotifications(false); // paint the badge on first page load
    }
})();
