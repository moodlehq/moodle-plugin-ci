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

namespace Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge;

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;

/**
 * Must override to avoid using Moodle API.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class DummyMoodle extends Moodle
{
    public function requireConfig()
    {
        // Don't do anything.
    }

    public function normalizeComponent($component)
    {
        return ['local', 'travis'];
    }

    public function getComponentInstallDirectory($component)
    {
        return $this->directory.'/local/travis';
    }

    public function getBehatDataDirectory()
    {
        return $this->directory;
    }
}
