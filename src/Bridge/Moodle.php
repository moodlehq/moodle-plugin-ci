<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Bridge;

use MoodlePluginCI\Parser\CodeParser;
use MoodlePluginCI\Parser\StatementFilter;
use PhpParser\Node\Scalar\String_;

/**
 * Bridge to Moodle.
 */
class Moodle
{
    /**
     * Absolute path to Moodle directory.
     */
    public string $directory;

    /**
     * Moodle config.
     */
    protected ?object $cfg;

    /**
     * @param string $directory Absolute path to Moodle directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Load Moodle config so we can use Moodle APIs.
     */
    public function requireConfig(): void
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
        $path = $this->directory . '/config.php';

        if (!is_file($path)) {
            throw new \RuntimeException('Failed to find Moodle config file');
        }

        /** @noinspection PhpIncludeInspection */
        require_once $path;

        // Save a local reference to Moodle config.
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
    public function normalizeComponent(string $component): array
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
    public function getComponentInstallDirectory(string $component): string
    {
        list($type, $name) = $this->normalizeComponent($component);

        // Must use reflection to avoid using static cache.
        /* @noinspection PhpUndefinedClassInspection */
        $method = new \ReflectionMethod(\core_component::class, 'fetch_plugintypes');
        $result = $method->invoke(null);

        if (!array_key_exists($type, $result[0])) {
            throw new \InvalidArgumentException(sprintf('The component %s has an unknown plugin type of %s', $component, $type));
        }

        return $result[0][$type] . '/' . $name;
    }

    /**
     * Get the branch number, EG: 38, 39, 310, 311, 400, ...
     *
     * @return int
     */
    public function getBranch(): int
    {
        $filter = new StatementFilter();
        $parser = new CodeParser();

        $statements = $parser->parseFile($this->directory . '/version.php');
        $assign     = $filter->findFirstVariableAssignment($statements, 'branch', 'Failed to find $branch in Moodle version.php');

        if ($assign->expr instanceof String_) {
            return (int) $assign->expr->value;
        }

        throw new \RuntimeException('Failed to find Moodle branch version');
    }

    /**
     * Get a Moodle config value.
     *
     * @param string $name the config name
     *
     * @return string
     */
    public function getConfig(string $name): string
    {
        $this->requireConfig();

        if (null === $this->cfg || !property_exists($this->cfg, $name)) {
            throw new \RuntimeException(sprintf('Failed to find $CFG->%s in Moodle config file', $name));
        }

        return $this->cfg->$name;
    }
}
