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
            new DummyExecute()
        );
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());
    }

    public function testCanInstallNode()
    {
        $installer = new VendorInstaller(
            new DummyMoodle($this->moodleDir),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );

        $this->assertTrue($installer->canInstallNode());

        // Set current version of node to match required.
        $reqVer = file_get_contents($this->moodleDir.'/.nvmrc');
        $nvmBin = getenv('NVM_BIN');
        $modVer = preg_replace('/^(\/.+\/)(v\d.+)(\/bin)$/', '$1'.$reqVer.'$2', $nvmBin);
        putenv('NVM_BIN='.$modVer);
        $this->assertFalse($installer->canInstallNode());

        // Revert env change.
        putenv('NVM_BIN='.$nvmBin);
        $this->assertTrue($installer->canInstallNode());

        // Remove .nvmrc
        $this->fs->remove($this->moodleDir.'/.nvmrc');
        $this->assertFalse($installer->canInstallNode());
    }
}
