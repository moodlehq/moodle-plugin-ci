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

namespace MoodlePluginCI\MissingStrings;

use MoodlePluginCI\MissingStrings\Cache\FileContentCache;

/**
 * Utility for finding where strings are used or defined in files.
 *
 * Provides methods to locate specific string keys within files
 * and return their line numbers for better error context.
 */
class StringUsageFinder
{
    /**
     * Find the line number where a string key is defined or used in a specific file.
     *
     * @param string $filePath  The file to search in
     * @param string $stringKey The string key to find
     * @param string $pattern   Optional custom regex pattern to use for matching
     *
     * @return int|null Line number if found, null otherwise
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function findLineInFile(string $filePath, string $stringKey, ?string $pattern = null): ?int
    {
        if (!FileContentCache::fileExists($filePath)) {
            return null;
        }

        $lines = FileContentCache::getLines($filePath);
        if (false === $lines) {
            return null;
        }

        // Use custom pattern if provided, otherwise use default
        $searchPattern = $pattern ?: $this->getDefaultPattern($stringKey);

        // Look for the string key in the file
        foreach ($lines as $lineNumber => $line) {
            // @psalm-suppress ArgumentTypeCoercion
            if (preg_match($searchPattern, $line)) {
                return (int) $lineNumber + 1; // Convert to 1-based line numbers
            }
        }

        return null;
    }

    /**
     * Find line number for array key definitions (like in db/access.php).
     *
     * @param string $filePath The file to search in
     * @param string $arrayKey The array key to find
     *
     * @return int|null Line number if found, null otherwise
     */
    public function findArrayKeyLine(string $filePath, string $arrayKey): ?int
    {
        // Use ~ as delimiter to avoid conflicts with / in capability names
        $pattern = '~[\'"](?:' . preg_quote($arrayKey, '~') . ')[\'\"]\s*=>~';

        return $this->findLineInFile($filePath, $arrayKey, $pattern);
    }

    /**
     * Find line number for string literals in code.
     *
     * @param string $filePath  The file to search in
     * @param string $stringKey The string key to find
     *
     * @return int|null Line number if found, null otherwise
     */
    public function findStringLiteralLine(string $filePath, string $stringKey): ?int
    {
        if (!FileContentCache::fileExists($filePath)) {
            return null;
        }

        $lines = FileContentCache::getLines($filePath);
        if (false === $lines) {
            return null;
        }

        // Look for the string key in the file, handling escaped quotes
        foreach ($lines as $lineNumber => $line) {
            // Handle both single and double quoted strings with escaping
            if ($this->containsStringLiteral($line, $stringKey)) {
                return (int) $lineNumber + 1; // Convert to 1-based line numbers
            }
        }

        return null;
    }

    /**
     * Get the default search pattern for a string key.
     *
     * @param string $stringKey The string key
     *
     * @return string Regex pattern to match the string key
     */
    private function getDefaultPattern(string $stringKey): string
    {
        // Default pattern looks for string literals in quotes
        // Use ~ as delimiter to avoid conflicts with / in string keys
        return '~[\'"](?:' . preg_quote($stringKey, '~') . ')[\'"]~';
    }

    /**
     * Check if a line contains a string literal with the given content.
     *
     * @param string $line      The line to check
     * @param string $stringKey The string key to find
     *
     * @return bool True if the string literal is found
     */
    private function containsStringLiteral(string $line, string $stringKey): bool
    {
        // Handle single-quoted strings (with escaped single quotes)
        if (preg_match_all("~'((?:[^'\\\\]|\\\\.)*)'~", $line, $matches)) {
            foreach ($matches[1] as $match) {
                // Unescape the content - handle escaped single quotes
                $unescaped = str_replace("\\'", "'", $match);
                if ($unescaped === $stringKey) {
                    return true;
                }
            }
        }

        // Handle double-quoted strings (with escaped double quotes)
        if (preg_match_all('~"((?:[^"\\\\]|\\\\.)*)"~', $line, $matches)) {
            foreach ($matches[1] as $match) {
                // Unescape the content - handle escaped double quotes
                $unescaped = str_replace('\\"', '"', $match);
                if ($unescaped === $stringKey) {
                    return true;
                }
            }
        }

        return false;
    }
}
