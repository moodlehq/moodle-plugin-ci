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

use Moodlerooms\MoodlePluginCI\Command\AddPluginCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AddPluginCommandTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/AddPluginCommandTest'.time();

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    protected function getCommandTester()
    {
        $command          = new AddPluginCommand($this->tempDir.'/.env');
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('add-plugin'));
    }

    public function testExecute()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'project'   => 'user/moodle-mod_foo',
            '--storage' => $this->tempDir.'/plugins',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertTrue(is_dir($this->tempDir.'/plugins'));
        $this->assertFileExists($this->tempDir.'/.env');
        $this->assertEquals(
            sprintf("EXTRA_PLUGINS_DIR=%s/plugins\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir.'/.env')
        );
    }

    public function testExecuteWithClone()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--clone'   => 'https://github.com/user/moodle-mod_foo.git',
            '--storage' => $this->tempDir.'/plugins',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertTrue(is_dir($this->tempDir.'/plugins'));
        $this->assertFileExists($this->tempDir.'/.env');
        $this->assertEquals(
            sprintf("EXTRA_PLUGINS_DIR=%s/plugins\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir.'/.env')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteBothProjectAndClone()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'project'   => 'user/moodle-mod_foo',
            '--clone'   => 'https://github.com/user/moodle-mod_foo.git',
            '--storage' => $this->tempDir.'/plugins',
        ]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteMissingProjectAndClone()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--storage' => $this->tempDir.'/plugins',
        ]);
    }
}
