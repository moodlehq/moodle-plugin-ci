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

use MoodlePluginCI\Installer\MoodleAppInstaller;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;

class MoodleAppInstallerTest extends MoodleTestCase
{
    public function testInstall(): void
    {
        $execute   = new DummyExecute();
        $installer = new MoodleAppInstaller($execute, $this->tempDir);
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());

        $this->assertSame(['MOODLE_APP' => 'true'], $installer->getEnv());

        $this->assertMatchesRegularExpression('/docker/', $execute->allCmds[0]);
        $this->assertMatchesRegularExpression('/run/', $execute->allCmds[0]);
        $this->assertMatchesRegularExpression('/moodlehq\/moodleapp:latest-test/', $execute->allCmds[0]);

        $this->assertMatchesRegularExpression('/git/', $execute->allCmds[1]);
        $this->assertMatchesRegularExpression('/clone/', $execute->allCmds[1]);
        $this->assertMatchesRegularExpression(
            '/https:\/\/github\.com\/moodlehq\/moodle-local_moodleappbehat\.git/',
            $execute->allCmds[1]
        );
    }
}
