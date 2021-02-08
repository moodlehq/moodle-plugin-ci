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

namespace MoodlePluginCI\Tests;

class MoodleTestCase extends FilesystemTestCase
{
    /**
     * @var string
     */
    protected $moodleDir;

    /**
     * @var string
     */
    protected $pluginDir;

    protected function setUp()
    {
        parent::setUp();

        $this->moodleDir = $this->tempDir;
        $this->pluginDir = $this->tempDir.'/local/travis';

        $this->fs->mirror(__DIR__.'/Fixture/moodle', $this->moodleDir);
        $this->fs->mirror(__DIR__.'/Fixture/moodle-local_ci', $this->pluginDir);
    }
}
