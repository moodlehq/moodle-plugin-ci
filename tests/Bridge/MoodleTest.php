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

namespace MoodlePluginCI\Tests\Bridge;

use MoodlePluginCI\Bridge\Moodle;

class MoodleTest extends \PHPUnit\Framework\TestCase
{
    public function testGetBranch(): void
    {
        $moodle = new Moodle(__DIR__ . '/../Fixture/moodle');
        $this->assertSame(39, $moodle->getBranch());
    }
}
