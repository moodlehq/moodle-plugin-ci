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

use Moodlerooms\MoodlePluginCI\Installer\ExtraPluginsInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ExtraPluginsInstallerTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;
    private $pluginsDir;

    protected function setUp()
    {
        $this->tempDir    = sys_get_temp_dir().'/moodle-plugin-ci/ExtraPluginsInstallerTest'.time();
        $this->pluginsDir = $this->tempDir.'/plugins';

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginsDir.'/moodle-local_travis');
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testInstall()
    {
        $installer = new ExtraPluginsInstaller(new DummyMoodle($this->tempDir), $this->pluginsDir);
        $installer->install();

        $this->assertEquals($installer->stepCount(), $installer->getOutput()->getStepCount());
        $this->assertTrue(is_dir($this->tempDir.'/local/travis'));
    }
}
