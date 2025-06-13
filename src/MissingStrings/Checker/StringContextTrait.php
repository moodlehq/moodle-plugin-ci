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

namespace MoodlePluginCI\MissingStrings\Checker;

use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\MissingStrings\StringUsageFinder;
use MoodlePluginCI\MissingStrings\ValidationResult;

/**
 * Trait providing common functionality for string context creation and line number detection.
 *
 * This trait eliminates code duplication across checkers by providing standardized methods
 * for creating StringContext objects with appropriate line number detection.
 */
trait StringContextTrait
{
    /**
     * String usage finder utility.
     *
     * @var StringUsageFinder
     */
    private $usageFinder;

    /**
     * Initialize the string usage finder.
     * Call this from the checker's constructor.
     */
    private function initializeStringUsageFinder(): void
    {
        $this->usageFinder = new StringUsageFinder();
    }

    /**
     * Add a required string with context based on array key detection.
     * Suitable for database files like db/access.php, db/messages.php, etc.
     *
     * @param ValidationResult $result      the result to add the string to
     * @param string           $stringKey   the language string key to add
     * @param string           $filePath    the file path where the key is defined
     * @param string           $arrayKey    the array key to search for in the file
     * @param string           $description description of what this string is for
     */
    protected function addRequiredStringWithArrayKey(
        ValidationResult $result,
        string $stringKey,
        string $filePath,
        string $arrayKey,
        string $description
    ): void {
        $context = new StringContext($filePath, null, $description);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, $arrayKey);
        if (null !== $lineNumber) {
            $context->setLine($lineNumber);
        }

        $result->addRequiredString($stringKey, $context);
    }

    /**
     * Add a required string with context based on string literal detection.
     * Suitable for finding quoted strings in PHP code.
     *
     * @param ValidationResult $result      the result to add the string to
     * @param string           $stringKey   the language string key to add
     * @param string           $filePath    the file path where the key is used
     * @param string           $searchKey   the string literal to search for
     * @param string           $description description of what this string is for
     */
    protected function addRequiredStringWithStringLiteral(
        ValidationResult $result,
        string $stringKey,
        string $filePath,
        string $searchKey,
        string $description
    ): void {
        $context = new StringContext($filePath, null, $description);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, $searchKey);
        if (null !== $lineNumber) {
            $context->setLine($lineNumber);
        }

        $result->addRequiredString($stringKey, $context);
    }

    /**
     * Add a required string with context based on custom pattern detection.
     * Suitable for complex pattern matching scenarios.
     *
     * @param ValidationResult $result      the result to add the string to
     * @param string           $stringKey   the language string key to add
     * @param string           $filePath    the file path where the key is used
     * @param string           $searchKey   the key to search for
     * @param string           $pattern     the regex pattern to use for matching
     * @param string           $description description of what this string is for
     */
    protected function addRequiredStringWithCustomPattern(
        ValidationResult $result,
        string $stringKey,
        string $filePath,
        string $searchKey,
        string $pattern,
        string $description
    ): void {
        $context = new StringContext($filePath, null, $description);

        $lineNumber = $this->usageFinder->findLineInFile($filePath, $searchKey, $pattern);
        if (null !== $lineNumber) {
            $context->setLine($lineNumber);
        }

        $result->addRequiredString($stringKey, $context);
    }

    /**
     * Create a StringContext object with optional line number detection.
     * Generic helper for cases that don't fit the standard patterns.
     *
     * @param string      $filePath    the file path where the string is used
     * @param string      $description description of what this string is for
     * @param string|null $searchKey   optional key to search for line number
     * @param string|null $pattern     optional custom pattern for line detection
     *
     * @return StringContext the created context object
     */
    protected function createStringContext(
        string $filePath,
        string $description,
        ?string $searchKey = null,
        ?string $pattern = null
    ): StringContext {
        $context = new StringContext($filePath, null, $description);

        if (null !== $searchKey) {
            $lineNumber = null;
            if (null !== $pattern) {
                $lineNumber = $this->usageFinder->findLineInFile($filePath, $searchKey, $pattern);
            } else {
                $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, $searchKey);
            }

            if (null !== $lineNumber) {
                $context->setLine($lineNumber);
            }
        }

        return $context;
    }
}
