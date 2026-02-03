<?php
/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Cybersalt\Component\CsDiskUsage\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Disk Usage Model
 *
 * @since  1.0.0
 */
class DiskusageModel extends BaseDatabaseModel
{
    /**
     * Get disk usage data for a given path
     *
     * @param   string  $path        The path to scan
     * @param   int     $maxDepth    Maximum depth to scan (0 = unlimited)
     * @param   int     $currentDepth  Current depth level
     *
     * @return  array  Array of folder data with sizes
     *
     * @since   1.0.0
     */
    public function getDiskUsage(string $path, int $maxDepth = 0, int $currentDepth = 0): array
    {
        $result = [
            'path' => $path,
            'name' => basename($path),
            'size' => 0,
            'files_count' => 0,
            'folders_count' => 0,
            'children' => [],
            'depth' => $currentDepth,
        ];

        if (!is_dir($path) || !is_readable($path)) {
            return $result;
        }

        try {
            $iterator = new \DirectoryIterator($path);
        } catch (\Exception $e) {
            return $result;
        }

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = $item->getPathname();

            if ($item->isFile()) {
                try {
                    $result['size'] += $item->getSize();
                    $result['files_count']++;
                } catch (\Exception $e) {
                    // Skip files we can't read
                }
            } elseif ($item->isDir()) {
                $result['folders_count']++;

                // Recursively get child folder data
                if ($maxDepth === 0 || $currentDepth < $maxDepth) {
                    $childData = $this->getDiskUsage($itemPath, $maxDepth, $currentDepth + 1);
                    $result['children'][] = $childData;
                    $result['size'] += $childData['size'];
                    $result['files_count'] += $childData['files_count'];
                    $result['folders_count'] += $childData['folders_count'];
                } else {
                    // Just get the size without children details
                    $folderSize = $this->getFolderSize($itemPath);
                    $result['size'] += $folderSize;
                }
            }
        }

        // Sort children by size (largest first)
        usort($result['children'], function ($a, $b) {
            return $b['size'] <=> $a['size'];
        });

        return $result;
    }

    /**
     * Get folder size without detailed breakdown
     *
     * @param   string  $path  The path to scan
     *
     * @return  int  Total size in bytes
     *
     * @since   1.0.0
     */
    public function getFolderSize(string $path): int
    {
        $size = 0;

        if (!is_dir($path) || !is_readable($path)) {
            return $size;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                try {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                } catch (\Exception $e) {
                    // Skip files we can't read
                }
            }
        } catch (\Exception $e) {
            // Return what we have
        }

        return $size;
    }

    /**
     * Get the Joomla root path
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function getJoomlaRoot(): string
    {
        return JPATH_ROOT;
    }

    /**
     * Get commonly used Joomla directories
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function getCommonDirectories(): array
    {
        return [
            'administrator' => JPATH_ADMINISTRATOR,
            'cache' => JPATH_CACHE,
            'components' => JPATH_ROOT . '/components',
            'images' => JPATH_ROOT . '/images',
            'media' => JPATH_ROOT . '/media',
            'modules' => JPATH_ROOT . '/modules',
            'plugins' => JPATH_PLUGINS,
            'templates' => JPATH_ROOT . '/templates',
            'tmp' => JPATH_ROOT . '/tmp',
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param   int  $bytes  The size in bytes
     * @param   int  $precision  Decimal precision
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
