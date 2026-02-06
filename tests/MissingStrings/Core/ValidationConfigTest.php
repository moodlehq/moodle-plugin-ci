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

use MoodlePluginCI\MissingStrings\ValidationConfig;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for ValidationConfig class.
 *
 * @covers \MoodlePluginCI\MissingStrings\ValidationConfig
 */
class ValidationConfigTest extends MissingStringsTestCase
{
    /**
     * Test default constructor values.
     */
    public function testConstructorWithDefaultsSetsCorrectValues(): void
    {
        $config = new ValidationConfig();

        $this->assertSame('en', $config->getLanguage(), 'Default language should be en');
        $this->assertFalse($config->isStrict(), 'Default strict mode should be false');
        $this->assertFalse($config->shouldCheckUnused(), 'Default unused checking should be false');
        $this->assertEmpty($config->getExcludePatterns(), 'Default exclude patterns should be empty');
        $this->assertEmpty($config->getCustomCheckers(), 'Default custom checkers should be empty');
        $this->assertTrue($config->shouldUseDefaultCheckers(), 'Default checkers should be enabled by default');
        $this->assertFalse($config->isDebugEnabled(), 'Default debug mode should be false');
    }

    /**
     * Test constructor with custom values.
     */
    public function testConstructorWithCustomValuesSetsCorrectValues(): void
    {
        $excludePatterns = ['test_*', 'debug_*'];
        $customCheckers  = ['MyChecker', 'AnotherChecker'];

        $config = new ValidationConfig(
            'es',
            true,
            true,
            $excludePatterns,
            $customCheckers,
            false,
            true
        );

        $this->assertSame('es', $config->getLanguage(), 'Language should be set to es');
        $this->assertTrue($config->isStrict(), 'Strict mode should be enabled');
        $this->assertTrue($config->shouldCheckUnused(), 'Unused checking should be enabled');
        $this->assertSame($excludePatterns, $config->getExcludePatterns(), 'Exclude patterns should match');
        $this->assertSame($customCheckers, $config->getCustomCheckers(), 'Custom checkers should match');
        $this->assertFalse($config->shouldUseDefaultCheckers(), 'Default checkers should be disabled');
        $this->assertTrue($config->isDebugEnabled(), 'Debug mode should be enabled');
    }

    /**
     * Test fromOptions factory method with minimal options.
     */
    public function testFromOptionsWithMinimalOptionsCreatesConfigWithDefaults(): void
    {
        $options = [];
        $config  = ValidationConfig::fromOptions($options);

        $this->assertSame('en', $config->getLanguage(), 'Should use default language');
        $this->assertFalse($config->isStrict(), 'Should use default strict mode');
        $this->assertFalse($config->shouldCheckUnused(), 'Should use default unused checking');
        $this->assertEmpty($config->getExcludePatterns(), 'Should use default exclude patterns');
        $this->assertFalse($config->isDebugEnabled(), 'Should use default debug mode');
    }

    /**
     * Test fromOptions factory method with all options.
     */
    public function testFromOptionsWithAllOptionsCreatesConfigCorrectly(): void
    {
        $options = [
            'lang'             => 'fr',
            'strict'           => true,
            'unused'           => true,
            'exclude-patterns' => 'test_*,debug_*,temp_*',
            'debug'            => true,
        ];

        $config = ValidationConfig::fromOptions($options);

        $this->assertSame('fr', $config->getLanguage(), 'Should set language from options');
        $this->assertTrue($config->isStrict(), 'Should set strict mode from options');
        $this->assertTrue($config->shouldCheckUnused(), 'Should set unused checking from options');
        $this->assertTrue($config->isDebugEnabled(), 'Should set debug mode from options');

        $expectedPatterns = ['test_*', 'debug_*', 'temp_*'];
        $this->assertSame($expectedPatterns, $config->getExcludePatterns(), 'Should parse exclude patterns correctly');
    }

    /**
     * Test fromOptions with empty exclude patterns.
     */
    public function testFromOptionsWithEmptyExcludePatternsHandlesCorrectly(): void
    {
        $options = [
            'exclude-patterns' => '',
        ];

        $config = ValidationConfig::fromOptions($options);
        $this->assertEmpty($config->getExcludePatterns(), 'Empty exclude patterns should result in empty array');
    }

    /**
     * Test fromOptions with exclude patterns containing spaces.
     */
    public function testFromOptionsWithExcludePatternsWithSpacesTrimsCorrectly(): void
    {
        $options = [
            'exclude-patterns' => ' test_* , debug_* , temp_* ',
        ];

        $config           = ValidationConfig::fromOptions($options);
        $expectedPatterns = ['test_*', 'debug_*', 'temp_*'];
        $this->assertSame($expectedPatterns, $config->getExcludePatterns(), 'Should trim spaces from patterns');
    }

    /**
     * Test shouldExcludeString with no patterns.
     */
    public function testShouldExcludeStringWithNoPatternsReturnsFalse(): void
    {
        $config = new ValidationConfig();

        $this->assertFalse($config->shouldExcludeString('any_string'), 'Should not exclude when no patterns');
        $this->assertFalse($config->shouldExcludeString('test_string'), 'Should not exclude when no patterns');
    }

