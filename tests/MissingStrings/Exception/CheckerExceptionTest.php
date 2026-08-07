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

use MoodlePluginCI\MissingStrings\Exception\CheckerException;
use MoodlePluginCI\MissingStrings\Exception\StringValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CheckerException class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Exception\CheckerException
 */
class CheckerExceptionTest extends TestCase
{
    /**
     * Test basic constructor.
     */
    public function testConstructor(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Test error message';
        $context     = ['component' => 'local_test'];
        $severity    = 'warning';
        $previous    = new \RuntimeException('Previous error');

        $exception = new CheckerException($checkerName, $message, $context, $severity, $previous);

        $this->assertInstanceOf(StringValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame($severity, $exception->getSeverity());
        $this->assertSame($previous, $exception->getPrevious());

        $expectedContext = array_merge($context, ['checker' => $checkerName]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test constructor with minimal parameters.
     */
    public function testConstructorWithMinimalParameters(): void
    {
        $checkerName = 'TestChecker';

        $exception = new CheckerException($checkerName);

        $this->assertSame('', $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame(['checker' => $checkerName], $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test constructor with default values.
     */
    public function testConstructorWithDefaultValues(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Test message';
        $context     = ['key' => 'value'];

        $exception = new CheckerException($checkerName, $message, $context);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());

        $expectedContext = array_merge($context, ['checker' => $checkerName]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test getCheckerName method.
     */
    public function testGetCheckerName(): void
    {
        $checkerName = 'CapabilitiesChecker';
        $exception   = new CheckerException($checkerName, 'Test message');

        $this->assertSame($checkerName, $exception->getCheckerName());
    }

    /**
     * Test checkerError static method.
     */
    public function testCheckerErrorStaticMethod(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Error message';
        $context     = ['component' => 'local_test'];
        $previous    = new \RuntimeException('Previous error');

        $exception = CheckerException::checkerError($checkerName, $message, $context, $previous);

        $this->assertInstanceOf(CheckerException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertSame($previous, $exception->getPrevious());

        $expectedContext = array_merge($context, ['checker' => $checkerName]);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertTrue($exception->isError());
    }

    /**
     * Test checkerError static method with minimal parameters.
     */
    public function testCheckerErrorStaticMethodWithMinimalParameters(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Error message';

        $exception = CheckerException::checkerError($checkerName, $message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame(['checker' => $checkerName], $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test checkerWarning static method.
     */
    public function testCheckerWarningStaticMethod(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Warning message';
        $context     = ['component' => 'local_test'];
        $previous    = new \RuntimeException('Previous error');

        $exception = CheckerException::checkerWarning($checkerName, $message, $context, $previous);

        $this->assertInstanceOf(CheckerException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame('warning', $exception->getSeverity());
        $this->assertSame($previous, $exception->getPrevious());

        $expectedContext = array_merge($context, ['checker' => $checkerName]);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertTrue($exception->isWarning());
    }

    /**
     * Test checkerWarning static method with minimal parameters.
     */
    public function testCheckerWarningStaticMethodWithMinimalParameters(): void
    {
        $checkerName = 'TestChecker';
        $message     = 'Warning message';

        $exception = CheckerException::checkerWarning($checkerName, $message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($checkerName, $exception->getCheckerName());
        $this->assertSame(['checker' => $checkerName], $exception->getContext());
        $this->assertSame('warning', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test that checker name is added to context automatically.
     */
    public function testCheckerNameAddedToContext(): void
    {
        $checkerName = 'ExampleChecker';
        $context     = ['existing' => 'value', 'another' => 'item'];

        $exception = new CheckerException($checkerName, 'Test', $context);

        $resultContext = $exception->getContext();
        $this->assertArrayHasKey('checker', $resultContext);
        $this->assertSame($checkerName, $resultContext['checker']);
        $this->assertSame('value', $resultContext['existing']);
        $this->assertSame('item', $resultContext['another']);
    }

    /**
     * Test that checker name overwrites existing 'checker' key in context.
     */
    public function testCheckerNameOverwritesContextChecker(): void
    {
        $checkerName = 'NewChecker';
        $context     = ['checker' => 'OldChecker', 'other' => 'value'];

        $exception = new CheckerException($checkerName, 'Test', $context);

        $resultContext = $exception->getContext();
        $this->assertSame($checkerName, $resultContext['checker']);
        $this->assertSame('value', $resultContext['other']);
    }
}
