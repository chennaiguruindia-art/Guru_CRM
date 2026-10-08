/**
 * Horticulture CRM - Sidebar pins
 *
 * Pins are stored per-browser in localStorage (no server round trip).
 * A pinned link is only rendered if the user can still see its source link,
 * so a role change can never leave a shortcut to a page they can't access.
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'horti_crm_pins';
    const MAX_PINS = 8;

    const sidebar = document.getElementById('sidebar');
    const pinWrap = document.getElementById('sidebar-pinned');
    const pinList = document.getElementById('sidebar-pinned-list');

    if (!sidebar || !pinWrap || !pinList) return;

    function load() {
        try {
            const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(parsed) ? parsed.filter((k) => typeof k === 'string') : [];
        } catch (error) {
            return [];
        }
    }

    let pins = load();

    function save() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(pins));
        } catch (error) {
            /* storage full / private mode — pins just won't persist */
        }
    }

    function sources() {
        return Array.from(sidebar.querySelectorAll('.side-item [data-pin-key]:not([data-pin-clone])'));
    }

    function renderPins() {
        const byKey = {};
        sources().forEach((el) => {
            byKey[el.dataset.pinKey] = el;
        });

        const kept = [];
        let html = '';

        pins.forEach((key) => {
            const src = byKey[key];
            if (!src) return; // module no longer visible to this user

            kept.push(key);

            html +=
                '<div class="side-item">' +
                '<a href="' + src.dataset.pinHref + '" class="sidebar-link" data-pin-clone="1"' +
                ' data-pin-key="' + src.dataset.pinKey + '"' +
                ' data-pin-label="' + src.dataset.pinLabel + '"' +
                ' data-pin-icon="' + src.dataset.pinIcon + '"' +
                ' data-pin-href="' + src.dataset.pinHref + '">' +
                '<span class="sidebar-icon"><i class="bi ' + src.dataset.pinIcon + '"></i></span>' +
                '<span class="sidebar-text">' + src.dataset.pinLabel + '</span>' +
                '</a>' +
                '<button type="button" class="pin-btn is-pinned" data-pin-target="' + key + '"' +
                ' title="Unpin" aria-label="Unpin ' + src.dataset.pinLabel + '">' +
                '<i class="bi bi-star-fill"></i>' +
                '</button>' +
                '</div>';
        });

        // Drop pins the user can no longer reach
        if (kept.length !== pins.length) {
            pins = kept;
            save();
        }

        pinList.innerHTML = html;
        pinWrap.hidden = pins.length === 0;
    }

    function syncStars() {
        sidebar.querySelectorAll('.pin-btn').forEach((button) => {
            const on = pins.indexOf(button.dataset.pinTarget) !== -1;
            button.classList.toggle('is-pinned', on);
            button.classList.toggle('is-hidden', false);
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-star-fill', on);
                icon.classList.toggle('bi-star', !on);
            }
            button.title = on ? 'Unpin' : 'Pin to top';
        });
    }

    function toggle(key) {
        const index = pins.indexOf(key);
        if (index === -1) {
            pins.unshift(key);
            pins = pins.slice(0, MAX_PINS);
        } else {
            pins.splice(index, 1);
        }
        save();
        renderPins();
        syncStars();
    }

    // Event delegation so cloned (pinned) buttons work too
    sidebar.addEventListener('click', (event) => {
        const button = event.target.closest('.pin-btn');
        if (!button) return;
        event.preventDefault();
        event.stopPropagation();
        toggle(button.dataset.pinTarget);
    });

    renderPins();
    syncStars();
})();
