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

namespace MoodlePluginCI\Tests\MissingStrings\Extractor;

use MoodlePluginCI\MissingStrings\Extractor\PhpStringExtractor;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for PhpStringExtractor class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Extractor\PhpStringExtractor
 */
class PhpStringExtractorTest extends MissingStringsTestCase
{
    /** @var PhpStringExtractor */
    private $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new PhpStringExtractor();
    }

    /**
     * Test canHandle method.
     */
    public function testCanHandle(): void
    {
        $this->assertTrue($this->extractor->canHandle('test.php'));
        $this->assertTrue($this->extractor->canHandle('/path/to/file.php'));
        $this->assertTrue($this->extractor->canHandle('file.PHP')); // Case insensitive

        $this->assertFalse($this->extractor->canHandle('test.js'));
        $this->assertFalse($this->extractor->canHandle('test.mustache'));
        $this->assertFalse($this->extractor->canHandle('test.xml'));
        $this->assertFalse($this->extractor->canHandle('test'));
    }

    /**
     * Test getName method.
     */
    public function testGetName(): void
    {
        $this->assertSame('PHP String Extractor', $this->extractor->getName());
    }

    /**
     * Test extract with get_string calls.
     */
    public function testExtractGetStringCalls(): void
    {
        $content = "<?php\n" .
                   "get_string('test_string', 'mod_test');\n" .
                   "get_string('another_string', 'mod_test', \$param);\n" .
                   "get_string('third_string', 'mod_test', \$param, \$another);\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('test_string', $result);
        $this->assertArrayHasKey('another_string', $result);
        $this->assertArrayHasKey('third_string', $result);

        // Check context information
        $this->assertSame(2, $result['test_string'][0]['line']);
        $this->assertStringContainsString('get_string', $result['test_string'][0]['context']);
        $this->assertStringContainsString('test.php', $result['test_string'][0]['file']);
    }

    /**
     * Test extract with new lang_string calls.
     */
    public function testExtractLangStringCalls(): void
    {
        $content = "<?php\n" .
                   "new lang_string('lang_test', 'mod_test');\n" .
                   "new lang_string('lang_with_param', 'mod_test', \$param);\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('lang_test', $result);
        $this->assertArrayHasKey('lang_with_param', $result);

        $this->assertSame(2, $result['lang_test'][0]['line']);
        $this->assertStringContainsString('new lang_string', $result['lang_test'][0]['context']);
    }

    /**
     * Test extract with addHelpButton calls.
     */
    public function testExtractAddHelpButtonCalls(): void
    {
        $content = "<?php\n" .
                   "\$mform->addHelpButton('help_string', 'mod_test');\n" .
                   "\$form->addHelpButton('another_help', 'mod_test', \$plugin);\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('help_string', $result);
        $this->assertArrayHasKey('another_help', $result);

        $this->assertSame(2, $result['help_string'][0]['line']);
        $this->assertStringContainsString('addHelpButton', $result['help_string'][0]['context']);
    }

    /**
     * Test extract with string manager calls.
     */
    public function testExtractStringManagerCalls(): void
    {
        $content = "<?php\n" .
                   "get_string_manager()->get_string('manager_string', 'mod_test');\n" .
                   "get_string_manager()->get_string('manager_with_param', 'mod_test', \$param);\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('manager_string', $result);
        $this->assertArrayHasKey('manager_with_param', $result);

        $this->assertSame(2, $result['manager_string'][0]['line']);
        $this->assertStringContainsString('get_string_manager', $result['manager_string'][0]['context']);
    }

    /**
     * Test extract with component filtering.
     */
    public function testExtractWithComponentFiltering(): void
    {
        $content = "<?php\n" .
                   "get_string('test_string', 'mod_test');\n" .
                   "get_string('other_string', 'mod_other');\n" .
                   "get_string('core_string', 'core');\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('test_string', $result);
        $this->assertArrayNotHasKey('other_string', $result);
        $this->assertArrayNotHasKey('core_string', $result);
    }

    /**
     * Test extract with module short component names.
     */
    public function testExtractWithModuleShortComponentNames(): void
    {
        $content = "<?php\n" .
                   "get_string('test_string', 'quiz');\n" .        // Short form
                   "get_string('another_string', 'mod_quiz');\n";   // Full form

        $result = $this->extractor->extract($content, 'mod_quiz', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('test_string', $result);
        $this->assertArrayHasKey('another_string', $result);
    }

    /**
     * Test extract skips dynamic strings with variables.
     */
    public function testExtractSkipsDynamicStringsWithVariables(): void
    {
        $content = "<?php\n" .
                   "get_string('static_string', 'mod_test');\n" .
                   "get_string(\$dynamic_string, 'mod_test');\n" .
                   "get_string('prefix_' . \$suffix, 'mod_test');\n" .
                   "get_string('template_{\$var}', 'mod_test');\n" .
                   "get_string('{dynamic}', 'mod_test');\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('static_string', $result);
        $this->assertArrayNotHasKey('dynamic_string', $result);
    }

    /**
     * Test extract with multiline strings.
     */
    public function testExtractWithMultilineStrings(): void
    {
        $content = "<?php\n" .
                   "get_string(\n" .
                   "    'multiline_string',\n" .
                   "    'mod_test'\n" .
                   ");\n";

        // This should not match because regex is line-based
        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertEmpty($result);
    }

    /**
     * Test extract with multiple occurrences of same string.
     */
    public function testExtractWithMultipleOccurrences(): void
    {
        $content = "<?php\n" .
                   "get_string('same_string', 'mod_test');\n" .
                   "// Some comment\n" .
                   "get_string('same_string', 'mod_test');\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('same_string', $result);
        $this->assertCount(2, $result['same_string']); // Two occurrences

        $this->assertSame(2, $result['same_string'][0]['line']);
        $this->assertSame(4, $result['same_string'][1]['line']);
    }

    /**
     * Test extract with mixed quote types.
     */
    public function testExtractWithMixedQuoteTypes(): void
    {
        $content = "<?php\n" .
                   "get_string('single_quoted', 'mod_test');\n" .
                   'get_string("double_quoted", "mod_test");' . "\n" .
                   "get_string('mixed1', \"mod_test\");\n" .
                   'get_string("mixed2", \'mod_test\');' . "\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(4, $result);
        $this->assertArrayHasKey('single_quoted', $result);
        $this->assertArrayHasKey('double_quoted', $result);
        $this->assertArrayHasKey('mixed1', $result);
        $this->assertArrayHasKey('mixed2', $result);
    }

    /**
     * Test extract with spacing variations.
     */
    public function testExtractWithSpacingVariations(): void
    {
        $content = "<?php\n" .
                   "get_string ( 'spaced1' , 'mod_test' );\n" .
                   "get_string('nospace','mod_test');\n" .
                   "get_string(  'lots_of_spaces'  ,  'mod_test'  );\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(3, $result);
        $this->assertArrayHasKey('spaced1', $result);
        $this->assertArrayHasKey('nospace', $result);
        $this->assertArrayHasKey('lots_of_spaces', $result);
    }

    /**
     * Test extract with commented out strings.
     */
    public function testExtractWithCommentedOutStrings(): void
    {
        $content = "<?php\n" .
                   "get_string('active_string', 'mod_test');\n" .
                   "// get_string('commented_string', 'mod_test');\n" .
                   "/* get_string('block_commented', 'mod_test'); */\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        // Comments are still matched by regex - this is intentional as some
        // commented code might still be relevant for string analysis
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('active_string', $result);
        $this->assertArrayHasKey('commented_string', $result);
        $this->assertArrayHasKey('block_commented', $result);
    }

    /**
     * Test extract with empty content.
     */
    public function testExtractWithEmptyContent(): void
    {
        $result = $this->extractor->extract('', 'mod_test', '/path/to/test.php');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test extract with no matching strings.
     */
    public function testExtractWithNoMatchingStrings(): void
    {
        $content = "<?php\n" .
                   "echo 'Hello world';\n" .
                   "\$var = 'test';\n" .
                   "function test() { return true; }\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test extract with invalid PHP syntax.
     */
    public function testExtractWithInvalidPhpSyntax(): void
    {
        $content = "<?php\n" .
                   "get_string('valid_string', 'mod_test');\n" .
                   "syntax error here\n" .
                   "get_string('another_valid', 'mod_test');\n";

        // Extractor should still work with individual lines
        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('valid_string', $result);
        $this->assertArrayHasKey('another_valid', $result);
    }

    /**
     * Test relative file path generation.
     */
    public function testRelativeFilePathGeneration(): void
    {
        $testCases = [
            '/var/www/html/mod/test/lib.php'                     => 'mod/test/lib.php',
            '/path/to/moodle/local/plugin/version.php'           => 'local/plugin/version.php',
            '/some/path/moodle.local/blocks/test/block_test.php' => 'blocks/test/block_test.php',
            '/deep/path/structure/file.php'                      => 'path/structure/file.php',
            '/short/file.php'                                    => 'short/file.php',
            'file.php'                                           => 'file.php',
        ];

        foreach ($testCases as $input => $expected) {
            $content = "<?php\nget_string('test', 'mod_test');";
            $result  = $this->extractor->extract($content, 'mod_test', $input);

            $this->assertArrayHasKey('test', $result);
            $this->assertStringContainsString($expected, $result['test'][0]['file']);
        }
    }

    /**
     * Test extract with special characters in string keys.
     */
    public function testExtractWithSpecialCharactersInStringKeys(): void
    {
        $content = "<?php\n" .
                   "get_string('string_with_underscores', 'mod_test');\n" .
                   "get_string('string-with-dashes', 'mod_test');\n" .
                   "get_string('string:with:colons', 'mod_test');\n" .
                   "get_string('string.with.dots', 'mod_test');\n" .
                   "get_string('string123numbers', 'mod_test');\n";

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('string_with_underscores', $result);
        $this->assertArrayHasKey('string-with-dashes', $result);
        $this->assertArrayHasKey('string:with:colons', $result);
        $this->assertArrayHasKey('string.with.dots', $result);
        $this->assertArrayHasKey('string123numbers', $result);
    }

    /**
     * Test dynamic string detection.
     */
    public function testDynamicStringDetection(): void
    {
        $content = "<?php\n" .
                   "get_string('static_string', 'mod_test');\n" .                    // Static - should match
                   "get_string('string_with_\$var', 'mod_test');\n" .               // Dynamic - should skip
                   "get_string('string_{\$template}', 'mod_test');\n" .             // Dynamic - should skip
                   "get_string('string_{placeholder}', 'mod_test');\n" .            // Dynamic - should skip
                   "get_string('string_.\$concat', 'mod_test');\n" .                // Dynamic - should skip
                   "get_string('normal_string_name', 'mod_test');\n";               // Static - should match

        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('static_string', $result);
        $this->assertArrayHasKey('normal_string_name', $result);

        // Ensure dynamic strings are not included
        $this->assertArrayNotHasKey('string_with_$var', $result);
        $this->assertArrayNotHasKey('string_{$template}', $result);
        $this->assertArrayNotHasKey('string_{placeholder}', $result);
        $this->assertArrayNotHasKey('string_.$concat', $result);
    }

    /**
     * Test extract performance with large content.
     */
    public function testExtractPerformanceWithLargeContent(): void
    {
        $lines = [];
        for ($i = 0; $i < 1000; ++$i) {
            $lines[] = "get_string('string_{$i}', 'mod_test');";
        }
        $content = "<?php\n" . implode("\n", $lines);

        $startTime = microtime(true);
        $result    = $this->extractor->extract($content, 'mod_test', '/path/to/test.php');
        $endTime   = microtime(true);

        $this->assertCount(1000, $result);
        $this->assertLessThan(1.0, $endTime - $startTime, 'Extraction should be reasonably fast');
    }

    /**
     * Test extract with non-PHP file extension (edge case).
     */
    public function testExtractWithNonPhpFile(): void
    {
        $content = "<?php\nget_string('test_string', 'mod_test');";

        // Even though canHandle would return false for .txt files,
        // extract should still work if called directly
        $result = $this->extractor->extract($content, 'mod_test', '/path/to/test.txt');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('test_string', $result);
        $this->assertStringContainsString('test.txt', $result['test_string'][0]['file']);
    }
}
