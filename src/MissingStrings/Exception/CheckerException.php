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
 * Exception for checker-related errors.
 *
 * Thrown when string checkers encounter errors during execution.
 */
class CheckerException extends StringValidationException
{
    /**
     * Name of the checker that failed.
     *
     * @var string
     */
    private $checkerName;

    /**
     * Constructor.
     *
     * @param string          $checkerName name of the checker that failed
     * @param string          $message     error message
     * @param array           $context     additional context information
     * @param string          $severity    error severity
     * @param \Throwable|null $previous    previous exception
     */
    public function __construct(
        string $checkerName,
        string $message = '',
        array $context = [],
        string $severity = 'error',
        ?\Throwable $previous = null
    ) {
        $this->checkerName = $checkerName;

        // Add checker name to context
        $context['checker'] = $checkerName;

        parent::__construct($message, $context, $severity, 0, $previous);
    }

    /**
     * Get the name of the checker that failed.
     *
     * @return string checker name
     */
    public function getCheckerName(): string
    {
        return $this->checkerName;
    }

    /**
     * Create a checker error.
     *
     * @param string          $checkerName name of the checker
     * @param string          $message     error message
     * @param array           $context     context information
     * @param \Throwable|null $previous    previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function checkerError(
        string $checkerName,
        string $message,
        array $context = [],
        ?\Throwable $previous = null
    ): self {
        return new static($checkerName, $message, $context, 'error', $previous);
    }

    /**
     * Create a checker warning.
     *
     * @param string          $checkerName name of the checker
     * @param string          $message     warning message
     * @param array           $context     context information
     * @param \Throwable|null $previous    previous exception
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function checkerWarning(
        string $checkerName,
        string $message,
        array $context = [],
        ?\Throwable $previous = null
    ): self {
        return new static($checkerName, $message, $context, 'warning', $previous);
    }
}
