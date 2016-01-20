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

namespace Moodlerooms\MoodlePluginCI\Tests\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\TestSuiteInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TestSuiteInstallerTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->tempDir   = sys_get_temp_dir().'/moodle-plugin-ci/TestSuiteInstallerTest'.time();
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

    public function testInstall()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->install();

        $this->assertEquals($installer->stepCount(), $installer->getOutput()->getStepCount());
    }

    public function testBehatProcesses()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(4, $installer->getPostInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat');

        $this->assertEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(1, $installer->getPostInstallProcesses());
    }

    public function testUnitTestProcesses()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(4, $installer->getPostInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/lib_test.php');

        $this->assertEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(3, $installer->getPostInstallProcesses());
    }
}
