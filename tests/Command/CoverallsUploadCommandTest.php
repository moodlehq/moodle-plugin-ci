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

use MoodlePluginCI\Command\CoverallsUploadCommand;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class CoverallsUploadCommandTest extends MoodleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->touch($this->moodleDir . '/coverage.xml');
    }

    protected function executeCommand(?string $pluginDir = null): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command          = new CoverallsUploadCommand();
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('coveralls-upload'));
        $commandTester->execute([
            'plugin'          => $pluginDir,
            '--coverage-file' => $this->moodleDir . '/coverage.xml',
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertFileExists($this->pluginDir . '/build/logs/clover.xml');
    }

    public function testExecuteNoPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand($this->moodleDir . '/no/plugin');
    }

    public function testExecuteNoCoverageFile(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir . '/coverage.xml');

        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Did not find coverage file/', $commandTester->getDisplay());
    }
}
