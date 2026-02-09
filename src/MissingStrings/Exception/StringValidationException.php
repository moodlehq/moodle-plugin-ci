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
 * Base exception for all string validation related errors.
 *
 * Provides common functionality for error context and user-friendly messages.
 */
class StringValidationException extends \Exception
{
    /**
     * Error context information.
     *
     * @var array
     */
    private $context;

    /**
     * Error severity level.
     *
     * @var string
     */
    private $severity;

    /**
     * Constructor.
     *
     * @param string          $message  error message
     * @param array           $context  additional context information
     * @param string          $severity error severity (error, warning, info)
     * @param int             $code     error code
     * @param \Throwable|null $previous previous exception
     */
    public function __construct(
        string $message = '',
        array $context = [],
        string $severity = 'error',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context  = $context;
        $this->severity = $severity;
    }

    /**
     * Get error context.
     *
     * @return array context information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get error severity.
     *
     * @return string severity level
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Check if this is a warning-level error.
     *
     * @return bool true if warning level
     */
    public function isWarning(): bool
    {
        return 'warning' === $this->severity;
    }

    /**
     * Check if this is an error-level error.
     *
     * @return bool true if error level
     */
    public function isError(): bool
    {
        return 'error' === $this->severity;
    }

    /**
     * Get formatted error message with context.
     *
     * @return string formatted message
     */
    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if (!empty($this->context)) {
            $contextParts = [];
            foreach ($this->context as $key => $value) {
                if (is_scalar($value)) {
                    $contextParts[] = "{$key}: {$value}";
                }
            }

            if (!empty($contextParts)) {
                $message .= ' (' . implode(', ', $contextParts) . ')';
            }
        }

        return $message;
    }

    /**
     * Create an error-level exception.
     *
     * @param string          $message  error message
     * @param array           $context  context information
     * @param \Throwable|null $previous previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function error(string $message, array $context = [], ?\Throwable $previous = null): self
    {
        return new static($message, $context, 'error', 0, $previous);
    }

    /**
     * Create a warning-level exception.
     *
     * @param string          $message  warning message
     * @param array           $context  context information
     * @param \Throwable|null $previous previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function warning(string $message, array $context = [], ?\Throwable $previous = null): self
    {
        return new static($message, $context, 'warning', 0, $previous);
    }

    /**
     * Create an info-level exception.
     *
     * @param string          $message  info message
     * @param array           $context  context information
     * @param \Throwable|null $previous previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function info(string $message, array $context = [], ?\Throwable $previous = null): self
    {
        return new static($message, $context, 'info', 0, $previous);
    }
}
