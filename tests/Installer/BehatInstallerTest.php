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

use Moodlerooms\MoodlePluginCI\Installer\BehatInstaller;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BehatInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstall()
    {
        $output    = new InstallOutput();
        $installer = new BehatInstaller(new DummyMoodle(''), new DummyExecute());
        $installer->setInstallOutput($output);
        $installer->install();

        $this->assertEquals($installer->stepCount(), $output->getStepCount());
    }
}
