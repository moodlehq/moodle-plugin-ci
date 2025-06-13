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
 * Extracts string usage from Mustache template files.
 */
class MustacheStringExtractor implements StringExtractorInterface
{
    /**
     * Extract string usage from Mustache content.
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
            // Extract strings from Mustache helpers
            $helperStrings = $this->extractMustacheHelpers($line, $component, $filePath, $lineNumber + 1);
            $strings       = array_merge_recursive($strings, $helperStrings);

            // Extract strings from JavaScript blocks
            $jsStrings = $this->extractJavaScriptBlocks($line, $component, $filePath, $lineNumber + 1);
            $strings   = array_merge_recursive($strings, $jsStrings);
        }

        // Also extract from multi-line JavaScript blocks
        $multiLineJsStrings = $this->extractMultiLineJavaScript($content, $component, $filePath);
        $strings            = array_merge_recursive($strings, $multiLineJsStrings);

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
        return 'mustache' === pathinfo($filePath, PATHINFO_EXTENSION);
    }

    /**
     * Get the name of this extractor.
     *
     * @return string Extractor name
     */
    public function getName(): string
    {
        return 'Mustache String Extractor';
    }

    /**
     * Extract strings from Mustache helpers like {{#str}} and {{#cleanstr}}.
     *
     * @param string $line       Line content
     * @param string $component  Plugin component to filter by
     * @param string $filePath   File path for context
     * @param int    $lineNumber Line number
     *
     * @return array Array of strings found
     */
    private function extractMustacheHelpers(string $line, string $component, string $filePath, int $lineNumber): array
    {
        $strings = [];

        // Combined pattern that handles both 2 and 3 parameter cases
        // For the 3rd parameter, we need to match everything until we find the closing {{/str}} or {{/cleanstr}}
        // This uses a more sophisticated approach to handle nested {{}} in the 3rd parameter
        // Component pattern [^,\s{}]+ ensures we don't match past }} characters
        $pattern = '/\{\{\#(str|cleanstr)\}\}\s*([^,\s]+)\s*,\s*([^,\s{}]+)(?:\s*,\s*((?:(?!\{\{\/\1\}\}).)*))?\s*\{\{\/\1\}\}/';

        if (preg_match_all($pattern, $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = trim($match[2]);
                $stringComponent = trim($match[3]);

                // Skip dynamic strings (containing variables or expressions)
                if ($this->isDynamicString($stringKey)) {
                    continue;
                }

                if ($stringComponent === $component) {
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
     * Extract strings from JavaScript blocks within single lines.
     *
     * @param string $line       Line content
     * @param string $component  Plugin component to filter by
     * @param string $filePath   File path for context
     * @param int    $lineNumber Line number
     *
     * @return array Array of strings found
     */
    private function extractJavaScriptBlocks(string $line, string $component, string $filePath, int $lineNumber): array
    {
        $strings = [];

        // Check if line contains JavaScript string calls
        if (false !== strpos($line, 'str.get_string')) {
            // Pattern: str.get_string('stringkey', 'component', ...) - handles optional parameters
            if (preg_match_all('/str\.get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $stringKey       = $match[1];
                    $stringComponent = $match[2];

                    // Skip dynamic strings (containing variables or expressions)
                    if ($this->isDynamicString($stringKey)) {
                        continue;
                    }

                    if ($stringComponent === $component) {
                        $strings[$stringKey][] = [
                            'file'    => $this->getRelativeFilePath($filePath),
                            'line'    => $lineNumber,
                            'context' => trim($line),
                        ];
                    }
                }
            }
        }

        return $strings;
    }

    /**
     * Extract strings from multi-line JavaScript blocks {{#js}}...{{/js}}.
     *
     * @param string $content   Full file content
     * @param string $component Plugin component to filter by
     * @param string $filePath  File path for context
     *
     * @return array Array of strings found
     */
    private function extractMultiLineJavaScript(string $content, string $component, string $filePath): array
    {
        $strings = [];

        // Extract JavaScript blocks
        if (preg_match_all('/\{\{\#js\}\}(.*?)\{\{\/js\}\}/s', $content, $jsBlocks, PREG_SET_ORDER)) {
            foreach ($jsBlocks as $block) {
                $jsCode    = $block[1];
                $jsStrings = $this->extractJavaScriptStrings($jsCode, $component, $filePath);
                $strings   = array_merge_recursive($strings, $jsStrings);
            }
        }

        return $strings;
    }

    /**
     * Extract strings from JavaScript code.
     *
     * @param string $jsCode    JavaScript code
     * @param string $component Plugin component to filter by
     * @param string $filePath  File path for context
     *
     * @return array Array of strings found
     */
    private function extractJavaScriptStrings(string $jsCode, string $component, string $filePath): array
    {
        $strings = [];
        $lines   = explode("\n", $jsCode);

        foreach ($lines as $lineNumber => $line) {
            // Pattern 1: str.get_string('stringkey', 'component', ...) - handles optional parameters
            if (preg_match_all('/str\.get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $stringKey       = $match[1];
                    $stringComponent = $match[2];

                    // Skip dynamic strings (containing variables or expressions)
                    if ($this->isDynamicString($stringKey)) {
                        continue;
                    }

                    if ($stringComponent === $component) {
                        $strings[$stringKey][] = [
                            'file'    => $this->getRelativeFilePath($filePath),
                            'line'    => $lineNumber + 1, // Approximate line number
                            'context' => 'JavaScript block: ' . trim($line),
                        ];
                    }
                }
            }

            // Pattern 2: str.get_strings([{key: 'stringkey', component: 'component'}])
            if (preg_match_all('/\{\s*key\s*:\s*[\'"]([^\'\"]+)[\'"]\s*,\s*component\s*:\s*[\'"]([^\'\"]+)[\'"]\s*\}/', $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $stringKey       = $match[1];
                    $stringComponent = $match[2];

                    // Skip dynamic strings (containing variables or expressions)
                    if ($this->isDynamicString($stringKey)) {
                        continue;
                    }

                    if ($stringComponent === $component) {
                        $strings[$stringKey][] = [
                            'file'    => $this->getRelativeFilePath($filePath),
                            'line'    => $lineNumber + 1,
                            'context' => 'JavaScript block: ' . trim($line),
                        ];
                    }
                }
            }
        }

        return $strings;
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
