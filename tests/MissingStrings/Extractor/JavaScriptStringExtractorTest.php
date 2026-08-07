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

use MoodlePluginCI\MissingStrings\Extractor\JavaScriptStringExtractor;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test case for JavaScriptStringExtractor.
 */
class JavaScriptStringExtractorTest extends MissingStringsTestCase
{
    /**
     * @var JavaScriptStringExtractor
     */
    private $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new JavaScriptStringExtractor();
    }

    /**
     * Test extraction of strings from str.get_string() calls.
     */
    public function testExtractStrGetString(): void
    {
        $content   = "str.get_string('hello', 'block_test');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('hello', $result);
        $this->assertSame('test.js', $result['hello'][0]['file']);
        $this->assertSame(1, $result['hello'][0]['line']);
    }

    /**
     * Test extraction of strings from str.get_string() calls with third parameter.
     */
    public function testExtractStrGetStringWithThirdParameter(): void
    {
        $content   = "str.get_string('greeting', 'block_test', {name: 'John'});";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('greeting', $result);
        $this->assertSame('test.js', $result['greeting'][0]['file']);
        $this->assertSame(1, $result['greeting'][0]['line']);
    }

    /**
     * Test extraction of strings from str.get_strings() with object array format.
     */
    public function testExtractStrGetStringsObjectArray(): void
    {
        $content   = "str.get_strings([{key: 'hello', component: 'block_test'}, {key: 'goodbye', component: 'block_test'}]);";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('hello', $result);
        $this->assertArrayHasKey('goodbye', $result);
        $this->assertSame('test.js', $result['hello'][0]['file']);
        $this->assertSame('test.js', $result['goodbye'][0]['file']);
    }

    /**
     * Test extraction of strings from str.get_strings() with separate arrays format.
     */
    public function testExtractStrGetStringsSeparateArrays(): void
    {
        $content   = "str.get_strings(['hello', 'goodbye', 'welcome'], 'block_test');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('hello', $result);
        $this->assertArrayHasKey('goodbye', $result);
        $this->assertArrayHasKey('welcome', $result);
        $this->assertSame('test.js', $result['hello'][0]['file']);
    }

    /**
     * Test extraction of strings from core/str getString() calls.
     */
    public function testExtractGetString(): void
    {
        $content   = "getString('message', 'block_test');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('message', $result);
        $this->assertSame('test.js', $result['message'][0]['file']);
        $this->assertSame(1, $result['message'][0]['line']);
    }

    /**
     * Test extraction of strings from core/str getString() calls with third parameter.
     */
    public function testExtractGetStringWithThirdParameter(): void
    {
        $content   = "getString('error', 'block_test', {code: 404});";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('error', $result);
        $this->assertSame('test.js', $result['error'][0]['file']);
        $this->assertSame(1, $result['error'][0]['line']);
    }

    /**
     * Test extraction of strings from core/str getStrings() calls.
     */
    public function testExtractGetStrings(): void
    {
        $content   = "getStrings([{key: 'title', component: 'block_test'}, {key: 'subtitle', component: 'block_test'}]);";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('subtitle', $result);
        $this->assertSame('test.js', $result['title'][0]['file']);
    }

    /**
     * Test extraction of strings from Prefetch.prefetchString() calls.
     */
    public function testExtractPrefetchString(): void
    {
        $content   = "Prefetch.prefetchString('discussion', 'mod_forum');";
        $component = 'mod_forum';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('discussion', $result);
        $this->assertSame('test.js', $result['discussion'][0]['file']);
        $this->assertSame(1, $result['discussion'][0]['line']);
    }

    /**
     * Test extraction of strings from Prefetch.prefetchStrings() calls.
     */
    public function testExtractPrefetchStrings(): void
    {
        $content   = "Prefetch.prefetchStrings('core', ['yes', 'no', 'maybe']);";
        $component = 'core';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('yes', $result);
        $this->assertArrayHasKey('no', $result);
        $this->assertArrayHasKey('maybe', $result);
        $this->assertSame('test.js', $result['yes'][0]['file']);
    }

    /**
     * Test that strings with different components are filtered out.
     */
    public function testFilterByComponent(): void
    {
        $content   = "str.get_string('test1', 'block_test'); str.get_string('test2', 'core');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('test1', $result);
        $this->assertArrayNotHasKey('test2', $result);
    }

    /**
     * Test extraction from multiple lines.
     */
    public function testExtractMultipleLines(): void
    {
        $content   = "str.get_string('line1', 'block_test');\ngetString('line2', 'block_test');\nPrefetch.prefetchString('line3', 'block_test');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('line1', $result);
        $this->assertArrayHasKey('line2', $result);
        $this->assertArrayHasKey('line3', $result);
        $this->assertSame(1, $result['line1'][0]['line']);
        $this->assertSame(2, $result['line2'][0]['line']);
        $this->assertSame(3, $result['line3'][0]['line']);
    }

    /**
     * Test real-world example with mixed quotes and spacing.
     */
    public function testExtractRealWorldExample(): void
    {
        $content   = 'str.get_string( "save_changes" , "block_uteluqchatbot" );';
        $component = 'block_uteluqchatbot';
        $filePath  = 'chatbot.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('save_changes', $result);
        $this->assertSame('chatbot.js', $result['save_changes'][0]['file']);
        $this->assertSame(1, $result['save_changes'][0]['line']);
    }

    /**
     * Test canHandle method for AMD JavaScript files.
     */
    public function testCanHandle(): void
    {
        $this->assertTrue($this->extractor->canHandle('/path/to/amd/src/module.js'));
        $this->assertTrue($this->extractor->canHandle('/path/to/amd/build/module.min.js'));
        $this->assertFalse($this->extractor->canHandle('/path/to/lib/script.js'));
        $this->assertFalse($this->extractor->canHandle('template.mustache'));
        $this->assertFalse($this->extractor->canHandle('code.php'));
    }

    /**
     * Test getName method.
     */
    public function testGetName(): void
    {
        $this->assertSame('JavaScript String Extractor', $this->extractor->getName());
    }

    /**
     * Test complex getStrings pattern with mixed spacing.
     */
    public function testExtractComplexGetStringsPattern(): void
    {
        $content   = "getStrings( [ { key: 'confirm', component: 'block_test' } , { key : 'cancel' , component : 'block_test' } ] );";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('confirm', $result);
        $this->assertArrayHasKey('cancel', $result);
    }

    /**
     * Test that no extraction occurs for non-matching components.
     */
    public function testNoExtractionForNonMatchingComponents(): void
    {
        $content   = "str.get_string('test', 'different_component');";
        $component = 'block_test';
        $filePath  = 'test.js';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertEmpty($result);
    }
}
