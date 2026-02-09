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
 * Unified result container for string validation operations.
 *
 * Handles both data collection (required strings, errors, warnings) and
 * presentation (formatted messages, statistics, validation status).
 */
class ValidationResult
{
    /**
     * Required strings with their context information.
     *
     * @var array<string, StringContext>
     */
    private $requiredStrings = [];

    /**
     * Raw error messages (without formatting).
     *
     * @var string[]
     */
    private $errors = [];

    /**
     * Raw warning messages (without formatting).
     *
     * @var string[]
     */
    private $warnings = [];

    /**
     * Formatted messages for display.
     *
     * @var string[]
     */
    private $messages = [];

    /**
     * Success count for statistics.
     */
    private int $successCount = 0;

    /**
     * Strict mode flag.
     */
    private $strict;

    /**
     * Debug performance data.
     *
     * @var array
     */
    private $debugData = [
        'processing_time' => 0.0,
        'file_counts'     => [],
        'string_counts'   => [],
        'phase_timings'   => [],
        'plugin_count'    => 0,
        'subplugin_count' => 0,
    ];

    public function __construct(bool $strict = false)
    {
        $this->strict = $strict;
    }

    // === Data Collection Methods (for checkers) ===

    /**
     * Add a required string.
     *
     * @param string        $stringKey the string identifier
     * @param StringContext $context   context information about why this string is required
     */
    public function addRequiredString(string $stringKey, StringContext $context): void
    {
        $this->requiredStrings[$stringKey] = $context;
    }

    /**
     * Add a raw error message (without formatting).
     *
     * @param string $message the error message
     */
    public function addRawError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Add a raw warning message (without formatting).
     *
     * @param string $message the warning message
     */
    public function addRawWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    // === Presentation Methods (for main validator) ===

    /**
     * Add a formatted error message for display.
     *
     * @param string $message the error message
     */
    public function addError(string $message): void
    {
        $this->errors[]   = $message;
        $this->messages[] = sprintf('<fg=red>✗ %s</>', $message);
    }

