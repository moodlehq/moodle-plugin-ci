<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\MessDetectorCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MessDetectorCommandTest extends \PHPUnit\Framework\TestCase
{
    private string $pluginDir;

    protected function setUp(): void
    {
        $this->pluginDir = __DIR__ . '/../Fixture/moodle-local_ci';
    }

    protected function executeCommand(?string $pluginDir = null): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command         = new MessDetectorCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phpmd'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithProblems(): void
    {
        $commandTester = $this->executeCommand(__DIR__ . '/../Fixture/phpmd');

        $this->assertSame(0, $commandTester->getStatusCode()); // PHPMD always return 0, no matter the violations/errors.
        $this->assertStringContainsString('Constant testConst should be defined in uppercase', $commandTester->getDisplay());
        $this->assertStringContainsString('long variable names like $reallyVeryLongVariableName', $commandTester->getDisplay());
        $this->assertStringContainsString('Unexpected end of token stream in file', $commandTester->getDisplay());
    }

    public function testExecuteNoFiles(): void
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('/path/to/no/plugin');
    }
}
