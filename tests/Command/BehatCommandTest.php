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

use Moodlerooms\MoodlePluginCI\Command\BehatCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BehatCommandTest extends \PHPUnit_Framework_TestCase
{
    private $moodleDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->moodleDir = sys_get_temp_dir().'/moodle-plugin-ci/BehatCommandTest'.time();
        $this->pluginDir = $this->moodleDir.'/local/travis';

        $fs = new Filesystem();
        $fs->mkdir($this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle', $this->moodleDir);
        $fs->mkdir($this->moodleDir.'/behat');
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
        $fs->touch($this->moodleDir.'/behat/behat.yml');
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir);
    }

    protected function executeCommand($pluginDir = null, $moodleDir = null)
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
        $commandTester->execute([
            'plugin'   => $pluginDir,
            '--moodle' => $moodleDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoFeatures()
    {
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat');

        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/No Behat features to run, free pass!/', $commandTester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteNoConfig()
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir.'/behat/behat.yml');

        $this->executeCommand();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteNoPlugin()
    {
        $this->executeCommand($this->moodleDir.'/no/plugin');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteNoMoodle()
    {
        $this->executeCommand($this->moodleDir.'/no/moodle');
    }
}
