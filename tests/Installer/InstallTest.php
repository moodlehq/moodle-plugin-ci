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

use Moodlerooms\MoodlePluginCI\Installer\EnvDumper;
use Moodlerooms\MoodlePluginCI\Installer\Install;
use Moodlerooms\MoodlePluginCI\Installer\InstallerCollection;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallTest extends \PHPUnit_Framework_TestCase
{
    public function testRunInstallation()
    {
        $installer  = new DummyInstaller();
        $installers = new InstallerCollection(new NullLogger(), new ProgressBar(new NullOutput()));
        $installers->add($installer);

        $manager = new Install();
        $manager->runInstallation($installers, new EnvDumper());

        $this->assertEquals($installer->stepCount(), $installer->actualStepCount());
    }
}
