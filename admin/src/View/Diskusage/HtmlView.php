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
use Cybersalt\Component\CsDiskUsage\Administrator\Helper\SupportHelper;

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

        // Defence in depth: the dispatcher already enforces core.manage, but this
        // screen lists the server's folder structure, so check it here as well.
        if (!$app->getIdentity()->authorise('core.manage', 'com_csdiskusage')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Get the requested path from input
        $input = $app->getInput();
        $requestedPath = $input->getString('path', '');

        // Get root and common directories
        $this->rootPath = realpath($model->getJoomlaRoot()) ?: $model->getJoomlaRoot();
        $this->commonDirectories = $model->getCommonDirectories();

        // Determine the path to scan
        $this->currentPath = $this->rootPath;

        if ($requestedPath !== '') {
            // Security: decode, resolve (realpath) and make sure the folder is the
            // Joomla root or a real sub-folder of it (separator-aware check).
            $resolved = $model->resolveRequestedPath($requestedPath);

            if ($resolved !== null) {
                $this->currentPath = $resolved;
            } else {
                $app->enqueueMessage(
                    Text::_('COM_CSDISKUSAGE_ERROR_INVALID_PATH') . ' ' . SupportHelper::getContactSentence(),
                    'warning'
                );
            }
        }

        if (!is_readable($this->currentPath)) {
            $app->enqueueMessage(
                Text::sprintf('COM_CSDISKUSAGE_ERROR_UNREADABLE', htmlspecialchars(basename($this->currentPath), ENT_QUOTES, 'UTF-8'))
                . ' ' . SupportHelper::getContactSentence(),
                'error'
            );
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
        ToolbarHelper::title(Text::_('COM_CSDISKUSAGE_TITLE_DASHBOARD'), 'folder');
        ToolbarHelper::preferences('com_csdiskusage');
    }
}