    /**
     * Add a formatted warning message for display.
     *
     * @param string $message the warning message
     */
    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
        $this->messages[] = sprintf('<comment>⚠ %s</comment>', $message);
    }

    /**
     * Add a success (for statistics, optionally with display message).
     *
     * @param string $message optional success message for display
     */
    public function addSuccess(string $message = ''): void
    {
        // Only add to messages if message is not empty (for display purposes)
        if (!empty($message)) {
            $this->messages[] = sprintf('<info>✓ %s</info>', $message);
        }
        ++$this->successCount;
    }

    // === Data Access Methods ===

    /**
     * Get required strings.
     *
     * @return array array of string key => context pairs
     */
    public function getRequiredStrings(): array
    {
        return $this->requiredStrings;
    }

    /**
     * Get all required string keys (without context).
     *
     * @return array array of string keys
     */
    public function getRequiredStringKeys(): array
    {
        return array_keys($this->requiredStrings);
    }

    /**
     * Get raw error messages.
     *
     * @return array array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get raw warning messages.
     *
     * @return array array of warning messages
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get formatted messages for display.
     *
     * @return array the formatted messages
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    // === Statistics and Status Methods ===

    /**
     * Get error count.
     *
     * @return int the error count
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Get warning count.
     *
     * @return int the warning count
     */
    public function getWarningCount(): int
    {
        return count($this->warnings);
    }

    /**
     * Get success count.
     *
     * @return int the success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get total issues count.
     *
     * @return int the total issues count
     */
    public function getTotalIssues(): int
    {
        return $this->getErrorCount() + $this->getWarningCount();
    }

    /**
     * Check if there are any required strings.
     *
     * @return bool true if there are required strings
     */
    public function hasRequiredStrings(): bool
    {
        return !empty($this->requiredStrings);
    }

    /**
     * Check if there are any errors.
     *
     * @return bool true if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if there are any warnings.
     *
     * @return bool true if there are warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Check if validation is valid.
     *
     * @return bool true if the validation is valid, false otherwise
     */
    public function isValid(): bool
    {
        if ($this->strict) {
            return 0 === $this->getErrorCount() && 0 === $this->getWarningCount();
        }

        return 0 === $this->getErrorCount();
    }

    /**
     * Get summary statistics.
     *
     * @return array the summary statistics
     */
    public function getSummary(): array
    {
        return [
            'errors'       => $this->getErrorCount(),
            'warnings'     => $this->getWarningCount(),
            'successes'    => $this->successCount,
            'total_issues' => $this->getTotalIssues(),
            'is_valid'     => $this->isValid(),
        ];
    }

    /**
     * Merge another result into this one.
     *
     * @param ValidationResult $other the other result to merge
     */
    public function merge(self $other): void
    {
        // Merge required strings
        foreach ($other->getRequiredStrings() as $key => $context) {
            $this->addRequiredString($key, $context);
        }

        // Merge raw errors and warnings (without formatting)
        foreach ($other->getErrors() as $error) {
            $this->addRawError($error);
        }

        foreach ($other->getWarnings() as $warning) {
            $this->addRawWarning($warning);
        }

        // Add success count
        $this->successCount += $other->getSuccessCount();

        // Merge debug data
        $otherDebug = $other->getDebugData();
        $this->debugData['processing_time'] += $otherDebug['processing_time'];
        $this->debugData['plugin_count'] += $otherDebug['plugin_count'];
        $this->debugData['subplugin_count'] += $otherDebug['subplugin_count'];

        // Merge file counts
        foreach ($otherDebug['file_counts'] as $type => $count) {
            $this->debugData['file_counts'][$type] = ($this->debugData['file_counts'][$type] ?? 0) + $count;
        }

        // Merge string counts
        foreach ($otherDebug['string_counts'] as $type => $count) {
            $this->debugData['string_counts'][$type] = ($this->debugData['string_counts'][$type] ?? 0) + $count;
        }

        // Merge phase timings
        foreach ($otherDebug['phase_timings'] as $phase => $timing) {
            $this->debugData['phase_timings'][$phase] = ($this->debugData['phase_timings'][$phase] ?? 0) + $timing;
        }
    }

    // === Debug Data Methods ===

    /**
     * Set debug data for performance tracking.
     *
     * @param array $data debug data to set
     */
    public function setDebugData(array $data): void
    {
        $this->debugData = array_merge($this->debugData, $data);
    }

    /**
     * Add debug timing for a specific phase.
     *
     * @param string $phase phase name
     * @param float  $time  time in seconds
     */
    public function addPhaseTime(string $phase, float $time): void
    {
        $this->debugData['phase_timings'][$phase] = ($this->debugData['phase_timings'][$phase] ?? 0) + $time;
    }

    /**
     * Add file count data.
     *
     * @param array $fileCounts array of file type => count pairs
     */
    public function addFileCounts(array $fileCounts): void
    {
        foreach ($fileCounts as $type => $count) {
            $this->debugData['file_counts'][$type] = ($this->debugData['file_counts'][$type] ?? 0) + $count;
        }
    }

    /**
     * Add string count data.
     *
     * @param array $stringCounts array of string type => count pairs
     */
    public function addStringCounts(array $stringCounts): void
    {
        foreach ($stringCounts as $type => $count) {
            $this->debugData['string_counts'][$type] = ($this->debugData['string_counts'][$type] ?? 0) + $count;
        }
    }

    /**
     * Increment plugin count.
     */
    public function incrementPluginCount(): void
    {
        ++$this->debugData['plugin_count'];
    }

    /**
     * Increment subplugin count.
     */
    public function incrementSubpluginCount(): void
    {
        ++$this->debugData['subplugin_count'];
    }

    /**
     * Set total processing time.
     *
     * @param float $time time in seconds
     */
    public function setProcessingTime(float $time): void
    {
        $this->debugData['processing_time'] = $time;
    }

    /**
     * Get debug data.
     *
     * @return array debug performance data
     */
    public function getDebugData(): array
    {
        return $this->debugData;
    }
}
