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

use Moodlerooms\MoodlePluginCI\Installer\Install;
use Moodlerooms\MoodlePluginCI\Installer\InstallerCollection;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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

        $this->assertEquals($installer->stepCount(), $output->getStepCount());
    }
}
