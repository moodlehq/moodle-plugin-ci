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

use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for StringContext class.
 *
 * @covers \MoodlePluginCI\MissingStrings\StringContext
 */
class StringContextTest extends MissingStringsTestCase
{
    /**
     * Test default constructor with no parameters.
     */
    public function testConstructorWithNoParametersSetsNullValues(): void
    {
        $context = new StringContext();

        $this->assertNull($context->getFile(), 'File should be null by default');
        $this->assertNull($context->getLine(), 'Line should be null by default');
        $this->assertNull($context->getDescription(), 'Description should be null by default');
        $this->assertFalse($context->hasLocation(), 'Should not have location with null values');
    }

    /**
     * Test constructor with all parameters.
     */
    public function testConstructorWithAllParametersSetsCorrectValues(): void
    {
        $file        = 'test.php';
        $line        = 42;
        $description = 'Test description';

        $context = new StringContext($file, $line, $description);

        $this->assertSame($file, $context->getFile(), 'File should be set correctly');
        $this->assertSame($line, $context->getLine(), 'Line should be set correctly');
        $this->assertSame($description, $context->getDescription(), 'Description should be set correctly');
        $this->assertTrue($context->hasLocation(), 'Should have location with file and line');
    }

    /**
     * Test constructor with partial parameters.
     */
    public function testConstructorWithPartialParametersSetsCorrectValues(): void
    {
        $file    = 'partial.php';
        $context = new StringContext($file);

        $this->assertSame($file, $context->getFile(), 'File should be set correctly');
        $this->assertNull($context->getLine(), 'Line should be null');
        $this->assertNull($context->getDescription(), 'Description should be null');
        $this->assertFalse($context->hasLocation(), 'Should not have location without line number');
    }

    /**
     * Test constructor with file and line only.
     */
    public function testConstructorWithFileAndLineSetsCorrectValues(): void
    {
        $file = 'location.php';
        $line = 123;

        $context = new StringContext($file, $line);

        $this->assertSame($file, $context->getFile(), 'File should be set correctly');
        $this->assertSame($line, $context->getLine(), 'Line should be set correctly');
        $this->assertNull($context->getDescription(), 'Description should be null');
        $this->assertTrue($context->hasLocation(), 'Should have location with file and line');
    }

    /**
     * Test setLine method.
     */
    public function testSetLineUpdatesLineNumber(): void
    {
        $context = new StringContext('test.php');
        $this->assertFalse($context->hasLocation(), 'Should not have location initially');

        $context->setLine(100);

        $this->assertSame(100, $context->getLine(), 'Line should be updated');
        $this->assertTrue($context->hasLocation(), 'Should have location after setting line');
    }

    /**
     * Test setLine with zero line number.
     */
    public function testSetLineWithZeroSetsCorrectly(): void
    {
        $context = new StringContext('test.php');
        $context->setLine(0);

        $this->assertSame(0, $context->getLine(), 'Line should be set to 0');
        $this->assertTrue($context->hasLocation(), 'Should have location with line 0');
    }

    /**
     * Test setLine with negative line number.
     */
    public function testSetLineWithNegativeSetsCorrectly(): void
    {
        $context = new StringContext('test.php');
        $context->setLine(-1);

        $this->assertSame(-1, $context->getLine(), 'Line should be set to -1');
        $this->assertTrue($context->hasLocation(), 'Should have location with negative line');
    }

    /**
     * Test hasLocation with various combinations.
     */
    public function testHasLocationWithVariousCombinationsReturnsCorrectly(): void
    {
        // No file, no line
        $context1 = new StringContext();
        $this->assertFalse($context1->hasLocation(), 'Should not have location with null file and line');

        // File only, no line
        $context2 = new StringContext('file.php');
        $this->assertFalse($context2->hasLocation(), 'Should not have location with only file');

        // No file, line only
        $context3 = new StringContext(null, 42);
        $this->assertFalse($context3->hasLocation(), 'Should not have location with only line');

        // Both file and line
        $context4 = new StringContext('file.php', 42);
        $this->assertTrue($context4->hasLocation(), 'Should have location with both file and line');

        // Empty string file with line
        $context5 = new StringContext('', 42);
        $this->assertFalse($context5->hasLocation(), 'Should not have location with empty file');
    }

