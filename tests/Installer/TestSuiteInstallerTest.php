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
use Symfony\Component\Yaml\Yaml;

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

        $phpunit = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
</phpunit>
XML;

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
        $fs->dumpFile($this->pluginDir.'/phpunit.xml', $phpunit);

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $this->pluginDir = realpath($this->pluginDir);
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

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <filter>
        <whitelist addUncoveredFilesFromWhitelist="true">
            <file>$this->pluginDir/classes/math.php</file>
            <file>$this->pluginDir/lib.php</file>
        </whitelist>
    </filter>
</phpunit>
XML;
        $this->assertEquals($expected, file_get_contents($this->pluginDir.'/phpunit.xml'));
    }

    public function testBehatProcesses()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(5, $installer->getPostInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat');

        $this->assertEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(2, $installer->getPostInstallProcesses());
    }

    public function testUnitTestProcesses()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(5, $installer->getPostInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/lib_test.php');

        $this->assertEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(3, $installer->getPostInstallProcesses());
    }
}
