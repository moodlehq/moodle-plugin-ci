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

use MoodlePluginCI\Installer\InstallerCollection;
use MoodlePluginCI\Installer\InstallOutput;
use MoodlePluginCI\Tests\Fake\Installer\DummyInstaller;

class InstallerCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testAll(): void
    {
        $installer1 = new DummyInstaller();
        $installer2 = new DummyInstaller();
        $installers = new InstallerCollection(new InstallOutput());

        $installers->add($installer1);
        $installers->add($installer2);

        $this->assertSame([$installer1, $installer2], $installers->all());
    }

    public function testMergeEnv(): void
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
            'THIS' => 'that',
            'BAT'  => 'baz',
        ];

        $this->assertSame($expected, $installers->mergeEnv());
    }

    public function testTotalSteps(): void
    {
        $installer  = new DummyInstaller();
        $installers = new InstallerCollection(new InstallOutput());

        $installers->add($installer);
        $installers->add(new DummyInstaller());
        $installers->add(new DummyInstaller());

        $this->assertSame($installers->sumStepCount(), $installer->stepCount() * 3);
    }
}
