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
use MoodlePluginCI\MissingStrings\Requirements\ModuleStringRequirements;
use MoodlePluginCI\MissingStrings\Requirements\StringRequirementsResolver;
use MoodlePluginCI\PluginValidate\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * Tests for StringRequirementsResolver class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Requirements\StringRequirementsResolver
 */
class StringRequirementsResolverTest extends TestCase
{
    /** @var StringRequirementsResolver */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new StringRequirementsResolver();
    }

    /**
     * Test resolve method with module plugin returns ModuleStringRequirements.
     */
    public function testResolveWithModulePlugin(): void
    {
        $plugin = new Plugin('mod_quiz', 'mod', 'quiz', '/path/to/quiz');

        $requirements = $this->resolver->resolve($plugin, 400);

        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements);
        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
    }

    /**
     * Test resolve method with local plugin returns GenericStringRequirements.
     */
    public function testResolveWithLocalPlugin(): void
    {
        $plugin = new Plugin('local_test', 'local', 'test', '/path/to/local');

        $requirements = $this->resolver->resolve($plugin, 400);

        $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
    }

    /**
     * Test resolve method with block plugin returns GenericStringRequirements.
     */
    public function testResolveWithBlockPlugin(): void
    {
        $plugin = new Plugin('block_html', 'block', 'html', '/path/to/block');

        $requirements = $this->resolver->resolve($plugin, 400);

        $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
    }

    /**
     * Test resolve method with theme plugin returns GenericStringRequirements.
     */
    public function testResolveWithThemePlugin(): void
    {
        $plugin = new Plugin('theme_boost', 'theme', 'boost', '/path/to/theme');

        $requirements = $this->resolver->resolve($plugin, 400);

        $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
    }

    /**
     * Test resolve method with auth plugin returns GenericStringRequirements.
     */
    public function testResolveWithAuthPlugin(): void
    {
        $plugin = new Plugin('auth_manual', 'auth', 'manual', '/path/to/auth');

        $requirements = $this->resolver->resolve($plugin, 400);

        $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
        $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
    }

    /**
     * Test resolve method with different Moodle versions.
     */
    public function testResolveWithDifferentMoodleVersions(): void
    {
        $modPlugin   = new Plugin('mod_assign', 'mod', 'assign', '/path/to/assign');
        $localPlugin = new Plugin('local_test', 'local', 'test', '/path/to/local');

        // Test with different Moodle versions
        $requirements39 = $this->resolver->resolve($modPlugin, 39);
        $requirements40 = $this->resolver->resolve($modPlugin, 40);
        $requirements41 = $this->resolver->resolve($modPlugin, 41);

        $localRequirements39 = $this->resolver->resolve($localPlugin, 39);
        $localRequirements40 = $this->resolver->resolve($localPlugin, 40);

        // All module plugins should return ModuleStringRequirements regardless of version
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements39);
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements40);
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements41);

        // All non-module plugins should return GenericStringRequirements
        $this->assertInstanceOf(GenericStringRequirements::class, $localRequirements39);
        $this->assertInstanceOf(GenericStringRequirements::class, $localRequirements40);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $localRequirements39);
    }

    /**
     * Test resolve method with multiple module plugins.
     */
    public function testResolveWithMultipleModulePlugins(): void
    {
        $plugins = [
            new Plugin('mod_quiz', 'mod', 'quiz', '/path/quiz'),
            new Plugin('mod_assign', 'mod', 'assign', '/path/assign'),
            new Plugin('mod_forum', 'mod', 'forum', '/path/forum'),
            new Plugin('mod_book', 'mod', 'book', '/path/book'),
        ];

        foreach ($plugins as $plugin) {
            $requirements = $this->resolver->resolve($plugin, 400);

            $this->assertInstanceOf(ModuleStringRequirements::class, $requirements);
            $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
        }
    }

    /**
     * Test resolve method with multiple non-module plugins.
     */
    public function testResolveWithMultipleNonModulePlugins(): void
    {
        $plugins = [
            new Plugin('local_test', 'local', 'test', '/path/local'),
            new Plugin('block_html', 'block', 'html', '/path/block'),
            new Plugin('theme_boost', 'theme', 'boost', '/path/theme'),
            new Plugin('auth_manual', 'auth', 'manual', '/path/auth'),
            new Plugin('enrol_manual', 'enrol', 'manual', '/path/enrol'),
            new Plugin('filter_tex', 'filter', 'tex', '/path/filter'),
            new Plugin('qtype_essay', 'qtype', 'essay', '/path/qtype'),
            new Plugin('format_topics', 'format', 'topics', '/path/format'),
        ];

        foreach ($plugins as $plugin) {
            $requirements = $this->resolver->resolve($plugin, 400);

            $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
            $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
            $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
        }
    }

    /**
     * Test resolve method returns different instances for same plugin.
     */
    public function testResolveReturnsDifferentInstances(): void
    {
        $plugin = new Plugin('mod_quiz', 'mod', 'quiz', '/path/to/quiz');

        $requirements1 = $this->resolver->resolve($plugin, 400);
        $requirements2 = $this->resolver->resolve($plugin, 400);

        $this->assertNotSame($requirements1, $requirements2);
        $this->assertSame(get_class($requirements1), get_class($requirements2));
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements1);
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements2);
    }

    /**
     * Test resolve method case sensitivity.
     */
    public function testResolveCaseSensitivity(): void
    {
        // Plugin type should be exactly 'mod', not 'MOD' or 'Mod'
        $plugin1 = new Plugin('mod_test', 'mod', 'test', '/path/test');
        $plugin2 = new Plugin('MOD_test', 'MOD', 'test', '/path/test');   // Uppercase
        $plugin3 = new Plugin('Mod_test', 'Mod', 'test', '/path/test');   // Mixed case

        $requirements1 = $this->resolver->resolve($plugin1, 400);
        $requirements2 = $this->resolver->resolve($plugin2, 400);
        $requirements3 = $this->resolver->resolve($plugin3, 400);

        // Only exact 'mod' should return ModuleStringRequirements
        $this->assertInstanceOf(ModuleStringRequirements::class, $requirements1);
        $this->assertInstanceOf(GenericStringRequirements::class, $requirements2);
        $this->assertInstanceOf(GenericStringRequirements::class, $requirements3);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements2);
        $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements3);
    }

    /**
     * Test resolve method with edge case plugin types.
     */
    public function testResolveWithEdgeCasePluginTypes(): void
    {
        $edgeCases = [
            new Plugin('unknown_test', '', 'test', '/path/test'),           // Empty type
            new Plugin('space_test', ' mod ', 'test', '/path/test'),        // Type with spaces
            new Plugin('null_test', 'null', 'test', '/path/test'),          // Literal 'null'
            new Plugin('false_test', 'false', 'test', '/path/test'),        // Literal 'false'
            new Plugin('numeric_test', '123', 'test', '/path/test'),        // Numeric type
        ];

        foreach ($edgeCases as $plugin) {
            $requirements = $this->resolver->resolve($plugin, 400);

            // All edge cases should fall back to GenericStringRequirements
            $this->assertInstanceOf(GenericStringRequirements::class, $requirements);
            $this->assertInstanceOf(AbstractStringRequirements::class, $requirements);
            $this->assertNotInstanceOf(ModuleStringRequirements::class, $requirements);
        }
    }
}
