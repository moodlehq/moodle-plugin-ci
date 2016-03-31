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

use Moodlerooms\MoodlePluginCI\Command\CoverallsUploadCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CoverallsUploadCommandTest extends \PHPUnit_Framework_TestCase
{
    private $moodleDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->moodleDir = sys_get_temp_dir().'/moodle-plugin-ci/CoverallsUploadCommandTest'.time();
        $this->pluginDir = $this->moodleDir.'/local/travis';

        $fs = new Filesystem();
        $fs->mkdir($this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle', $this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
        $fs->touch($this->moodleDir.'/coverage.xml');
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir);
    }

    protected function executeCommand($pluginDir = null)
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command          = new CoverallsUploadCommand();
        $command->execute = new DummyExecute();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('coveralls-upload'));
        $commandTester->execute([
            'plugin'          => $pluginDir,
            '--coverage-file' => $this->moodleDir.'/coverage.xml',
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertFileExists($this->pluginDir.'/build/logs/clover.xml');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteNoPlugin()
    {
        $this->executeCommand($this->moodleDir.'/no/plugin');
    }

    public function testExecuteNoCoverageFile()
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir.'/coverage.xml');

        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/Did not find coverage file/', $commandTester->getDisplay());
    }
}
