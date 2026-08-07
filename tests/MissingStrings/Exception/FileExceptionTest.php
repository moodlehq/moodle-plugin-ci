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

use MoodlePluginCI\MissingStrings\Exception\FileException;
use MoodlePluginCI\MissingStrings\Exception\StringValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FileException class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Exception\FileException
 */
class FileExceptionTest extends TestCase
{
    /**
     * Test basic constructor.
     */
    public function testConstructor(): void
    {
        $filePath = '/path/to/file.php';
        $message  = 'Test error message';
        $context  = ['component' => 'local_test'];

        $exception = new FileException($filePath, $message, $context);

        $this->assertInstanceOf(StringValidationException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());

        $expectedContext = array_merge($context, ['file' => $filePath]);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
    }

    /**
     * Test constructor with all parameters.
     */
    public function testConstructorWithAllParameters(): void
    {
        $filePath = '/path/to/file.php';
        $message  = 'Test error message';
        $context  = ['component' => 'local_test'];
        $severity = 'warning';
        $previous = new \RuntimeException('Previous error');

        $exception = new FileException($filePath, $message, $context, $severity, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame($severity, $exception->getSeverity());
        $this->assertSame($previous, $exception->getPrevious());

        $expectedContext = array_merge($context, ['file' => $filePath]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test constructor with minimal parameters.
     */
    public function testConstructorWithMinimalParameters(): void
    {
        $filePath = '/path/to/file.php';

        $exception = new FileException($filePath);

        $this->assertSame('', $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame(['file' => $filePath], $exception->getContext());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test fileNotFound static method.
     */
    public function testFileNotFound(): void
    {
        $filePath = '/path/to/missing.php';
        $context  = ['component' => 'local_test'];

        $exception = FileException::fileNotFound($filePath, $context);

        $this->assertInstanceOf(FileException::class, $exception);
        $this->assertSame("File not found: {$filePath}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame('error', $exception->getSeverity());

        $expectedContext = array_merge($context, ['file' => $filePath]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test fileNotFound static method without context.
     */
    public function testFileNotFoundWithoutContext(): void
    {
        $filePath = '/path/to/missing.php';

        $exception = FileException::fileNotFound($filePath);

        $this->assertSame("File not found: {$filePath}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame(['file' => $filePath], $exception->getContext());
    }

    /**
     * Test fileNotReadable static method.
     */
    public function testFileNotReadable(): void
    {
        $filePath = '/path/to/unreadable.php';
        $context  = ['component' => 'local_test', 'permissions' => '000'];

        $exception = FileException::fileNotReadable($filePath, $context);

        $this->assertInstanceOf(FileException::class, $exception);
        $this->assertSame("File not readable: {$filePath}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame('error', $exception->getSeverity());

        $expectedContext = array_merge($context, ['file' => $filePath]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test fileNotReadable static method without context.
     */
    public function testFileNotReadableWithoutContext(): void
    {
        $filePath = '/path/to/unreadable.php';

        $exception = FileException::fileNotReadable($filePath);

        $this->assertSame("File not readable: {$filePath}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame(['file' => $filePath], $exception->getContext());
    }

    /**
     * Test parsingError static method.
     */
    public function testParsingError(): void
    {
        $filePath = '/path/to/invalid.php';
        $reason   = 'Syntax error on line 10';
        $context  = ['component' => 'local_test', 'line' => 10];
        $previous = new \ParseError('Parse error');

        $exception = FileException::parsingError($filePath, $reason, $context, $previous);

        $this->assertInstanceOf(FileException::class, $exception);
        $this->assertSame("Failed to parse file {$filePath}: {$reason}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame('error', $exception->getSeverity());
        $this->assertSame($previous, $exception->getPrevious());

        $expectedContext = array_merge($context, ['file' => $filePath]);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    /**
     * Test parsingError static method with minimal parameters.
     */
    public function testParsingErrorWithMinimalParameters(): void
    {
        $filePath = '/path/to/invalid.php';
        $reason   = 'Syntax error';

        $exception = FileException::parsingError($filePath, $reason);

        $this->assertSame("Failed to parse file {$filePath}: {$reason}", $exception->getMessage());
        $this->assertSame($filePath, $exception->getFilePath());
        $this->assertSame(['file' => $filePath], $exception->getContext());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test getFilePath method.
     */
    public function testGetFilePath(): void
    {
        $filePath  = '/some/path/to/file.php';
        $exception = new FileException($filePath, 'Test message');

        $this->assertSame($filePath, $exception->getFilePath());
    }
}
