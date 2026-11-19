<?php
/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Cybersalt\Component\CsDiskUsage\Administrator\View\Diskusage\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.modal');

HTMLHelper::_('stylesheet', 'com_csdiskusage/diskusage.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'com_csdiskusage/diskusage.js', ['version' => 'auto', 'relative' => true]);

// Strings used by diskusage.js (lock-out modal, filter, sorting)
Text::script('COM_CSDISKUSAGE_MEASURING');
Text::script('COM_CSDISKUSAGE_MEASURING_ROOT');

// Calculate max size for percentage calculations
$maxSize = $this->diskData['size'] > 0 ? $this->diskData['size'] : 1;

/**
 * Format bytes helper
 */
$formatBytes = static function ($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
};

/**
 * Usage bar colour class based on percentage. Uses Bootstrap contextual
 * background classes so the bars follow the light/dark admin theme.
 */
$getBarClass = static function ($percentage) {
    if ($percentage >= 80) {
        return 'bg-danger';
    }

    if ($percentage >= 40) {
        return 'bg-warning';
    }

    return 'bg-success';
};

/**
 * Build a link to a folder (the path travels base64 encoded).
 */
$folderLink = static function (string $path): string {
    return Route::_('index.php?option=com_csdiskusage&path=' . rawurlencode(base64_encode($path)));
};

$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<div class="cs-diskusage">
    <!-- Breadcrumb Navigation -->
    <nav class="cs-diskusage-breadcrumb" aria-label="<?php echo $esc(Text::_('COM_CSDISKUSAGE_BREADCRUMB')); ?>">
        <?php
        $currentBreadcrumb = $this->rootPath;
        $relativePath = substr($this->currentPath, strlen($this->rootPath));
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $relativePath), 'strlen');
        ?>
        <a href="<?php echo $folderLink($this->rootPath); ?>" class="breadcrumb-item" data-folder-name="">
            <span class="icon-home" aria-hidden="true"></span>
            <?php echo Text::_('COM_CSDISKUSAGE_ROOT'); ?>
        </a>

        <?php foreach ($parts as $part) : ?>
            <?php $currentBreadcrumb .= DIRECTORY_SEPARATOR . $part; ?>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo $folderLink($currentBreadcrumb); ?>" class="breadcrumb-item" data-folder-name="<?php echo $esc($part); ?>">
                <?php echo $esc($part); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Summary Card -->
    <div class="cs-diskusage-summary card">
        <div class="card-body">
            <h4 class="card-title">
                <span class="icon-folder" aria-hidden="true"></span>
                <?php echo $esc($this->diskData['name'] ?: basename($this->currentPath)); ?>
            </h4>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label"><?php echo Text::_('COM_CSDISKUSAGE_TOTAL_SIZE'); ?></span>
                    <span class="stat-value"><?php echo $formatBytes($this->diskData['size']); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo Text::_('COM_CSDISKUSAGE_FILES'); ?></span>
                    <span class="stat-value"><?php echo number_format($this->diskData['files_count']); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo Text::_('COM_CSDISKUSAGE_FOLDERS'); ?></span>
                    <span class="stat-value"><?php echo number_format($this->diskData['folders_count']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Folder List -->
    <?php if (!empty($this->diskData['children'])) : ?>
    <div class="cs-diskusage-table card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5><?php echo Text::_('COM_CSDISKUSAGE_SUBFOLDERS'); ?></h5>
            <div class="cs-diskusage-filter">
                <label for="csdu-filter" class="visually-hidden"><?php echo Text::_('COM_CSDISKUSAGE_FILTER_LABEL'); ?></label>
                <input type="search" id="csdu-filter" class="form-control form-control-sm"
                       placeholder="<?php echo $esc(Text::_('COM_CSDISKUSAGE_FILTER_PLACEHOLDER')); ?>"
                       autocomplete="off">
            </div>
        </div>
        <table class="table table-striped" id="csdu-table">
            <thead>
                <tr>
                    <th class="w-40" scope="col" aria-sort="none">
                        <button type="button" class="csdu-sort btn btn-link p-0 text-reset fw-bold" data-sort="name">
                            <?php echo Text::_('COM_CSDISKUSAGE_FOLDER_NAME'); ?> <span class="csdu-sort-icon" aria-hidden="true"></span>
                        </button>
                    </th>
                    <th class="w-30" scope="col" aria-sort="descending">
                        <button type="button" class="csdu-sort btn btn-link p-0 text-reset fw-bold" data-sort="size">
                            <?php echo Text::_('COM_CSDISKUSAGE_USAGE'); ?> <span class="csdu-sort-icon" aria-hidden="true"></span>
                        </button>
                    </th>
                    <th class="w-15 text-end" scope="col" aria-sort="descending">
                        <button type="button" class="csdu-sort btn btn-link p-0 text-reset fw-bold" data-sort="size">
                            <?php echo Text::_('COM_CSDISKUSAGE_SIZE'); ?> <span class="csdu-sort-icon" aria-hidden="true"></span>
                        </button>
                    </th>
                    <th class="w-15 text-end" scope="col" aria-sort="none">
                        <button type="button" class="csdu-sort btn btn-link p-0 text-reset fw-bold" data-sort="items">
                            <?php echo Text::_('COM_CSDISKUSAGE_ITEMS'); ?> <span class="csdu-sort-icon" aria-hidden="true"></span>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->diskData['children'] as $child) : ?>
                    <?php
                    $percentage = $maxSize > 0 ? ($child['size'] / $maxSize) * 100 : 0;
                    $barClass = $getBarClass($percentage);
                    $items = $child['files_count'] + $child['folders_count'];
                    ?>
                    <tr data-name="<?php echo $esc(strtolower($child['name'])); ?>"
                        data-size="<?php echo (int) $child['size']; ?>"
                        data-items="<?php echo (int) $items; ?>">
                        <td>
                            <a href="<?php echo $folderLink($child['path']); ?>" class="folder-link" data-folder-name="<?php echo $esc($child['name']); ?>">
                                <span class="icon-folder" aria-hidden="true"></span>
                                <?php echo $esc($child['name']); ?>
                            </a>
                        </td>
                        <td>
                            <div class="usage-bar-container">
                                <div class="usage-bar <?php echo $barClass; ?>" style="width: <?php echo round($percentage, 2); ?>%;"></div>
                                <span class="usage-percentage"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="size-value"><?php echo $formatBytes($child['size']); ?></span>
                        </td>
                        <td class="text-end">
                            <span class="items-count" title="<?php echo $esc(Text::sprintf('COM_CSDISKUSAGE_FILES_FOLDERS', $child['files_count'], $child['folders_count'])); ?>">
                                <?php echo number_format($items); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="csdu-no-match" class="d-none">
                    <td colspan="4" class="text-center text-muted"><?php echo Text::_('COM_CSDISKUSAGE_FILTER_NO_MATCH'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span>
        <?php echo Text::_('COM_CSDISKUSAGE_NO_SUBFOLDERS'); ?>
    </div>
    <?php endif; ?>

    <!-- Quick Access -->
    <div class="cs-diskusage-quickaccess card mt-4">
        <div class="card-header">
            <h5><?php echo Text::_('COM_CSDISKUSAGE_QUICK_ACCESS'); ?></h5>
        </div>
        <div class="card-body">
            <div class="quick-access-grid">
                <?php foreach ($this->commonDirectories as $name => $path) : ?>
                    <a href="<?php echo $folderLink($path); ?>" class="quick-access-item" data-folder-name="<?php echo $esc($name); ?>">
                        <span class="icon-folder" aria-hidden="true"></span>
                        <?php echo $esc($name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Lock-out modal: shown while the next folder is being measured -->
    <div class="modal fade" id="csdu-busy-modal" tabindex="-1"
         data-bs-backdrop="static" data-bs-keyboard="false"
         aria-labelledby="csdu-busy-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body d-flex align-items-center gap-3 py-4">
                    <div class="spinner-border flex-shrink-0" role="status" aria-hidden="true"></div>
                    <div>
                        <h5 class="modal-title mb-1" id="csdu-busy-title" aria-live="polite"></h5>
                        <p class="mb-0 text-muted small"><?php echo Text::_('COM_CSDISKUSAGE_MEASURING_HINT'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
