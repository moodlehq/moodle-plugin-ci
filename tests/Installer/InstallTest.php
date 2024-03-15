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

use MoodlePluginCI\Installer\Install;
use MoodlePluginCI\Installer\InstallerCollection;
use MoodlePluginCI\Installer\InstallOutput;
use MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;

class InstallTest extends \PHPUnit\Framework\TestCase
{
    public function testRunInstallation(): void
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
