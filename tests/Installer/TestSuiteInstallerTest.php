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

class TestSuiteInstallerTest extends MoodleTestCase
{
    public function testInstall(): void
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());
    }

    public function testBehatProcesses(): void
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(3, $installer->getPostInstallProcesses());

        $this->fs->remove($this->pluginDir . '/tests/behat');

        $this->assertEmpty($installer->getBehatInstallProcesses());
        $this->assertCount(2, $installer->getPostInstallProcesses());
    }

    public function testUnitTestProcesses(): void
    {
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertNotEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(3, $installer->getPostInstallProcesses());

        $this->fs->remove($this->pluginDir . '/tests/lib_test.php');

        $this->assertEmpty($installer->getUnitTestInstallProcesses());
        $this->assertCount(1, $installer->getPostInstallProcesses());
    }
}
