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

use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\CapabilityFinder;
use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CapabilityFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindTokens()
    {
        $file       = __DIR__.'/../../Fixture/moodle-local_travis/db/access.php';
        $fileTokens = FileTokens::create('db/access.php')->mustHave('local/travis:view');

        $finder = new CapabilityFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }
}
