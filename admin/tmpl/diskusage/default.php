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

HTMLHelper::_('stylesheet', 'com_csdiskusage/diskusage.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'com_csdiskusage/diskusage.js', ['version' => 'auto', 'relative' => true]);

// Calculate max size for percentage calculations
$maxSize = $this->diskData['size'] > 0 ? $this->diskData['size'] : 1;

/**
 * Format bytes helper
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get usage bar color based on percentage
 */
function getBarColor($percentage) {
    if ($percentage >= 80) return '#dc3545'; // Red
    if ($percentage >= 60) return '#fd7e14'; // Orange
    if ($percentage >= 40) return '#ffc107'; // Yellow
    return '#28a745'; // Green
}
?>

<div class="cs-diskusage">
    <!-- Breadcrumb Navigation -->
    <nav class="cs-diskusage-breadcrumb">
        <?php
        $pathParts = [];
        $currentBreadcrumb = $this->rootPath;
        $relativePath = str_replace($this->rootPath, '', $this->currentPath);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $relativePath));

        // Root link
        $rootEncoded = base64_encode($this->rootPath);
        ?>
        <a href="<?php echo Route::_('index.php?option=com_csdiskusage&path=' . $rootEncoded); ?>" class="breadcrumb-item">
            <span class="icon-home" aria-hidden="true"></span>
            <?php echo Text::_('COM_CSDISKUSAGE_ROOT'); ?>
        </a>

        <?php
        foreach ($parts as $part) {
            $currentBreadcrumb .= DIRECTORY_SEPARATOR . $part;
            $encoded = base64_encode($currentBreadcrumb);
            ?>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo Route::_('index.php?option=com_csdiskusage&path=' . $encoded); ?>" class="breadcrumb-item">
                <?php echo htmlspecialchars($part); ?>
            </a>
        <?php } ?>
    </nav>

    <!-- Summary Card -->
    <div class="cs-diskusage-summary card">
        <div class="card-body">
            <h4 class="card-title">
                <span class="icon-folder" aria-hidden="true"></span>
                <?php echo htmlspecialchars($this->diskData['name'] ?: basename($this->currentPath)); ?>
            </h4>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label"><?php echo Text::_('COM_CSDISKUSAGE_TOTAL_SIZE'); ?></span>
                    <span class="stat-value"><?php echo formatBytes($this->diskData['size']); ?></span>
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
    <?php if (!empty($this->diskData['children'])): ?>
    <div class="cs-diskusage-table card">
        <div class="card-header">
            <h5><?php echo Text::_('COM_CSDISKUSAGE_SUBFOLDERS'); ?></h5>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="w-40"><?php echo Text::_('COM_CSDISKUSAGE_FOLDER_NAME'); ?></th>
                    <th class="w-30"><?php echo Text::_('COM_CSDISKUSAGE_USAGE'); ?></th>
                    <th class="w-15 text-end"><?php echo Text::_('COM_CSDISKUSAGE_SIZE'); ?></th>
                    <th class="w-15 text-end"><?php echo Text::_('COM_CSDISKUSAGE_ITEMS'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->diskData['children'] as $child): ?>
                    <?php
                    $percentage = $maxSize > 0 ? ($child['size'] / $maxSize) * 100 : 0;
                    $barColor = getBarColor($percentage);
                    $encoded = base64_encode($child['path']);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_csdiskusage&path=' . $encoded); ?>" class="folder-link">
                                <span class="icon-folder" aria-hidden="true"></span>
                                <?php echo htmlspecialchars($child['name']); ?>
                            </a>
                        </td>
                        <td>
                            <div class="usage-bar-container">
                                <div class="usage-bar" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $barColor; ?>;"></div>
                                <span class="usage-percentage"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="size-value"><?php echo formatBytes($child['size']); ?></span>
                        </td>
                        <td class="text-end">
                            <span class="items-count" title="<?php echo Text::sprintf('COM_CSDISKUSAGE_FILES_FOLDERS', $child['files_count'], $child['folders_count']); ?>">
                                <?php echo number_format($child['files_count'] + $child['folders_count']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
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
                <?php foreach ($this->commonDirectories as $name => $path): ?>
                    <?php $encoded = base64_encode($path); ?>
                    <a href="<?php echo Route::_('index.php?option=com_csdiskusage&path=' . $encoded); ?>" class="quick-access-item">
                        <span class="icon-folder" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
