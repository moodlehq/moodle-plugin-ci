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

use Moodlerooms\MoodlePluginCI\Command\ValidateCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ValidateCommandTest extends \PHPUnit_Framework_TestCase
{
    private $moodleDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->moodleDir = sys_get_temp_dir().'/moodle-plugin-ci/PHPUnitCommandTest'.time();
        $this->pluginDir = $this->moodleDir.'/local/travis';

        $fs = new Filesystem();
        $fs->mkdir($this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle', $this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->moodleDir);
    }

    protected function executeCommand()
    {
        $command         = new ValidateCommand();
        $command->moodle = new DummyMoodle($this->moodleDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('validate'));
        $commandTester->execute([
            'plugin' => $this->pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
