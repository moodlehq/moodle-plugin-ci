<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Command;

use Moodlerooms\MoodlePluginCI\Command\InstallCommand;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstall;
use Moodlerooms\MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InstallCommandTest extends MoodleTestCase
{
    protected function executeCommand()
    {
        $command          = new InstallCommand($this->tempDir.'/.env');
        $command->install = new DummyInstall(new InstallOutput());

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('install'));
        $commandTester->execute([
            '--moodle'        => $this->tempDir.'/moodle',
            '--plugin'        => $this->pluginDir,
            '--data'          => $this->tempDir.'/moodledata',
            '--branch'        => 'MOODLE_29_STABLE',
            '--db-type'       => 'mysqli',
            '--extra-plugins' => $this->tempDir, // Not accurate, but tests more code.
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @param string $value
     * @param array  $expected
     *
     * @dataProvider csvToArrayProvider
     */
    public function testCsvToArray($value, array $expected)
    {
        $command = new InstallCommand($this->tempDir.'/.env');
        $this->assertSame($expected, $command->csvToArray($value), "Converting this value: '$value'");
    }

    public function csvToArrayProvider()
    {
        return [
            [' , foo , bar ', ['foo', 'bar']],
            [' , ', []],
            [null, []],
        ];
    }
}
