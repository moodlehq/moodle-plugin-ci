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
 * Interface for string extractors that find string usage in different file types.
 */
interface StringExtractorInterface
{
    /**
     * Extract string usage from content.
     *
     * @param string $content   File content to analyze
     * @param string $component Plugin component to filter by (only return strings for this component)
     * @param string $filePath  File path for context information
     *
     * @return array Array of string usage: ['stringkey' => [['file' => 'path', 'line' => 123, 'context' => '...']]]
     */
    public function extract(string $content, string $component, string $filePath): array;

    /**
     * Check if this extractor can handle the given file.
     *
     * @param string $filePath Path to the file
     *
     * @return bool True if this extractor can handle the file
     */
    public function canHandle(string $filePath): bool;

    /**
     * Get the name of this extractor for debugging/logging.
     *
     * @return string Extractor name
     */
    public function getName(): string;
}
