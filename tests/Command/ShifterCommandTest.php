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

use Moodlerooms\MoodlePluginCI\Command\ShifterCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ShifterCommandTest extends \PHPUnit_Framework_TestCase
{
    private $pluginDir;

    protected function setUp()
    {
        $this->pluginDir = sys_get_temp_dir().'/moodle-plugin-ci/ShifterCommandTest'.time();

        $fs = new Filesystem();
        $fs->mkdir($this->pluginDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
        $fs->mkdir($this->pluginDir.'/yui/buildci'); // Make the code think Shifter ran.
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->pluginDir);
    }

    protected function executeCommand($pluginDir = null)
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command          = new ShifterCommand();
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('shifter'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoSource()
    {
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/yui/src');

        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertRegExp('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoBuild()
    {
        $this->expectException(\RuntimeException::class);

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/yui/build');

        $this->executeCommand();
    }

    public function testExecuteShifterFail()
    {
        $this->expectException(\RuntimeException::class);

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/yui/buildci');

        $this->executeCommand();
    }

    public function testExecuteNoPlugin()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand($this->pluginDir.'/no/plugin');
    }
}
