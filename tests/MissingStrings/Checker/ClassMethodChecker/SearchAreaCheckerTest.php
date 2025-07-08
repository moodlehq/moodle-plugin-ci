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

namespace MoodlePluginCI\Tests\MissingStrings\Checker\ClassMethodChecker;

use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\SearchAreaChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test the SearchAreaChecker class.
 *
 * Tests search area class detection and required search:{classname} strings
 * for various types of search implementations.
 */
class SearchAreaCheckerTest extends MissingStringsTestCase
{
    private SearchAreaChecker $checker;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new SearchAreaChecker();
    }

    /**
     * Test checker name.
     */
    public function testGetName(): void
    {
        $this->assertSame('Search Area', $this->checker->getName());
    }

    /**
     * Test that checker applies to plugins with search area classes.
     */
    public function testAppliesToWithSearchAreaClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker doesn't apply to plugins without search area classes.
     */
    public function testAppliesToWithoutSearchAreaClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    return "No search classes";
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test basic search area class extending core_search\base.
     */
    public function testCheckBasicSearchAreaClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:content', $requiredStrings);
    }

    /**
     * Test search area class extending core_search\base_mod.
     */
    public function testCheckSearchAreaBaseModClass(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/search/activity.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class activity extends \core_search\base_mod {
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:activity', $requiredStrings);
    }

    /**
     * Test search area class extending core_search\base_activity.
     */
    public function testCheckSearchAreaBaseActivityClass(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/search/post.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class post extends \core_search\base_activity {
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:post', $requiredStrings);
    }

    /**
     * Test search area class with short class names (without namespace).
     */
    public function testCheckSearchAreaShortClassName(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/data.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class data extends base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:data', $requiredStrings);
    }

    /**
     * Test multiple search area classes in the same plugin.
     */
    public function testCheckMultipleSearchAreaClasses(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
            'classes/search/document.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class document extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
            'classes/search/item.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class item extends base_mod {
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('search:content', $requiredStrings);
        $this->assertArrayHasKey('search:document', $requiredStrings);
        $this->assertArrayHasKey('search:item', $requiredStrings);
    }

    /**
     * Test that non-search classes in classes/search/ are ignored.
     */
    public function testCheckNonSearchClassIgnored(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/helper.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class helper {
    public function process_data($data) {
        return $data;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test classes outside of classes/search/ directory are ignored.
     */
    public function testCheckClassesOutsideSearchDirectoryIgnored(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test search area class with uppercase characters in name.
     */
    public function testCheckSearchAreaClassWithUppercase(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/MyContent.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class MyContent extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should convert to lowercase for string key
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:mycontent', $requiredStrings);
    }

    /**
     * Test error handling for malformed search class file.
     */
    public function testCheckMalformedSearchClassFile(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/invalid.php' => '<?php
defined("MOODLE_INTERNAL") || die();

// Malformed PHP syntax
class invalid extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        // Missing closing brace
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);
    }

    /**
     * Test error handling for unreadable search class file.
     */
    public function testCheckUnreadableSearchClassFile(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        // Make the file unreadable
        $searchFile = $pluginDir . '/classes/search/content.php';
        chmod($searchFile, 0000);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);

        // Restore permissions for cleanup
        chmod($searchFile, 0644);
    }

    /**
     * Test context information includes correct file paths.
     */
    public function testCheckContextInformation(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);

        $errors     = $result->getErrors();
        $searchFile = $pluginDir . '/classes/search/content.php';

        foreach ($errors as $error) {
            $this->assertSame($searchFile, $error['file']);
            $this->assertGreaterThan(0, $error['line']);
            $this->assertNotEmpty($error['description']);
            $this->assertStringContainsString('Search area display name', $error['description']);
        }
    }

    /**
     * Test search area classes in subdirectories.
     */
    public function testCheckSearchAreaClassInSubdirectory(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/forum/post.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class post extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:post', $requiredStrings);
    }

    /**
     * Test complex search area class with interfaces and additional methods.
     */
    public function testCheckComplexSearchAreaClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/advanced_content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class advanced_content extends \core_search\base implements \core_search\base_document_filtering {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    public function get_document($record, $options = array()) {
        return false;
    }

    public function uses_file_indexing() {
        return true;
    }

    public function attach_files(\core_search\document $document) {
        return false;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:advanced_content', $requiredStrings);
    }

    /**
     * Test abstract search area class (should still require string).
     */
    public function testCheckAbstractSearchAreaClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/search/base_content.php' => '<?php
defined("MOODLE_INTERNAL") || die();

abstract class base_content extends \core_search\base {
    public function get_document_recordset($modifiedfrom = 0) {
        return false;
    }

    abstract public function get_custom_data($record);
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('search:base_content', $requiredStrings);
    }
}
