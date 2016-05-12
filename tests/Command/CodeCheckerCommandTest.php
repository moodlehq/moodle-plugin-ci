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

use Moodlerooms\MoodlePluginCI\Command\CodeCheckerCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CodeCheckerCommandTest extends \PHPUnit_Framework_TestCase
{
    private $pluginDir;

    protected function setUp()
    {
        $this->pluginDir = sys_get_temp_dir().'/moodle-plugin-ci/TestSuiteInstallerTest'.time();

        $fs = new Filesystem();
        $fs->mkdir($this->pluginDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $this->pluginDir = realpath($this->pluginDir);
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

        $command         = new CodeCheckerCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('codechecker'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $this->expectOutputRegex('/\.+/');
        $commandTester = $this->executeCommand();
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoFiles()
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir.'/tests/behat');
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteNoPlugin()
    {
        $this->executeCommand('/path/to/no/plugin');
    }
}
