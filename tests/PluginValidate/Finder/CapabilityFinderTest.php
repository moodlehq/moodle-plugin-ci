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

namespace MoodlePluginCI\Tests\PluginValidate\Finder;

use MoodlePluginCI\PluginValidate\Finder\CapabilityFinder;
use MoodlePluginCI\PluginValidate\Finder\FileTokens;

class CapabilityFinderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindTokens(): void
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/db/access.php';
        $fileTokens = FileTokens::create('db/access.php')->mustHave('local/travis:view');

        $finder = new CapabilityFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }
}
