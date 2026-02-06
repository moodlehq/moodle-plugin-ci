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

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\SubpluginsChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for SubpluginsChecker class.
 *
 * Tests the subplugin type string detection in db/subplugins.json and db/subplugins.php files
 * including line detection, context information, and various subplugin type formats.
 */
class SubpluginsCheckerTest extends MissingStringsTestCase
{
    private SubpluginsChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new SubpluginsChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Subplugins', $this->checker->getName());
    }

    /**
     * Test that the checker applies when subplugins.json exists.
     */
    public function testAppliesToWithSubpluginsJsonFileReturnsTrue(): void
    {
        $subpluginTypes = [
            'testtype' => 'testmod/testtype',
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['subplugintypes' => $subpluginTypes]),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker applies when subplugins.php exists.
     */
    public function testAppliesToWithSubpluginsPhpFileReturnsTrue(): void
    {
        $subpluginsContent = "<?php\n\n" .
                            "defined('MOODLE_INTERNAL') || die();\n\n" .
                            "\$subplugins = ['testtype' => 'testmod/testtype'];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.php' => $subpluginsContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when neither file exists.
     */
    public function testAppliesToWithoutSubpluginsFilesReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing JSON format with single subplugin type.
     */
    public function testCheckWithJsonFormatSingleTypeAddsRequiredStrings(): void
    {
        $subpluginTypes = [
            'customtype' => 'mod_testmod/customtype',
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['subplugintypes' => $subpluginTypes]),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings); // Singular and plural

        $this->assertArrayHasKey('subplugintype_customtype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_customtype_plural', $requiredStrings);

        // Check context information
        $singularContext = $requiredStrings['subplugintype_customtype'];
        $this->assertStringContainsString('db/subplugins.json', $singularContext->getFile());
        $this->assertSame('Subplugin type: customtype (singular)', $singularContext->getDescription());

        $pluralContext = $requiredStrings['subplugintype_customtype_plural'];
        $this->assertSame('Subplugin type: customtype (plural)', $pluralContext->getDescription());
    }

    /**
     * Test processing PHP format with single subplugin type.
     */
    public function testCheckWithPhpFormatSingleTypeAddsRequiredStrings(): void
    {
        $subpluginsContent = "<?php\n\n" .
                            "defined('MOODLE_INTERNAL') || die();\n\n" .
                            "\$subplugins = [\n" .
                            "    'customtype' => 'mod_testmod/customtype'\n" .
                            "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.php' => $subpluginsContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings); // Singular and plural

        $this->assertArrayHasKey('subplugintype_customtype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_customtype_plural', $requiredStrings);

        // Check context information
        $singularContext = $requiredStrings['subplugintype_customtype'];
        $this->assertStringContainsString('db/subplugins.php', $singularContext->getFile());
        $this->assertSame('Subplugin type: customtype (singular)', $singularContext->getDescription());
    }

    /**
     * Test processing multiple subplugin types.
     */
    public function testCheckWithMultipleSubpluginTypesAddsAllRequiredStrings(): void
    {
        $subpluginTypes = [
            'customtype' => 'mod_testmod/customtype',
            'reporttype' => 'mod_testmod/reporttype',
            'sourcetype' => 'mod_testmod/sourcetype',
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['subplugintypes' => $subpluginTypes]),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(6, $requiredStrings); // 3 types × 2 strings each

        // Check all singular strings
        $this->assertArrayHasKey('subplugintype_customtype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_reporttype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_sourcetype', $requiredStrings);

        // Check all plural strings
        $this->assertArrayHasKey('subplugintype_customtype_plural', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_reporttype_plural', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_sourcetype_plural', $requiredStrings);
    }

    /**
     * Test JSON format with legacy 'plugintypes' key.
     */
    public function testCheckWithLegacyPluginTypesKeyAddsRequiredStrings(): void
    {
        $subpluginTypes = [
            'legacytype' => 'mod_testmod/legacytype',
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['plugintypes' => $subpluginTypes]),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings);

        $this->assertArrayHasKey('subplugintype_legacytype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_legacytype_plural', $requiredStrings);
    }

    /**
     * Test preference for JSON format over PHP format.
     */
    public function testCheckWithBothFormatsPrefersJsonFormat(): void
    {
        $jsonTypes = [
            'jsontype' => 'mod_testmod/jsontype',
        ];

        $phpContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "\$subplugins = ['phptype' => 'mod_testmod/phptype'];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['subplugintypes' => $jsonTypes]),
            'db/subplugins.php'  => $phpContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings);

        // Should have JSON types, not PHP types
        $this->assertArrayHasKey('subplugintype_jsontype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_jsontype_plural', $requiredStrings);
        $this->assertArrayNotHasKey('subplugintype_phptype', $requiredStrings);

        // Context should reference JSON file
        $context = $requiredStrings['subplugintype_jsontype'];
        $this->assertStringContainsString('db/subplugins.json', $context->getFile());
    }

    /**
     * Test line detection accuracy for JSON format.
     */
    public function testCheckWithJsonFormatDetectsCorrectLineNumbers(): void
    {
        $jsonContent = "{\n" .                                      // Line 1
                      "    \"subplugintypes\": {\n" .               // Line 2
                      "        \"customtype\": \"mod_testmod/customtype\",\n" .  // Line 3
                      "        \"reporttype\": \"mod_testmod/reporttype\"\n" .   // Line 4
                      "    }\n" .                                   // Line 5
                      "}\n";                                        // Line 6

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => $jsonContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        // Line detection is based on string literal search for the type name
        $this->assertStringContextHasLine($requiredStrings['subplugintype_customtype'], 3);
        $this->assertStringContextHasLine($requiredStrings['subplugintype_reporttype'], 4);
    }

    /**
     * Test line detection accuracy for PHP format.
     */
    public function testCheckWithPhpFormatDetectsCorrectLineNumbers(): void
    {
        $phpContent = "<?php\n\n" .                               // Lines 1-2
                     "defined('MOODLE_INTERNAL') || die();\n\n" .  // Lines 3-4
                     "\$subplugins = [\n" .                        // Line 5
                     "    'customtype' => 'mod_testmod/customtype',\n" .  // Line 6
                     "    'reporttype' => 'mod_testmod/reporttype'\n" .   // Line 7
                     "];\n";                                       // Line 8

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.php' => $phpContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        // Line detection is based on string literal search for the type name
        $this->assertStringContextHasLine($requiredStrings['subplugintype_customtype'], 6);
        $this->assertStringContextHasLine($requiredStrings['subplugintype_reporttype'], 7);
    }

    /**
     * Test handling of missing subplugins files.
     */
    public function testCheckWithMissingFilesAddsError(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('No subplugins file found', $errors[0]);
    }

    /**
     * Test handling of invalid JSON format.
     */
    public function testCheckWithInvalidJsonFormatAddsError(): void
    {
        $invalidJson = "{\n" .
                      "    \"subplugintypes\": {\n" .
                      "        \"customtype\": \"mod_testmod/customtype\",\n" .
                      "    }\n" .  // Trailing comma - invalid JSON
                      '}';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => $invalidJson,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing subplugins file', $errors[0]);
    }

    /**
     * Test handling of JSON without valid subplugin types.
     */
    public function testCheckWithJsonWithoutValidTypesAddsError(): void
    {
        $invalidStructure = json_encode([
            'invalid_key' => [
                'customtype' => 'mod_testmod/customtype',
            ],
        ]);

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => $invalidStructure,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing subplugins file', $errors[0]);
    }

    /**
     * Test handling of PHP without valid subplugins array.
     */
    public function testCheckWithPhpWithoutValidArrayAddsError(): void
    {
        $phpContent = "<?php\n\n" .
                     "defined('MOODLE_INTERNAL') || die();\n\n" .
                     "// No \$subplugins defined\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.php' => $phpContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing subplugins file', $errors[0]);
    }

    /**
     * Test complex subplugin configurations.
     */
    public function testCheckWithComplexSubpluginTypesProcessesCorrectly(): void
    {
        $subpluginTypes = [
            'simple_type'        => 'mod_testmod/simple',
            'complex_subtype'    => 'mod_testmod/complex',
            'reporting123'       => 'mod_testmod/reporting',
            'special-chars_type' => 'mod_testmod/special',
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => json_encode(['subplugintypes' => $subpluginTypes]),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(8, $requiredStrings); // 4 types × 2 strings each

        // Check all singular strings
        $this->assertArrayHasKey('subplugintype_simple_type', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_complex_subtype', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_reporting123', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_special-chars_type', $requiredStrings);

        // Check all plural strings
        $this->assertArrayHasKey('subplugintype_simple_type_plural', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_complex_subtype_plural', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_reporting123_plural', $requiredStrings);
        $this->assertArrayHasKey('subplugintype_special-chars_type_plural', $requiredStrings);

        // Verify context information
        $singularContext = $requiredStrings['subplugintype_simple_type'];
        $this->assertSame('Subplugin type: simple_type (singular)', $singularContext->getDescription());

        $pluralContext = $requiredStrings['subplugintype_simple_type_plural'];
        $this->assertSame('Subplugin type: simple_type (plural)', $pluralContext->getDescription());
    }

    /**
     * Test error handling for corrupted files.
     */
    public function testCheckWithCorruptedFilesHandlesGracefully(): void
    {
        $corruptedContent = 'corrupted content - not valid JSON or PHP';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/subplugins.json' => $corruptedContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertErrorCount($result, 1);

        $errors = $result->getErrors();
        $this->assertStringContainsString('Error parsing subplugins file', $errors[0]);
    }
}
