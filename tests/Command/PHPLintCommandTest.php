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

use MoodlePluginCI\Command\PHPLintCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PHPLintCommandTest extends \PHPUnit\Framework\TestCase
{
    private string $pluginDir = __DIR__ . '/../Fixture/moodle-local_ci';

    protected function executeCommand(?string $customPluginDir = null)
    {
        if ($customPluginDir) {
            $this->pluginDir = $customPluginDir;
        }

        $command         = new PHPLintCommand();
        $command->plugin = new DummyMoodlePlugin($this->pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phplint'));
        $commandTester->execute([
            'plugin' => $this->pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $this->expectOutputRegex('/No syntax error found/');
        $commandTester = $this->executeCommand();

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoFiles()
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('/path/to/no/plugin');
    }
}
