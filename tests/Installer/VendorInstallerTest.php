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
use Moodlerooms\MoodlePluginCI\Installer\VendorInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;

class VendorInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstall()
    {
        $installer = new VendorInstaller(
            new DummyMoodle(''),
            new MoodlePlugin(__DIR__.'/../Fixture/moodle-local_travis'),
            new DummyExecute()
        );
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());
    }
}
