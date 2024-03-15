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

use MoodlePluginCI\Command\PHPUnitCommand;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class PHPUnitCommandTest extends MoodleTestCase
{
    protected function executeCommand(?string $pluginDir = null, ?string $moodleDir = null, array $cmdOptions = []): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }
        if ($moodleDir === null) {
            $moodleDir = $this->moodleDir;
        }

        $command          = new PHPUnitCommand();
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phpunit'));
        $cmdOptions    = array_merge(
            [
                'plugin'   => $pluginDir,
                '--moodle' => $moodleDir,
            ],
            $cmdOptions
        );
        $commandTester->execute($cmdOptions);

        // We need these for assertions against the commands run.
        $this->allCmds = $command->execute->allCmds;
        $this->lastCmd = $command->execute->lastCmd;

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/vendor.bin.phpunit/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--testsuite.*local_ci_testsuite/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--configuration.*local\/ci/', $this->lastCmd);
    }

    public function testExecuteWithCustomPHPUnitXMLFile(): void
    {
        $commandTester = $this->executeCommand(null, null, ['--configuration' => 'some_config.xml']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/vendor.bin.phpunit/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--configuration.*.*local\/ci\/some_config.xml/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--configuration.*local\/ci\/phpunit.xml/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--testsuite.*local_ci_testsuite/', $this->lastCmd);
    }

    public function testExecuteWithGeneratedPHPUnitXMLFile(): void
    {
        $fs = new Filesystem();
        $fs->touch($this->pluginDir . '/phpunit.xml');
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/vendor.bin.phpunit/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--configuration.*local\/ci\/phpunit.xml/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--testsuite.*local_ci_testsuite/', $this->lastCmd);
    }

    public function testExecuteWithTestSuite(): void
    {
        $commandTester = $this->executeCommand(null, null, ['--testsuite' => 'some_testsuite']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/vendor.bin.phpunit/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--testsuite.*some_testsuite/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--configuration.*local\/ci/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--testsuite.*local_ci_testsuite/', $this->lastCmd);
    }

    public function testExecuteWithFilter(): void
    {
        $commandTester = $this->executeCommand(null, null, ['--filter' => 'some_filter']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/vendor.bin.phpunit/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--filter.*some_filter/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--testsuite.*local_ci_testsuite/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--configuration.*local\/ci/', $this->lastCmd);
    }

    public function testExecuteNoTests(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->pluginDir . '/tests/lib_test.php');

        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No PHPUnit tests to run, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand($this->moodleDir . '/no/plugin');
    }

    public function testExecuteNoMoodle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // TODO: Check what's happening here. moodleDir should be the 2nd parameter, but then the test fails.
        $this->executeCommand($this->moodleDir . '/no/moodle');
    }
}
