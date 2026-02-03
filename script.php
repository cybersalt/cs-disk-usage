<?php
/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_CsdiskusageInstallerScript
{
    /**
     * Clear the autoload cache to ensure namespace is registered correctly
     *
     * @return void
     */
    protected function clearAutoloadCache(): void
    {
        $cacheFile = JPATH_ADMINISTRATOR . '/cache/autoload_psr4.php';

        if (file_exists($cacheFile)) {
            try {
                @unlink($cacheFile);
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    'Please manually delete administrator/cache/autoload_psr4.php',
                    'warning'
                );
            }
        }
    }

    /**
     * Method to run after installation
     *
     * @param   string            $type    Type of change (install, update, discover_install)
     * @param   InstallerAdapter  $parent  The parent object
     *
     * @return  boolean
     */
    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        $this->clearAutoloadCache();
        return true;
    }

    /**
     * Method to run on uninstallation
     *
     * @param   InstallerAdapter  $parent  The parent object
     *
     * @return  boolean
     */
    public function uninstall(InstallerAdapter $parent): bool
    {
        $this->clearAutoloadCache();
        return true;
    }
}
