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

use Moodlerooms\MoodlePluginCI\Installer\InstallerCollection;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallerCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $installer1 = new DummyInstaller();
        $installer2 = new DummyInstaller();
        $installers = new InstallerCollection(new InstallOutput());

        $installers->add($installer1);
        $installers->add($installer2);

        $this->assertSame([$installer1, $installer2], $installers->all());
    }

    public function testMergeEnv()
    {
        $installer1 = new DummyInstaller();
        $installer1->addEnv('FOO', 'bar');
        $installer1->addEnv('THIS', 'notThat');

        $installer2 = new DummyInstaller();
        $installer2->addEnv('BAT', 'baz');
        $installer2->addEnv('THIS', 'that');

        $installers = new InstallerCollection(new InstallOutput());
        $installers->add($installer1);
        $installers->add($installer2);

        $expected = [
            'FOO'  => 'bar',
            'BAT'  => 'baz',
            'THIS' => 'that',
        ];

        $this->assertEquals($expected, $installers->mergeEnv());
    }

    public function testTotalSteps()
    {
        $installer  = new DummyInstaller();
        $installers = new InstallerCollection(new InstallOutput());

        $installers->add($installer);
        $installers->add(new DummyInstaller());
        $installers->add(new DummyInstaller());

        $this->assertEquals($installers->sumStepCount(), $installer->stepCount() * 3);
    }
}
