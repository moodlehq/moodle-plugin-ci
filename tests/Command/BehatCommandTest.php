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

use MoodlePluginCI\Command\BehatCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BehatCommandTest extends MoodleTestCase
{
    protected function executeCommand(?string $pluginDir = null, ?string $moodleDir = null, array $cmdOptions = []): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }
        if ($moodleDir === null) {
            $moodleDir = $this->moodleDir;
        }

        $command          = new BehatCommand();
        $command->moodle  = new DummyMoodle($moodleDir);
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('behat'));
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
        $this->assertMatchesRegularExpression('/php.*admin.tool.behat.cli.run/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--profile=default.*--suite=default/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--tags=@local_ci/', $this->lastCmd);
        $this->assertMatchesRegularExpression('/--verbose.*-vvv/', $this->lastCmd);
    }

    public function testExecuteWithTags(): void
    {
        $commandTester = $this->executeCommand(null, null, ['--tags' => '@tag1&&@tag2']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/--tags=@tag1&&@tag2/', $this->lastCmd);
        $this->assertDoesNotMatchRegularExpression('/--tags=@local_ci/', $this->lastCmd);
    }

    public function testExecuteWithSeleniumImageOption(): void
    {
        $commandTester = $this->executeCommand(
            null,
            null,
            ['--start-servers' => true, '--selenium' => 'seleniarm/standalone-chromium:latest']
        );
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/seleniarm\/standalone-chromium:latest/', $this->allCmds[1]);
    }

    public function testExecuteWithSeleniumImageEnv(): void
    {
        putenv('MOODLE_BEHAT_SELENIUM_IMAGE=seleniarm/standalone-chromium:latest');

        $commandTester = $this->executeCommand(null, null, ['--start-servers' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/seleniarm\/standalone-chromium:latest/', $this->allCmds[1]);
    }

    public function testExecuteWithChromeProfile(): void
    {
        putenv('MOODLE_BEHAT_SELENIUM_IMAGE=');

        $commandTester = $this->executeCommand(null, null, ['--start-servers' => true, '--profile' => 'chrome']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/selenium\/standalone-chrome:3/', $this->allCmds[1]);
    }

    public function testExecuteWithFirefoxProfile(): void
    {
        putenv('MOODLE_BEHAT_SELENIUM_IMAGE=');
        file_put_contents("$this->moodleDir/composer.lock", '');

        $commandTester = $this->executeCommand(null, null, ['--start-servers' => true, '--profile' => 'firefox']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/selenium\/standalone-firefox:3/', $this->allCmds[1]);
    }

    public function testExecuteWithLegacyFirefoxProfile(): void
    {
        putenv('MOODLE_BEHAT_SELENIUM_IMAGE=');
        file_put_contents("$this->moodleDir/composer.lock", 'instaclick/php-webdriver');

        $commandTester = $this->executeCommand(null, null, ['--start-servers' => true, '--profile' => 'firefox']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/selenium\/standalone-firefox:2.53.1/', $this->allCmds[1]);
    }

    public function testExecuteWithName(): void
    {
        $featureName = 'With "double quotes" and \'single quotes\'';
        // Note that everything is escaped for shell execution, plus own regexp quoting.
        $expectedName  = preg_quote(escapeshellarg("--name='$featureName'"));
        $commandTester = $this->executeCommand(null, null, ['--name' => $featureName]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression("/$expectedName/", $this->lastCmd);
    }

    public function testExecuteNoFeatures(): void
    {
        $this->fs->remove($this->pluginDir . '/tests/behat');

        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No Behat features to run, free pass!/', $commandTester->getDisplay());
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
