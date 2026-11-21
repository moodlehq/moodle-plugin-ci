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

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MobileChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for MobileChecker class.
 *
 * Tests the mobile app language string detection in db/mobile.php files including
 * line detection, context information, and various mobile addon formats.
 */
class MobileCheckerTest extends MissingStringsTestCase
{
    private MobileChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new MobileChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Mobile', $this->checker->getName());
    }

    /**
     * Test that the checker applies when mobile.php exists.
     */
    public function testAppliesToWithMobileFileReturnsTrue(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when mobile.php doesn't exist.
     */
    public function testAppliesToWithoutMobileFileReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing single mobile addon with language strings.
     */
    public function testCheckWithSingleMobileAddonAddsRequiredStrings(): void
    {
        $addons = [
            'mod_testmod_course_module' => [
                'handlers' => [
                    'mod_testmod_course_module' => [
                        'displaydata' => [
                            'title' => 'testmod_title',
                            'icon'  => 'icon.svg',
                        ],
                    ],
                ],
                'lang' => [
                    ['testmod_title', 'mod_testmod'],
                    ['testmod_description', 'mod_testmod'],
                    ['mobile_view_activity', 'mod_testmod'],
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', $addons),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('testmod_title', $requiredStrings);
        $this->assertArrayHasKey('testmod_description', $requiredStrings);
        $this->assertArrayHasKey('mobile_view_activity', $requiredStrings);

        // Check context information
        $context = $requiredStrings['testmod_title'];
        $this->assertStringContainsString('db/mobile.php', $context->getFile());
        $this->assertSame("Mobile addon 'mod_testmod_course_module' language string", $context->getDescription());
        $this->assertNotNull($context->getLine());
    }

    /**
     * Test processing multiple mobile addons.
     */
    public function testCheckWithMultipleMobileAddonsAddsAllRequiredStrings(): void
    {
        $addons = [
            'mod_testmod_course_module' => [
                'lang' => [
                    ['testmod_title', 'mod_testmod'],
                    ['testmod_view', 'mod_testmod'],
                ],
            ],
            'mod_testmod_user_handler' => [
                'lang' => [
                    ['user_profile_view', 'mod_testmod'],
                    ['user_preferences', 'mod_testmod'],
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', $addons),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(4, $requiredStrings);

        $this->assertArrayHasKey('testmod_title', $requiredStrings);
        $this->assertArrayHasKey('testmod_view', $requiredStrings);
        $this->assertArrayHasKey('user_profile_view', $requiredStrings);
        $this->assertArrayHasKey('user_preferences', $requiredStrings);

        // Verify different addon names in context
        $this->assertSame("Mobile addon 'mod_testmod_course_module' language string",
            $requiredStrings['testmod_title']->getDescription());
        $this->assertSame("Mobile addon 'mod_testmod_user_handler' language string",
            $requiredStrings['user_profile_view']->getDescription());
    }

    /**
     * Test component filtering - only current plugin strings.
     */
    public function testCheckWithMixedComponentsOnlyIncludesCurrentPluginStrings(): void
    {
        $addons = [
            'mod_testmod_addon' => [
                'lang' => [
                    ['testmod_title', 'mod_testmod'],           // Should be included
                    ['core_string', 'core'],                   // Should be excluded
                    ['other_plugin_string', 'mod_other'],      // Should be excluded
                    ['testmod_description', 'mod_testmod'],     // Should be included
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', $addons),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings); // Only strings for mod_testmod

        $this->assertArrayHasKey('testmod_title', $requiredStrings);
        $this->assertArrayHasKey('testmod_description', $requiredStrings);
        $this->assertArrayNotHasKey('core_string', $requiredStrings);
        $this->assertArrayNotHasKey('other_plugin_string', $requiredStrings);
    }

    /**
     * Test line detection accuracy.
     */
    public function testCheckWithMobileAddonsDetectsCorrectLineNumbers(): void
    {
        $mobileContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$addons = [\n" .
                        "    'mod_testmod_addon' => [\n" .
                        "        'lang' => [\n" .
                        "            ['testmod_title', 'mod_testmod'],\n" .       // Line 8
                        "            ['testmod_view', 'mod_testmod']\n" .         // Line 9
                        "        ]\n" .
                        "    ]\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $mobileContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        // Line detection is based on string literal search for the string key
        $this->assertStringContextHasLine($requiredStrings['testmod_title'], 8);
        $this->assertStringContextHasLine($requiredStrings['testmod_view'], 9);
    }

    /**
     * Test handling of empty mobile file.
     */
    public function testCheckWithEmptyMobileFileReturnsEmptyResult(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertCount(0, $result->getWarnings());
        $this->assertErrorCount($result, 0); // Empty addons array is valid, just means no mobile addons defined
    }

    /**
     * Test handling of addon without language strings.
     */
    public function testCheckWithAddonWithoutLangStringsSkipsAddon(): void
    {
        $addons = [
            'mod_testmod_no_lang' => [
                'handlers' => [
                    'mod_testmod_course_module' => [
                        'displaydata' => [
                            'title' => 'testmod_title',
                            'icon'  => 'icon.svg',
                        ],
                    ],
                ],
                // No 'lang' array
            ],
            'mod_testmod_with_lang' => [
                'lang' => [
                    ['testmod_title', 'mod_testmod'],
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', $addons),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings); // Only addon with lang strings
        $this->assertArrayHasKey('testmod_title', $requiredStrings);
    }

    /**
     * Test handling of malformed language entries.
     */
    public function testCheckWithMalformedLangEntriesSkipsInvalidEntries(): void
    {
        $mobileContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$addons = [\n" .
                        "    'mod_testmod_addon' => [\n" .
                        "        'lang' => [\n" .
                        "            ['valid_string', 'mod_testmod'],\n" .
                        "            'invalid_string_not_array',\n" .
                        "            ['incomplete_entry'],\n" .                    // Missing component
                        "            ['another_valid_string', 'mod_testmod']\n" .
                        "        ]\n" .
                        "    ]\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $mobileContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings); // Only valid entries should be processed

        $this->assertArrayHasKey('valid_string', $requiredStrings);
        $this->assertArrayHasKey('another_valid_string', $requiredStrings);
    }

    /**
     * Test handling of missing addons array.
     */
    public function testCheckWithMissingAddonsArrayAddsError(): void
    {
        $mobileContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "// No \$addons defined\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $mobileContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('No valid $addons array found', $errors[0]);
    }

    /**
     * Test handling of invalid addons array.
     */
    public function testCheckWithInvalidAddonsArrayAddsError(): void
    {
        $mobileContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$addons = 'not_an_array';\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $mobileContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('No valid $addons array found', $errors[0]);
    }

    /**
     * Test complex mobile addons with various configurations.
     */
    public function testCheckWithComplexMobileAddonsProcessesCorrectly(): void
    {
        $addons = [
            'mod_testmod_complex' => [
                'handlers' => [
                    'mod_testmod_course_module' => [
                        'displaydata' => [
                            'title' => 'testmod_title',
                            'icon'  => 'icon.svg',
                        ],
                        'restrict' => [
                            'courses' => true,
                        ],
                    ],
                ],
                'lang' => [
                    ['testmod_title', 'mod_testmod'],
                    ['testmod_complex_view', 'mod_testmod'],
                    ['core_string', 'core'], // Should be excluded
                ],
            ],
            'mod_testmod_minimal' => [
                'lang' => [
                    ['minimal_string', 'mod_testmod'],
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $this->createDatabaseFileContent('mobile', $addons),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('testmod_title', $requiredStrings);
        $this->assertArrayHasKey('testmod_complex_view', $requiredStrings);
        $this->assertArrayHasKey('minimal_string', $requiredStrings);

        // Verify context information
        $complexContext = $requiredStrings['testmod_title'];
        $this->assertSame("Mobile addon 'mod_testmod_complex' language string", $complexContext->getDescription());

        $minimalContext = $requiredStrings['minimal_string'];
        $this->assertSame("Mobile addon 'mod_testmod_minimal' language string", $minimalContext->getDescription());
    }

    /**
     * Test error handling for corrupted mobile file.
     */
    public function testCheckWithCorruptedMobileFileHandlesGracefully(): void
    {
        $mobileContent = "<?php\n\n" .
                        "syntax error - invalid PHP\n" .
                        '$addons = [';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/mobile.php' => $mobileContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing db/mobile.php', $errors[0]);
    }
}
