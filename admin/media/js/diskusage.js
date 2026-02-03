/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Add loading indicator to folder links
    const folderLinks = document.querySelectorAll('.folder-link, .quick-access-item, .breadcrumb-item');

    folderLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            // Add loading class to the clicked element
            this.classList.add('loading');

            // Show loading indicator
            const icon = this.querySelector('.icon-folder, .icon-home');
            if (icon) {
                icon.classList.add('icon-spinner', 'icon-spin');
                icon.classList.remove('icon-folder', 'icon-home');
            }
        });
    });

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
