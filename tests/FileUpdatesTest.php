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

class FileUpdatesTest extends \PHPUnit\Framework\TestCase
{
    public function testLocalCIPackageJSON(): void
    {
        $this->assertSame(
            '4b37d67998dd06d21c89865641135ed435baf65d',
            sha1_file(__DIR__ . '/../vendor/moodlehq/moodle-local_ci/package.json'),
            'Check changes to vendor/moodlehq/moodle-local_ci/package.json and update MustacheCommand, etc if necessary'
        );
    }
}
