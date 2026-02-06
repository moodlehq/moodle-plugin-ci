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

namespace MoodlePluginCI\MissingStrings\Exception;

/**
 * Exception for file-related errors.
 *
 * Thrown when file operations fail during string validation.
 */
class FileException extends StringValidationException
{
    /**
     * File path that caused the error.
     *
     * @var string
     */
    private $filePath;

    /**
     * Constructor.
     *
     * @param string          $filePath file path that caused the error
     * @param string          $message  error message
     * @param array           $context  additional context information
     * @param string          $severity error severity
     * @param \Throwable|null $previous previous exception
     */
    public function __construct(
        string $filePath,
        string $message = '',
        array $context = [],
        string $severity = 'error',
        ?\Throwable $previous = null
    ) {
        $this->filePath = $filePath;

        // Add file path to context
        $context['file'] = $filePath;

        parent::__construct($message, $context, $severity, 0, $previous);
    }

    /**
     * Get the file path that caused the error.
     *
     * @return string file path
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Create a file not found error.
     *
     * @param string $filePath file path
     * @param array  $context  context information
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fileNotFound(string $filePath, array $context = []): self
    {
        return new static(
            $filePath,
            "File not found: {$filePath}",
            $context,
            'error'
        );
    }

    /**
     * Create a file not readable error.
     *
     * @param string $filePath file path
     * @param array  $context  context information
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function fileNotReadable(string $filePath, array $context = []): self
    {
        return new static(
            $filePath,
            "File not readable: {$filePath}",
            $context,
            'error'
        );
    }

    /**
     * Create a file parsing error.
     *
     * @param string          $filePath file path
     * @param string          $reason   parsing error reason
     * @param array           $context  context information
     * @param \Throwable|null $previous previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function parsingError(
        string $filePath,
        string $reason,
        array $context = [],
        ?\Throwable $previous = null
    ): self {
        return new static(
            $filePath,
            "Failed to parse file {$filePath}: {$reason}",
            $context,
            'error',
            $previous
        );
    }

    /**
     * Create a file content warning.
     *
     * @param string $filePath file path
     * @param string $message  warning message
     * @param array  $context  context information
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function contentWarning(string $filePath, string $message, array $context = []): self
    {
        return new static(
            $filePath,
            $message,
            $context,
            'warning'
        );
    }
}
