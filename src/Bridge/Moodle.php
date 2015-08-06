<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Bridge;

/**
 * Bridge to Moodle.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Moodle
{
    /**
     * Absolute path to Moodle directory.
     *
     * @var string
     */
    public $directory;

    /**
     * Moodle's config.
     *
     * @var object
     */
    protected $cfg;

    /**
     * @param string $directory Absolute path to Moodle directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Load's Moodle config so we can use Moodle APIs.
     */
    public function requireConfig()
    {
        global $CFG;

        if (!defined('CLI_SCRIPT')) {
            define('CLI_SCRIPT', true);
        }
        if (!defined('IGNORE_COMPONENT_CACHE')) {
            define('IGNORE_COMPONENT_CACHE', true);
        }
        if (!defined('ABORT_AFTER_CONFIG')) {
            // Need this since Moodle will not be fully installed.
            define('ABORT_AFTER_CONFIG', true);
        }
        $path = $this->directory.'/config.php';

        if (!is_file($path)) {
            throw new \RuntimeException('Failed to find Moodle config file');
        }

        /** @noinspection PhpIncludeInspection */
        require_once $path;

        // Save a local reference to Moodle's config.
        if (empty($this->cfg)) {
            $this->cfg = $CFG;
        }
    }

    /**
     * Get the absolute install directory path within Moodle.
     *
     * @param string $component Moodle component, EG: mod_forum
     *
     * @return string Absolute path, EG: /path/to/mod/forum
     */
    public function getComponentInstallDirectory($component)
    {
        $this->requireConfig();

        /* @noinspection PhpUndefinedClassInspection */
        list($type, $name) = \core_component::normalize_component($component);
        /* @noinspection PhpUndefinedClassInspection */
        $types = \core_component::get_plugin_types();

        if (!array_key_exists($type, $types)) {
            throw new \InvalidArgumentException(sprintf('The component %s has an unknown plugin type of %s', $component, $type));
        }

        return $types[$type].'/'.$name;
    }

    /**
     * Get the Behat data directory.
     *
     * @return string
     */
    public function getBehatDataDirectory()
    {
        $this->requireConfig();

        if (!property_exists($this->cfg, 'behat_dataroot')) {
            throw new \RuntimeException('Failed to find $CFG->behat_dataroot in Moodle config file');
        }

        return $this->cfg->behat_dataroot;
    }
}
