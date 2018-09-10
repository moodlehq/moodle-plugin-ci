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

use MoodlePluginCI\Command\ParallelCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class ParallelCommandTest extends \PHPUnit_Framework_TestCase
{
    protected function executeCommand(array $processes)
    {
        $command            = new ParallelCommand();
        $command->moodle    = new DummyMoodle(__DIR__);
        $command->plugin    = new DummyMoodlePlugin(__DIR__);
        $command->processes = $processes;

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('parallel'));
        $commandTester->execute([
            'plugin' => __DIR__,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand([
            'foo' => new Process('php -r "usleep(100);"'),
            'bar' => new Process('php -r "usleep(100);"'),
        ]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteFailedProcess()
    {
        $commandTester = $this->executeCommand([
            'foo' => new Process('php -r "fwrite(STDERR, \"Write to error\n\"); exit(1);"'),
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertRegExp('/Command foo failed/', $commandTester->getDisplay());
        $this->assertRegExp('/Error output for foo command/', $commandTester->getDisplay());
        $this->assertRegExp('/Write to error/', $commandTester->getDisplay());
    }

    public function testInitializeProcesses()
    {
        $command         = new ParallelCommand();
        $command->moodle = new DummyMoodle(__DIR__);
        $command->plugin = new DummyMoodlePlugin(__DIR__);
        $processes       = $command->initializeProcesses();

        foreach ($processes as $name => $process) {
            $this->assertInstanceOf('Symfony\Component\Process\Process', $process);
            $this->assertInternalType('string', $name);
            $this->assertGreaterThan(1, strlen($name));
        }
    }
}
