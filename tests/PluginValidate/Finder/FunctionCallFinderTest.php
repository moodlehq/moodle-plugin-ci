<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2024 Moodle Pty Ltd <support@moodle.com>
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\PluginValidate\Finder;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;
use MoodlePluginCI\PluginValidate\Finder\FunctionCallFinder;

class FunctionCallFinderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindTokens()
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/lib.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('local_ci_subtract');

        $finder = new FunctionCallFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
        $this->assertFalse($fileTokens->hasHint());
    }

    public function testFindTokensNotFound()
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/lib.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('exit')->notFoundHint('Exit not found');

        $finder = new FunctionCallFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertFalse($fileTokens->hasFoundAllTokens());
        $this->assertTrue($fileTokens->hasHint());
        $this->assertSame('Exit not found', $fileTokens->hint);
    }
}