    /**
     * Test toArray method with complete context.
     */
    public function testToArrayWithCompleteContextReturnsCorrectArray(): void
    {
        $file        = 'complete.php';
        $line        = 55;
        $description = 'Complete context description';

        $context = new StringContext($file, $line, $description);
        $array   = $context->toArray();

        $expected = [
            'file'    => $file,
            'line'    => $line,
            'context' => $description,
        ];

        $this->assertSame($expected, $array, 'Array should contain all context information');
    }

    /**
     * Test toArray method with location only.
     */
    public function testToArrayWithLocationOnlyReturnsLocationArray(): void
    {
        $file = 'location.php';
        $line = 77;

        $context = new StringContext($file, $line);
        $array   = $context->toArray();

        $expected = [
            'file' => $file,
            'line' => $line,
        ];

        $this->assertSame($expected, $array, 'Array should contain only location information');
    }

    /**
     * Test toArray method with description only.
     */
    public function testToArrayWithDescriptionOnlyReturnsDescriptionArray(): void
    {
        $description = 'Only description';

        $context = new StringContext(null, null, $description);
        $array   = $context->toArray();

        $expected = [
            'context' => $description,
        ];

        $this->assertSame($expected, $array, 'Array should contain only description');
    }

    /**
     * Test toArray method with no context information.
     */
    public function testToArrayWithNoContextReturnsEmptyArray(): void
    {
        $context = new StringContext();
        $array   = $context->toArray();

        $this->assertEmpty($array, 'Array should be empty with no context information');
    }

    /**
     * Test toArray method with empty string values.
     */
    public function testToArrayWithEmptyStringValuesHandlesCorrectly(): void
    {
        $context = new StringContext('', null, '');
        $array   = $context->toArray();

        // Empty strings should not be included
        $this->assertEmpty($array, 'Array should be empty with empty string values');
    }

    /**
     * Test __toString method with complete context.
     */
    public function testToStringWithCompleteContextReturnsFormattedString(): void
    {
        $file        = 'example.php';
        $line        = 99;
        $description = 'Example description';

        $context = new StringContext($file, $line, $description);
        $result  = (string) $context;

        $expected = 'Example description in example.php:99';
        $this->assertSame($expected, $result, 'String representation should be formatted correctly');
    }

    /**
     * Test __toString method with location only.
     */
    public function testToStringWithLocationOnlyReturnsLocationString(): void
    {
        $file = 'location.php';
        $line = 88;

        $context = new StringContext($file, $line);
        $result  = (string) $context;

        $expected = 'in location.php:88';
        $this->assertSame($expected, $result, 'String representation should show location only');
    }

    /**
     * Test __toString method with description only.
     */
    public function testToStringWithDescriptionOnlyReturnsDescriptionString(): void
    {
        $description = 'Just a description';

        $context = new StringContext(null, null, $description);
        $result  = (string) $context;

        $expected = 'Just a description';
        $this->assertSame($expected, $result, 'String representation should show description only');
    }

    /**
     * Test __toString method with no context.
     */
    public function testToStringWithNoContextReturnsEmptyString(): void
    {
        $context = new StringContext();
        $result  = (string) $context;

        $this->assertSame('', $result, 'String representation should be empty with no context');
    }

    /**
     * Test __toString method with empty string values.
     */
    public function testToStringWithEmptyStringValuesHandlesCorrectly(): void
    {
        $context = new StringContext('', null, '');
        $result  = (string) $context;

        $this->assertSame('', $result, 'String representation should be empty with empty string values');
    }

    /**
     * Test context with special characters in file names.
     */
    public function testContextWithSpecialCharactersHandlesCorrectly(): void
    {
        $file        = 'special-file_name.with.dots.php';
        $line        = 123;
        $description = 'Description with special chars: @#$%^&*()';

        $context = new StringContext($file, $line, $description);

        $this->assertSame($file, $context->getFile(), 'Should handle special characters in file name');
        $this->assertSame($description, $context->getDescription(), 'Should handle special characters in description');

        $stringResult = (string) $context;
        $this->assertStringContainsString($file, $stringResult, 'String representation should contain file name');
        $this->assertStringContainsString($description, $stringResult, 'String representation should contain description');
    }

