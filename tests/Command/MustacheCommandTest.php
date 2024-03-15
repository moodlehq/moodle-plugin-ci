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

use MoodlePluginCI\Command\MustacheCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class MustacheCommandTest extends MoodleTestCase
{
    protected function executeCommand(?string $pluginDir = null, ?string $moodleDir = null, ?MustacheCommand $command = null): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }
        if ($moodleDir === null) {
            $moodleDir = $this->moodleDir;
        }

        if ($command === null) {
            $command = new MustacheCommand();
        }

        $command->moodle  = new DummyMoodle($moodleDir);
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('mustache'));
        $commandTester->execute([
            'plugin'   => $pluginDir,
            '--moodle' => $moodleDir,
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        // Assert that the mobile app template is skipped.
        $command = $this->getMockBuilder(MustacheCommand::class)
            ->onlyMethods(['outputSkip'])
            ->getMock();

        $command->expects($this->once())
            ->method('outputSkip')
            ->with(
                $this->isInstanceOf(OutputInterface::class),
                $this->stringContains('/templates/mobileapp/item.mustache'),
            )
            ->willReturn(0);

        $commandTester = $this->executeCommand(null, null, $command);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand($this->moodleDir . '/no/plugin');
    }

    public function testExecuteNoMoodle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand($this->moodleDir . '/no/moodle');
    }
}
