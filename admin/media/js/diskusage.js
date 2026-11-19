/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const text = function(key, fallback) {
        const value = (window.Joomla && Joomla.Text && Joomla.Text._(key)) || '';

        return value && value !== key ? value : fallback;
    };

    // ------------------------------------------------------------------
    // Lock-out modal while the next folder is measured (the scan is a
    // synchronous walk of the file system on the server, so the page can
    // take several seconds to come back).
    // ------------------------------------------------------------------
    const modalEl = document.getElementById('csdu-busy-modal');
    const titleEl = document.getElementById('csdu-busy-title');
    let modal = null;

    const showBusy = function(folderName) {
        if (!modalEl || !titleEl) {
            return;
        }

        titleEl.textContent = folderName
            ? text('COM_CSDISKUSAGE_MEASURING', 'Measuring folder sizes in %s…').replace('%s', folderName)
            : text('COM_CSDISKUSAGE_MEASURING_ROOT', 'Measuring folder sizes in the site root…');

        if (window.bootstrap && window.bootstrap.Modal) {
            modal = window.bootstrap.Modal.getOrCreateInstance(modalEl, {backdrop: 'static', keyboard: false});
            modal.show();
        } else {
            // Bootstrap not available: fall back to a plain overlay
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
        }
    };

    const hideBusy = function() {
        if (modal) {
            modal.hide();
            modal = null;
        }

        if (modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = '';
            document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    };

    document.querySelectorAll('.folder-link, .quick-access-item, .breadcrumb-item').forEach(function(link) {
        link.addEventListener('click', function(event) {
            // Let ctrl/cmd/shift/middle clicks open a new tab without locking this one
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) {
                return;
            }

            // Show loading indicator on the clicked element
            this.classList.add('loading');

            const icon = this.querySelector('.icon-folder, .icon-home');

            if (icon) {
                icon.classList.add('icon-spinner', 'icon-spin');
                icon.classList.remove('icon-folder', 'icon-home');
            }

            showBusy(this.getAttribute('data-folder-name') || '');
        });
    });

    // Coming back with the browser's Back button restores the page from cache
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            hideBusy();
        }
    });

    // ------------------------------------------------------------------
    // Client-side name filter + sortable columns for the folder table
    // ------------------------------------------------------------------
    const table = document.getElementById('csdu-table');

    if (table) {
        const tbody = table.querySelector('tbody');
        const noMatch = document.getElementById('csdu-no-match');
        const filterInput = document.getElementById('csdu-filter');
        const rows = function() {
            return Array.prototype.slice.call(tbody.querySelectorAll('tr[data-name]'));
        };

        if (filterInput) {
            filterInput.addEventListener('input', function() {
                const needle = this.value.trim().toLowerCase();
                let visible = 0;

                rows().forEach(function(row) {
                    const match = needle === '' || row.getAttribute('data-name').indexOf(needle) !== -1;
                    row.classList.toggle('d-none', !match);

                    if (match) {
                        visible++;
                    }
                });

                if (noMatch) {
                    noMatch.classList.toggle('d-none', visible > 0);
                }
            });
        }

        // Server order is size, largest first
        let sortKey = 'size';
        let sortDir = 'desc';

        const applySort = function() {
            const numeric = sortKey !== 'name';
            const factor = sortDir === 'asc' ? 1 : -1;

            rows().sort(function(a, b) {
                const av = a.getAttribute('data-' + sortKey);
                const bv = b.getAttribute('data-' + sortKey);

                if (numeric) {
                    return (parseInt(av, 10) - parseInt(bv, 10)) * factor;
                }

                return av.localeCompare(bv) * factor;
            }).forEach(function(row) {
                tbody.insertBefore(row, noMatch);
            });

            table.querySelectorAll('th').forEach(function(th) {
                const button = th.querySelector('.csdu-sort');
                const icon = th.querySelector('.csdu-sort-icon');
                const active = button && button.getAttribute('data-sort') === sortKey;

                th.setAttribute('aria-sort', active ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none');

                if (icon) {
                    icon.textContent = active ? (sortDir === 'asc' ? '▲' : '▼') : '';
                }
            });
        };

        table.querySelectorAll('.csdu-sort').forEach(function(button) {
            button.addEventListener('click', function() {
                const key = this.getAttribute('data-sort');

                if (key === sortKey) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortKey = key;
                    sortDir = key === 'name' ? 'asc' : 'desc';
                }

                applySort();
            });
        });

        applySort();
    }

    // Animate usage bars on load
    const usageBars = document.querySelectorAll('.usage-bar');
    usageBars.forEach(function(bar, index) {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';

        setTimeout(function() {
            bar.style.width = targetWidth;
        }, 50 + (index * 30));
    });
});