    /**
     * Test context with Unicode characters.
     */
    public function testContextWithUnicodeCharactersHandlesCorrectly(): void
    {
        $file        = 'файл.php'; // Cyrillic characters
        $line        = 456;
        $description = 'Описание с юникодом'; // Cyrillic description

        $context = new StringContext($file, $line, $description);

        $this->assertSame($file, $context->getFile(), 'Should handle Unicode characters in file name');
        $this->assertSame($description, $context->getDescription(), 'Should handle Unicode characters in description');

        $array = $context->toArray();
        $this->assertSame($file, $array['file'], 'Array should contain Unicode file name');
        $this->assertSame($description, $array['context'], 'Array should contain Unicode description');
    }

    /**
     * Test context with very long strings.
     */
    public function testContextWithLongStringsHandlesCorrectly(): void
    {
        $longFile        = str_repeat('very_long_file_name_', 50) . '.php';
        $longDescription = str_repeat('This is a very long description that goes on and on. ', 100);

        $context = new StringContext($longFile, 789, $longDescription);

        $this->assertSame($longFile, $context->getFile(), 'Should handle long file names');
        $this->assertSame($longDescription, $context->getDescription(), 'Should handle long descriptions');

        // Test that toString doesn't break with long strings
        $stringResult = (string) $context;
        $this->assertStringContainsString($longFile, $stringResult, 'String representation should contain long file name');
        $this->assertStringContainsString($longDescription, $stringResult, 'String representation should contain long description');
    }

    /**
     * Test immutability of context after creation.
     */
    public function testContextIsImmutableExceptForSetLine(): void
    {
        $originalFile        = 'original.php';
        $originalLine        = 100;
        $originalDescription = 'Original description';

        $context = new StringContext($originalFile, $originalLine, $originalDescription);

        // The only way to modify context is through setLine
        $context->setLine(200);

        $this->assertSame($originalFile, $context->getFile(), 'File should remain unchanged');
        $this->assertSame(200, $context->getLine(), 'Line should be updated');
        $this->assertSame($originalDescription, $context->getDescription(), 'Description should remain unchanged');
    }

    /**
     * Test edge cases with boundary values.
     */
    public function testContextWithBoundaryValuesHandlesCorrectly(): void
    {
        // Test with maximum integer value
        $maxInt   = PHP_INT_MAX;
        $context1 = new StringContext('test.php', $maxInt);
        $this->assertSame($maxInt, $context1->getLine(), 'Should handle maximum integer line number');

        // Test with minimum integer value
        $minInt   = PHP_INT_MIN;
        $context2 = new StringContext('test.php', $minInt);
        $this->assertSame($minInt, $context2->getLine(), 'Should handle minimum integer line number');

        // Test with very short strings
        $context3 = new StringContext('a', 1, 'b');
        $this->assertSame('a', $context3->getFile(), 'Should handle single character file name');
        $this->assertSame('b', $context3->getDescription(), 'Should handle single character description');
    }

    /**
     * Test context behavior with null values after construction.
     */
    public function testContextWithNullValuesBehavesCorrectly(): void
    {
        // Test partial null values
        $context1 = new StringContext('file.php', null, 'description');
        $this->assertFalse($context1->hasLocation(), 'Should not have location with null line');

        $context2 = new StringContext(null, 42, 'description');
        $this->assertFalse($context2->hasLocation(), 'Should not have location with null file');

        // Test array conversion with null values
        $array1 = $context1->toArray();
        $this->assertArrayNotHasKey('file', $array1, 'Array should not contain null file');
        $this->assertArrayNotHasKey('line', $array1, 'Array should not contain null line');
        $this->assertArrayHasKey('context', $array1, 'Array should contain description');

        $array2 = $context2->toArray();
        $this->assertArrayNotHasKey('file', $array2, 'Array should not contain null file');
        $this->assertArrayNotHasKey('line', $array2, 'Array should not contain null line');
        $this->assertArrayHasKey('context', $array2, 'Array should contain description');
    }
}
