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

use MoodlePluginCI\Command\AddPluginCommand;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class AddPluginCommandTest extends FilesystemTestCase
{
    protected function getCommandTester(): CommandTester
    {
        $command          = new AddPluginCommand($this->tempDir . '/.env');
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('add-plugin'));
    }

    public function testExecute(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'project'   => 'user/moodle-mod_foo',
            '--storage' => $this->tempDir . '/plugins',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertTrue(is_dir($this->tempDir . '/plugins'));
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertSame(
            sprintf("EXTRA_PLUGINS_DIR=%s/plugins\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir . '/.env')
        );
    }

    public function testExecuteWithClone(): void
    {
        $commandTester = $this->getCommandTester();
        // Execute with verbosity, so process helper outputs command line.
        $commandTester->execute([
            '--clone'   => 'https://github.com/user/moodle-mod_foo.git',
            '--storage' => $this->tempDir . '/plugins',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString(
            "'git' 'clone' '--depth' '1' 'https://github.com/user/moodle-mod_foo.git'",
            $commandTester->getDisplay()
        );
        $this->assertTrue(is_dir($this->tempDir . '/plugins'));
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertSame(
            sprintf("EXTRA_PLUGINS_DIR=%s/plugins\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir . '/.env')
        );
    }

    public function testExecuteWithCloneAndBranch(): void
    {
        $commandTester = $this->getCommandTester();
        // Execute with verbosity, so process helper outputs command line.
        $commandTester->execute([
            '--clone'   => 'https://github.com/user/moodle-mod_foo.git',
            '--branch'  => 'dev',
            '--storage' => $this->tempDir . '/plugins',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString(
            "'git' 'clone' '--depth' '1' '--branch' 'dev' 'https://github.com/user/moodle-mod_foo.git'",
            $commandTester->getDisplay()
        );
        $this->assertTrue(is_dir($this->tempDir . '/plugins'));
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertSame(
            sprintf("EXTRA_PLUGINS_DIR=%s/plugins\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir . '/.env')
        );
    }

    public function testExecuteBothProjectAndClone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'project'   => 'user/moodle-mod_foo',
            '--clone'   => 'https://github.com/user/moodle-mod_foo.git',
            '--storage' => $this->tempDir . '/plugins',
        ]);
    }

    public function testExecuteMissingProjectAndClone(): void
    {
        $this->expectException(\RuntimeException::class);

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--storage' => $this->tempDir . '/plugins',
        ]);
    }
}
