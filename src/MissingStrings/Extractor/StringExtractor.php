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

use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * String extraction orchestrator.
 *
 * Coordinates multiple string extractors to find all string usage
 * across different file types in a plugin.
 */
class StringExtractor
{
    /**
     * Available string extractors.
     *
     * @var StringExtractorInterface[]
     */
    private $extractors;

    /**
     * File discovery service.
     */
    private ?FileDiscovery $fileDiscovery = null;

    /**
     * String processing metrics.
     *
     * @var array
     */
    private $metrics = [
        'extraction_time'     => 0.0,
        'files_processed'     => 0,
        'strings_extracted'   => 0,
        'string_usages_found' => 0,
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->extractors = [
            new PhpStringExtractor(),
            new MustacheStringExtractor(),
            new JavaScriptStringExtractor(),
        ];
    }

    /**
     * Set file discovery service.
     *
     * @param FileDiscovery $fileDiscovery file discovery service
     */
    public function setFileDiscovery(FileDiscovery $fileDiscovery): void
    {
        $this->fileDiscovery = $fileDiscovery;
    }

    /**
     * Extract strings from a plugin.
     *
     * @param Plugin $plugin plugin to extract strings from
     *
     * @return array array of string keys with their usage information
     */
    public function extractFromPlugin(Plugin $plugin): array
    {
        if (!$this->fileDiscovery) {
            throw new \RuntimeException('File discovery service not set');
        }

        $startTime = microtime(true);

        // Reset metrics for this extraction
        $this->metrics = [
            'extraction_time'     => 0.0,
            'files_processed'     => 0,
            'strings_extracted'   => 0,
            'string_usages_found' => 0,
        ];

        $allStrings      = [];
        $filesByCategory = $this->fileDiscovery->getAllFiles();

        // Flatten the categorized files into a single array
        $allFiles = [];
        foreach ($filesByCategory as $category => $files) {
            $allFiles = array_merge($allFiles, $files);
        }

        foreach ($allFiles as $file) {
            $extractor = $this->getExtractorForFile($file);
            if (!$extractor) {
                continue;
            }

            // Read file content
            if (!file_exists($file) || !is_readable($file)) {
                continue;
            }

            $content = file_get_contents($file);
            if (false === $content) {
                continue;
            }

            ++$this->metrics['files_processed'];

            $strings = $extractor->extract($content, $plugin->component, $file);

            // Track string extraction metrics
            $this->metrics['strings_extracted'] += count($strings);
            foreach ($strings as $stringUsages) {
                $this->metrics['string_usages_found'] += count($stringUsages);
            }

            $allStrings = $this->mergeStringUsages($allStrings, $strings);
        }

        $this->metrics['extraction_time'] = microtime(true) - $startTime;

        return $allStrings;
    }

    /**
     * Get appropriate extractor for a file.
     *
     * @param string $file file path
     *
     * @return StringExtractorInterface|null extractor or null if none found
     */
    private function getExtractorForFile(string $file): ?StringExtractorInterface
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->canHandle($file)) {
                return $extractor;
            }
        }

        return null;
    }

    /**
     * Merge string usages from multiple sources.
     *
     * @param array $existing existing string usages
     * @param array $new      new string usages to merge
     *
     * @return array merged string usages
     */
    private function mergeStringUsages(array $existing, array $new): array
    {
        foreach ($new as $stringKey => $usages) {
            if (!isset($existing[$stringKey])) {
                $existing[$stringKey] = [];
            }
            $existing[$stringKey] = array_merge($existing[$stringKey], $usages);
        }

        return $existing;
    }

    /**
     * Add a custom extractor.
     *
     * @param StringExtractorInterface $extractor extractor to add
     */
    public function addExtractor(StringExtractorInterface $extractor): void
    {
        $this->extractors[] = $extractor;
    }

    /**
     * Set custom extractors (replaces all existing ones).
     *
     * @param StringExtractorInterface[] $extractors array of extractors
     */
    public function setExtractors(array $extractors): void
    {
        $this->extractors = $extractors;
    }

    /**
     * Get all registered extractors.
     *
     * @return StringExtractorInterface[] array of extractors
     */
    public function getExtractors(): array
    {
        return $this->extractors;
    }

    /**
     * Get string processing performance metrics.
     *
     * @return array performance metrics for string extraction
     */
    public function getPerformanceMetrics(): array
    {
        return $this->metrics;
    }
}
