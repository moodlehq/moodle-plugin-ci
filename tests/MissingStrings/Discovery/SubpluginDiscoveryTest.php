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

namespace MoodlePluginCI\Tests\MissingStrings\Discovery;

use MoodlePluginCI\MissingStrings\Discovery\SubpluginDiscovery;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for SubpluginDiscovery class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Discovery\SubpluginDiscovery
 */
class SubpluginDiscoveryTest extends MissingStringsTestCase
{
    /** @var SubpluginDiscovery */
    private $discovery;

    /** @var string */
    private $testPluginPath;

    /** @var Plugin */
    private $testPlugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discovery = new SubpluginDiscovery();

        // Create mock Moodle root structure
        $moodleRoot = $this->createTempDir('moodle_');

        // Create Moodle config files
        file_put_contents($moodleRoot . '/config.php', "<?php\n// Mock config\n");
        file_put_contents($moodleRoot . '/version.php', "<?php\n\$version = 2023100900;\n\$branch = 401;\n");

        // Create local plugin directory
        $this->createDirectorySafe($moodleRoot . '/local');
        $this->testPluginPath = $moodleRoot . '/local/testplugin';
        $this->createDirectorySafe($this->testPluginPath);
        $this->createSimpleTestPlugin();

        // Create plugin instance
        $this->testPlugin = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Temp directories are cleaned up automatically by base class
    }

    /**
     * Test discovering subplugins with no subplugin definitions.
     */
    public function testDiscoverSubpluginsWithNoDefinitions(): void
    {
        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test discovering subplugins from JSON definition.
     */
    public function testDiscoverSubpluginsFromJson(): void
    {
        // Create subplugins.json
        $subpluginsData = [
            'plugintypes' => [
                'testsubtype' => 'mod/testmod/subplugins',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/subplugins');

        // Create subplugin structure
        $this->createSubplugin('testsubtype', 'subplugin1', 'mod/testmod/subplugins');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertInstanceOf(Plugin::class, $subplugins[0]);
        $this->assertSame('testsubtype_subplugin1', $subplugins[0]->component);
        $this->assertSame('testsubtype', $subplugins[0]->type);
        $this->assertSame('subplugin1', $subplugins[0]->name);
    }

    /**
     * Test discovering subplugins from JSON with legacy key.
     */
    public function testDiscoverSubpluginsFromJsonWithLegacyKey(): void
    {
        // Create subplugins.json with legacy 'subplugintypes' key
        $subpluginsData = [
            'subplugintypes' => [
                'legacytype' => 'mod/testmod/legacy',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/legacy');

        // Create subplugin structure
        $this->createSubplugin('legacytype', 'legacy1', 'mod/testmod/legacy');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertSame('legacytype_legacy1', $subplugins[0]->component);
    }

    /**
     * Test discovering subplugins from PHP definition.
     */
    public function testDiscoverSubpluginsFromPhp(): void
    {
        // Create subplugins.php
        $subpluginsPhp = "<?php\n\$subplugins = [\n    'phptype' => 'mod/testmod/phpsubplugins',\n];\n";
        $this->createSubpluginsPhp($subpluginsPhp);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/phpsubplugins');

        // Create subplugin structure
        $this->createSubplugin('phptype', 'phpsubplugin1', 'mod/testmod/phpsubplugins');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertSame('phptype_phpsubplugin1', $subplugins[0]->component);
    }

    /**
     * Test discovering multiple subplugins of different types.
     */
    public function testDiscoverMultipleSubpluginTypes(): void
    {
        // Create multiple subplugin types
        $subpluginsData = [
            'plugintypes' => [
                'type1' => 'mod/testmod/type1',
                'type2' => 'mod/testmod/type2',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/type1');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/type2');

        // Create subplugins of different types
        $this->createSubplugin('type1', 'subplugin1', 'mod/testmod/type1');
        $this->createSubplugin('type1', 'subplugin2', 'mod/testmod/type1');
        $this->createSubplugin('type2', 'subplugin3', 'mod/testmod/type2');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(3, $subplugins);

        // Check components
        $components = array_map(function ($plugin) {
            return $plugin->component;
        }, $subplugins);

        $this->assertContains('type1_subplugin1', $components);
        $this->assertContains('type1_subplugin2', $components);
        $this->assertContains('type2_subplugin3', $components);
    }

    /**
     * Test discovering subplugins with invalid JSON.
     */
    public function testDiscoverSubpluginsWithInvalidJson(): void
    {
        // Create invalid JSON file
        $this->createDirectorySafe($this->testPluginPath . '/db');
        file_put_contents($this->testPluginPath . '/db/subplugins.json', '{ invalid json }');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test discovering subplugins with corrupted PHP file.
     */
    public function testDiscoverSubpluginsWithCorruptedPhp(): void
    {
        // Create corrupted PHP file
        $this->createDirectorySafe($this->testPluginPath . '/db');
        file_put_contents($this->testPluginPath . '/db/subplugins.php', '<?php syntax error here');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test discovering subplugins with non-existent base path.
     */
    public function testDiscoverSubpluginsWithNonExistentBasePath(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'nonexistent' => 'mod/nonexistent/path',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test discovering subplugins with invalid plugin structure.
     */
    public function testDiscoverSubpluginsWithInvalidStructure(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'testtype' => 'mod/testmod/invalid',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create directory but no valid plugin files
        $basePath = $this->getMoodleRoot() . '/mod/testmod/invalid';
        $this->createDirectorySafe($basePath . '/invalidplugin');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test discovering subplugins that have only lang files.
     */
    public function testDiscoverSubpluginsWithOnlyLangFiles(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'langonly' => 'mod/testmod/langonly',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/langonly');

        // Create subplugin with only language files
        $basePath      = $this->getMoodleRoot() . '/mod/testmod/langonly';
        $subpluginPath = $basePath . '/langplugin';
        $this->createDirectorySafe($subpluginPath . '/lang/en');
        file_put_contents($subpluginPath . '/lang/en/langonly_langplugin.php', "<?php\n\$string['test'] = 'Test';\n");

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertSame('langonly_langplugin', $subplugins[0]->component);
    }

    /**
     * Test discovering subplugins with lib.php only.
     */
    public function testDiscoverSubpluginsWithLibOnly(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'libonly' => 'mod/testmod/libonly',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/libonly');

        // Create subplugin with only lib.php
        $basePath      = $this->getMoodleRoot() . '/mod/testmod/libonly';
        $subpluginPath = $basePath . '/libplugin';
        $this->createDirectorySafe($subpluginPath);
        file_put_contents($subpluginPath . '/lib.php', "<?php\n// Library functions\n");

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertSame('libonly_libplugin', $subplugins[0]->component);
    }

    /**
     * Test discovering subplugins skips hidden and common directories.
     */
    public function testDiscoverSubpluginsSkipsHiddenAndCommonDirectories(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'skiptest' => 'mod/testmod/skiptest',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        // Create required directory structure
        $moodleRoot = $this->getMoodleRoot();
        $this->createDirectorySafe($moodleRoot . '/mod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod');
        $this->createDirectorySafe($moodleRoot . '/mod/testmod/skiptest');

        $basePath = $this->getMoodleRoot() . '/mod/testmod/skiptest';

        // Create directories that should be skipped
        $this->createDirectorySafe($basePath . '/.hidden');
        $this->createDirectorySafe($basePath . '/tests');
        $this->createDirectorySafe($basePath . '/backup');
        $this->createDirectorySafe($basePath . '/tmp');

        // Create valid subplugin
        $this->createSubplugin('skiptest', 'validplugin', 'mod/testmod/skiptest');

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        $this->assertCount(1, $subplugins);
        $this->assertSame('skiptest_validplugin', $subplugins[0]->component);
    }

    /**
     * Test getSubpluginPaths method directly.
     */
    public function testGetSubpluginPaths(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'jsontype' => 'mod/testmod/json',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        $paths = $this->discovery->getSubpluginPaths($this->testPlugin);

        $this->assertIsArray($paths);
        $this->assertArrayHasKey('jsontype', $paths);
        $this->assertSame('mod/testmod/json', $paths['jsontype']);
    }

    /**
     * Test getSubpluginPaths with both JSON and PHP definitions.
     */
    public function testGetSubpluginPathsWithBothFormats(): void
    {
        // JSON takes precedence
        $subpluginsJson = [
            'plugintypes' => [
                'jsontype' => 'mod/testmod/json',
            ],
        ];

        $this->createSubpluginsJson($subpluginsJson);

        $subpluginsPhp = "<?php\n\$subplugins = [\n    'phptype' => 'mod/testmod/php',\n];\n";
        $this->createSubpluginsPhp($subpluginsPhp);

        $paths = $this->discovery->getSubpluginPaths($this->testPlugin);

        $this->assertCount(2, $paths);
        $this->assertArrayHasKey('jsontype', $paths);
        $this->assertArrayHasKey('phptype', $paths);
        $this->assertSame('mod/testmod/json', $paths['jsontype']);
        $this->assertSame('mod/testmod/php', $paths['phptype']);
    }

    /**
     * Test with unreadable subplugin directory.
     */
    public function testDiscoverSubpluginsWithUnreadableDirectory(): void
    {
        $subpluginsData = [
            'plugintypes' => [
                'unreadable' => 'mod/testmod/unreadable',
            ],
        ];

        $this->createSubpluginsJson($subpluginsData);

        $basePath = $this->getMoodleRoot() . '/mod/testmod/unreadable';
        $this->createDirectorySafe($basePath);

        // Make directory unreadable
        chmod($basePath, 0000);

        $subplugins = $this->discovery->discoverSubplugins($this->testPlugin);

        // Restore permissions for cleanup
        chmod($basePath, 0755);

        $this->assertIsArray($subplugins);
        $this->assertEmpty($subplugins);
    }

    /**
     * Test Moodle root detection for various plugin types.
     */
    public function testMoodleRootDetectionForDifferentPluginTypes(): void
    {
        // Test with different plugin types
        $pluginTypes = [
            'mod'   => 'mod/testmod',
            'local' => 'local/testlocal',
            'block' => 'blocks/testblock',
            'theme' => 'theme/testtheme',
        ];

        foreach ($pluginTypes as $type => $path) {
            $pluginPath = $this->getMoodleRoot() . '/' . $path;
            $this->createDirectorySafe($pluginPath);

            $versionContent = "<?php\n\$plugin->component = '{$type}_test';\n\$plugin->version = 2023010100;\n";
            file_put_contents($pluginPath . '/version.php', $versionContent);

            $plugin = new Plugin($type . '_test', $type, 'test', $pluginPath);

            $subpluginsData = [
                'plugintypes' => [
                    'testtype' => $path . '/subplugins',
                ],
            ];

            // Create db directory and subplugins.json
            $this->createDirectorySafe($pluginPath . '/db');
            file_put_contents($pluginPath . '/db/subplugins.json', json_encode($subpluginsData));

            // Create subplugin
            $this->createSubplugin('testtype', 'testsub', $path . '/subplugins');

            $subplugins = $this->discovery->discoverSubplugins($plugin);

            $this->assertNotEmpty($subplugins, "Failed to discover subplugins for {$type}");
        }
    }

    /**
     * Create a test plugin directory structure.
     */
    private function createSimpleTestPlugin(): void
    {
        // Directory is already created by createTempDir in setUp

        // Create version.php
        $versionContent = "<?php\n\$plugin->component = 'local_testplugin';\n\$plugin->version = 2023010100;\n";
        file_put_contents($this->testPluginPath . '/version.php', $versionContent);
    }

    /**
     * Safely create a directory if it doesn't exist.
     */
    private function createDirectorySafe(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Create subplugins.json file.
     *
     * @param array $data The subplugin data
     */
    private function createSubpluginsJson(array $data): void
    {
        $this->createDirectorySafe($this->testPluginPath . '/db');
        file_put_contents(
            $this->testPluginPath . '/db/subplugins.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create subplugins.php file.
     *
     * @param string $content The PHP content
     */
    private function createSubpluginsPhp(string $content): void
    {
        $this->createDirectorySafe($this->testPluginPath . '/db');
        file_put_contents($this->testPluginPath . '/db/subplugins.php', $content);
    }

    /**
     * Create a subplugin with proper structure.
     *
     * @param string $type     The subplugin type
     * @param string $name     The subplugin name
     * @param string $basePath The base path relative to Moodle root
     */
    private function createSubplugin(string $type, string $name, string $basePath): void
    {
        $moodleRoot    = $this->getMoodleRoot();
        $subpluginPath = $moodleRoot . '/' . $basePath . '/' . $name;

        $this->createDirectorySafe($subpluginPath);

        // Create version.php
        $component      = $type . '_' . $name;
        $versionContent = "<?php\n\$plugin->component = '{$component}';\n\$plugin->version = 2023010100;\n";
        file_put_contents($subpluginPath . '/version.php', $versionContent);

        // Create language file
        $this->createDirectorySafe($subpluginPath . '/lang/en');
        $langContent = "<?php\n\$string['pluginname'] = 'Test Subplugin {$name}';\n";
        file_put_contents($subpluginPath . "/lang/en/{$component}.php", $langContent);
    }

    /**
     * Get mock Moodle root directory.
     *
     * @return string
     */
    private function getMoodleRoot(): string
    {
        // Return the Moodle root based on the test plugin's directory structure
        // Since testPlugin is at /moodle_root/local/testplugin, go up 2 levels
        return dirname($this->testPluginPath, 2);
    }
}
