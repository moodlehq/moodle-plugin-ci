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

/**
 * Configuration object for missing strings validation.
 *
 * Centralizes all validation parameters in a single, immutable object.
 * Created from command line options and used by the StringValidator.
 */
class ValidationConfig
{
    /**
     * Language to validate against.
     */
    private $language;

    /**
     * Strict mode flag - treat warnings as errors.
     */
    private $strict;

    /**
     * Check for unused strings flag.
     */
    private $checkUnused;

    /**
     * Patterns to exclude from validation.
     */
    private $excludePatterns;

    /**
     * Custom string checkers to use.
     */
    private $customCheckers;

    /**
     * Whether to use default checkers.
     */
    private $useDefaultCheckers;

    /**
     * Debug mode flag - include detailed error information.
     */
    private $debug;

    /**
     * Constructor with default values.
     *
     * @param string $language           language to validate against
     * @param bool   $strict             strict mode flag
     * @param bool   $checkUnused        check unused strings flag
     * @param array  $excludePatterns    patterns to exclude from validation
     * @param array  $customCheckers     custom string checkers
     * @param bool   $useDefaultCheckers whether to use default checkers
     * @param bool   $debug              debug mode flag
     */
    public function __construct(
        string $language = 'en',
        bool $strict = false,
        bool $checkUnused = false,
        array $excludePatterns = [],
        array $customCheckers = [],
        bool $useDefaultCheckers = true,
        bool $debug = false
    ) {
        $this->language           = $language;
        $this->strict             = $strict;
        $this->checkUnused        = $checkUnused;
        $this->excludePatterns    = $excludePatterns;
        $this->customCheckers     = $customCheckers;
        $this->useDefaultCheckers = $useDefaultCheckers;
        $this->debug              = $debug;
    }

    /**
     * Create configuration from command line options.
     *
     * @param array $options Array of options (e.g., from Symfony Console Input).
     */
    public static function fromOptions(array $options): self
    {
        $excludePatterns = [];
        if (!empty($options['exclude-patterns'])) {
            $patterns        = explode(',', $options['exclude-patterns']);
            $excludePatterns = array_filter(array_map('trim', $patterns));
        }

        return new self(
            $options['lang'] ?? 'en',
            $options['strict'] ?? false,
            $options['unused'] ?? false,
            $excludePatterns,
            [],
            true,
            $options['debug'] ?? false
        );
    }

    // === Getter Methods ===

    /**
     * Get the language to validate against.
     *
     * @return string language code
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Check if strict mode is enabled.
     *
     * @return bool true if strict mode is enabled
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * Check if unused string checking is enabled.
     *
     * @return bool true if unused checking is enabled
     */
    public function shouldCheckUnused(): bool
    {
        return $this->checkUnused;
    }

    /**
     * Get exclude patterns.
     *
     * @return array array of exclude patterns
     */
    public function getExcludePatterns(): array
    {
        return $this->excludePatterns;
    }

    /**
     * Get custom checkers.
     *
     * @return array array of custom checkers
     */
    public function getCustomCheckers(): array
    {
        return $this->customCheckers;
    }

    /**
     * Check if default checkers should be used.
     *
     * @return bool true if default checkers should be used
     */
    public function shouldUseDefaultCheckers(): bool
    {
        return $this->useDefaultCheckers;
    }

    /**
     * Check if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled
     */
    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }

    /**
     * Check if a string should be excluded based on patterns.
     *
     * @param string $stringKey the string key to check
     *
     * @return bool true if the string should be excluded
     */
    public function shouldExcludeString(string $stringKey): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (fnmatch($pattern, $stringKey)) {
                return true;
            }
        }

        return false;
    }
}
