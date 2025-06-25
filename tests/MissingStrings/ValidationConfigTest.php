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

namespace MoodlePluginCI\Tests\MissingStrings;

use MoodlePluginCI\MissingStrings\ValidationConfig;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ValidationConfig class.
 *
 * @covers \MoodlePluginCI\MissingStrings\ValidationConfig
 */
class ValidationConfigTest extends TestCase
{
    /**
     * Test constructor with default values.
     */
    public function testConstructorWithDefaults(): void
    {
        $config = new ValidationConfig();

        $this->assertSame('en', $config->getLanguage());
        $this->assertFalse($config->isStrict());
        $this->assertFalse($config->shouldCheckUnused());
        $this->assertSame([], $config->getExcludePatterns());
        $this->assertSame([], $config->getCustomCheckers());
        $this->assertTrue($config->shouldUseDefaultCheckers());
        $this->assertFalse($config->isDebugEnabled());
    }

    /**
     * Test constructor with all parameters.
     */
    public function testConstructorWithAllParameters(): void
    {
        $language           = 'fr';
        $strict             = true;
        $checkUnused        = true;
        $excludePatterns    = ['pattern1', 'pattern2'];
        $customCheckers     = ['checker1', 'checker2'];
        $useDefaultCheckers = false;
        $debug              = true;

        $config = new ValidationConfig(
            $language,
            $strict,
            $checkUnused,
            $excludePatterns,
            $customCheckers,
            $useDefaultCheckers,
            $debug
        );

        $this->assertSame($language, $config->getLanguage());
        $this->assertTrue($config->isStrict());
        $this->assertTrue($config->shouldCheckUnused());
        $this->assertSame($excludePatterns, $config->getExcludePatterns());
        $this->assertSame($customCheckers, $config->getCustomCheckers());
        $this->assertFalse($config->shouldUseDefaultCheckers());
        $this->assertTrue($config->isDebugEnabled());
    }

    /**
     * Test fromOptions static method with empty options.
     */
    public function testFromOptionsWithEmptyOptions(): void
    {
        $config = ValidationConfig::fromOptions([]);

        $this->assertSame('en', $config->getLanguage());
        $this->assertFalse($config->isStrict());
        $this->assertFalse($config->shouldCheckUnused());
        $this->assertSame([], $config->getExcludePatterns());
        $this->assertSame([], $config->getCustomCheckers());
        $this->assertTrue($config->shouldUseDefaultCheckers());
        $this->assertFalse($config->isDebugEnabled());
    }

    /**
     * Test fromOptions static method with all options.
     */
    public function testFromOptionsWithAllOptions(): void
    {
        $options = [
            'lang'             => 'de',
            'strict'           => true,
            'unused'           => true,
            'exclude-patterns' => 'pattern1,pattern2,pattern3',
            'debug'            => true,
        ];

        $config = ValidationConfig::fromOptions($options);

        $this->assertSame('de', $config->getLanguage());
        $this->assertTrue($config->isStrict());
        $this->assertTrue($config->shouldCheckUnused());
        $this->assertSame(['pattern1', 'pattern2', 'pattern3'], $config->getExcludePatterns());
        $this->assertSame([], $config->getCustomCheckers());
        $this->assertTrue($config->shouldUseDefaultCheckers());
        $this->assertTrue($config->isDebugEnabled());
    }

    /**
     * Test fromOptions with exclude patterns containing spaces.
     */
    public function testFromOptionsWithSpacedExcludePatterns(): void
    {
        $options = [
            'exclude-patterns' => 'pattern1, pattern2 , pattern3,  pattern4  ',
        ];

        $config = ValidationConfig::fromOptions($options);

        $this->assertSame(['pattern1', 'pattern2', 'pattern3', 'pattern4'], $config->getExcludePatterns());
    }

    /**
     * Test fromOptions with empty exclude patterns.
     */
    public function testFromOptionsWithEmptyExcludePatterns(): void
    {
        $options = [
            'exclude-patterns' => '',
        ];

        $config = ValidationConfig::fromOptions($options);

        $this->assertSame([], $config->getExcludePatterns());
    }

    /**
     * Test fromOptions with exclude patterns containing empty values.
     */
    public function testFromOptionsWithEmptyExcludePatternValues(): void
    {
        $options = [
            'exclude-patterns' => 'pattern1,,pattern2,   ,pattern3',
        ];

        $config = ValidationConfig::fromOptions($options);

        // Empty values should be filtered out, but array_filter preserves keys
        $patterns = $config->getExcludePatterns();
        $this->assertContains('pattern1', $patterns);
        $this->assertContains('pattern2', $patterns);
        $this->assertContains('pattern3', $patterns);
        $this->assertCount(3, $patterns);
    }

    /**
     * Test fromOptions with single exclude pattern.
     */
    public function testFromOptionsWithSingleExcludePattern(): void
    {
        $options = [
            'exclude-patterns' => 'single_pattern',
        ];

        $config = ValidationConfig::fromOptions($options);

        $this->assertSame(['single_pattern'], $config->getExcludePatterns());
    }

