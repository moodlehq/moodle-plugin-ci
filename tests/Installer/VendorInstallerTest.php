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
use MoodlePluginCI\Installer\VendorInstaller;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;

class VendorInstallerTest extends MoodleTestCase
{
    public function testInstall()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            false,
            null,
            false,
        );
        // Unset NVM_DIR.
        putenv('NVM_DIR');

        $installer->install();

        $this->assertNotEmpty(getenv('NVM_DIR'));
        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());
    }

    public function testInstallNodeNoNvmrc()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            false,
            null,
            false,
        );

        // Remove .nvmrc
        $this->fs->remove($this->moodleDir . '/.nvmrc');

        // Expect .nvmrc containing legacy version of Node.
        $installer->installNode();
        $this->assertTrue(is_file($this->moodleDir . '/.nvmrc'));
        $this->assertSame('lts/carbon', file_get_contents($this->moodleDir . '/.nvmrc'));
    }

    public function testInstallNoNvm()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            false,
            null,
            true,
        );
        // Unset NVM_DIR.
        putenv('NVM_DIR');

        $installer->install();

        $this->assertFalse(getenv('NVM_DIR'));
        $this->assertSame(2, $installer->getOutput()->getStepCount());
    }

    public function testInstallNodeUserVersion()
    {
        $userVersion = '8.9';
        $installer   = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            false,
            $userVersion,
            false,
        );

        $installer->installNode();

        // Expect .nvmrc containing user specified version.
        $this->assertTrue(is_file($this->moodleDir . '/.nvmrc'));
        $this->assertSame($userVersion, file_get_contents($this->moodleDir . '/.nvmrc'));
    }

    public function testInstallNodePluginDependencies()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            false,
            null,
            false,
        );

        $this->fs->copy(__DIR__ . '/../Fixture/plugin/package.json', $this->pluginDir . '/package.json');

        $installer->install();

        $this->assertSame(5, $installer->getOutput()->getStepCount());
    }

    public function testSkipNodePluginDependencies()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute(),
            true,
            null,
            false,
        );

        $this->fs->copy(__DIR__ . '/../Fixture/plugin/package.json', $this->pluginDir . '/package.json');

        $installer->install();

        $this->assertSame(4, $installer->getOutput()->getStepCount());
    }
}
