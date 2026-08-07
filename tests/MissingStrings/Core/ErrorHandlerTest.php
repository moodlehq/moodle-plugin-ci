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

namespace MoodlePluginCI\Tests\MissingStrings\Core;

use MoodlePluginCI\MissingStrings\ErrorHandler;
use MoodlePluginCI\MissingStrings\Exception\CheckerException;
use MoodlePluginCI\MissingStrings\Exception\FileException;
use MoodlePluginCI\MissingStrings\Exception\StringValidationException;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for ErrorHandler class.
 *
 * @covers \MoodlePluginCI\MissingStrings\ErrorHandler
 */
class ErrorHandlerTest extends MissingStringsTestCase
{
    /** @var ValidationResult */
    private $result;

    /** @var ErrorHandler */
    private $errorHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->result       = new ValidationResult();
        $this->errorHandler = new ErrorHandler($this->result);
    }

    /**
     * Test constructor sets properties correctly.
     */
    public function testConstructorSetsPropertiesCorrectly(): void
    {
        $result  = new ValidationResult();
        $handler = new ErrorHandler($result, true);

        $this->assertSame($result, $handler->getResult());
        $this->assertTrue($handler->isDebugEnabled());

        $handler2 = new ErrorHandler($result, false);
        $this->assertFalse($handler2->isDebugEnabled());
    }

    /**
     * Test handleException with error level exception.
     */
    public function testHandleExceptionWithErrorLevel(): void
    {
        $exception = new StringValidationException(
            'Test error message',
            ['file' => 'test.php', 'line' => 10],
            'error'
        );

        $this->errorHandler->handleException($exception);

        $this->assertSame(1, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
        $this->assertSame(0, $this->result->getSuccessCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Test error message', $errors[0]);
        $this->assertStringContainsString('test.php', $errors[0]);
        $this->assertStringContainsString('10', $errors[0]);
    }

    /**
     * Test handleException with warning level exception.
     */
    public function testHandleExceptionWithWarningLevel(): void
    {
        $exception = new StringValidationException(
            'Test warning message',
            ['context' => 'test context'],
            'warning'
        );

        $this->errorHandler->handleException($exception);

        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());
        $this->assertSame(0, $this->result->getSuccessCount());

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('Test warning message', $warnings[0]);
        $this->assertStringContainsString('test context', $warnings[0]);
    }

    /**
     * Test handleException with info level exception.
     */
    public function testHandleExceptionWithInfoLevel(): void
    {
        $exception = new StringValidationException(
            'Test info message',
            [],
            'info'
        );

        $this->errorHandler->handleException($exception);

        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
        $this->assertSame(1, $this->result->getSuccessCount());
    }

    /**
     * Test handleGenericException.
     */
    public function testHandleGenericException(): void
    {
        $originalException = new \RuntimeException('Original error message', 123);

        $this->errorHandler->handleGenericException($originalException, 'Test context');

        $this->assertSame(1, $this->result->getErrorCount());
        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Original error message', $errors[0]);
        $this->assertStringContainsString('Test context', $errors[0]);
    }

    /**
     * Test handleGenericException with warning severity.
     */
    public function testHandleGenericExceptionWithWarningSeverity(): void
    {
        $originalException = new \InvalidArgumentException('Invalid argument');

        $this->errorHandler->handleGenericException($originalException, 'Test context', 'warning');

        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('Invalid argument', $warnings[0]);
    }

    /**
     * Test handleCheckerError with continue on error.
     */
    public function testHandleCheckerErrorWithContinueOnError(): void
    {
        $originalException = new \RuntimeException('Checker failed');

        $result = $this->errorHandler->handleCheckerError('TestChecker', $originalException, true);

        $this->assertTrue($result);
        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('TestChecker', $warnings[0]);
        $this->assertStringContainsString('Checker failed but validation continues', $warnings[0]);
    }

    /**
     * Test handleCheckerError without continue on error.
     */
    public function testHandleCheckerErrorWithoutContinueOnError(): void
    {
        $originalException = new \RuntimeException('Critical checker error');

        $result = $this->errorHandler->handleCheckerError('CriticalChecker', $originalException, false);

        $this->assertFalse($result);
        $this->assertSame(1, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('CriticalChecker', $errors[0]);
        $this->assertStringContainsString('Critical checker error', $errors[0]);
    }

    /**
     * Test handleFileError.
     */
    public function testHandleFileError(): void
    {
        $originalException = new \Exception('File parsing failed');

        $this->errorHandler->handleFileError('/path/to/file.php', $originalException, 'parse');

        $this->assertSame(1, $this->result->getErrorCount());
        $errors = $this->result->getErrors();
        $this->assertStringContainsString('file.php', $errors[0]);
        $this->assertStringContainsString('Failed to parse file', $errors[0]);
        $this->assertStringContainsString('File parsing failed', $errors[0]);
    }

    /**
     * Test addError method.
     */
    public function testAddError(): void
    {
        $this->errorHandler->addError(
            'Custom error message',
            ['component' => 'mod_test', 'string_key' => 'missing_string']
        );

        $this->assertSame(1, $this->result->getErrorCount());
        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Custom error message', $errors[0]);
        $this->assertStringContainsString('mod_test', $errors[0]);
        $this->assertStringContainsString('missing_string', $errors[0]);
    }

    /**
     * Test addWarning method.
     */
    public function testAddWarning(): void
    {
        $this->errorHandler->addWarning(
            'Custom warning message',
            ['unused_string' => 'old_feature']
        );

        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('Custom warning message', $warnings[0]);
        $this->assertStringContainsString('old_feature', $warnings[0]);
    }

    /**
     * Test addInfo method.
     */
    public function testAddInfo(): void
    {
        $this->errorHandler->addInfo(
            'Custom info message',
            ['processed_files' => 5]
        );

        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
        $this->assertSame(1, $this->result->getSuccessCount());
    }

    /**
     * Test safeExecute with successful callback.
     */
    public function testSafeExecuteWithSuccessfulCallback(): void
    {
        $callback = function () {
            return 'success result';
        };

        $result = $this->errorHandler->safeExecute($callback, 'Test operation');

        $this->assertSame('success result', $result);
        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
    }

    /**
     * Test safeExecute with StringValidationException.
     */
    public function testSafeExecuteWithStringValidationException(): void
    {
        $callback = function () {
            throw new StringValidationException('Validation failed', [], 'error');
        };

        $result = $this->errorHandler->safeExecute($callback, 'Test operation');

        $this->assertNull($result);
        $this->assertSame(1, $this->result->getErrorCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Validation failed', $errors[0]);
    }

    /**
     * Test safeExecute with generic exception and continue on error.
     */
    public function testSafeExecuteWithGenericExceptionContinueOnError(): void
    {
        $callback = function () {
            throw new \RuntimeException('Generic error');
        };

        $result = $this->errorHandler->safeExecute($callback, 'Test operation', true);

        $this->assertNull($result);
        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('Generic error', $warnings[0]);
        $this->assertStringContainsString('Test operation', $warnings[0]);
    }

    /**
     * Test safeExecute with generic exception and stop on error.
     */
    public function testSafeExecuteWithGenericExceptionStopOnError(): void
    {
        $callback = function () {
            throw new \RuntimeException('Critical error');
        };

        $result = $this->errorHandler->safeExecute($callback, 'Critical operation', false);

        $this->assertFalse($result);
        $this->assertSame(1, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Critical error', $errors[0]);
    }

    /**
     * Test debug mode with exception handling.
     */
    public function testDebugModeWithExceptionHandling(): void
    {
        $debugHandler = new ErrorHandler($this->result, true);

        $originalException         = new \RuntimeException('Original error', 0);
        $stringValidationException = new StringValidationException(
            'Validation error',
            [],
            'error',
            0,
            $originalException
        );

        $debugHandler->handleException($stringValidationException);

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Debug:', $errors[0]);
        $this->assertStringContainsString('RuntimeException', $errors[0]);
    }

    /**
     * Test setDebug method.
     */
    public function testSetDebug(): void
    {
        $this->assertFalse($this->errorHandler->isDebugEnabled());

        $this->errorHandler->setDebug(true);
        $this->assertTrue($this->errorHandler->isDebugEnabled());

        $this->errorHandler->setDebug(false);
        $this->assertFalse($this->errorHandler->isDebugEnabled());
    }

    /**
     * Test exception formatting without debug info.
     */
    public function testExceptionFormattingWithoutDebugInfo(): void
    {
        $exception = new StringValidationException(
            'Simple error',
            ['key' => 'value'],
            'error'
        );

        $this->errorHandler->handleException($exception);

        $errors = $this->result->getErrors();
        $this->assertStringNotContainsString('Debug:', $errors[0]);
        $this->assertStringContainsString('Simple error', $errors[0]);
        $this->assertStringContainsString('value', $errors[0]);
    }

    /**
     * Test handling multiple errors.
     */
    public function testHandlingMultipleErrors(): void
    {
        $this->errorHandler->addError('Error 1', ['context' => 'first']);
        $this->errorHandler->addWarning('Warning 1', ['context' => 'second']);
        $this->errorHandler->addError('Error 2', ['context' => 'third']);
        $this->errorHandler->addInfo('Info 1', ['context' => 'fourth']);

        $this->assertSame(2, $this->result->getErrorCount());
        $this->assertSame(1, $this->result->getWarningCount());
        $this->assertSame(1, $this->result->getSuccessCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Error 1', $errors[0]);
        $this->assertStringContainsString('Error 2', $errors[1]);

        $warnings = $this->result->getWarnings();
        $this->assertStringContainsString('Warning 1', $warnings[0]);
    }

    /**
     * Test with specific exception types.
     */
    public function testWithSpecificExceptionTypes(): void
    {
        // Test FileException
        $fileException = FileException::fileNotFound('/path/to/missing.php', ['component' => 'mod_test']);
        $this->errorHandler->handleException($fileException);

        // Test CheckerException
        $checkerException = CheckerException::checkerError('TestChecker', 'Checker failed', []);
        $this->errorHandler->handleException($checkerException);

        $this->assertSame(2, $this->result->getErrorCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('missing.php', $errors[0]);
        $this->assertStringContainsString('TestChecker', $errors[1]);
    }

    /**
     * Test error handler with edge cases.
     */
    public function testErrorHandlerWithEdgeCases(): void
    {
        // Empty message
        $this->errorHandler->addError('', []);
        $this->assertSame(1, $this->result->getErrorCount());

        // Empty context
        $this->errorHandler->addWarning('Warning message', []);
        $this->assertSame(1, $this->result->getWarningCount());

        // Null values in context
        $this->errorHandler->addError('Error with nulls', ['key' => null, 'file' => '/test.php']);
        $this->assertSame(2, $this->result->getErrorCount());

        $errors = $this->result->getErrors();
        $this->assertStringContainsString('test.php', $errors[1]);
    }

    /**
     * Test safeExecute with callback returning false.
     */
    public function testSafeExecuteWithCallbackReturningFalse(): void
    {
        $callback = function () {
            return false;
        };

        $result = $this->errorHandler->safeExecute($callback, 'Test operation');

        $this->assertFalse($result);
        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
    }

    /**
     * Test safeExecute with callback returning null.
     */
    public function testSafeExecuteWithCallbackReturningNull(): void
    {
        $callback = function () {
            return null;
        };

        $result = $this->errorHandler->safeExecute($callback, 'Test operation');

        $this->assertNull($result);
        $this->assertSame(0, $this->result->getErrorCount());
        $this->assertSame(0, $this->result->getWarningCount());
    }

    /**
     * Test performance with many errors.
     */
    public function testPerformanceWithManyErrors(): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < 1000; ++$i) {
            $this->errorHandler->addError("Error {$i}", ['index' => $i]);
        }

        $endTime  = microtime(true);
        $duration = $endTime - $startTime;

        $this->assertSame(1000, $this->result->getErrorCount());
        $this->assertLessThan(1.0, $duration, 'Error handling should be reasonably fast');
    }

    /**
     * Test error handler preserves original exception chain.
     */
    public function testErrorHandlerPreservesExceptionChain(): void
    {
        $originalException = new \InvalidArgumentException('Original error');
        $wrappedException  = new \RuntimeException('Wrapped error', 0, $originalException);

        $this->errorHandler->handleGenericException($wrappedException, 'Test context');

        $this->assertSame(1, $this->result->getErrorCount());
        $errors = $this->result->getErrors();
        $this->assertStringContainsString('Wrapped error', $errors[0]);
    }
}
