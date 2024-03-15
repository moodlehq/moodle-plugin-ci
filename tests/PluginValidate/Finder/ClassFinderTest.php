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

use MoodlePluginCI\PluginValidate\Finder\ClassFinder;
use MoodlePluginCI\PluginValidate\Finder\FileTokens;

class ClassFinderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindTokens(): void
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/lib.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('local_ci_math');

        $finder = new ClassFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }

    public function testFindTokensNameSpaceClass(): void
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/classes/math.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('local_ci\math');

        $finder = new ClassFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }
}
