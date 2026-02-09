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

use MoodlePluginCI\MissingStrings\Requirements\GenericStringRequirements;
use MoodlePluginCI\MissingStrings\Requirements\ModuleStringRequirements;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for ModuleStringRequirements class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Requirements\ModuleStringRequirements
 */
class ModuleStringRequirementsTest extends MissingStringsTestCase
{
    /** @var ModuleStringRequirements */
    private $requirements;

    /** @var Plugin */
    private $plugin;

    /** @var string */
    private $testPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testPluginPath = $this->createTempDir('test_module_');
        $this->plugin         = new Plugin('mod_testactivity', 'mod', 'testactivity', $this->testPluginPath);
        $this->requirements   = new ModuleStringRequirements($this->plugin, 400);
    }

    /**
     * Test constructor.
     */
    public function testConstructor(): void
    {
        $plugin       = new Plugin('mod_quiz', 'mod', 'quiz', '/path/to/quiz');
        $requirements = new ModuleStringRequirements($plugin, 311);

        $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
    }

    /**
     * Test getRequiredStrings method includes module-specific strings.
     */
    public function testGetRequiredStrings(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        $this->assertIsArray($requiredStrings);
        $this->assertContains('pluginname', $requiredStrings);       // From parent
        $this->assertContains('modulename', $requiredStrings);       // Module specific
        $this->assertContains('modulenameplural', $requiredStrings); // Module specific
        $this->assertCount(3, $requiredStrings);
    }

    /**
     * Test getRequiredStrings includes parent requirements.
     */
    public function testGetRequiredStringsIncludesParent(): void
    {
        $genericRequirements = new GenericStringRequirements($this->plugin, 400);
        $moduleRequirements  = $this->requirements;

        $genericStrings = $genericRequirements->getRequiredStrings();
        $moduleStrings  = $moduleRequirements->getRequiredStrings();

        // Module requirements should include all generic requirements
        foreach ($genericStrings as $string) {
            $this->assertContains($string, $moduleStrings);
        }

        // Module requirements should have more strings than generic
        $this->assertGreaterThan(count($genericStrings), count($moduleStrings));
    }

    /**
     * Test module-specific strings are added.
     */
    public function testModuleSpecificStrings(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        $moduleSpecificStrings = ['modulename', 'modulenameplural'];

        foreach ($moduleSpecificStrings as $string) {
            $this->assertContains($string, $requiredStrings);
        }
    }

    /**
     * Test getRequiredStrings with different Moodle versions.
     */
    public function testGetRequiredStringsWithDifferentMoodleVersions(): void
    {
        $requirements39 = new ModuleStringRequirements($this->plugin, 39);
        $requirements40 = new ModuleStringRequirements($this->plugin, 40);
        $requirements41 = new ModuleStringRequirements($this->plugin, 41);

        $strings39 = $requirements39->getRequiredStrings();
        $strings40 = $requirements40->getRequiredStrings();
        $strings41 = $requirements41->getRequiredStrings();

        // Module requirements should be same regardless of Moodle version
        $this->assertSame($strings39, $strings40);
        $this->assertSame($strings40, $strings41);

        // Should contain both generic and module-specific strings
        $this->assertContains('pluginname', $strings39);
        $this->assertContains('modulename', $strings39);
        $this->assertContains('modulenameplural', $strings39);
    }

    /**
     * Test inheritance from GenericStringRequirements.
     */
    public function testInheritance(): void
    {
        $this->assertInstanceOf(GenericStringRequirements::class, $this->requirements);
    }

    /**
     * Test with different module plugins.
     */
    public function testWithDifferentModulePlugins(): void
    {
        $quizPlugin   = new Plugin('mod_quiz', 'mod', 'quiz', '/path/quiz');
        $assignPlugin = new Plugin('mod_assign', 'mod', 'assign', '/path/assign');
        $forumPlugin  = new Plugin('mod_forum', 'mod', 'forum', '/path/forum');

        $quizReq   = new ModuleStringRequirements($quizPlugin, 400);
        $assignReq = new ModuleStringRequirements($assignPlugin, 400);
        $forumReq  = new ModuleStringRequirements($forumPlugin, 400);

        // All module plugins should have same required strings
        $this->assertSame($quizReq->getRequiredStrings(), $assignReq->getRequiredStrings());
        $this->assertSame($assignReq->getRequiredStrings(), $forumReq->getRequiredStrings());
    }

    /**
     * Test getRequiredStrings returns consistent array.
     */
    public function testGetRequiredStringsConsistency(): void
    {
        $strings1 = $this->requirements->getRequiredStrings();
        $strings2 = $this->requirements->getRequiredStrings();

        $this->assertSame($strings1, $strings2);
    }

    /**
     * Test that module requirements contain expected string count.
     */
    public function testRequiredStringCount(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        // Should have: pluginname (from generic) + modulename + modulenameplural
        $this->assertCount(3, $requiredStrings);
    }

    /**
     * Test string ordering in requirements.
     */
    public function testStringOrdering(): void
    {
        $requiredStrings = $this->requirements->getRequiredStrings();

        // Generic strings should come first (from parent::getRequiredStrings())
        $this->assertSame('pluginname', $requiredStrings[0]);

        // Module-specific strings should follow
        $this->assertContains('modulename', array_slice($requiredStrings, 1));
        $this->assertContains('modulenameplural', array_slice($requiredStrings, 1));
    }

    /**
     * Test plugin property access through protected methods.
     */
    public function testPluginPropertyAccess(): void
    {
        // Create a test subclass to access protected methods
        $testRequirements = new class($this->plugin, 400) extends ModuleStringRequirements {
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
        };

        $this->assertSame('mod_testactivity', $testRequirements->getTestComponent());
        $this->assertSame('mod', $testRequirements->getTestPluginType());
        $this->assertSame('testactivity', $testRequirements->getTestPluginName());
    }
}
