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

namespace MoodlePluginCI\Tests\MissingStrings\Requirements;

use MoodlePluginCI\MissingStrings\Requirements\AbstractStringRequirements;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for AbstractStringRequirements class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Requirements\AbstractStringRequirements
 */
class AbstractStringRequirementsTest extends MissingStringsTestCase
{
    /** @var AbstractStringRequirements */
    private $requirements;

    /** @var Plugin */
    private $plugin;

    /** @var string */
    private $testPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testPluginPath = $this->createTempDir('test_abstract_');
        $this->plugin         = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);

        // Create a concrete implementation of the abstract class for testing
        $this->requirements = new class($this->plugin, 400) extends AbstractStringRequirements {
            public function getRequiredStrings(): array
            {
                return ['test_string'];
            }

            // Expose protected methods for testing
            public function testFileExists(string $file): bool
            {
                return $this->fileExists($file);
            }

            public function testGetComponent(): string
            {
                return $this->getComponent();
            }

            public function testGetPluginType(): string
            {
                return $this->getPluginType();
            }

            public function testGetPluginName(): string
            {
                return $this->getPluginName();
            }
        };
    }

    /**
     * Test constructor sets properties correctly.
     */
    public function testConstructor(): void
    {
        $plugin        = new Plugin('block_test', 'block', 'test', '/path/to/plugin');
        $moodleVersion = 311;

        $requirements = new class($plugin, $moodleVersion) extends AbstractStringRequirements {
            public function getRequiredStrings(): array
            {
                return [];
            }

            public function getTestPlugin(): Plugin
            {
                return $this->plugin;
            }

            public function getTestMoodleVersion(): int
            {
                return $this->moodleVersion;
            }
        };

        $this->assertSame($plugin, $requirements->getTestPlugin());
        $this->assertSame($moodleVersion, $requirements->getTestMoodleVersion());
    }

    /**
     * Test getPluginTypePatterns default implementation.
     */
    public function testGetPluginTypePatternsDefault(): void
    {
        $patterns = $this->requirements->getPluginTypePatterns();

        $this->assertIsArray($patterns);
        $this->assertEmpty($patterns);
    }

    /**
     * Test getPluginTypePatterns can be overridden.
     */
    public function testGetPluginTypePatternsOverride(): void
    {
        $customRequirements = new class($this->plugin, 400) extends AbstractStringRequirements {
            public function getRequiredStrings(): array
            {
                return [];
            }

            public function getPluginTypePatterns(): array
            {
                return ['pattern1', 'pattern2'];
            }
        };

        $patterns = $customRequirements->getPluginTypePatterns();

        $this->assertIsArray($patterns);
        $this->assertCount(2, $patterns);
        $this->assertContains('pattern1', $patterns);
        $this->assertContains('pattern2', $patterns);
    }

    /**
     * Test fileExists method with existing file.
     */
    public function testFileExistsWithExistingFile(): void
    {
        $testFile = 'existing.txt';
        file_put_contents($this->testPluginPath . '/' . $testFile, 'test content');

        $this->assertTrue($this->requirements->testFileExists($testFile));
    }

    /**
     * Test fileExists method with non-existing file.
     */
    public function testFileExistsWithNonExistingFile(): void
    {
        $this->assertFalse($this->requirements->testFileExists('nonexistent.txt'));
    }

    /**
     * Test fileExists method with subdirectory file.
     */
    public function testFileExistsWithSubdirectoryFile(): void
    {
        mkdir($this->testPluginPath . '/subdir', 0777, true);
        $testFile = 'subdir/nested.php';
        file_put_contents($this->testPluginPath . '/' . $testFile, '<?php echo "test";');

        $this->assertTrue($this->requirements->testFileExists($testFile));
    }

    /**
     * Test fileExists method with file in non-existing subdirectory.
     */
    public function testFileExistsWithNonExistingSubdirectory(): void
    {
        $this->assertFalse($this->requirements->testFileExists('nonexistent/file.txt'));
    }

    /**
     * Test getComponent method.
     */
    public function testGetComponent(): void
    {
        $this->assertSame('local_testplugin', $this->requirements->testGetComponent());
    }

    /**
     * Test getPluginType method.
     */
    public function testGetPluginType(): void
    {
        $this->assertSame('local', $this->requirements->testGetPluginType());
    }

    /**
     * Test getPluginName method.
     */
    public function testGetPluginName(): void
    {
        $this->assertSame('testplugin', $this->requirements->testGetPluginName());
    }

    /**
     * Test with different plugin types.
     */
    public function testWithDifferentPluginTypes(): void
    {
        $testCases = [
            ['mod_quiz', 'mod', 'quiz'],
            ['block_html', 'block', 'html'],
            ['theme_boost', 'theme', 'boost'],
            ['auth_manual', 'auth', 'manual'],
            ['enrol_guest', 'enrol', 'guest'],
        ];

        foreach ($testCases as [$component, $type, $name]) {
            $plugin = new Plugin($component, $type, $name, '/path/test');

            $requirements = new class($plugin, 400) extends AbstractStringRequirements {
                public function getRequiredStrings(): array
                {
                    return [];
                }

                public function testGetComponent(): string
                {
                    return $this->getComponent();
                }

                public function testGetPluginType(): string
                {
                    return $this->getPluginType();
                }

                public function testGetPluginName(): string
                {
                    return $this->getPluginName();
                }
            };

            $this->assertSame($component, $requirements->testGetComponent());
            $this->assertSame($type, $requirements->testGetPluginType());
            $this->assertSame($name, $requirements->testGetPluginName());
        }
    }

    /**
     * Test with different Moodle versions.
     */
    public function testWithDifferentMoodleVersions(): void
    {
        $versions = [29, 30, 31, 39, 40, 41, 42];

        foreach ($versions as $version) {
            $requirements = new class($this->plugin, $version) extends AbstractStringRequirements {
                public function getRequiredStrings(): array
                {
                    return [];
                }

                public function getTestMoodleVersion(): int
                {
                    return $this->moodleVersion;
                }
            };

            $this->assertSame($version, $requirements->getTestMoodleVersion());
        }
    }

    /**
     * Test fileExists method with edge cases.
     */
    public function testFileExistsEdgeCases(): void
    {
        // Test with parent directory reference
        $this->assertFalse($this->requirements->testFileExists('../test.txt'));

        // Test with absolute path (should not work as it's relative to plugin dir)
        $this->assertFalse($this->requirements->testFileExists('/etc/passwd'));

        // Test with non-existent nested path
        $this->assertFalse($this->requirements->testFileExists('non/existent/path/file.txt'));
    }

    /**
     * Test protected method behavior with special characters in plugin data.
     */
    public function testWithSpecialCharacters(): void
    {
        $specialPlugin = new Plugin('local_test-plugin_123', 'local', 'test-plugin_123', $this->testPluginPath);

        $requirements = new class($specialPlugin, 400) extends AbstractStringRequirements {
            public function getRequiredStrings(): array
            {
                return [];
            }

            public function testGetComponent(): string
            {
                return $this->getComponent();
            }

            public function testGetPluginType(): string
            {
                return $this->getPluginType();
            }

            public function testGetPluginName(): string
            {
                return $this->getPluginName();
            }
        };

        $this->assertSame('local_test-plugin_123', $requirements->testGetComponent());
        $this->assertSame('local', $requirements->testGetPluginType());
        $this->assertSame('test-plugin_123', $requirements->testGetPluginName());
    }

    /**
     * Test that abstract method must be implemented.
     */
    public function testAbstractMethodImplementation(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        $this->assertIsArray($requiredStrings);
        $this->assertContains('test_string', $requiredStrings);
    }
}
