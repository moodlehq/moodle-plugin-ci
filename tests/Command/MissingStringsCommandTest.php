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

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Command\MissingStringsCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests for MissingStringsCommand class.
 *
 * @covers \MoodlePluginCI\Command\MissingStringsCommand
 */
class MissingStringsCommandTest extends MissingStringsTestCase
{
    /** @var Application */
    private $application;

    /** @var MissingStringsCommand */
    private $command;

    /** @var CommandTester */
    private $commandTester;

    /** @var string */
    private $testPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test plugin directory
        $this->testPluginPath = $this->createTempDir('test_plugin_');
        $this->createCommandTestPlugin();

        // Set up command with mocked dependencies
        $this->application = new Application();
        $this->command     = new MissingStringsCommand();

        // Mock the Moodle and plugin dependencies
        $this->command->moodle = new DummyMoodle($this->createTempDir('moodle_'));
        $this->command->plugin = new MoodlePlugin($this->testPluginPath);

        $this->application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Temp directories are cleaned up automatically by base class
    }

    /**
     * Test command configuration.
     */
    public function testCommandConfiguration(): void
    {
        $this->assertSame('missingstrings', $this->command->getName());
        $this->assertContains('missing-strings', $this->command->getAliases());
        $this->assertStringContainsString('missing language strings', $this->command->getDescription());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('lang'));
        $this->assertTrue($definition->hasOption('strict'));
        $this->assertTrue($definition->hasOption('unused'));
        $this->assertTrue($definition->hasOption('exclude-patterns'));
        $this->assertTrue($definition->hasOption('debug'));
    }

    /**
     * Test command with valid plugin.
     */
    public function testCommandWithValidPlugin(): void
    {
        $this->createLanguageFile(['pluginname' => 'Test Plugin']);

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('All language strings are valid', $output);
        $this->assertStringContainsString('No issues found', $output);
    }

    /**
     * Test command with missing required string.
     */
    public function testCommandWithMissingRequiredString(): void
    {
        // Don't create language file, should fail

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
        ]);

        $this->assertSame(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Language string validation failed', $output);
        $this->assertStringContainsString('Errors:', $output);
    }

    /**
     * Test command with strict mode option.
     */
    public function testCommandWithStrictMode(): void
    {
        $this->createLanguageFile([
            'pluginname'    => 'Test Plugin',
            'unused_string' => 'Unused string',
        ]);

        $exitCode = $this->commandTester->execute([
            'plugin'   => $this->testPluginPath,
            '--strict' => true,
            '--unused' => true,
        ]);

        $this->assertSame(1, $exitCode); // Should fail due to unused string in strict mode
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Warnings:', $output);
    }

    /**
     * Test command with unused strings option.
     */
    public function testCommandWithUnusedStringsOption(): void
    {
        $this->createLanguageFile([
            'pluginname'    => 'Test Plugin',
            'unused_string' => 'Unused string',
        ]);

        $exitCode = $this->commandTester->execute([
            'plugin'   => $this->testPluginPath,
            '--unused' => true,
        ]);

        $this->assertSame(0, $exitCode); // Should pass (warnings don't fail in non-strict mode)
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Warnings:', $output);
        $this->assertStringContainsString('unused_string', $output);
    }

    /**
     * Test command with exclude patterns option.
     */
    public function testCommandWithExcludePatternsOption(): void
    {
        $this->createLanguageFile([
            'pluginname'    => 'Test Plugin',
            'test_string'   => 'Test string',
            'debug_message' => 'Debug message',
        ]);

        $exitCode = $this->commandTester->execute([
            'plugin'             => $this->testPluginPath,
            '--unused'           => true,
            '--exclude-patterns' => 'test_*,debug_*',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();

        // Should not report excluded strings as unused
        $this->assertStringNotContainsString('test_string', $output);
        $this->assertStringNotContainsString('debug_message', $output);
    }

    /**
     * Test command with language option.
     */
    public function testCommandWithLanguageOption(): void
    {
        // Create French language file
        $langDir = $this->testPluginPath . '/lang/fr';
        if (!is_dir($langDir)) {
            mkdir($langDir, 0755, true);
        }
        $langContent = "<?php\n\$string['pluginname'] = 'Plugin de test';\n";
        file_put_contents($this->testPluginPath . '/lang/fr/local_testplugin.php', $langContent);

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
            '--lang' => 'fr',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('All language strings are valid', $output);
    }

    /**
     * Test command with debug option.
     */
    public function testCommandWithDebugOption(): void
    {
        $this->createLanguageFile(['pluginname' => 'Test Plugin']);

        $exitCode = $this->commandTester->execute([
            'plugin'  => $this->testPluginPath,
            '--debug' => true,
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $this->assertSame(0, $exitCode);
        // Debug mode doesn't change success output much, but enables debug information for errors
    }

    /**
     * Test command with missing language file for specified language.
     */
    public function testCommandWithMissingLanguageFileForSpecifiedLanguage(): void
    {
        $this->createLanguageFile(['pluginname' => 'Test Plugin']); // Creates English only

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
            '--lang' => 'de', // German not available
        ]);

        $this->assertSame(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Language string validation failed', $output);
    }

    /**
     * Test command with plugin containing used strings.
     */
    public function testCommandWithPluginContainingUsedStrings(): void
    {
        $this->createLanguageFile([
            'pluginname'     => 'Test Plugin',
            'used_string'    => 'Used string',
            'missing_string' => 'This will be missing from code',
        ]);

        // Create PHP file with string usage
        file_put_contents(
            $this->testPluginPath . '/lib.php',
            "<?php\nget_string('used_string', 'local_testplugin');\nget_string('nonexistent', 'local_testplugin');\n"
        );

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
        ]);

        $this->assertSame(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('nonexistent', $output); // Should report missing used string
        $this->assertStringContainsString('Errors:', $output);
    }

    /**
     * Test command shows proper summary.
     */
    public function testCommandShowsProperSummary(): void
    {
        $this->createLanguageFile([
            'pluginname'    => 'Test Plugin',
            'unused_string' => 'Unused string',
        ]);

        // Create PHP file that uses a non-existent string
        file_put_contents(
            $this->testPluginPath . '/lib.php',
            "<?php\nget_string('missing_string', 'local_testplugin');\n"
        );

        $exitCode = $this->commandTester->execute([
            'plugin'   => $this->testPluginPath,
            '--unused' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $output = $this->commandTester->getDisplay();

        // Should show summary
        $this->assertStringContainsString('Summary:', $output);
        $this->assertStringContainsString('Errors:', $output);
        $this->assertStringContainsString('Warnings:', $output);
        $this->assertStringContainsString('Total issues:', $output);
        $this->assertStringContainsString('Language string validation failed', $output);
    }

    /**
     * Test command with empty exclude patterns.
     */
    public function testCommandWithEmptyExcludePatterns(): void
    {
        $this->createLanguageFile(['pluginname' => 'Test Plugin']);

        $exitCode = $this->commandTester->execute([
            'plugin'             => $this->testPluginPath,
            '--exclude-patterns' => '',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('All language strings are valid', $output);
    }

    /**
     * Test command header output.
     */
    public function testCommandHeaderOutput(): void
    {
        $this->createLanguageFile(['pluginname' => 'Test Plugin']);

        $exitCode = $this->commandTester->execute([
            'plugin' => $this->testPluginPath,
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Checking for missing language strings', $output);
    }

    /**
     * Test command with module plugin (different naming convention).
     */
    public function testCommandWithModulePlugin(): void
    {
        // Create module plugin structure
        $modulePath = $this->createTempDir('test_module_');
        // Directory is already created by createTempDir()

        // Create version.php for module
        $versionContent = "<?php\n\$plugin->component = 'mod_testmodule';\n\$plugin->version = 2023010100;\n";
        file_put_contents($modulePath . '/version.php', $versionContent);

        // Create language file with module naming convention
        $langDir = $modulePath . '/lang/en';
        if (!is_dir($langDir)) {
            mkdir($langDir, 0755, true);
        }
        $langContent = "<?php\n\$string['pluginname'] = 'Test Module';\n\$string['modulename'] = 'Test Module';\n";
        file_put_contents($modulePath . '/lang/en/mod_testmodule.php', $langContent);

        // Unset the plugin property so the command will use the new plugin path
        unset($this->command->plugin);

        $exitCode = $this->commandTester->execute([
            'plugin' => $modulePath,
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('All language strings are valid', $output);

        // Temp directories are cleaned up automatically by base class
    }

    /**
     * Create a test plugin directory structure.
     */
    private function createCommandTestPlugin(): void
    {
        // Directory is already created by createTempDir()

        // Create version.php
        $versionContent = "<?php\n\$plugin->component = 'local_testplugin';\n\$plugin->version = 2023010100;\n";
        file_put_contents($this->testPluginPath . '/version.php', $versionContent);
    }

    /**
     * Create a language file for the test plugin.
     *
     * @param array $strings Array of string key => value pairs
     */
    private function createLanguageFile(array $strings): void
    {
        $langDir = $this->testPluginPath . '/lang/en';
        if (!is_dir($langDir)) {
            mkdir($langDir, 0755, true);
        }

        $langContent = "<?php\n";
        foreach ($strings as $key => $value) {
            $langContent .= "\$string['{$key}'] = '{$value}';\n";
        }

        file_put_contents($this->testPluginPath . '/lang/en/local_testplugin.php', $langContent);
    }
}
