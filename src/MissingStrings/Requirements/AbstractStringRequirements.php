<?php

declare(strict_types=1);

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\MissingStrings\Requirements;

use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Abstract base class for plugin string requirements.
 */
abstract class AbstractStringRequirements
{
    /**
     * Plugin to be validated.
     */
    protected $plugin;

    /**
     * The major Moodle version, EG: 29, 30, 31.
     */
    protected int $moodleVersion;

    /**
     * Constructor.
     *
     * @param Plugin $plugin        Details about the plugin
     * @param int    $moodleVersion The major Moodle version, EG: 29, 30, 31
     */
    public function __construct(Plugin $plugin, int $moodleVersion)
    {
        $this->plugin        = $plugin;
        $this->moodleVersion = $moodleVersion;
    }

    /**
     * Get required strings that must exist for this plugin type.
     *
     * @return array Array of string keys that are required
     */
    abstract public function getRequiredStrings(): array;

    /**
     * Get plugin-type specific string patterns.
     * These are strings that follow specific naming conventions for the plugin type.
     *
     * @return array Array of string patterns specific to this plugin type
     */
    public function getPluginTypePatterns(): array
    {
        return [];
    }

    /**
     * Check if a file exists in the plugin directory.
     *
     * @param string $file Relative path to file
     *
     * @return bool True if file exists
     */
    protected function fileExists(string $file): bool
    {
        return file_exists($this->plugin->directory . '/' . $file);
    }

    /**
     * Get the plugin component name.
     *
     * @return string The plugin component
     */
    protected function getComponent(): string
    {
        return $this->plugin->component;
    }

    /**
     * Get the plugin type.
     *
     * @return string The plugin type (e.g., 'local', 'mod', 'block')
     */
    protected function getPluginType(): string
    {
        return $this->plugin->type;
    }

    /**
     * Get the plugin name.
     *
     * @return string The plugin name (e.g., 'wikicreator')
     */
    protected function getPluginName(): string
    {
        return $this->plugin->name;
    }
}
