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

namespace MoodlePluginCI\MissingStrings\Discovery;

use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Discovers subplugins from a main plugin.
 *
 * This class handles reading subplugin definitions from db/subplugins.json
 * and db/subplugins.php files, then scans the filesystem to find actual
 * subplugin instances.
 */
class SubpluginDiscovery
{
    /**
     * Discover all subplugins for a given main plugin.
     *
     * @param Plugin $mainPlugin The main plugin to discover subplugins for
     *
     * @return Plugin[] Array of Plugin objects representing discovered subplugins
     */
    public function discoverSubplugins(Plugin $mainPlugin): array
    {
        $subplugins     = [];
        $subpluginPaths = $this->getSubpluginPaths($mainPlugin);

        foreach ($subpluginPaths as $subpluginType => $basePath) {
            $typeSubplugins = $this->discoverSubpluginsOfType($mainPlugin, $subpluginType, $basePath);
            $subplugins     = array_merge($subplugins, $typeSubplugins);
        }

        return $subplugins;
    }

    /**
     * Get subplugin type definitions and their base paths.
     *
     * @param Plugin $mainPlugin The main plugin
     *
     * @return array Array mapping subplugin type to base path
     */
    public function getSubpluginPaths(Plugin $mainPlugin): array
    {
        $paths = [];

        // Try JSON format first (preferred)
        $jsonPaths = $this->readSubpluginsJson($mainPlugin);
        if (!empty($jsonPaths)) {
            $paths = array_merge($paths, $jsonPaths);
        }

        // Try PHP format as fallback
        $phpPaths = $this->readSubpluginsPhp($mainPlugin);
        if (!empty($phpPaths)) {
            $paths = array_merge($paths, $phpPaths);
        }

        return $paths;
    }

    /**
     * Discover subplugins of a specific type in a base directory.
     *
     * @param Plugin $mainPlugin    The main plugin
     * @param string $subpluginType The subplugin type (e.g., 'assessfreqreport')
     * @param string $basePath      The base path to scan for subplugins (relative to Moodle root)
     *
     * @return Plugin[] Array of discovered subplugins
     */
    private function discoverSubpluginsOfType(Plugin $mainPlugin, string $subpluginType, string $basePath): array
    {
        $subplugins = [];

        // Paths in subplugins.json are relative to Moodle root, not plugin directory
        $moodleRoot   = $this->getMoodleRoot($mainPlugin);
        $fullBasePath = $moodleRoot . '/' . $basePath;

        if (!is_dir($fullBasePath)) {
            return $subplugins;
        }

        $subpluginDirs = $this->scanSubpluginDirectories($fullBasePath);

        foreach ($subpluginDirs as $subpluginName) {
            $subpluginPath = $fullBasePath . '/' . $subpluginName;

            // Check if this looks like a valid Moodle plugin
            if ($this->isValidSubplugin($subpluginPath)) {
                $component    = $subpluginType . '_' . $subpluginName;
                $subplugins[] = new Plugin($component, $subpluginType, $subpluginName, $subpluginPath);
            }
        }

        return $subplugins;
    }

    /**
     * Read subplugin definitions from db/subplugins.json.
     *
     * @param Plugin $mainPlugin The main plugin
     *
     * @return array Array mapping subplugin type to base path
     */
    private function readSubpluginsJson(Plugin $mainPlugin): array
    {
        $jsonFile = $mainPlugin->directory . '/db/subplugins.json';

        if (!file_exists($jsonFile)) {
            return [];
        }

        $content = file_get_contents($jsonFile);
        if (false === $content) {
            return [];
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }

        // Support both 'plugintypes' (preferred) and 'subplugintypes' (legacy)
        $pluginTypes = $data['plugintypes'] ?? $data['subplugintypes'] ?? [];

        if (!is_array($pluginTypes)) {
            return [];
        }

        return $pluginTypes;
    }

    /**
     * Read subplugin definitions from db/subplugins.php.
     *
     * @param Plugin $mainPlugin The main plugin
     *
     * @return array Array mapping subplugin type to base path
     *
     * @psalm-suppress UnresolvableInclude
     */
    private function readSubpluginsPhp(Plugin $mainPlugin): array
    {
        $phpFile = $mainPlugin->directory . '/db/subplugins.php';

        if (!file_exists($phpFile)) {
            return [];
        }

        // Safely include the PHP file and extract subplugin types
        $subplugins = [];

        // Create isolated scope to prevent variable pollution
        $extractSubplugins = function () use ($phpFile): array {
            $subplugins = [];
            // @psalm-suppress UnresolvableInclude
            include $phpFile;

            return $subplugins;
        };

        try {
            return $extractSubplugins();
        } catch (\Throwable $e) {
            // If there's any error reading the PHP file, return empty array
            return [];
        }
    }