    /**
     * Test shouldExcludeString with exact match patterns.
     */
    public function testShouldExcludeStringWithExactMatchPatternsReturnsCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['exact_match', 'another_exact']);

        $this->assertTrue($config->shouldExcludeString('exact_match'), 'Should exclude exact match');
        $this->assertTrue($config->shouldExcludeString('another_exact'), 'Should exclude another exact match');
        $this->assertFalse($config->shouldExcludeString('no_match'), 'Should not exclude non-matching string');
        $this->assertFalse($config->shouldExcludeString('exact_match_extended'), 'Should not exclude partial match');
    }

    /**
     * Test shouldExcludeString with wildcard patterns.
     */
    public function testShouldExcludeStringWithWildcardPatternsReturnsCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['test_*', '*_debug', 'temp*file']);

        // Test prefix wildcard
        $this->assertTrue($config->shouldExcludeString('test_string'), 'Should exclude prefix wildcard match');
        $this->assertTrue($config->shouldExcludeString('test_'), 'Should exclude prefix wildcard match');
        $this->assertFalse($config->shouldExcludeString('testing_string'), 'Should not exclude non-matching prefix');

        // Test suffix wildcard
        $this->assertTrue($config->shouldExcludeString('my_debug'), 'Should exclude suffix wildcard match');
        $this->assertTrue($config->shouldExcludeString('_debug'), 'Should exclude suffix wildcard match');
        $this->assertFalse($config->shouldExcludeString('debug_mode'), 'Should not exclude non-matching suffix');

        // Test middle wildcard - fnmatch matches anything between temp and file
        $this->assertTrue($config->shouldExcludeString('tempfile'), 'Should exclude middle wildcard match');
        $this->assertTrue($config->shouldExcludeString('temp123file'), 'Should exclude middle wildcard match');
        $this->assertTrue($config->shouldExcludeString('temporary_file'), 'Should exclude middle wildcard match (fnmatch behavior)');
    }

    /**
     * Test shouldExcludeString with complex patterns.
     */
    public function testShouldExcludeStringWithComplexPatternsReturnsCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['*test*', '???_temp', 'a*b*c']);

        // Test multiple wildcards
        $this->assertTrue($config->shouldExcludeString('mytest'), 'Should match *test*');
        $this->assertTrue($config->shouldExcludeString('teststring'), 'Should match *test*');
        $this->assertTrue($config->shouldExcludeString('myteststring'), 'Should match *test*');

        // Test question mark wildcards
        $this->assertTrue($config->shouldExcludeString('abc_temp'), 'Should match ???_temp');
        $this->assertTrue($config->shouldExcludeString('123_temp'), 'Should match ???_temp');
        $this->assertFalse($config->shouldExcludeString('ab_temp'), 'Should not match ???_temp (too short)');
        $this->assertFalse($config->shouldExcludeString('abcd_temp'), 'Should not match ???_temp (too long)');

        // Test complex pattern
        $this->assertTrue($config->shouldExcludeString('abc'), 'Should match a*b*c');
        $this->assertTrue($config->shouldExcludeString('a123b456c'), 'Should match a*b*c');
        $this->assertFalse($config->shouldExcludeString('ab'), 'Should not match a*b*c');
    }

    /**
     * Test shouldExcludeString with case sensitivity.
     */
    public function testShouldExcludeStringCaseSensitiveReturnsCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['Test_*', 'DEBUG']);

        $this->assertTrue($config->shouldExcludeString('Test_string'), 'Should match case-sensitive pattern');
        $this->assertFalse($config->shouldExcludeString('test_string'), 'Should not match different case');
        $this->assertTrue($config->shouldExcludeString('DEBUG'), 'Should match exact case');
        $this->assertFalse($config->shouldExcludeString('debug'), 'Should not match different case');
    }

    /**
     * Test shouldExcludeString with empty string.
     */
    public function testShouldExcludeStringWithEmptyStringHandlesCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['*', 'test_*']);

        $this->assertTrue($config->shouldExcludeString(''), 'Empty string should match * pattern');

        $config2 = new ValidationConfig('en', false, false, ['test_*']);
        $this->assertFalse($config2->shouldExcludeString(''), 'Empty string should not match specific patterns');
    }

    /**
     * Test shouldExcludeString with special characters.
     */
    public function testShouldExcludeStringWithSpecialCharactersHandlesCorrectly(): void
    {
        $config = new ValidationConfig('en', false, false, ['test-*', 'debug_*_info', 'temp.*.log']);

        $this->assertTrue($config->shouldExcludeString('test-string'), 'Should handle hyphens');
        $this->assertTrue($config->shouldExcludeString('debug_special_info'), 'Should handle underscores and wildcards');
        $this->assertTrue($config->shouldExcludeString('temp.error.log'), 'Should handle dots');
    }

    /**
     * Test shouldExcludeString performance with many patterns.
     */
    public function testShouldExcludeStringWithManyPatternsPerformsReasonably(): void
    {
        $patterns = [];
        for ($i = 0; $i < 100; ++$i) {
            $patterns[] = "pattern_{$i}_*";
        }

        $config = new ValidationConfig('en', false, false, $patterns);

        $startTime = microtime(true);

        // Test multiple strings
        for ($i = 0; $i < 50; ++$i) {
            $config->shouldExcludeString("test_string_{$i}");
        }

        $endTime  = microtime(true);
        $duration = $endTime - $startTime;

        // Should complete in reasonable time (less than 1 second for this test)
        $this->assertLessThan(1.0, $duration, 'Pattern matching should be reasonably fast');
    }

    /**
     * Test that configuration is immutable after creation.
     */
    public function testConfigurationIsImmutable(): void
    {
        $originalPatterns = ['test_*'];
        $config           = new ValidationConfig('en', false, false, $originalPatterns);

        // Get patterns and modify the returned array
        $patterns   = $config->getExcludePatterns();
        $patterns[] = 'new_pattern';

        // Original config should be unchanged
        $this->assertSame($originalPatterns, $config->getExcludePatterns(), 'Config should be immutable');
    }
}
