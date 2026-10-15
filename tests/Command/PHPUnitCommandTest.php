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
    protected function executeCommand($pluginDir = null, $moodleDir = null, array $cmdOptions = []): CommandTester
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

    public function testExecuteWithCoverageCobertura(): void
    {
        $this->setMoodleBranch(311);
        $commandTester = $this->executeCommand(null, null, ['--coverage-cobertura' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/--coverage-cobertura\' \'\S*\/cobertura\.xml/', $this->lastCmd);
        // A coverage driver must be selected, so the command isn't run directly with phpunit.
        $this->assertMatchesRegularExpression('/^\'(php|phpdbg)\' .*vendor.bin.phpunit/', $this->lastCmd);
    }

    public function testExecuteWithCoverageCoberturaUnsupported(): void
    {
        $this->setMoodleBranch(310);
        $commandTester = $this->executeCommand(null, null, ['--coverage-cobertura' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Cobertura coverage only can be used with Moodle 3.11 and up/', $commandTester->getDisplay());
        $this->assertDoesNotMatchRegularExpression('/--coverage-cobertura/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/^\'\S*vendor.bin.phpunit/', $this->lastCmd);
    }

    public function testExecuteWithLogJunit(): void
    {
        $commandTester = $this->executeCommand(null, null, ['--log-junit' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/--log-junit\' \'\S*\/junit\.xml/', $this->lastCmd);
        // No coverage requested, so no coverage driver is needed.
        $this->assertMatchesRegularExpression('/^\'\S*vendor.bin.phpunit/', $this->lastCmd);
    }

    public function testExecuteWithLogJunitAndCoverage(): void
    {
        $this->setMoodleBranch(311);
        $commandTester = $this->executeCommand(null, null, ['--log-junit' => true, '--coverage-clover' => true, '--coverage-cobertura' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/--coverage-clover\' \'\S*\/coverage\.xml/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--coverage-cobertura\' \'\S*\/cobertura\.xml/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--log-junit\' \'\S*\/junit\.xml/', $this->lastCmd);
    }

    private function setMoodleBranch(int $branch): void
    {
        $file = $this->moodleDir . '/version.php';
        file_put_contents($file, preg_replace('/\$branch\s*=\s*\'\d+\';/', "\$branch = '{$branch}';", file_get_contents($file)));
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
