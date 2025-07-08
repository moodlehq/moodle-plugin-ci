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

namespace MoodlePluginCI\MissingStrings\FileDiscovery;

use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Centralized file discovery service for string validation.
 *
 * Performs a single scan of the plugin directory and categorizes files
 * by type for efficient access by all checkers.
 */
class FileDiscovery
{
    /**
     * Discovered files categorized by type.
     *
     * @var array
     */
    private $files = [];

    /**
     * Whether discovery has been performed.
     *
     * @var bool
     */
    private $discovered = false;

    /**
     * The plugin being analyzed.
     */
    private $plugin;

    /**
     * Performance metrics.
     *
     * @var array{discovery_time: float, directories_scanned: int, files_processed: int}
     */
    private $metrics = [
        'discovery_time'      => 0.0,
        'directories_scanned' => 0,
        'files_processed'     => 0,
    ];

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Perform file discovery if not already done.
     */
    private function ensureDiscovered(): void
    {
        if ($this->discovered) {
            return;
        }

        $this->discoverFiles();
        $this->discovered = true;
    }

    /**
     * Discover and categorize all files in the plugin.
     */
    private function discoverFiles(): void
    {
        $startTime = microtime(true);

        $this->files = [
            'php'        => [],
            'mustache'   => [],
            'javascript' => [],
            'database'   => [],
            'classes'    => [],
            'templates'  => [],
            'amd'        => [],
        ];

        $this->scanDirectory($this->plugin->directory);

        $this->metrics['discovery_time'] = microtime(true) - $startTime;
    }

    /**
     * Recursively scan a directory and categorize files.
     *
     * @param string $directory    directory to scan
     * @param string $relativePath relative path from plugin root
     */
    private function scanDirectory(string $directory, string $relativePath = ''): void
    {
        if (!is_dir($directory)) {
            return;
        }

        ++$this->metrics['directories_scanned'];
        $iterator = new \DirectoryIterator($directory);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $itemPath         = $item->getPathname();
            $itemRelativePath = $relativePath ? $relativePath . '/' . $item->getFilename() : $item->getFilename();

            if ($item->isDir()) {
                $this->scanDirectory($itemPath, $itemRelativePath);
            } elseif ($item->isFile()) {
                ++$this->metrics['files_processed'];
                $this->categorizeFile($itemPath, $itemRelativePath);
            }
        }
    }

    /**
     * Categorize a file based on its path and extension.
     *
     * @param string $filePath     full path to the file
     * @param string $relativePath relative path from plugin root
     */
    private function categorizeFile(string $filePath, string $relativePath): void
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $dirname   = dirname($relativePath);

        // All PHP files
        if ('php' === $extension) {
            $this->files['php'][] = $filePath;

            // Database files
            if (str_starts_with($relativePath, 'db/')) {
                $this->files['database'][] = $filePath;
            }

            // Class files
            if (str_starts_with($relativePath, 'classes/')) {
                $this->files['classes'][] = $filePath;
            }
        }

        // Mustache templates
        if ('mustache' === $extension) {
            $this->files['mustache'][] = $filePath;

            if (str_starts_with($relativePath, 'templates/')) {
                $this->files['templates'][] = $filePath;
            }
        }

        // JavaScript files
        if ('js' === $extension) {
            $this->files['javascript'][] = $filePath;

            if (str_starts_with($relativePath, 'amd/src/')) {
                $this->files['amd'][] = $filePath;
            }
        }
    }

    /**
     * Get all PHP files in the plugin.
     *
     * @return array array of file paths
     */
    public function getPhpFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['php'];
    }

    /**
     * Get all Mustache template files.
     *
     * @return array array of file paths
     */
    public function getMustacheFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['mustache'];
    }

    /**
     * Get all JavaScript files.
     *
     * @return array array of file paths
     */
    public function getJavaScriptFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['javascript'];
    }

    /**
     * Get all database definition files (db/*.php).
     *
     * @return array array of file paths
     */
    public function getDatabaseFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['database'];
    }

    /**
     * Get all class files (classes/*.php).
     *
     * @return array array of file paths
     */
    public function getClassFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['classes'];
    }

    /**
     * Get template files (templates/*.mustache).
     *
     * @return array array of file paths
     */
    public function getTemplateFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['templates'];
    }

    /**
     * Get AMD JavaScript files (amd/src/*.js).
     *
     * @return array array of file paths
     */
    public function getAmdFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files['amd'];
    }

    /**
     * Get a specific database file if it exists.
     *
     * @param string $filename Database filename (e.g., 'access.php').
     *
     * @return string|null full path to the file or null if not found
     */
    public function getDatabaseFile(string $filename): ?string
    {
        $targetPath = $this->plugin->directory . '/db/' . $filename;

        foreach ($this->getDatabaseFiles() as $file) {
            if ($file === $targetPath) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Check if a specific database file exists.
     *
     * @param string $filename Database filename (e.g., 'access.php').
     *
     * @return bool true if the file exists
     */
    public function hasDatabaseFile(string $filename): bool
    {
        return null !== $this->getDatabaseFile($filename);
    }

    /**
     * Get class files in a specific subdirectory.
     *
     * @param string $subdirectory Subdirectory within classes/ (e.g., 'privacy').
     *
     * @return array array of file paths
     */
    public function getClassFilesInSubdirectory(string $subdirectory): array
    {
        $targetPrefix = $this->plugin->directory . '/classes/' . trim($subdirectory, '/') . '/';
        $files        = [];

        foreach ($this->getClassFiles() as $file) {
            if (str_starts_with($file, $targetPrefix)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Get all discovered files with their categories.
     *
     * @return array array of category => files pairs
     */
    public function getAllFiles(): array
    {
        $this->ensureDiscovered();

        return $this->files;
    }

    /**
     * Get discovery statistics.
     *
     * @return array statistics about discovered files
     */
    public function getStatistics(): array
    {
        $this->ensureDiscovered();

        return [
            'total_files'      => array_sum(array_map('count', $this->files)),
            'php_files'        => count($this->files['php']),
            'mustache_files'   => count($this->files['mustache']),
            'javascript_files' => count($this->files['javascript']),
            'database_files'   => count($this->files['database']),
            'class_files'      => count($this->files['classes']),
            'template_files'   => count($this->files['templates']),
            'amd_files'        => count($this->files['amd']),
        ];
    }

    /**
     * Get performance metrics.
     *
     * @return array performance metrics including timing and file counts
     */
    public function getPerformanceMetrics(): array
    {
        $this->ensureDiscovered();

        return array_merge($this->metrics, [
            'file_types' => $this->getStatistics(),
        ]);
    }
}
