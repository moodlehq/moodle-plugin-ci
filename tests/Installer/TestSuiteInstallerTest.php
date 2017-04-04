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

namespace Moodlerooms\MoodlePluginCI\Tests\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\TestSuiteInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Moodlerooms\MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Yaml\Yaml;

class TestSuiteInstallerTest extends MoodleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $config  = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $phpunit = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
</phpunit>
XML;

        $this->fs->dumpFile($this->pluginDir.'/phpunit.xml', $phpunit);
        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));
    }

    public function testInstall()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());

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
        $this->assertSame($expected, file_get_contents($this->pluginDir.'/phpunit.xml'));
    }

    public function testBehatProcesses()
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(3, $installer->getPostInstallProcesses());

        $this->fs->remove($this->pluginDir.'/tests/behat');

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
        $this->assertCount(3, $installer->getPostInstallProcesses());

        $this->fs->remove($this->pluginDir.'/tests/lib_test.php');

        $this->assertEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(1, $installer->getPostInstallProcesses());
    }
}
