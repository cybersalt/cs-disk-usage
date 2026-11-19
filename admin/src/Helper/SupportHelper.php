<?php
/**
 * @package     Cybersalt.CsDiskUsage
 * @subpackage  com_csdiskusage
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Cybersalt\Component\CsDiskUsage\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

/**
 * Builds the "please contact support" sentence from the Support options.
 *
 * @since  1.0.1
 */
class SupportHelper
{
    /**
     * Default support values (also the defaults in config.xml).
     */
    private const DEFAULT_EMAIL = 'support@cybersalt.com';
    private const DEFAULT_URL   = 'https://www.cybersalt.com/services/support-request-form';
    private const DEFAULT_LABEL = 'Cybersalt support';

    /**
     * Get one support value, falling back to the Cybersalt default when empty.
     *
     * @param   string  $key  email, url or label
     *
     * @return  string
     *
     * @since   1.0.1
     */
    public static function get(string $key): string
    {
        $defaults = [
            'email' => self::DEFAULT_EMAIL,
            'url'   => self::DEFAULT_URL,
            'label' => self::DEFAULT_LABEL,
        ];

        $params = ComponentHelper::getParams('com_csdiskusage');
        $value  = trim((string) $params->get('support_' . $key, ''));

        return $value !== '' ? $value : ($defaults[$key] ?? '');
    }

    /**
     * Get a plain-text "Please contact ... for assistance." sentence.
     *
     * @return  string
     *
     * @since   1.0.1
     */
    public static function getContactSentence(): string
    {
        // The sentence ends up in the system message bar (HTML), so escape the option values.
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $label  = $escape(self::get('label'));
        $parts  = array_filter([$escape(self::get('email')), $escape(self::get('url'))]);

        if (empty($parts)) {
            return Text::sprintf('COM_CSDISKUSAGE_SUPPORT_CONTACT_PLAIN', $label);
        }

        return Text::sprintf('COM_CSDISKUSAGE_SUPPORT_CONTACT', $label, implode(' / ', $parts));
    }
}
