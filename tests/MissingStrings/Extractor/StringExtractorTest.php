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

namespace MoodlePluginCI\Tests\MissingStrings\Extractor;

use MoodlePluginCI\MissingStrings\Extractor\StringExtractor;
use MoodlePluginCI\MissingStrings\Extractor\StringExtractorInterface;
use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for StringExtractor class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Extractor\StringExtractor
 */
class StringExtractorTest extends MissingStringsTestCase
{
    /** @var StringExtractor */
    private $extractor;

    /** @var string */
    private $testPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor      = new StringExtractor();
        $this->testPluginPath = $this->createTempDir('test_plugin_');
        $this->createTestPluginStructure();
    }

    /**
     * Create a test plugin structure.
     */
    private function createTestPluginStructure(): void
    {
        // Create basic plugin structure
        mkdir($this->testPluginPath . '/classes', 0777, true);
        mkdir($this->testPluginPath . '/templates', 0777, true);
        mkdir($this->testPluginPath . '/amd/src', 0777, true);

        // Create PHP file with strings
        $phpContent = <<<'PHP'
<?php
class TestClass {
    public function test() {
        get_string('test_string', 'local_testplugin');
        get_string('another_string', 'local_testplugin');
        new lang_string('third_string', 'local_testplugin');
    }
}
PHP;
        file_put_contents($this->testPluginPath . '/classes/test.php', $phpContent);

        // Create Mustache template with strings
        $mustacheContent = <<<'MUSTACHE'
<div>
    {{#str}}template_string, local_testplugin{{/str}}
    {{#cleanstr}}clean_string, local_testplugin{{/cleanstr}}
</div>
MUSTACHE;
        file_put_contents($this->testPluginPath . '/templates/test.mustache', $mustacheContent);

        // Create JavaScript file with strings
        $jsContent = <<<'JS'
define(['core/str'], function(str) {
    str.get_string('js_string', 'local_testplugin');
    str.get_strings([
        {key: 'js_string2', component: 'local_testplugin'}
    ]);
});
JS;
        file_put_contents($this->testPluginPath . '/amd/src/test.js', $jsContent);
    }

    /**
     * Test extractFromPlugin method.
     */
    public function testExtractFromPlugin(): void
    {
        $plugin        = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);
        $fileDiscovery = new FileDiscovery($plugin);
        $this->extractor->setFileDiscovery($fileDiscovery);

        $strings = $this->extractor->extractFromPlugin($plugin);

        // Should extract strings from all file types
        $this->assertArrayHasKey('test_string', $strings);
        $this->assertArrayHasKey('another_string', $strings);
        $this->assertArrayHasKey('third_string', $strings);
        $this->assertArrayHasKey('template_string', $strings);
        $this->assertArrayHasKey('clean_string', $strings);
        $this->assertArrayHasKey('js_string', $strings);
        $this->assertArrayHasKey('js_string2', $strings);

        // Check string usage information
        $this->assertNotEmpty($strings['test_string']);
        $firstUsage = $strings['test_string'][0];
        $this->assertArrayHasKey('file', $firstUsage);
        $this->assertArrayHasKey('line', $firstUsage);
        $this->assertStringContainsString('test.php', $firstUsage['file']);
    }

    /**
     * Test extractFromPlugin with no file discovery throws exception.
     */
    public function testExtractFromPluginWithoutFileDiscoveryThrowsException(): void
    {
        $plugin = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File discovery service not set');

        $this->extractor->extractFromPlugin($plugin);
    }

    /**
     * Test getPerformanceMetrics method.
     */
    public function testGetPerformanceMetrics(): void
    {
        $plugin        = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);
        $fileDiscovery = new FileDiscovery($plugin);
        $this->extractor->setFileDiscovery($fileDiscovery);

        // Extract strings to populate metrics
        $this->extractor->extractFromPlugin($plugin);

        $metrics = $this->extractor->getPerformanceMetrics();

        $this->assertArrayHasKey('extraction_time', $metrics);
        $this->assertArrayHasKey('files_processed', $metrics);
        $this->assertArrayHasKey('strings_extracted', $metrics);
        $this->assertArrayHasKey('string_usages_found', $metrics);

        $this->assertIsFloat($metrics['extraction_time']);
        $this->assertGreaterThan(0, $metrics['files_processed']);
        $this->assertGreaterThan(0, $metrics['strings_extracted']);
        $this->assertGreaterThan(0, $metrics['string_usages_found']);
    }

    /**
     * Test addExtractor method.
     */
    public function testAddExtractor(): void
    {
        $mockExtractor = $this->createMock(StringExtractorInterface::class);
        $mockExtractor->method('canHandle')->willReturn(true);
        $mockExtractor->method('extract')->willReturn(['custom_string' => [['file' => 'test.php', 'line' => 1]]]);

        $this->extractor->addExtractor($mockExtractor);

        $extractors = $this->extractor->getExtractors();
        $this->assertCount(4, $extractors); // 3 default + 1 custom
        $this->assertContains($mockExtractor, $extractors);
    }

    /**
     * Test setExtractors method.
     */
    public function testSetExtractors(): void
    {
        $mockExtractor1 = $this->createMock(StringExtractorInterface::class);
        $mockExtractor2 = $this->createMock(StringExtractorInterface::class);

        $customExtractors = [$mockExtractor1, $mockExtractor2];
        $this->extractor->setExtractors($customExtractors);

        $extractors = $this->extractor->getExtractors();
        $this->assertCount(2, $extractors);
        $this->assertSame($customExtractors, $extractors);
    }

    /**
     * Test getExtractors method.
     */
    public function testGetExtractors(): void
    {
        $extractors = $this->extractor->getExtractors();

        $this->assertCount(3, $extractors); // Default extractors
        $this->assertContainsOnlyInstancesOf(StringExtractorInterface::class, $extractors);
    }

    /**
     * Test extractFromPlugin with empty plugin directory.
     */
    public function testExtractFromPluginWithEmptyDirectory(): void
    {
        $emptyPath     = $this->createTempDir('empty_plugin_');
        $plugin        = new Plugin('local_empty', 'local', 'empty', $emptyPath);
        $fileDiscovery = new FileDiscovery($plugin);
        $this->extractor->setFileDiscovery($fileDiscovery);

        $strings = $this->extractor->extractFromPlugin($plugin);

        $this->assertIsArray($strings);
        $this->assertEmpty($strings);

        // Check metrics for empty plugin
        $metrics = $this->extractor->getPerformanceMetrics();
        $this->assertSame(0, $metrics['files_processed']);
        $this->assertSame(0, $metrics['strings_extracted']);
    }

    /**
     * Test extractFromPlugin with unreadable files.
     */
    public function testExtractFromPluginWithUnreadableFiles(): void
    {
        $pluginPath = $this->createTempDir('unreadable_plugin_');

        // Create a file
        $testFile = $pluginPath . '/test.php';
        file_put_contents($testFile, '<?php get_string("test", "component");');

        // Make it unreadable (this might not work in all environments)
        if (function_exists('chmod')) {
            chmod($testFile, 0000);
        }

        $plugin        = new Plugin('local_unreadable', 'local', 'unreadable', $pluginPath);
        $fileDiscovery = new FileDiscovery($plugin);
        $this->extractor->setFileDiscovery($fileDiscovery);

        $strings = $this->extractor->extractFromPlugin($plugin);

        // Should handle unreadable files gracefully
        $this->assertIsArray($strings);

        // Restore permissions for cleanup
        if (function_exists('chmod')) {
            chmod($testFile, 0644);
        }
    }
}
