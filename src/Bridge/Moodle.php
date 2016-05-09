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

use Moodlerooms\MoodlePluginCI\Parser\CodeParser;
use Moodlerooms\MoodlePluginCI\Parser\StatementFilter;
use PhpParser\Node\Scalar\String_;

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
        if (!defined('CACHE_DISABLE_ALL')) {
            define('CACHE_DISABLE_ALL', true);
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
     * Normalize the component into the type and plugin name.
     *
     * @param string $component
     *
     * @return array
     */
    public function normalizeComponent($component)
    {
        $this->requireConfig();

        /* @noinspection PhpUndefinedClassInspection */
        return \core_component::normalize_component($component);
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
        list($type, $name) = $this->normalizeComponent($component);

        // Must use reflection to avoid using static cache.
        $method = new \ReflectionMethod('core_component', 'fetch_plugintypes');
        $method->setAccessible(true);
        $result = $method->invoke(null);

        if (!array_key_exists($type, $result[0])) {
            throw new \InvalidArgumentException(sprintf('The component %s has an unknown plugin type of %s', $component, $type));
        }

        return $result[0][$type].'/'.$name;
    }

    /**
     * Get the branch number, EG: 29, 30, etc.
     *
     * @return int
     */
    public function getBranch()
    {
        $filter = new StatementFilter();
        $parser = new CodeParser();

        $statements = $parser->parseFile($this->directory.'/version.php');
        $assign     = $filter->findFirstVariableAssignment($statements, 'branch', 'Failed to find $branch in Moodle version.php');

        if ($assign->expr instanceof String_) {
            return (int) $assign->expr->value;
        }

        throw new \RuntimeException('Failed to find Moodle branch version');
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
