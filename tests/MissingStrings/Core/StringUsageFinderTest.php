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

use MoodlePluginCI\MissingStrings\StringUsageFinder;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for StringUsageFinder class.
 *
 * Tests the critical line detection functionality for various file types and patterns.
 */
class StringUsageFinderTest extends MissingStringsTestCase
{
    private StringUsageFinder $usageFinder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usageFinder = new StringUsageFinder();
    }

    /**
     * Test finding array key lines in database files.
     */
    public function testFindArrayKeyLineWithValidKeyReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "defined('MOODLE_INTERNAL') || die();\n\n" .
                   "\$capabilities = [\n" .
                   "    'mod/assign:addinstance' => [\n" .      // Line 6
                   "        'riskbitmask' => RISK_XSS,\n" .
                   "        'captype' => 'write',\n" .
                   "        'contextlevel' => CONTEXT_COURSE,\n" .
                   "        'archetypes' => [\n" .
                   "            'editingteacher' => CAP_ALLOW,\n" .
                   "            'manager' => CAP_ALLOW,\n" .
                   "        ],\n" .
                   "    ],\n" .
                   "    'mod/assign:grade' => [\n" .            // Line 15
                   "        'riskbitmask' => RISK_PERSONAL,\n" .
                   "        'captype' => 'write',\n" .
                   "    ],\n" .
                   "];\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:addinstance');
        $this->assertSame(6, $lineNumber);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:grade');
        $this->assertSame(15, $lineNumber);
    }

    /**
     * Test finding array key lines with various quote styles.
     */
    public function testFindArrayKeyLineWithDifferentQuoteStylesReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "\$definitions = [\n" .
                   "    'cache_one' => [\n" .                  // Line 4 - single quotes
                   "        'mode' => cache_store::MODE_APPLICATION,\n" .
                   "    ],\n" .
                   '    "cache_two" => [' . "\n" .             // Line 7 - double quotes
                   "        'mode' => cache_store::MODE_SESSION,\n" .
                   "    ],\n" .
                   "];\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'cache_one');
        $this->assertSame(4, $lineNumber);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'cache_two');
        $this->assertSame(7, $lineNumber);
    }

    /**
     * Test finding string literals in PHP code.
     */
    public function testFindStringLiteralLineWithGetStringReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "function some_function() {\n" .
                   "    echo 'Starting function';\n" .
                   "    \$str = get_string('modulename', 'mod_assign');\n" .  // Line 5
                   "    if (\$condition) {\n" .
                   "        throw new moodle_exception('error_occurred');\n" . // Line 7
                   "    }\n" .
                   "    return \$str;\n" .
                   "}\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'modulename');
        $this->assertSame(5, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'error_occurred');
        $this->assertSame(7, $lineNumber);
    }

    /**
     * Test finding lines with custom patterns.
     */
    public function testFindLineInFileWithCustomPatternReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "class privacy_provider implements \\core_privacy\\local\\metadata\\provider {\n" .
                   "    public static function get_metadata(collection \$collection): collection {\n" .
                   "        \$collection->add_database_table('assign_submission', [\n" .      // Line 5
                   "            'assignment' => 'privacy:metadata:assign_submission:assignment',\n" .
                   "            'userid' => 'privacy:metadata:assign_submission:userid',\n" .
                   "        ], 'privacy:metadata:assign_submission');\n" .
                   "        return \$collection;\n" .
                   "    }\n" .
                   "}\n";

        $filePath = $this->createTempFile($content);

        // Find class declaration
        $pattern    = '~class\s+privacy_provider~';
        $lineNumber = $this->usageFinder->findLineInFile($filePath, 'privacy_provider', $pattern);
        $this->assertSame(3, $lineNumber);

        // Find specific privacy string
        $pattern    = '~[\'"]privacy:metadata:assign_submission:assignment[\'"]~';
        $lineNumber = $this->usageFinder->findLineInFile($filePath, 'privacy:metadata:assign_submission:assignment', $pattern);
        $this->assertSame(6, $lineNumber);
    }

    /**
     * Test handling of files with complex capability names (containing forward slashes).
     */
    public function testFindArrayKeyLineWithSlashesInKeyReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "\$capabilities = [\n" .
                   "    'mod/assign:submit' => [\n" .           // Line 4
                   "        'captype' => 'write',\n" .
                   "    ],\n" .
                   "    'mod/forum:addquestion' => [\n" .       // Line 7
                   "        'captype' => 'write',\n" .
                   "    ],\n" .
                   "];\n";

        $filePath = $this->createTempFile($content);

        // This tests the fix where we changed delimiter from / to ~ to avoid escaping issues
        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:submit');
        $this->assertSame(4, $lineNumber);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/forum:addquestion');
        $this->assertSame(7, $lineNumber);
    }

    /**
     * Test with empty lines to ensure line numbering is correct.
     */
    public function testFindArrayKeyLineWithEmptyLinesPreservesCorrectLineNumbers(): void
    {
        $content = "<?php\n\n" .            // Lines 1-2
                   "\n" .                    // Line 3 (empty)
                   "defined('MOODLE_INTERNAL') || die();\n\n" .  // Lines 4-5
                   "\n" .                    // Line 6 (empty)
                   "\$capabilities = [\n" . // Line 7
                   "\n" .                    // Line 8 (empty)
                   "    'mod/test:view' => [\n" .    // Line 9
                   "        'captype' => 'read',\n" .
                   "    ],\n" .
                   "];\n";

        $filePath = $this->createTempFile($content);

        // This tests that we removed FILE_SKIP_EMPTY_LINES to preserve correct line numbers
        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/test:view');
        $this->assertSame(9, $lineNumber);
    }

    /**
     * Test handling of non-existent keys.
     */
    public function testFindArrayKeyLineWithNonExistentKeyReturnsNull(): void
    {
        $content = "<?php\n\n" .
                   "\$capabilities = [\n" .
                   "    'mod/assign:view' => [\n" .
                   "        'captype' => 'read',\n" .
                   "    ],\n" .
                   "];\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:nonexistent');
        $this->assertNull($lineNumber);
    }

    /**
     * Test handling of non-existent files.
     */
    public function testFindArrayKeyLineWithNonExistentFileReturnsNull(): void
    {
        $lineNumber = $this->usageFinder->findArrayKeyLine('/path/to/nonexistent/file.php', 'some_key');
        $this->assertNull($lineNumber);
    }

    /**
     * Test finding string literals with complex quote escaping.
     */
    public function testFindStringLiteralLineWithEscapedQuotesReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "function test() {\n" .
                   "    \$str1 = get_string('simple_string');\n" .        // Line 4
                   "    \$str2 = get_string(\"double_quote_string\");\n" .  // Line 5
                   "    \$str3 = get_string('string_with\\'_apostrophe');\n" . // Line 6 (escaped quote)
                   "    return \$str1;\n" .
                   "}\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'simple_string');
        $this->assertSame(4, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'double_quote_string');
        $this->assertSame(5, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, "string_with'_apostrophe");
        $this->assertSame(6, $lineNumber);
    }

    /**
     * Test performance with large files.
     */
    public function testFindArrayKeyLineWithLargeFilePerformsReasonably(): void
    {
        // Create a large file with many capabilities
        $content = "<?php\n\n\$capabilities = [\n";

        for ($i = 1; $i <= 1000; ++$i) {
            $content .= "    'mod/test:capability{$i}' => ['captype' => 'read'],\n";
        }

        // Add our target capability near the end
        $content .= "    'mod/test:target_capability' => ['captype' => 'write'],\n";  // Line 1004
        $content .= "];\n";

        $filePath = $this->createTempFile($content);

        $startTime  = microtime(true);
        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/test:target_capability');
        $endTime    = microtime(true);

        $this->assertSame(1004, $lineNumber);

        // Should complete in reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $endTime - $startTime, 'Line detection should be performant');
    }

    /**
     * Test handling of Unicode characters.
     */
    public function testFindStringLiteralLineWithUnicodeCharactersReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "function test_unicode() {\n" .
                   "    \$str1 = get_string('unicode_test');\n" .         // Line 4
                   "    \$str2 = get_string('тест_кириллица');\n" .        // Line 5 - Cyrillic
                   "    \$str3 = get_string('测试中文');\n" .                // Line 6 - Chinese
                   "    \$str4 = get_string('tëst_áccënts');\n" .          // Line 7 - Accents
                   "}\n";

        $filePath = $this->createTempFile($content);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'unicode_test');
        $this->assertSame(4, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'тест_кириллица');
        $this->assertSame(5, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, '测试中文');
        $this->assertSame(6, $lineNumber);

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'tëst_áccënts');
        $this->assertSame(7, $lineNumber);
    }

    /**
     * Test that exact matches are required (no partial matches).
     */
    public function testFindArrayKeyLineRequiresExactMatchNoPartialMatches(): void
    {
        $content = "<?php\n\n" .
                   "\$capabilities = [\n" .
                   "    'mod/assign:view' => ['captype' => 'read'],\n" .      // Line 4
                   "    'mod/assign:viewall' => ['captype' => 'read'],\n" .   // Line 5
                   "];\n";

        $filePath = $this->createTempFile($content);

        // Should find exact matches
        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:view');
        $this->assertSame(4, $lineNumber);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign:viewall');
        $this->assertSame(5, $lineNumber);

        // Should not find partial matches
        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'assign:view');
        $this->assertNull($lineNumber);

        $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, 'mod/assign');
        $this->assertNull($lineNumber);
    }

    /**
     * Test finding get_string() calls with optional parameters in PHP code.
     *
     * This tests the fix for the issue where get_string() calls with third or fourth
     * parameters were not being detected by the regex patterns.
     */
    public function testFindStringLiteralLineWithGetStringOptionalParametersReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "function test_get_string_patterns() {\n" .
                   "    // Basic 2-parameter calls (should always work)\n" .
                   "    \$str1 = get_string('basicstring', 'component');\n" .          // Line 5
                   "    \$str2 = get_string('anotherstring', 'mod_assign');\n" .       // Line 6
                   "\n" .
                   "    // 3-parameter calls (third parameter: \$a)\n" .
                   "    \$str3 = get_string('withparams', 'component', \$params);\n" .  // Line 9
                   "    \$str4 = get_string('withnull', 'component', null);\n" .       // Line 10
                   "    \$str5 = get_string('witharray', 'component', array('key' => 'value'));\n" . // Line 11
                   "    \$str6 = get_string('witharray2', 'component', ['key' => 'value']);\n" .     // Line 12
                   "\n" .
                   "    // 4-parameter calls (fourth parameter: \$lazyload)\n" .
                   "    \$str7 = get_string('lazyload', 'component', \$a, true);\n" .   // Line 15
                   "    \$str8 = get_string('lazyload2', 'component', null, false);\n" . // Line 16
                   "    \$str9 = get_string('lazyload3', 'component', array(), true);\n" . // Line 17
                   "\n" .
                   "    // Complex parameter calls\n" .
                   "    \$str10 = get_string('complex', 'component', \$this->getParams(), true);\n" . // Line 20
                   "    \$str11 = get_string('complex2', 'component', array('key' => 'value', 'another' => 'param'), false);\n" . // Line 21
                   "\n" .
                   "    // Various spacing patterns\n" .
                   "    \$str12 = get_string( 'spaced' , 'component' , \$params );\n" . // Line 24
                   "    \$str13 = get_string('nospace','component',\$params,true);\n" .  // Line 25
                   "}\n";

        $filePath = $this->createTempFile($content);

        // Test basic 2-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'basicstring');
        $this->assertSame(5, $lineNumber, 'Should find basic 2-parameter get_string call');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'anotherstring');
        $this->assertSame(6, $lineNumber, 'Should find another basic 2-parameter get_string call');

        // Test 3-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withparams');
        $this->assertSame(9, $lineNumber, 'Should find get_string call with variable parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withnull');
        $this->assertSame(10, $lineNumber, 'Should find get_string call with null parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'witharray');
        $this->assertSame(11, $lineNumber, 'Should find get_string call with array() parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'witharray2');
        $this->assertSame(12, $lineNumber, 'Should find get_string call with [] parameter');

        // Test 4-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'lazyload');
        $this->assertSame(15, $lineNumber, 'Should find get_string call with lazyload=true');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'lazyload2');
        $this->assertSame(16, $lineNumber, 'Should find get_string call with lazyload=false');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'lazyload3');
        $this->assertSame(17, $lineNumber, 'Should find get_string call with empty array and lazyload');

        // Test complex parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'complex');
        $this->assertSame(20, $lineNumber, 'Should find get_string call with method call parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'complex2');
        $this->assertSame(21, $lineNumber, 'Should find get_string call with complex array parameter');

        // Test various spacing patterns
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'spaced');
        $this->assertSame(24, $lineNumber, 'Should find get_string call with extra spaces');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'nospace');
        $this->assertSame(25, $lineNumber, 'Should find get_string call with no spaces');
    }

    /**
     * Test finding JavaScript str.get_string() calls with optional parameters.
     *
     * This tests the fix for JavaScript patterns that should also handle optional parameters.
     * Note: This tests the pattern matching logic rather than the StringUsageFinder directly,
     * since StringUsageFinder is designed for PHP patterns.
     */
    public function testFindStringLiteralLineWithJavaScriptGetStringOptionalParametersReturnsCorrectLine(): void
    {
        $content = "// JavaScript file\n\n" .
                   "define(['core/str'], function(str) {\n" .
                   "    // Basic 2-parameter calls\n" .
                   "    var str1 = str.get_string('basicjs', 'component');\n" .        // Line 5
                   "    var str2 = str.get_string('anotherjs', 'mod_assign');\n" .     // Line 6
                   "\n" .
                   "    // 3-parameter calls (with data parameter)\n" .
                   "    var str3 = str.get_string('withdata', 'component', data);\n" . // Line 9
                   "    var str4 = str.get_string('withobj', 'component', {key: 'value'});\n" . // Line 10
                   "    var str5 = str.get_string('withnull', 'component', null);\n" . // Line 11
                   "\n" .
                   "    // Various spacing patterns\n" .
                   "    var str6 = str.get_string( 'spaced' , 'component' , params );\n" . // Line 14
                   "    var str7 = str.get_string('nospace','component',data);\n" .    // Line 15
                   "\n" .
                   "    return {str1, str2, str3, str4, str5, str6, str7};\n" .
                   "});\n";

        $filePath = $this->createTempFile($content);

        // Test the JavaScript pattern directly since StringUsageFinder is designed for PHP
        $lines   = file($filePath, FILE_IGNORE_NEW_LINES);
        $pattern = '~str\.get_string\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*,\s*[\'"]([^\'\"]+)[\'"](?:\s*,.*?)?\s*\)~';

        $foundLines = [];
        foreach ($lines as $lineNumber => $line) {
            if (preg_match($pattern, $line, $matches)) {
                $stringKey              = $matches[1];
                $foundLines[$stringKey] = $lineNumber + 1; // Convert to 1-based line numbers
            }
        }

        // Test basic 2-parameter calls
        $this->assertArrayHasKey('basicjs', $foundLines, 'Should find basicjs string key');
        $this->assertSame(5, $foundLines['basicjs'], 'Should find basicjs on line 5');

        $this->assertArrayHasKey('anotherjs', $foundLines, 'Should find anotherjs string key');
        $this->assertSame(6, $foundLines['anotherjs'], 'Should find anotherjs on line 6');

        // Test 3-parameter calls
        $this->assertArrayHasKey('withdata', $foundLines, 'Should find withdata string key');
        $this->assertSame(9, $foundLines['withdata'], 'Should find withdata on line 9');

        $this->assertArrayHasKey('withobj', $foundLines, 'Should find withobj string key');
        $this->assertSame(10, $foundLines['withobj'], 'Should find withobj on line 10');

        $this->assertArrayHasKey('withnull', $foundLines, 'Should find withnull string key');
        $this->assertSame(11, $foundLines['withnull'], 'Should find withnull on line 11');

        // Test various spacing patterns
        $this->assertArrayHasKey('spaced', $foundLines, 'Should find spaced string key');
        $this->assertSame(14, $foundLines['spaced'], 'Should find spaced on line 14');

        $this->assertArrayHasKey('nospace', $foundLines, 'Should find nospace string key');
        $this->assertSame(15, $foundLines['nospace'], 'Should find nospace on line 15');

        // Verify that all expected patterns were found
        $expectedKeys = ['basicjs', 'anotherjs', 'withdata', 'withobj', 'withnull', 'spaced', 'nospace'];
        $this->assertCount(count($expectedKeys), $foundLines, 'Should find all expected string keys');
    }

    /**
     * Test finding lang_string instantiations with optional parameters.
     *
     * This tests that new lang_string() calls with optional parameters are also handled correctly.
     */
    public function testFindStringLiteralLineWithLangStringOptionalParametersReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "function test_lang_string() {\n" .
                   "    // Basic 2-parameter calls\n" .
                   "    \$str1 = new lang_string('basiclang', 'component');\n" .       // Line 5
                   "    \$str2 = new lang_string('anotherlang', 'mod_assign');\n" .    // Line 6
                   "\n" .
                   "    // 3-parameter calls (with \$a parameter)\n" .
                   "    \$str3 = new lang_string('withlangparams', 'component', \$params);\n" . // Line 9
                   "    \$str4 = new lang_string('withlangnull', 'component', null);\n" .       // Line 10
                   "    \$str5 = new lang_string('withlangarray', 'component', array('key' => 'value'));\n" . // Line 11
                   "\n" .
                   "    // 4-parameter calls (with language parameter)\n" .
                   "    \$str6 = new lang_string('withlang', 'component', \$a, 'en');\n" .     // Line 14
                   "    \$str7 = new lang_string('withlang2', 'component', null, 'es');\n" .   // Line 15
                   "\n" .
                   "    // Various spacing\n" .
                   "    \$str8 = new lang_string( 'spacedlang' , 'component' , \$params );\n" . // Line 18
                   "}\n";

        $filePath = $this->createTempFile($content);

        // Test basic 2-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'basiclang');
        $this->assertSame(5, $lineNumber, 'Should find basic 2-parameter lang_string call');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'anotherlang');
        $this->assertSame(6, $lineNumber, 'Should find another basic 2-parameter lang_string call');

        // Test 3-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withlangparams');
        $this->assertSame(9, $lineNumber, 'Should find lang_string call with variable parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withlangnull');
        $this->assertSame(10, $lineNumber, 'Should find lang_string call with null parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withlangarray');
        $this->assertSame(11, $lineNumber, 'Should find lang_string call with array parameter');

        // Test 4-parameter calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withlang');
        $this->assertSame(14, $lineNumber, 'Should find lang_string call with language parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'withlang2');
        $this->assertSame(15, $lineNumber, 'Should find another lang_string call with language parameter');

        // Test spacing variations
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'spacedlang');
        $this->assertSame(18, $lineNumber, 'Should find lang_string call with extra spaces');
    }

    /**
     * Test finding addHelpButton() calls with optional parameters.
     *
     * This tests that addHelpButton() calls with optional parameters are also handled correctly.
     */
    public function testFindStringLiteralLineWithAddHelpButtonOptionalParametersReturnsCorrectLine(): void
    {
        $content = "<?php\n\n" .
                   "class some_form extends moodleform {\n" .
                   "    public function definition() {\n" .
                   "        \$mform = \$this->_form;\n" .
                   "\n" .
                   "        // Basic addHelpButton calls\n" .
                   "        \$mform->addHelpButton('element1', 'helpstring1', 'component');\n" .     // Line 8
                   "        \$mform->addHelpButton('element2', 'helpstring2', 'mod_assign');\n" .    // Line 9
                   "\n" .
                   "        // addHelpButton with additional parameters\n" .
                   "        \$mform->addHelpButton('element3', 'helpstring3', 'component', true);\n" .    // Line 12
                   "        \$mform->addHelpButton('element4', 'helpstring4', 'component', false, \$extra);\n" . // Line 13
                   "\n" .
                   "        // Various spacing\n" .
                   "        \$mform->addHelpButton( 'element5' , 'helpstring5' , 'component' );\n" . // Line 16
                   "        \$mform->addHelpButton('element6','helpstring6','component',true);\n" .  // Line 17
                   "    }\n" .
                   "}\n";

        $filePath = $this->createTempFile($content);

        // Test basic addHelpButton calls
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring1');
        $this->assertSame(8, $lineNumber, 'Should find basic addHelpButton call');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring2');
        $this->assertSame(9, $lineNumber, 'Should find another basic addHelpButton call');

        // Test addHelpButton with additional parameters
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring3');
        $this->assertSame(12, $lineNumber, 'Should find addHelpButton call with boolean parameter');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring4');
        $this->assertSame(13, $lineNumber, 'Should find addHelpButton call with multiple additional parameters');

        // Test spacing variations
        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring5');
        $this->assertSame(16, $lineNumber, 'Should find addHelpButton call with extra spaces');

        $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, 'helpstring6');
        $this->assertSame(17, $lineNumber, 'Should find addHelpButton call with no spaces');
    }

    /**
     * Test the specific case mentioned in the GitHub issue.
     *
     * This reproduces the exact problem reported: get_string() with a third parameter
     * was not being detected by the old regex pattern.
     */
    public function testFindStringLiteralLineReproduceGitHubIssueBothCasesWork(): void
    {
        $content = "<?php\n\n" .
                   "function reproduce_issue() {\n" .
                   "    // This worked before the fix\n" .
                   "    \$str1 = get_string('notexistingstring', 'block_accessreview');\n" .     // Line 5
                   "\n" .
                   "    // This did NOT work before the fix (the reported issue)\n" .
                   "    \$str2 = get_string('notexistingstring', 'block_accessreview', \$params);\n" . // Line 8
                   "\n" .
                   "    return [\$str1, \$str2];\n" .
                   "}\n";

        $filePath = $this->createTempFile($content);

        // Both cases should now work with our fixed regex patterns
        $lineNumber1 = $this->usageFinder->findStringLiteralLine($filePath, 'notexistingstring');
        $this->assertSame(5, $lineNumber1, 'Should find first get_string call (2 parameters) - this always worked');

        // Use a custom pattern to find the second occurrence
        $pattern   = '~get_string\s*\(\s*[\'"]notexistingstring[\'"]\s*,\s*[\'"]block_accessreview[\'"](?:\s*,.*?)?\s*\)~';
        $fileLines = file($filePath, FILE_IGNORE_NEW_LINES);
        $foundLine = null;

        foreach ($fileLines as $lineNum => $line) {
            if (preg_match($pattern, $line) && false !== strpos($line, '$params')) {
                $foundLine = $lineNum + 1; // Convert to 1-based line number
                break;
            }
        }

        $this->assertSame(8, $foundLine, 'Should find second get_string call (3 parameters) - this was broken before the fix');
    }
}
