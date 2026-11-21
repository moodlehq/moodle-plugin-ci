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

use MoodlePluginCI\MissingStrings\Extractor\MustacheStringExtractor;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test case for MustacheStringExtractor.
 */
class MustacheStringExtractorTest extends MissingStringsTestCase
{
    /**
     * @var MustacheStringExtractor
     */
    private $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new MustacheStringExtractor();
    }

    /**
     * Test extraction of strings from 2-parameter {{#str}} helpers.
     */
    public function testExtractTwoParameterStrings(): void
    {
        $content   = '{{#str}}modify_prompt, block_uteluqchatbot{{/str}}';
        $component = 'block_uteluqchatbot';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('modify_prompt', $result);
        $this->assertSame('test.mustache', $result['modify_prompt'][0]['file']);
        $this->assertSame(1, $result['modify_prompt'][0]['line']);
    }

    /**
     * Test extraction of strings from 3-parameter {{#str}} helpers with simple parameters.
     */
    public function testExtractThreeParameterStringsSimple(): void
    {
        $content   = '{{#str}} backto, core, Moodle.org {{/str}}';
        $component = 'core';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('backto', $result);
        $this->assertSame('test.mustache', $result['backto'][0]['file']);
        $this->assertSame(1, $result['backto'][0]['line']);
    }

    /**
     * Test extraction of strings from 3-parameter {{#str}} helpers with Mustache variables.
     */
    public function testExtractThreeParameterStringsWithVariables(): void
    {
        $content   = '{{#str}} backto, core, {{name}} {{/str}}';
        $component = 'core';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('backto', $result);
        $this->assertSame('test.mustache', $result['backto'][0]['file']);
        $this->assertSame(1, $result['backto'][0]['line']);
    }

    /**
     * Test extraction with the exact case from the real template file.
     */
    public function testExtractRealWorldExample(): void
    {
        $content   = '{{#str}}modify_prompt1, block_uteluqchatbot, {{name}}{{/str}}';
        $component = 'block_uteluqchatbot';
        $filePath  = 'prompt_modal.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('modify_prompt1', $result);
        $this->assertSame('prompt_modal.mustache', $result['modify_prompt1'][0]['file']);
        $this->assertSame(1, $result['modify_prompt1'][0]['line']);
    }

    /**
     * Test extraction of strings from 3-parameter {{#str}} helpers with JSON parameters.
     */
    public function testExtractThreeParameterStringsWithJson(): void
    {
        $content   = '{{#str}} counteditems, core, { "count": "42", "items": "courses" } {{/str}}';
        $component = 'core';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('counteditems', $result);
        $this->assertSame('test.mustache', $result['counteditems'][0]['file']);
        $this->assertSame(1, $result['counteditems'][0]['line']);
    }

    /**
     * Test extraction of strings from 3-parameter {{#str}} helpers with complex nested parameters.
     */
    public function testExtractThreeParameterStringsWithComplexParameters(): void
    {
        $content   = '{{#str}} counteditems, core, { "count": {{count}}, "items": {{#quote}} {{itemname}} {{/quote}} } {{/str}}';
        $component = 'core';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('counteditems', $result);
        $this->assertSame('test.mustache', $result['counteditems'][0]['file']);
        $this->assertSame(1, $result['counteditems'][0]['line']);
    }

    /**
     * Test extraction of strings from {{#cleanstr}} helpers with 3 parameters.
     */
    public function testExtractCleanstrThreeParameters(): void
    {
        $content   = '{{#cleanstr}}cleanstring, component, parameter{{/cleanstr}}';
        $component = 'component';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('cleanstring', $result);
        $this->assertSame('test.mustache', $result['cleanstring'][0]['file']);
        $this->assertSame(1, $result['cleanstring'][0]['line']);
    }

    /**
     * Test that strings with different components are filtered out.
     */
    public function testFilterByComponent(): void
    {
        $content   = '{{#str}}string1, block_uteluqchatbot{{/str}}{{#str}}string2, core{{/str}}';
        $component = 'block_uteluqchatbot';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('string1', $result);
        $this->assertArrayNotHasKey('string2', $result);
    }

    /**
     * Test extraction from multiple lines.
     */
    public function testExtractMultipleLines(): void
    {
        $content   = "{{#str}}string1, block_test{{/str}}\n{{#str}}string2, block_test, param{{/str}}";
        $component = 'block_test';
        $filePath  = 'test.mustache';

        $result = $this->extractor->extract($content, $component, $filePath);

        $this->assertArrayHasKey('string1', $result);
        $this->assertArrayHasKey('string2', $result);
        $this->assertSame(1, $result['string1'][0]['line']);
        $this->assertSame(2, $result['string2'][0]['line']);
    }

    /**
     * Test canHandle method.
     */
    public function testCanHandle(): void
    {
        $this->assertTrue($this->extractor->canHandle('template.mustache'));
        $this->assertFalse($this->extractor->canHandle('script.js'));
        $this->assertFalse($this->extractor->canHandle('code.php'));
    }

    /**
     * Test getName method.
     */
    public function testGetName(): void
    {
        $this->assertSame('Mustache String Extractor', $this->extractor->getName());
    }
}
