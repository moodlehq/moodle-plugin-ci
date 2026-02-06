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
 * Extracts string usage from JavaScript files (AMD modules).
 */
class JavaScriptStringExtractor implements StringExtractorInterface
{
    /**
     * Extract string usage from JavaScript content.
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
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Handle .js files, especially in amd/src/ directories
        return 'js' === $extension && (
            false !== strpos($filePath, '/amd/src/')
            || false !== strpos($filePath, '/amd/build/')
        );
    }

    /**
     * Get the name of this extractor.
     *
     * @return string Extractor name
     */
    public function getName(): string
    {
        return 'JavaScript String Extractor';
    }

    /**
     * Extract strings from a single line of JavaScript.
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

        // Pattern 1: str.get_string('stringkey', 'component', ...) - handles optional parameters
        if (preg_match_all('/str\.get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                if ($stringComponent === $component) {
                    $strings[$stringKey][] = [
                        'file'    => basename($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 2: str.get_strings([{key: 'stringkey', component: 'component'}])
        if (preg_match_all('/\{\s*key\s*:\s*[\'"]([^\'\"]+)[\'"]\s*,\s*component\s*:\s*[\'"]([^\'\"]+)[\'"]\s*\}/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                if ($stringComponent === $component) {
                    $strings[$stringKey][] = [
                        'file'    => basename($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 3: Alternative str.get_strings format with separate arrays
        // str.get_strings(['stringkey1', 'stringkey2'], 'component')
        if (preg_match('/str\.get_strings\s*\(\s*\[(.*?)\]\s*,\s*[\'"]([^\'\"]+)[\'"]\s*\)/', $line, $match)) {
            $stringKeysStr   = $match[1];
            $stringComponent = $match[2];

            if ($stringComponent === $component) {
                // Extract individual string keys from the array
                if (preg_match_all('/[\'"]([^\'\"]+)[\'"]/', $stringKeysStr, $keyMatches)) {
                    foreach ($keyMatches[1] as $stringKey) {
                        $strings[$stringKey][] = [
                            'file'    => basename($filePath),
                            'line'    => $lineNumber,
                            'context' => trim($line),
                        ];
                    }
                }
            }
        }

        // Pattern 4: Core/str getString method - core/str module getString function
        if (preg_match_all('/getString\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                if ($stringComponent === $component) {
                    $strings[$stringKey][] = [
                        'file'    => basename($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 5: Core/str getStrings method - core/str module getStrings function
        if (preg_match_all('/getStrings\s*\(\s*\[(.*?)\]\s*\)/', $line, $stringArrayMatches)) {
            foreach ($stringArrayMatches[1] as $stringArrayContent) {
                // Extract {key: 'stringkey', component: 'component'} objects
                if (preg_match_all('/\{\s*key\s*:\s*[\'"]([^\'\"]+)[\'"]\s*,\s*component\s*:\s*[\'"]([^\'\"]+)[\'"]\s*\}/', $stringArrayContent, $objectMatches, PREG_SET_ORDER)) {
                    foreach ($objectMatches as $match) {
                        $stringKey       = $match[1];
                        $stringComponent = $match[2];

                        if ($stringComponent === $component) {
                            $strings[$stringKey][] = [
                                'file'    => basename($filePath),
                                'line'    => $lineNumber,
                                'context' => trim($line),
                            ];
                        }
                    }
                }
            }
        }

        // Pattern 6: Prefetch.prefetchString method
        if (preg_match_all('/Prefetch\.prefetchString\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"]\s*\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $stringKey       = $match[1];
                $stringComponent = $match[2];

                if ($stringComponent === $component) {
                    $strings[$stringKey][] = [
                        'file'    => basename($filePath),
                        'line'    => $lineNumber,
                        'context' => trim($line),
                    ];
                }
            }
        }

        // Pattern 7: Prefetch.prefetchStrings method
        if (preg_match('/Prefetch\.prefetchStrings\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*\[(.*?)\]\s*\)/', $line, $match)) {
            $stringComponent = $match[1];
            $stringKeysStr   = $match[2];

            if ($stringComponent === $component) {
                // Extract individual string keys from the array
                if (preg_match_all('/[\'"]([^\'\"]+)[\'"]/', $stringKeysStr, $keyMatches)) {
                    foreach ($keyMatches[1] as $stringKey) {
                        $strings[$stringKey][] = [
                            'file'    => basename($filePath),
                            'line'    => $lineNumber,
                            'context' => trim($line),
                        ];
                    }
                }
            }
        }

        return $strings;
    }
}
