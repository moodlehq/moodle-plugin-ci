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

use Moodlerooms\MoodlePluginCI\Installer\Install;
use Moodlerooms\MoodlePluginCI\Installer\InstallerCollection;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;

class InstallTest extends \PHPUnit_Framework_TestCase
{
    public function testRunInstallation()
    {
        $output    = new InstallOutput();
        $installer = new DummyInstaller();

        $installers = new InstallerCollection($output);
        $installers->add($installer);

        $manager = new Install($output);
        $manager->runInstallation($installers);

        $this->assertSame($installer->stepCount(), $output->getStepCount());
    }
}
