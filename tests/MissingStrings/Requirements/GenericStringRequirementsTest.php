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
use MoodlePluginCI\MissingStrings\Requirements\GenericStringRequirements;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for GenericStringRequirements class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Requirements\GenericStringRequirements
 */
class GenericStringRequirementsTest extends MissingStringsTestCase
{
    /** @var GenericStringRequirements */
    private $requirements;

    /** @var Plugin */
    private $plugin;

    /** @var string */
    private $testPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testPluginPath = $this->createTempDir('test_plugin_');
        $this->plugin         = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);
        $this->requirements   = new GenericStringRequirements($this->plugin, 400);
    }

    /**
     * Test constructor.
     */
    public function testConstructor(): void
    {
        $plugin       = new Plugin('block_test', 'block', 'test', '/path/to/plugin');
        $requirements = new GenericStringRequirements($plugin, 311);

        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
    }

    /**
     * Test getRequiredStrings method.
     */
    public function testGetRequiredStrings(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        $this->assertIsArray($requiredStrings);
        $this->assertContains('pluginname', $requiredStrings);
        $this->assertCount(1, $requiredStrings);
    }

    /**
     * Test getRequiredStrings returns same strings for different instances.
     */
    public function testGetRequiredStringsConsistency(): void
    {
        $plugin1       = new Plugin('local_test1', 'local', 'test1', '/path/1');
        $plugin2       = new Plugin('block_test2', 'block', 'test2', '/path/2');
        $requirements1 = new GenericStringRequirements($plugin1, 400);
        $requirements2 = new GenericStringRequirements($plugin2, 311);

        $strings1 = $requirements1->getRequiredStrings();
        $strings2 = $requirements2->getRequiredStrings();

        $this->assertSame($strings1, $strings2);
    }

    /**
     * Test getRequiredStrings with different Moodle versions.
     */
    public function testGetRequiredStringsWithDifferentMoodleVersions(): void
    {
        $requirements39 = new GenericStringRequirements($this->plugin, 39);
        $requirements40 = new GenericStringRequirements($this->plugin, 40);
        $requirements41 = new GenericStringRequirements($this->plugin, 41);

        $strings39 = $requirements39->getRequiredStrings();
        $strings40 = $requirements40->getRequiredStrings();
        $strings41 = $requirements41->getRequiredStrings();

        // Generic requirements should be same regardless of Moodle version
        $this->assertSame($strings39, $strings40);
        $this->assertSame($strings40, $strings41);
        $this->assertContains('pluginname', $strings39);
    }

    /**
     * Test inheritance from AbstractStringRequirements.
     */
    public function testInheritance(): void
    {
        $this->assertInstanceOf(AbstractStringRequirements::class, $this->requirements);
    }

    /**
     * Test getPluginTypePatterns method (inherited from parent).
     */
    public function testGetPluginTypePatterns(): void
    {
        $patterns = $this->requirements->getPluginTypePatterns();

        $this->assertIsArray($patterns);
        $this->assertEmpty($patterns); // Generic requirements have no specific patterns
    }

    /**
     * Test plugin property access through protected methods.
     */
    public function testPluginPropertyAccess(): void
    {
        // Create a test subclass to access protected methods
        $testRequirements = new class($this->plugin, 400) extends GenericStringRequirements {
            public function getTestComponent(): string
            {
                return $this->getComponent();
            }

            public function getTestPluginType(): string
            {
                return $this->getPluginType();
            }

            public function getTestPluginName(): string
            {
                return $this->getPluginName();
            }

            public function testFileExists(string $file): bool
            {
                return $this->fileExists($file);
            }
        };

        $this->assertSame('local_testplugin', $testRequirements->getTestComponent());
        $this->assertSame('local', $testRequirements->getTestPluginType());
        $this->assertSame('testplugin', $testRequirements->getTestPluginName());
    }

    /**
     * Test fileExists method functionality.
     */
    public function testFileExists(): void
    {
        // Create a test subclass to access protected fileExists method
        $testRequirements = new class($this->plugin, 400) extends GenericStringRequirements {
            public function testFileExists(string $file): bool
            {
                return $this->fileExists($file);
            }
        };

        // Test with non-existent file
        $this->assertFalse($testRequirements->testFileExists('nonexistent.php'));

        // Create a test file
        $testFile = 'test.txt';
        file_put_contents($this->testPluginPath . '/' . $testFile, 'test content');

        // Test with existing file
        $this->assertTrue($testRequirements->testFileExists($testFile));
    }

    /**
     * Test with different plugin types.
     */
    public function testWithDifferentPluginTypes(): void
    {
        $localPlugin = new Plugin('local_test', 'local', 'test', '/path/local');
        $blockPlugin = new Plugin('block_test', 'block', 'test', '/path/block');
        $modPlugin   = new Plugin('mod_test', 'mod', 'test', '/path/mod');

        $localReq = new GenericStringRequirements($localPlugin, 400);
        $blockReq = new GenericStringRequirements($blockPlugin, 400);
        $modReq   = new GenericStringRequirements($modPlugin, 400);

        // All should have same required strings since they're generic
        $this->assertSame($localReq->getRequiredStrings(), $blockReq->getRequiredStrings());
        $this->assertSame($blockReq->getRequiredStrings(), $modReq->getRequiredStrings());
    }
}
