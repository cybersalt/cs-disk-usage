<?php
/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Cybersalt\Component\CsDiskUsage\Administrator\View\Diskusage;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Disk Usage component
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The disk usage data
     *
     * @var    array
     * @since  1.0.0
     */
    protected $diskData;

    /**
     * The current scan path
     *
     * @var    string
     * @since  1.0.0
     */
    protected $currentPath;

    /**
     * The Joomla root path
     *
     * @var    string
     * @since  1.0.0
     */
    protected $rootPath;

    /**
     * Common directories
     *
     * @var    array
     * @since  1.0.0
     */
    protected $commonDirectories;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function display($tpl = null): void
    {
        /** @var DiskusageModel $model */
        $model = $this->getModel();
        $app = Factory::getApplication();

        // Get the requested path from input
        $input = $app->getInput();
        $requestedPath = $input->getString('path', '');

        // Get root and common directories
        $this->rootPath = $model->getJoomlaRoot();
        $this->commonDirectories = $model->getCommonDirectories();

        // Determine the path to scan
        if (empty($requestedPath)) {
            $this->currentPath = $this->rootPath;
        } else {
            // Decode and sanitize the path
            $requestedPath = base64_decode($requestedPath);

            // Security: Ensure the path is within Joomla root
            $realPath = realpath($requestedPath);
            $realRoot = realpath($this->rootPath);

            if ($realPath !== false && strpos($realPath, $realRoot) === 0) {
                $this->currentPath = $realPath;
            } else {
                $this->currentPath = $this->rootPath;
                $app->enqueueMessage(Text::_('COM_CSDISKUSAGE_ERROR_INVALID_PATH'), 'warning');
            }
        }

        // Get disk usage data (limit depth to 1 for initial view)
        $this->diskData = $model->getDiskUsage($this->currentPath, 1);

        // Add the toolbar
        $this->addToolbar();

        // Display the view
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_CSDISKUSAGE'), 'folder');
        ToolbarHelper::preferences('com_csdiskusage');
    }
}
