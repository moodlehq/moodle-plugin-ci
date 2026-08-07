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

namespace MoodlePluginCI\MissingStrings\Extractor;

/**
 * Extracts string usage from PHP files.
 */
class PhpStringExtractor implements StringExtractorInterface
{
    /**
     * Extract string usage from PHP content.
     *
     * @param string $content   File content to analyze
     * @param string $component Plugin component to filter by
     * @param string $filePath  File path for context information
     *
     * @return array Array of string usage
     */
    public function extract(string $content, string $component, string $filePath): array
    {
        $strings = [];
        $lines   = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            $lineStrings = $this->extractFromLine($line, $component, $filePath, $lineNumber + 1);
            $strings     = array_merge_recursive($strings, $lineStrings);
        }

        return $strings;
    }

    /**
     * Check if this extractor can handle the given file.
     *
     * @param string $filePath Path to the file
     *
     * @return bool True if this extractor can handle the file
     */
    public function canHandle(string $filePath): bool
    {
        return 'php' === strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * Get the name of this extractor.
     *
     * @return string Extractor name
     */
    public function getName(): string
    {
        return 'PHP String Extractor';
    }

    /**
     * Extract strings from a single line.
     *
     * @param string $line       Line content
     * @param string $component  Plugin component to filter by
     * @param string $filePath   File path for context
     * @param int    $lineNumber Line number
     *
     * @return array Array of strings found in this line
     */
    private function extractFromLine(string $line, string $component, string $filePath, int $lineNumber): array
    {
        $strings = [];

        // Pattern 1: get_string('stringkey', 'component', ...) - handles optional parameters
        if (preg_match_all('/get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                // Skip dynamic strings (containing variables or expressions)
                if ($this->isDynamicString($stringKey)) {
                    continue;
                }

                if ($this->isValidComponent($stringComponent, $component)) {
                    $strings[$stringKey][] = [
                        'file'    => $this->getRelativeFilePath($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 2: new lang_string('stringkey', 'component', ...) - handles optional parameters
        if (preg_match_all('/new\s+lang_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                // Skip dynamic strings (containing variables or expressions)
                if ($this->isDynamicString($stringKey)) {
                    continue;
                }

                if ($this->isValidComponent($stringComponent, $component)) {
                    $strings[$stringKey][] = [
                        'file'    => $this->getRelativeFilePath($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 3: addHelpButton('stringkey', 'component', ...) - handles optional parameters
        if (preg_match_all('/addHelpButton\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                // Skip dynamic strings (containing variables or expressions)
                if ($this->isDynamicString($stringKey)) {
                    continue;
                }

                if ($this->isValidComponent($stringComponent, $component)) {
                    $strings[$stringKey][] = [
                        'file'    => $this->getRelativeFilePath($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 4: String manager calls - get_string_manager()->get_string(...) - handles optional parameters
        if (preg_match_all('/get_string_manager\s*\(\s*\)\s*->\s*get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                // Skip dynamic strings (containing variables or expressions)
                if ($this->isDynamicString($stringKey)) {
                    continue;
                }

                if ($this->isValidComponent($stringComponent, $component)) {
                    $strings[$stringKey][] = [
                        'file'    => $this->getRelativeFilePath($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        return $strings;
    }

    /**
     * Check if a string component is valid for the given plugin component.
     *
     * For mod_* plugins, both 'mod_pluginname' and 'pluginname' are valid.
     * For other plugins, exact match is required.
     *
     * @param string $stringComponent Component from the string call
     * @param string $pluginComponent Plugin component being validated
     *
     * @return bool True if the component is valid for this plugin
     */
    private function isValidComponent(string $stringComponent, string $pluginComponent): bool
    {
        // Exact match
        if ($stringComponent === $pluginComponent) {
            return true;
        }

        // For mod_* plugins, also accept the short form (e.g., 'quiz' for 'mod_quiz')
        if (0 === strpos($pluginComponent, 'mod_')) {
            $shortComponent = substr($pluginComponent, 4); // Remove 'mod_' prefix
            if ($stringComponent === $shortComponent) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string key contains dynamic content that should be ignored.
     *
     * @param string $stringKey The string key to check
     *
     * @return bool True if the string is dynamic and should be ignored
     */
    private function isDynamicString(string $stringKey): bool
    {
        // Check for PHP variables ($variable)
        if (false !== strpos($stringKey, '$')) {
            return true;
        }

        // Check for template variables ({$variable} or {variable})
        if (preg_match('/\{[^}]*\$[^}]*\}/', $stringKey)) {
            return true;
        }

        // Check for mustache-style variables ({variable})
        if (preg_match('/\{[^}]+\}/', $stringKey)) {
            return true;
        }

        // Check for concatenation operators (.something)
        if (preg_match('/\.\s*\$/', $stringKey)) {
            return true;
        }

        return false;
    }

    /**
     * Get a relative file path that's more informative than just the basename.
     *
     * @param string $filePath The full file path
     *
     * @return string A relative path or enhanced basename
     */
    private function getRelativeFilePath(string $filePath): string
    {
        // Try to make the path relative to common Moodle directories
        $moodlePatterns = [
            '/.*\/moodle\/(.*?)$/'         => '$1',              // Remove everything before /moodle/
            '/.*\/moodle\.local\/(.*?)$/'  => '$1',       // Remove everything before /moodle.local/
            '/.*\/var\/www\/html\/(.*?)$/' => '$1',      // Remove everything before /var/www/html/
        ];

        foreach ($moodlePatterns as $pattern => $replacement) {
            if (preg_match($pattern, $filePath, $matches)) {
                return $matches[1];
            }
        }

        // If no pattern matches, include the last 2-3 directory levels for context
        $parts = explode('/', $filePath);
        $count = count($parts);

        if ($count >= 3) {
            // Show last 3 parts: {parent_dir}/{dir}/{file.ext}
            return implode('/', array_slice($parts, -3));
        } elseif ($count >= 2) {
            // Show last 2 parts: {dir}/{file.ext}
            return implode('/', array_slice($parts, -2));
        }

        // Fallback to just the filename
        return basename($filePath);
    }
}
