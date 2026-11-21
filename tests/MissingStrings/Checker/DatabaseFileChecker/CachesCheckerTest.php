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

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CachesChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for CachesChecker class.
 *
 * Tests the cache definition string detection in db/caches.php files including
 * line detection, context information, and various cache definition formats.
 */
class CachesCheckerTest extends MissingStringsTestCase
{
    private CachesChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new CachesChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Caches', $this->checker->getName());
    }

    /**
     * Test that the checker applies when caches.php exists.
     */
    public function testAppliesToWithCachesFileReturnsTrue(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when caches.php doesn't exist.
     */
    public function testAppliesToWithoutCachesFileReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing single cache definition.
     */
    public function testCheckWithSingleCacheDefinitionAddsRequiredString(): void
    {
        $definitions = [
            'user_progress' => [
                'mode'               => 'application',
                'simplekeys'         => true,
                'staticacceleration' => true,
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', $definitions),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('cachedef_user_progress', $requiredStrings);

        $context = $requiredStrings['cachedef_user_progress'];
        $this->assertStringContainsString('db/caches.php', $context->getFile());
        $this->assertSame('Cache definition: user_progress', $context->getDescription());
        $this->assertNotNull($context->getLine());
    }

    /**
     * Test processing multiple cache definitions.
     */
    public function testCheckWithMultipleCacheDefinitionsAddsAllRequiredStrings(): void
    {
        $definitions = [
            'user_progress' => [
                'mode'       => 'application',
                'simplekeys' => true,
            ],
            'course_modules' => [
                'mode'       => 'request',
                'simplekeys' => false,
            ],
            'activity_data' => [
                'mode'               => 'session',
                'staticacceleration' => true,
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', $definitions),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings, 'Should have 3 required strings');

        $this->assertArrayHasKey('cachedef_user_progress', $requiredStrings);
        $this->assertArrayHasKey('cachedef_course_modules', $requiredStrings);
        $this->assertArrayHasKey('cachedef_activity_data', $requiredStrings);

        // Check that each has correct context
        foreach (['cachedef_user_progress', 'cachedef_course_modules', 'cachedef_activity_data'] as $expectedKey) {
            $context = $requiredStrings[$expectedKey];
            $this->assertStringContainsString('db/caches.php', $context->getFile());
            $this->assertStringContainsString('Cache definition:', $context->getDescription());
            $this->assertNotNull($context->getLine());
        }
    }

    /**
     * Test cache definition string pattern generation.
     */
    public function testCheckWithVariousCacheNamesGeneratesCorrectStringKeys(): void
    {
        $definitions = [
            'simple_cache'        => ['mode' => 'application'],
            'user_data_cache'     => ['mode' => 'session'],
            'course123'           => ['mode' => 'request'],
            'special-chars_cache' => ['mode' => 'application'],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', $definitions),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(4, $requiredStrings);

        // Check expected string keys with cachedef_ prefix
        $this->assertArrayHasKey('cachedef_simple_cache', $requiredStrings);
        $this->assertArrayHasKey('cachedef_user_data_cache', $requiredStrings);
        $this->assertArrayHasKey('cachedef_course123', $requiredStrings);
        $this->assertArrayHasKey('cachedef_special-chars_cache', $requiredStrings);
    }

    /**
     * Test line detection accuracy.
     */
    public function testCheckWithCacheDefinitionsDetectsCorrectLineNumbers(): void
    {
        $cachesContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$definitions = [\n" .
                        "    'user_progress' => [\n" .                  // Line 6
                        "        'mode' => 'application',\n" .
                        "        'simplekeys' => true\n" .
                        "    ],\n" .
                        "    'course_modules' => [\n" .                 // Line 10
                        "        'mode' => 'request',\n" .
                        "        'simplekeys' => false\n" .
                        "    ]\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $cachesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertStringContextHasLine($requiredStrings['cachedef_user_progress'], 6);
        $this->assertStringContextHasLine($requiredStrings['cachedef_course_modules'], 10);
    }

    /**
     * Test handling of empty caches file.
     */
    public function testCheckWithEmptyCachesFileReturnsEmptyResult(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertCount(0, $result->getErrors());
        $this->assertCount(0, $result->getWarnings());
    }

    /**
     * Test handling of malformed cache definition.
     */
    public function testCheckWithMalformedCacheDefinitionAddsWarning(): void
    {
        $cachesContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$definitions = [\n" .
                        "    'user_progress' => 'invalid_string_instead_of_array',\n" .
                        "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $cachesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('not an array', $warnings[0]);
    }

    /**
     * Test handling of invalid caches file.
     */
    public function testCheckWithInvalidCachesFileAddsWarning(): void
    {
        $cachesContent = "<?php\n\n" .
                        "defined('MOODLE_INTERNAL') || die();\n\n" .
                        "\$definitions = 'not_an_array';\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $cachesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('Could not load db/caches.php file', $warnings[0]);
    }

    /**
     * Test complex cache definitions with various options.
     */
    public function testCheckWithComplexCacheDefinitionsProcessesCorrectly(): void
    {
        $definitions = [
            'complex_cache' => [
                'mode'                   => 'application',
                'simplekeys'             => true,
                'simpledata'             => false,
                'staticacceleration'     => true,
                'staticaccelerationsize' => 100,
                'ttl'                    => 300,
                'invalidationevents'     => [
                    'changesincourse',
                    'changesincoursecat',
                ],
            ],
            'minimal_cache' => [
                'mode' => 'session',
            ],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/caches.php' => $this->createDatabaseFileContent('caches', $definitions),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings);

        $this->assertArrayHasKey('cachedef_complex_cache', $requiredStrings);
        $this->assertArrayHasKey('cachedef_minimal_cache', $requiredStrings);

        // Verify context information
        $complexContext = $requiredStrings['cachedef_complex_cache'];
        $this->assertSame('Cache definition: complex_cache', $complexContext->getDescription());

        $minimalContext = $requiredStrings['cachedef_minimal_cache'];
        $this->assertSame('Cache definition: minimal_cache', $minimalContext->getDescription());
    }

    /**
     * Test error handling for corrupted caches file.
     */
    public function testCheckWithCorruptedCachesFileHandlesGracefully(): void
    {
        $cachesContent = "<?php\n\n" .
                        "syntax error - invalid PHP\n" .
                        '$definitions = [';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/caches.php' => $cachesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('Could not load db/caches.php file', $warnings[0]);
    }
}