    /**
     * Test shouldExcludeString with no patterns.
     */
    public function testShouldExcludeStringWithNoPatterns(): void
    {
        $config = new ValidationConfig();

        $this->assertFalse($config->shouldExcludeString('any_string'));
        $this->assertFalse($config->shouldExcludeString(''));
        $this->assertFalse($config->shouldExcludeString('test_string'));
    }

    /**
     * Test shouldExcludeString with exact match patterns.
     */
    public function testShouldExcludeStringWithExactMatch(): void
    {
        $config = new ValidationConfig('en', false, false, ['test_string', 'another_string']);

        $this->assertTrue($config->shouldExcludeString('test_string'));
        $this->assertTrue($config->shouldExcludeString('another_string'));
        $this->assertFalse($config->shouldExcludeString('different_string'));
        $this->assertFalse($config->shouldExcludeString('test_string_suffix'));
    }

    /**
     * Test shouldExcludeString with wildcard patterns.
     */
    public function testShouldExcludeStringWithWildcardPatterns(): void
    {
        $config = new ValidationConfig('en', false, false, ['test_*', '*_suffix', 'exact_match']);

        // Test prefix wildcard
        $this->assertTrue($config->shouldExcludeString('test_string'));
        $this->assertTrue($config->shouldExcludeString('test_anything'));
        $this->assertTrue($config->shouldExcludeString('test_'));
        $this->assertFalse($config->shouldExcludeString('prefix_test_string'));

        // Test suffix wildcard
        $this->assertTrue($config->shouldExcludeString('anything_suffix'));
        $this->assertTrue($config->shouldExcludeString('test_suffix'));
        $this->assertFalse($config->shouldExcludeString('suffix_other'));

        // Test exact match
        $this->assertTrue($config->shouldExcludeString('exact_match'));

        // Test no match
        $this->assertFalse($config->shouldExcludeString('no_match'));
    }

    /**
     * Test shouldExcludeString with complex wildcard patterns.
     */
    public function testShouldExcludeStringWithComplexPatterns(): void
    {
        $config = new ValidationConfig('en', false, false, ['*test*', 'prefix_*_suffix']);

        // Test patterns with wildcards in middle
        $this->assertTrue($config->shouldExcludeString('anything_test_anything'));
        $this->assertTrue($config->shouldExcludeString('test'));
        $this->assertTrue($config->shouldExcludeString('test_suffix'));
        $this->assertTrue($config->shouldExcludeString('prefix_test'));

        // Test specific pattern
        $this->assertTrue($config->shouldExcludeString('prefix_anything_suffix'));
        $this->assertTrue($config->shouldExcludeString('prefix__suffix'));
        $this->assertFalse($config->shouldExcludeString('prefix_suffix')); // No underscore in middle
        $this->assertFalse($config->shouldExcludeString('wrong_anything_suffix'));
    }

    /**
     * Test shouldExcludeString with case sensitivity.
     */
    public function testShouldExcludeStringCaseSensitivity(): void
    {
        $config = new ValidationConfig('en', false, false, ['Test_String', 'UPPER_CASE']);

        // fnmatch is case-sensitive
        $this->assertTrue($config->shouldExcludeString('Test_String'));
        $this->assertFalse($config->shouldExcludeString('test_string'));
        $this->assertFalse($config->shouldExcludeString('TEST_STRING'));

        $this->assertTrue($config->shouldExcludeString('UPPER_CASE'));
        $this->assertFalse($config->shouldExcludeString('upper_case'));
    }

    /**
     * Test shouldExcludeString with empty string.
     */
    public function testShouldExcludeStringWithEmptyString(): void
    {
        $config = new ValidationConfig('en', false, false, ['', '*']);

        $this->assertTrue($config->shouldExcludeString(''));     // Matches empty pattern
        $this->assertTrue($config->shouldExcludeString('test')); // Matches wildcard
    }

    /**
     * Test shouldExcludeString with special characters.
     */
    public function testShouldExcludeStringWithSpecialCharacters(): void
    {
        $config = new ValidationConfig('en', false, false, ['test*special', 'exact_match']);

        // Test basic wildcard functionality
        $this->assertTrue($config->shouldExcludeString('testAnythingspecial'));
        $this->assertTrue($config->shouldExcludeString('test_special'));
        $this->assertFalse($config->shouldExcludeString('test_other'));

        // Test exact match
        $this->assertTrue($config->shouldExcludeString('exact_match'));
        $this->assertFalse($config->shouldExcludeString('not_exact_match'));
    }

    /**
     * Test getters return correct types.
     */
    public function testGettersReturnCorrectTypes(): void
    {
        $config = new ValidationConfig(
            'es',
            true,
            true,
            ['pattern'],
            ['checker'],
            false,
            true
        );

        $this->assertIsString($config->getLanguage());
        $this->assertIsBool($config->isStrict());
        $this->assertIsBool($config->shouldCheckUnused());
        $this->assertIsArray($config->getExcludePatterns());
        $this->assertIsArray($config->getCustomCheckers());
        $this->assertIsBool($config->shouldUseDefaultCheckers());
        $this->assertIsBool($config->isDebugEnabled());
        $this->assertIsBool($config->shouldExcludeString('test'));
    }
}
