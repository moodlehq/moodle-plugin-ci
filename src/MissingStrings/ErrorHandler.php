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

use MoodlePluginCI\MissingStrings\Exception\CheckerException;
use MoodlePluginCI\MissingStrings\Exception\FileException;
use MoodlePluginCI\MissingStrings\Exception\StringValidationException;

/**
 * Centralized error handler for string validation.
 *
 * Provides consistent error processing, formatting, and reporting
 * across all components of the string validation system.
 */
class ErrorHandler
{
    /**
     * Validation result to add errors to.
     */
    private $result;

    /**
     * Whether to include debug information in error messages.
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param ValidationResult $result validation result to add errors to
     * @param bool             $debug  whether to include debug information
     */
    public function __construct(ValidationResult $result, bool $debug = false)
    {
        $this->result = $result;
        $this->debug  = $debug;
    }

    /**
     * Handle a string validation exception.
     *
     * @param StringValidationException $exception exception to handle
     */
    public function handleException(StringValidationException $exception): void
    {
        $message = $this->formatExceptionMessage($exception);

        if ($exception->isError()) {
            $this->result->addError($message);
        } elseif ($exception->isWarning()) {
            $this->result->addWarning($message);
        } else {
            // Info level - add as success with message
            $this->result->addSuccess($message);
        }
    }

    /**
     * Handle a generic exception with context.
     *
     * @param \Throwable $exception exception to handle
     * @param string     $context   context description
     * @param string     $severity  error severity
     */
    public function handleGenericException(
        \Throwable $exception,
        string $context = '',
        string $severity = 'error'
    ): void {
        $contextInfo = [];
        if (!empty($context)) {
            $contextInfo['context'] = $context;
        }

        $validationException = new StringValidationException(
            $exception->getMessage(),
            $contextInfo,
            $severity,
            (int) $exception->getCode(),
            $exception
        );

        $this->handleException($validationException);
    }

    /**
     * Handle a checker error with graceful degradation.
     *
     * @param string     $checkerName     name of the checker that failed
     * @param \Throwable $exception       exception that occurred
     * @param bool       $continueOnError whether to continue validation after error
     *
     * @return bool true if validation should continue, false otherwise
     */
    public function handleCheckerError(
        string $checkerName,
        \Throwable $exception,
        bool $continueOnError = true
    ): bool {
        $checkerException = CheckerException::checkerError(
            $checkerName,
            $exception->getMessage(),
            ['original_error' => get_class($exception)],
            $exception
        );

        if ($continueOnError) {
            // Convert to warning to allow validation to continue
            $checkerException = CheckerException::checkerWarning(
                $checkerName,
                'Checker failed but validation continues: ' . $exception->getMessage(),
                ['original_error' => get_class($exception)],
                $exception
            );
        }

        $this->handleException($checkerException);

        return $continueOnError;
    }

    /**
     * Handle a file operation error.
     *
     * @param string     $filePath  file path that caused the error
     * @param \Throwable $exception exception that occurred
     * @param string     $operation Operation that failed (e.g., 'read', 'parse').
     */
    public function handleFileError(string $filePath, \Throwable $exception, string $operation = 'process'): void
    {
        $fileException = FileException::parsingError(
            $filePath,
            "Failed to {$operation} file: " . $exception->getMessage(),
            ['operation' => $operation, 'original_error' => get_class($exception)],
            $exception
        );

        $this->handleException($fileException);
    }

    /**
     * Add a contextual error message.
     *
     * @param string $message  error message
     * @param array  $context  context information
     * @param string $severity error severity
     */
    public function addError(string $message, array $context = [], string $severity = 'error'): void
    {
        $exception = new StringValidationException($message, $context, $severity);
        $this->handleException($exception);
    }

    /**
     * Add a contextual warning message.
     *
     * @param string $message warning message
     * @param array  $context context information
     */
    public function addWarning(string $message, array $context = []): void
    {
        $this->addError($message, $context, 'warning');
    }

    /**
     * Add a contextual info message.
     *
     * @param string $message info message
     * @param array  $context context information
     */
    public function addInfo(string $message, array $context = []): void
    {
        $this->addError($message, $context, 'info');
    }

    /**
     * Format an exception message for display.
     *
     * @param StringValidationException $exception exception to format
     *
     * @return string formatted message
     */
    private function formatExceptionMessage(StringValidationException $exception): string
    {
        $message = $exception->getFormattedMessage();

        // Add debug information if enabled
        if ($this->debug && $exception->getPrevious()) {
            $previous = $exception->getPrevious();
            $message .= ' [Debug: ' . get_class($previous) . ' in ' .
                       basename($previous->getFile()) . ':' . $previous->getLine() . ']';
        }

        return $message;
    }

    /**
     * Create a safe execution wrapper that handles exceptions.
     *
     * @param callable $callback        callback to execute safely
     * @param string   $context         context description for errors
     * @param bool     $continueOnError whether to continue on error
     *
     * @return mixed result of callback or null on error
     */
    public function safeExecute(callable $callback, string $context = '', bool $continueOnError = true)
    {
        try {
            return $callback();
        } catch (StringValidationException $e) {
            $this->handleException($e);

            return $continueOnError ? null : false;
        } catch (\Throwable $e) {
            $this->handleGenericException($e, $context, $continueOnError ? 'warning' : 'error');

            return $continueOnError ? null : false;
        }
    }

    /**
     * Get the validation result.
     *
     * @return ValidationResult current validation result
     */
    public function getResult(): ValidationResult
    {
        return $this->result;
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
     * Enable or disable debug mode.
     *
     * @param bool $debug whether to enable debug mode
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}
