<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Command;

use Moodlerooms\MoodlePluginCI\Command\InstallCommand;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstall;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->tempDir   = sys_get_temp_dir().'/moodle-plugin-ci/InstallCommandTest'.time();
        $this->pluginDir = $this->tempDir.'/plugin';

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

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
        $this->assertEquals(0, $commandTester->getStatusCode());
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
        $this->assertEquals($expected, $command->csvToArray($value), "Converting this value: '$value'");
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
