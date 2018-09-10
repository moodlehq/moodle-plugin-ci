<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\Installer;

use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Installer\TestSuiteInstaller;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Yaml\Yaml;

class TestSuiteInstallerTest extends MoodleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
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

    public function testPHPUnitXMLFile()
    {
        $xmlFile   = $this->pluginDir.'/phpunit.xml';
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        // Test Moodle 3.2 PHPUnit XML file.
        $this->fs->copy(__DIR__.'/../Fixture/phpunit/phpunit-32.xml', $xmlFile, true);
        $installer->injectPHPUnitFilter();
        $this->assertXmlFileEqualsXmlFile(__DIR__.'/../Fixture/phpunit/phpunit-expected.xml', $xmlFile);

        // Test Moodle 3.3 PHPUnit XML file.
        $this->fs->copy(__DIR__.'/../Fixture/phpunit/phpunit-33.xml', $xmlFile, true);
        $installer->injectPHPUnitFilter();
        $this->assertXmlFileEqualsXmlFile(__DIR__.'/../Fixture/phpunit/phpunit-expected.xml', $xmlFile);
    }
}
