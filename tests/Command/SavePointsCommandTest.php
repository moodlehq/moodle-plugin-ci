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

use MoodlePluginCI\Command\SavePointsCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SavePointsCommandTest extends MoodleTestCase
{
    protected function executeCommand(?string $pluginDir = null): CommandTester
    {
        if (null === $pluginDir) {
            $pluginDir = $this->pluginDir;
        }

        $command         = new SavePointsCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('savepoints'));
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

    public function testExecuteNoUpgradeFile(): void
    {
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteFail(): void
    {
        $this->fs->copy(__DIR__ . '/../Fixture/broken-upgrade.php', $this->pluginDir . '/db/upgrade.php', true);
        $commandTester = $this->executeCommand();
        $this->assertSame(1, $commandTester->getStatusCode());
    }
}
