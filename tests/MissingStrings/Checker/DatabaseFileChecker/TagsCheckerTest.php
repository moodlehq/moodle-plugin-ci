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

namespace MoodlePluginCI\Tests\MissingStrings\Checker\DatabaseFileChecker;

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\TagsChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for TagsChecker class.
 *
 * Tests the tag area string detection in db/tag.php files including
 * line detection, context information, and various tag area formats.
 */
class TagsCheckerTest extends MissingStringsTestCase
{
    private TagsChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new TagsChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Tags', $this->checker->getName());
    }

    /**
     * Test that the checker applies when tag.php exists.
     */
    public function testAppliesToWithTagFileReturnsTrue(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when tag.php doesn't exist.
     */
    public function testAppliesToWithoutTagFileReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing single tag area.
     */
    public function testCheckWithSingleTagAreaAddsRequiredString(): void
    {
        $tagareas = [
            [
                'itemtype'  => 'course_modules',
                'component' => 'mod_testmod',
                'callback'  => 'mod_testmod_get_tagged_course_modules',
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', $tagareas),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('tagarea_course_modules', $requiredStrings);

        $context = $requiredStrings['tagarea_course_modules'];
        $this->assertStringContainsString('db/tag.php', $context->getFile());
        $this->assertSame('Tag area: course_modules', $context->getDescription());
        $this->assertNotNull($context->getLine());
    }

    /**
     * Test processing multiple tag areas.
     */
    public function testCheckWithMultipleTagAreasAddsAllRequiredStrings(): void
    {
        $tagareas = [
            [
                'itemtype'  => 'course_modules',
                'component' => 'mod_testmod',
                'callback'  => 'mod_testmod_get_tagged_course_modules',
            ],
            [
                'itemtype'  => 'user_content',
                'component' => 'mod_testmod',
                'callback'  => 'mod_testmod_get_tagged_user_content',
            ],
            [
                'itemtype'  => 'activities',
                'component' => 'mod_testmod',
                'callback'  => 'mod_testmod_get_tagged_activities',
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', $tagareas),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('tagarea_course_modules', $requiredStrings);
        $this->assertArrayHasKey('tagarea_user_content', $requiredStrings);
        $this->assertArrayHasKey('tagarea_activities', $requiredStrings);

        // Check that each has correct context
        foreach (['tagarea_course_modules', 'tagarea_user_content', 'tagarea_activities'] as $expectedKey) {
            $context = $requiredStrings[$expectedKey];
            $this->assertStringContainsString('db/tag.php', $context->getFile());
            $this->assertStringContainsString('Tag area:', $context->getDescription());
            $this->assertNotNull($context->getLine());
        }
    }

    /**
     * Test tag area string pattern generation.
     */
    public function testCheckWithVariousItemTypesGeneratesCorrectStringKeys(): void
    {
        $tagareas = [
            [
                'itemtype'  => 'simple_item',
                'component' => 'local_testlocal',
            ],
            [
                'itemtype'  => 'user_generated_content',
                'component' => 'local_testlocal',
            ],
            [
                'itemtype'  => 'resource123',
                'component' => 'local_testlocal',
            ],
            [
                'itemtype'  => 'special-chars_item',
                'component' => 'local_testlocal',
            ],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', $tagareas),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(4, $requiredStrings);

        // Check expected string keys with tagarea_ prefix
        $this->assertArrayHasKey('tagarea_simple_item', $requiredStrings);
        $this->assertArrayHasKey('tagarea_user_generated_content', $requiredStrings);
        $this->assertArrayHasKey('tagarea_resource123', $requiredStrings);
        $this->assertArrayHasKey('tagarea_special-chars_item', $requiredStrings);
    }

    /**
     * Test line detection accuracy.
     */
    public function testCheckWithTagAreasDetectsCorrectLineNumbers(): void
    {
        $tagContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "\$tagareas = [\n" .
                     "    [\n" .                                     // Line 6
                     "        'itemtype' => 'course_modules',\n" .   // Line 7 - this is what we're looking for
                     "        'component' => 'mod_testmod',\n" .
                     "        'callback' => 'mod_testmod_get_tagged_course_modules'\n" .
                     "    ],\n" .
                     "    [\n" .                                     // Line 11
                     "        'itemtype' => 'user_content',\n" .     // Line 12 - this is what we're looking for
                     "        'component' => 'mod_testmod',\n" .
                     "        'callback' => 'mod_testmod_get_tagged_user_content'\n" .
                     "    ]\n" .
                     "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $tagContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        // Line detection is based on string literal search for the itemtype value
        $this->assertStringContextHasLine($requiredStrings['tagarea_course_modules'], 7);
        $this->assertStringContextHasLine($requiredStrings['tagarea_user_content'], 12);
    }

    /**
     * Test handling of empty tag file.
     */
    public function testCheckWithEmptyTagFileReturnsEmptyResult(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertCount(0, $result->getWarnings());
        $this->assertErrorCount($result, 0); // Empty tagareas array is valid, just means no tag areas defined
    }

    /**
     * Test handling of malformed tag area.
     */
    public function testCheckWithMalformedTagAreaSkipsInvalidEntries(): void
    {
        $tagContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "\$tagareas = [\n" .
                     "    [\n" .
                     "        'itemtype' => 'valid_item',\n" .
                     "        'component' => 'mod_testmod'\n" .
                     "    ],\n" .
                     "    'invalid_string_instead_of_array',\n" .
                     "    [\n" .
                     "        // Missing itemtype key\n" .
                     "        'component' => 'mod_testmod'\n" .
                     "    ],\n" .
                     "    [\n" .
                     "        'itemtype' => 'another_valid_item',\n" .
                     "        'component' => 'mod_testmod'\n" .
                     "    ]\n" .
                     "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $tagContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings); // Only valid entries should be processed

        $this->assertArrayHasKey('tagarea_valid_item', $requiredStrings);
        $this->assertArrayHasKey('tagarea_another_valid_item', $requiredStrings);
    }

    /**
     * Test handling of missing tagareas array.
     */
    public function testCheckWithMissingTagAreasArrayAddsError(): void
    {
        $tagContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "// No \$tagareas defined\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $tagContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('No valid $tagareas array found', $errors[0]);
    }

    /**
     * Test handling of invalid tagareas array.
     */
    public function testCheckWithInvalidTagAreasArrayAddsError(): void
    {
        $tagContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "\$tagareas = 'not_an_array';\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $tagContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('No valid $tagareas array found', $errors[0]);
    }

    /**
     * Test complex tag areas with various configurations.
     */
    public function testCheckWithComplexTagAreasProcessesCorrectly(): void
    {
        $tagareas = [
            [
                'itemtype'         => 'complex_content',
                'component'        => 'local_testlocal',
                'callback'         => 'local_testlocal_get_tagged_items',
                'showstandard'     => true,
                'multiplecontexts' => false,
            ],
            [
                'itemtype'  => 'minimal_item',
                'component' => 'local_testlocal',
            ],
            [
                'itemtype'         => 'advanced_resource',
                'component'        => 'local_testlocal',
                'callback'         => 'local_testlocal_get_advanced_resources',
                'showstandard'     => false,
                'multiplecontexts' => true,
                'collection'       => 'local_testlocal',
            ],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/tag.php' => $this->createDatabaseFileContent('tag', $tagareas),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('tagarea_complex_content', $requiredStrings);
        $this->assertArrayHasKey('tagarea_minimal_item', $requiredStrings);
        $this->assertArrayHasKey('tagarea_advanced_resource', $requiredStrings);

        // Verify context information
        $complexContext = $requiredStrings['tagarea_complex_content'];
        $this->assertSame('Tag area: complex_content', $complexContext->getDescription());

        $minimalContext = $requiredStrings['tagarea_minimal_item'];
        $this->assertSame('Tag area: minimal_item', $minimalContext->getDescription());
    }

    /**
     * Test error handling for corrupted tag file.
     */
    public function testCheckWithCorruptedTagFileHandlesGracefully(): void
    {
        $tagContent = "<?php\n\n" .
                     "syntax error - invalid PHP\n" .
                     '$tagareas = [';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/tag.php' => $tagContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing db/tag.php', $errors[0]);
    }
}
