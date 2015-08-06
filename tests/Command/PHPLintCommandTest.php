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

use Moodlerooms\MoodlePluginCI\Command\PHPLintCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PHPLintCommandTest extends \PHPUnit_Framework_TestCase
{
    private $pluginDir;

    protected function setUp()
    {
        $this->pluginDir = __DIR__.'/../Fixture/moodle-local_travis';
    }

    protected function executeCommand($pluginDir = null)
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command = new PHPLintCommand();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phplint'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        ob_start(); // PHPLint uses another output method.
        $commandTester = $this->executeCommand();
        ob_end_clean();

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoFiles()
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir.'/tests/behat');

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/Failed to find any files to process/', $commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteNoPlugin()
    {
        $this->executeCommand('/path/to/no/plugin');
    }
}
