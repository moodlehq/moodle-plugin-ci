<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\PluginValidate\Finder;

use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\ClassFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ClassFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindTokens()
    {
        $file       = __DIR__.'/../../Fixture/moodle-local_travis/lib.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('local_travis_math');

        $finder = new ClassFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }

    public function testFindTokensNameSpaceClass()
    {
        $file       = __DIR__.'/../../Fixture/moodle-local_travis/classes/math.php';
        $fileTokens = FileTokens::create('lib.php')->mustHave('local_travis\math');

        $finder = new ClassFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }
}
