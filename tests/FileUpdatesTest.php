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

namespace Moodlerooms\MoodlePluginCI\Tests;

class FileUpdatesTest extends \PHPUnit_Framework_TestCase
{
    public function testLocalCIPackageJSON()
    {
        $this->assertSame(
            'a9a38e585d04bf9ca45bf970a70c41303c731a02',
            sha1_file(__DIR__.'/../vendor/moodlehq/moodle-local_ci/package.json'),
            'Check changes to vendor/moodlehq/moodle-local_ci/package.json and update MustacheCommand, etc if necessary'
        );
    }
}
