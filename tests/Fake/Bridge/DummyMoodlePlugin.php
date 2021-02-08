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

namespace MoodlePluginCI\Tests\Fake\Bridge;

use MoodlePluginCI\Bridge\MoodlePlugin;

class DummyMoodlePlugin extends MoodlePlugin
{
    public $component    = 'local_ci';
    public $dependencies = ['mod_forum'];
}
