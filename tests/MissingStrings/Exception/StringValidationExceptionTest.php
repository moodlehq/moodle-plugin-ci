<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\MissingStrings\Exception;

use MoodlePluginCI\MissingStrings\Exception\StringValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for StringValidationException class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Exception\StringValidationException
 */
class StringValidationExceptionTest extends TestCase
{
    /**
     * Test basic constructor.
     */
    public function testConstructor(): void
    {
        $message  = 'Test error message';
        $context  = ['component' => 'local_test', 'string' => 'test_string'];
        $severity = 'warning';
        $code     = 123;
        $previous = new \RuntimeException('Previous error');

        $exception = new StringValidationException($message, $context, $severity, $code, $previous);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
        $this->assertSame($severity, $exception->getSeverity());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test constructor with minimal parameters.
     */
    public function testConstructorWithMinimalParameters(): void
    {
        $exception = new StringValidationException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame([], $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test constructor with default values.
     */
    public function testConstructorWithDefaultValues(): void
    {
        $message = 'Test message';
        $context = ['key' => 'value'];

        $exception = new StringValidationException($message, $context);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test getContext method.
     */
    public function testGetContext(): void
    {
        $context = [
            'component' => 'local_test',
            'string'    => 'test_string',
            'file'      => '/path/to/file.php',
            'line'      => 42,
        ];

        $exception = new StringValidationException('Test message', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * Test getSeverity method.
     */
    public function testGetSeverity(): void
    {
        $exception1 = new StringValidationException('Test', [], 'error');
        $exception2 = new StringValidationException('Test', [], 'warning');
        $exception3 = new StringValidationException('Test', [], 'info');

        $this->assertSame('error', $exception1->getSeverity());
        $this->assertSame('warning', $exception2->getSeverity());
        $this->assertSame('info', $exception3->getSeverity());
    }

    /**
     * Test isWarning method.
     */
    public function testIsWarning(): void
    {
        $errorException   = new StringValidationException('Test', [], 'error');
        $warningException = new StringValidationException('Test', [], 'warning');
        $infoException    = new StringValidationException('Test', [], 'info');

        $this->assertFalse($errorException->isWarning());
        $this->assertTrue($warningException->isWarning());
        $this->assertFalse($infoException->isWarning());
    }

    /**
     * Test isError method.
     */
    public function testIsError(): void
    {
        $errorException   = new StringValidationException('Test', [], 'error');
        $warningException = new StringValidationException('Test', [], 'warning');
        $infoException    = new StringValidationException('Test', [], 'info');

        $this->assertTrue($errorException->isError());
        $this->assertFalse($warningException->isError());
        $this->assertFalse($infoException->isError());
    }

    /**
     * Test getFormattedMessage method.
     */
    public function testGetFormattedMessage(): void
    {
        $message = 'Test error message';
        $context = [
            'component' => 'local_test',
            'string'    => 'test_string',
            'line'      => 42,
        ];

        $exception = new StringValidationException($message, $context);

        $formattedMessage = $exception->getFormattedMessage();

        $this->assertStringContainsString($message, $formattedMessage);
        $this->assertStringContainsString('component: local_test', $formattedMessage);
        $this->assertStringContainsString('string: test_string', $formattedMessage);
        $this->assertStringContainsString('line: 42', $formattedMessage);
    }

    /**
     * Test getFormattedMessage with empty context.
     */
    public function testGetFormattedMessageWithEmptyContext(): void
    {
        $message   = 'Test error message';
        $exception = new StringValidationException($message);

        $formattedMessage = $exception->getFormattedMessage();

        $this->assertSame($message, $formattedMessage);
    }

    /**
     * Test getFormattedMessage with non-scalar context values.
     */
    public function testGetFormattedMessageWithNonScalarContext(): void
    {
        $message = 'Test error message';
        $context = [
            'component'    => 'local_test',
            'array_value'  => ['item1', 'item2'],
            'object_value' => new \stdClass(),
            'string_value' => 'test_string',
        ];

        $exception = new StringValidationException($message, $context);

        $formattedMessage = $exception->getFormattedMessage();

        $this->assertStringContainsString($message, $formattedMessage);
        $this->assertStringContainsString('component: local_test', $formattedMessage);
        $this->assertStringContainsString('string_value: test_string', $formattedMessage);
        $this->assertStringNotContainsString('array_value', $formattedMessage);
        $this->assertStringNotContainsString('object_value', $formattedMessage);
    }

    /**
     * Test error static method.
     */
    public function testErrorStaticMethod(): void
    {
        $message  = 'Error message';
        $context  = ['component' => 'local_test'];
        $previous = new \RuntimeException('Previous error');

        $exception = StringValidationException::error($message, $context, $previous);

        $this->assertInstanceOf(StringValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertTrue($exception->isError());
    }

    /**
     * Test error static method with minimal parameters.
     */
    public function testErrorStaticMethodWithMinimalParameters(): void
    {
        $message = 'Error message';

        $exception = StringValidationException::error($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame([], $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test warning static method.
     */
    public function testWarningStaticMethod(): void
    {
        $message  = 'Warning message';
        $context  = ['component' => 'local_test'];
        $previous = new \RuntimeException('Previous error');

        $exception = StringValidationException::warning($message, $context, $previous);

        $this->assertInstanceOf(StringValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
        $this->assertSame('warning', $exception->getSeverity());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertTrue($exception->isWarning());
    }

    /**
     * Test warning static method with minimal parameters.
     */
    public function testWarningStaticMethodWithMinimalParameters(): void
    {
        $message = 'Warning message';

        $exception = StringValidationException::warning($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame([], $exception->getContext());
        $this->assertSame('warning', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test info static method.
     */
    public function testInfoStaticMethod(): void
    {
        $message  = 'Info message';
        $context  = ['component' => 'local_test'];
        $previous = new \RuntimeException('Previous error');

        $exception = StringValidationException::info($message, $context, $previous);

        $this->assertInstanceOf(StringValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
        $this->assertSame('info', $exception->getSeverity());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertFalse($exception->isWarning());
        $this->assertFalse($exception->isError());
    }

    /**
     * Test info static method with minimal parameters.
     */
    public function testInfoStaticMethodWithMinimalParameters(): void
    {
        $message = 'Info message';

        $exception = StringValidationException::info($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame([], $exception->getContext());
        $this->assertSame('info', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }
}