    /**
     * Scan a directory for potential subplugin directories.
     *
     * @param string $basePath The base path to scan
     *
     * @return string[] Array of directory names that could be subplugins
     */
    private function scanSubpluginDirectories(string $basePath): array
    {
        $directories = [];

        if (!is_readable($basePath)) {
            return $directories;
        }

        $iterator = new \DirectoryIterator($basePath);

        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) {
                continue;
            }

            $dirName = $item->getFilename();

            // Skip hidden directories and common non-plugin directories
            if (0 === strpos($dirName, '.') || in_array($dirName, ['tests', 'backup', 'tmp'], true)) {
                continue;
            }

            $directories[] = $dirName;
        }

        sort($directories);

        return $directories;
    }

    /**
     * Check if a directory contains a valid Moodle subplugin.
     *
     * @param string $subpluginPath Path to the potential subplugin directory
     *
     * @return bool True if this looks like a valid subplugin
     */
    private function isValidSubplugin(string $subpluginPath): bool
    {
        // A valid subplugin should have at least one of these files
        $requiredFiles = [
            'version.php',      // Standard version file
            'lang/en/*.php',    // Language files
            'lib.php',          // Library file
        ];

        foreach ($requiredFiles as $pattern) {
            if (false !== strpos($pattern, '*')) {
                // Handle glob patterns
                $files = glob($subpluginPath . '/' . $pattern);
                if (!empty($files)) {
                    return true;
                }
            } else {
                // Handle exact file matches
                if (file_exists($subpluginPath . '/' . $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the Moodle root directory from a plugin directory.
     *
     * @param Plugin $plugin The main plugin
     *
     * @return string The Moodle root directory path
     */
    private function getMoodleRoot(Plugin $plugin): string
    {
        // Plugin directory structure: {moodle_root}/{plugin_type}/{plugin_name}
        // For local plugins: {moodle_root}/local/{plugin_name}
        // For mod plugins: {moodle_root}/mod/{plugin_name}
        // etc.

        $pluginDir  = $plugin->directory;
        $pluginType = $plugin->type;

        // Calculate how many levels up we need to go to reach Moodle root
        if ('mod' === $pluginType || 'local' === $pluginType || 'block' === $pluginType
            || 'theme' === $pluginType || 'filter' === $pluginType || 'format' === $pluginType
            || 'repository' === $pluginType || 'portfolio' === $pluginType || 'qtype' === $pluginType
            || 'qformat' === $pluginType || 'auth' === $pluginType || 'enrol' === $pluginType
            || 'message' === $pluginType || 'dataformat' === $pluginType || 'webservice' === $pluginType
            || 'cachestore' === $pluginType || 'cachelock' === $pluginType || 'fileconverter' === $pluginType) {
            // These are direct subdirectories of Moodle root
            return dirname($pluginDir, 2);
        }

        if (0 === strpos($pluginType, 'tool_')) {
            // Admin tools: {moodle_root}/admin/tool/{plugin_name}
            return dirname($pluginDir, 3);
        }

        if (0 === strpos($pluginType, 'report_')) {
            // Reports: {moodle_root}/admin/report/{plugin_name} or {moodle_root}/course/report/{plugin_name}
            $parentDir = dirname($pluginDir);
            if ('report' === basename($parentDir)) {
                return dirname($pluginDir, 3);
            }
        }

        if (0 === strpos($pluginType, 'gradereport_')) {
            // Grade reports: {moodle_root}/grade/report/{plugin_name}
            return dirname($pluginDir, 3);
        }

        if (0 === strpos($pluginType, 'gradeimport_') || 0 === strpos($pluginType, 'gradeexport_')) {
            // Grade import/export: {moodle_root}/grade/import/{plugin_name} or {moodle_root}/grade/export/{plugin_name}
            return dirname($pluginDir, 3);
        }

        // For subplugin types (like assessfreqreport), the plugin might be in a custom location
        // Try to find Moodle root by looking for config.php or version.php
        $currentDir = $pluginDir;
        for ($i = 0; $i < 10; ++$i) { // Prevent infinite loop
            $parentDir = dirname($currentDir);
            if ($parentDir === $currentDir) {
                break; // Reached filesystem root
            }

            // Check if this looks like Moodle root
            if (file_exists($parentDir . '/config.php') && file_exists($parentDir . '/version.php')) {
                return $parentDir;
            }

            $currentDir = $parentDir;
        }

        // Fallback: assume plugin is 2 levels deep (most common case)
        return dirname($pluginDir, 2);
    }
}
