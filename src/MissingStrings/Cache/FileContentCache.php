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

namespace MoodlePluginCI\MissingStrings\Cache;

/**
 * Simple file content cache to avoid repeated file reads during validation.
 *
 * This cache is designed to be lightweight and session-scoped, improving
 * performance when multiple checkers need to read the same files.
 */
class FileContentCache
{
    /**
     * Cache storage for file contents.
     */
    private static array $contentCache = [];

    /**
     * Cache storage for file modification times.
     */
    private static array $mtimeCache = [];

    /**
     * Maximum number of files to cache (memory limit protection).
     */
    private const MAX_CACHED_FILES = 100;

    /**
     * Get file content with caching.
     *
     * @param string $filePath absolute path to the file
     *
     * @return string|false file content or false on failure
     */
    /**
     * @return string|false
     */
    public static function getContent(string $filePath)
    {
        // Normalize path for consistent caching
        $normalizedPath = realpath($filePath);
        if (false === $normalizedPath) {
            return false;
        }

        // Check if file exists and is readable
        if (!is_file($normalizedPath) || !is_readable($normalizedPath)) {
            return false;
        }

        $currentMtime = filemtime($normalizedPath);
        if (false === $currentMtime) {
            return false;
        }

        // Check if we have a cached version that's still valid
        if (
            isset(self::$contentCache[$normalizedPath], self::$mtimeCache[$normalizedPath])
            && self::$mtimeCache[$normalizedPath] === $currentMtime
        ) {
            return self::$contentCache[$normalizedPath];
        }

        // Read file content
        $content = file_get_contents($normalizedPath);
        if (false === $content) {
            return false;
        }

        // Store in cache (with size limit)
        if (count(self::$contentCache) >= self::MAX_CACHED_FILES) {
            // Remove oldest cache entry (simple FIFO)
            $oldestKey = array_key_first(self::$contentCache);
            if (null !== $oldestKey) {
                unset(self::$contentCache[$oldestKey], self::$mtimeCache[$oldestKey]);
            }
        }

        self::$contentCache[$normalizedPath] = $content;
        self::$mtimeCache[$normalizedPath]   = $currentMtime;

        return $content;
    }

    /**
     * Check if a file exists and is readable (with caching).
     *
     * @param string $filePath absolute path to the file
     *
     * @return bool true if file exists and is readable
     */
    public static function fileExists(string $filePath): bool
    {
        $normalizedPath = realpath($filePath);
        if (false === $normalizedPath) {
            return file_exists($filePath) && is_readable($filePath);
        }

        // If we have the file in cache, it exists and is readable
        if (isset(self::$contentCache[$normalizedPath])) {
            return true;
        }

        return is_file($normalizedPath) && is_readable($normalizedPath);
    }

    /**
     * Get file lines as array with caching.
     *
     * @param string $filePath absolute path to the file
     * @param int    $flags    flags for file() function
     *
     * @return array|false array of lines or false on failure
     */
    /**
     * @return array|false
     */
    public static function getLines(string $filePath, int $flags = FILE_IGNORE_NEW_LINES)
    {
        $content = self::getContent($filePath);
        if (false === $content) {
            return false;
        }

        if ($flags & FILE_IGNORE_NEW_LINES) {
            return explode("\n", rtrim($content, "\n"));
        }

        return explode("\n", $content);
    }

    /**
     * Clear the entire cache.
     *
     * Useful for testing or when memory usage needs to be reduced.
     */
    public static function clearCache(): void
    {
        self::$contentCache = [];
        self::$mtimeCache   = [];
    }

    /**
     * Get cache statistics for debugging.
     *
     * @return array cache statistics
     */
    public static function getStats(): array
    {
        return [
            'cached_files' => count(self::$contentCache),
            'max_files'    => self::MAX_CACHED_FILES,
            'memory_usage' => array_sum(array_map('strlen', self::$contentCache)),
        ];
    }
}
