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

use Moodlerooms\MoodlePluginCI\Command\AddConfigCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AddConfigCommandTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/AddConfigCommandTest'.time();

        $fs = new Filesystem();
        $fs->copy(__DIR__.'/../Fixture/example-config.php', $this->tempDir.'/config.php');
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    protected function executeCommand($line = '$CFG->foo = "bar";')
    {
        $command         = new AddConfigCommand();
        $command->moodle = new DummyMoodle($this->tempDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('add-config'));
        $commandTester->execute([
            '--moodle' => $this->tempDir.'/moodle',
            'line'     => $line,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/\$CFG->foo = "bar";\n/', file_get_contents($this->tempDir.'/config.php'));
    }

    public function testExecuteSyntaxError()
    {
        $commandTester = $this->executeCommand('$CFG->foo = "bar"');
        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertRegExp('/Syntax error found in 1 file/', $commandTester->getDisplay());
    }
}
