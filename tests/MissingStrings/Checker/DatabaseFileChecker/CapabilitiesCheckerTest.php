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

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CapabilitiesChecker;
use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for CapabilitiesChecker class.
 *
 * Tests the capability string detection in db/access.php files including
 * line detection, context information, and various capability formats.
 */
class CapabilitiesCheckerTest extends MissingStringsTestCase
{
    private CapabilitiesChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new CapabilitiesChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Capabilities', $this->checker->getName());
    }

    /**
     * Test that the checker applies when access.php exists.
     */
    public function testAppliesToWithAccessFileReturnsTrue(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $this->createDatabaseFileContent('access', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when access.php doesn't exist.
     */
    public function testAppliesToWithoutAccessFileReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing single capability with full plugin path format.
     */
    public function testCheckWithSingleCapabilityAddsRequiredString(): void
    {
        $capabilities = [
            'mod/testmod:addinstance' => [
                'riskbitmask'  => 'RISK_XSS',
                'captype'      => 'write',
                'contextlevel' => 'CONTEXT_COURSE',
                'archetypes'   => [
                    'editingteacher' => 'CAP_ALLOW',
                    'manager'        => 'CAP_ALLOW',
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $this->createDatabaseFileContent('access', $capabilities),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('testmod:addinstance', $requiredStrings);

        $context = $requiredStrings['testmod:addinstance'];
        $this->assertStringContainsString('db/access.php', $context->getFile());
        $this->assertSame('Capability: mod/testmod:addinstance', $context->getDescription());
        $this->assertNotNull($context->getLine());
    }

    /**
     * Test processing multiple capabilities.
     */
    public function testCheckWithMultipleCapabilitiesAddsAllRequiredStrings(): void
    {
        $capabilities = [
            'mod/testmod:addinstance' => [
                'captype'      => 'write',
                'contextlevel' => 'CONTEXT_COURSE',
            ],
            'mod/testmod:view' => [
                'captype'      => 'read',
                'contextlevel' => 'CONTEXT_MODULE',
            ],
            'mod/testmod:submit' => [
                'captype'      => 'write',
                'contextlevel' => 'CONTEXT_MODULE',
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $this->createDatabaseFileContent('access', $capabilities),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('testmod:addinstance', $requiredStrings);
        $this->assertArrayHasKey('testmod:view', $requiredStrings);
        $this->assertArrayHasKey('testmod:submit', $requiredStrings);

        // Check that each has correct context
        foreach (['testmod:addinstance', 'testmod:view', 'testmod:submit'] as $expectedKey) {
            $context = $requiredStrings[$expectedKey];
            $this->assertStringContainsString('db/access.php', $context->getFile());
            $this->assertStringContainsString('Capability:', $context->getDescription());
            $this->assertNotNull($context->getLine());
        }
    }

    /**
     * Test capability name extraction for different formats.
     */
    public function testCheckWithDifferentCapabilityFormatsExtractsCorrectStringKeys(): void
    {
        $capabilities = [
            'mod/testmod:addinstance'     => ['captype' => 'write'],
            'local/testlocal:manage'      => ['captype' => 'write'],
            'block/testblock:addinstance' => ['captype' => 'write'],
            'simple_capability'           => ['captype' => 'read'],  // No slash format
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/access.php' => $this->createDatabaseFileContent('access', $capabilities),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(4, $requiredStrings);

        // Check expected string keys are extracted correctly
        $this->assertArrayHasKey('testmod:addinstance', $requiredStrings);
        $this->assertArrayHasKey('testlocal:manage', $requiredStrings);
        $this->assertArrayHasKey('testblock:addinstance', $requiredStrings);
        $this->assertArrayHasKey('simple_capability', $requiredStrings);
    }

    /**
     * Test line detection accuracy.
     */
    public function testCheckWithCapabilitiesDetectsCorrectLineNumbers(): void
    {
        $accessContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$capabilities = [\n" .
                        "    'mod/testmod:addinstance' => [\n" .      // Line 6
                        "        'captype' => 'write',\n" .
                        "        'contextlevel' => 'CONTEXT_COURSE'\n" .
                        "    ],\n" .
                        "    'mod/testmod:view' => [\n" .            // Line 10
                        "        'captype' => 'read',\n" .
                        "        'contextlevel' => 'CONTEXT_MODULE'\n" .
                        "    ]\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $accessContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertStringContextHasLine($requiredStrings['testmod:addinstance'], 6);
        $this->assertStringContextHasLine($requiredStrings['testmod:view'], 10);
    }

    /**
     * Test handling of empty access file.
     */
    public function testCheckWithEmptyAccessFileReturnsEmptyResult(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $this->createDatabaseFileContent('access', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertCount(0, $result->getErrors());
        $this->assertCount(0, $result->getWarnings());
    }

    /**
     * Test handling of malformed capability definition.
     */
    public function testCheckWithMalformedCapabilityAddsWarning(): void
    {
        $accessContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$capabilities = [\n" .
                        "    'mod/testmod:addinstance' => 'invalid_string_instead_of_array',\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $accessContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('not an array', $warnings[0]);
    }

    /**
     * Test handling of invalid access file.
     */
    public function testCheckWithInvalidAccessFileAddsWarning(): void
    {
        $accessContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$capabilities = 'not_an_array';\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $accessContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('Could not load db/access.php file', $warnings[0]);
    }

    /**
     * Test FileDiscovery integration.
     */
    public function testCheckWithFileDiscoveryUsesFileDiscoveryService(): void
    {
        $capabilities = [
            'mod/testmod:addinstance' => ['captype' => 'write'],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $this->createDatabaseFileContent('access', $capabilities),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        // Set up FileDiscovery
        $fileDiscovery = new FileDiscovery($plugin);
        $this->checker->setFileDiscovery($fileDiscovery);

        // Test appliesTo with FileDiscovery
        $this->assertTrue($this->checker->appliesTo($plugin));

        // Test check with FileDiscovery
        $result          = $this->checker->check($plugin);
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('testmod:addinstance', $requiredStrings);
    }

    /**
     * Test error handling for corrupted access file.
     */
    public function testCheckWithCorruptedAccessFileHandlesGracefully(): void
    {
        $accessContent = "<?php\n\n" .
                        "syntax error - invalid PHP\n" .
                        '$capabilities = [';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/access.php' => $accessContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('Could not load db/access.php file', $warnings[0]);
    }
}
