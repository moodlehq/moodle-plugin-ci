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

namespace Moodlerooms\MoodlePluginCI\Tests;

use Moodlerooms\MoodlePluginCI\Validate;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ValidateTest extends \PHPUnit_Framework_TestCase
{
    public function testDirectory()
    {
        $validate = new Validate();
        $this->assertEquals(__DIR__, $validate->directory(__DIR__));
        $this->assertEquals(__DIR__.'/..', $validate->directory(__DIR__.'/..'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDirectoryRealPathFail()
    {
        $validate = new Validate();
        $validate->directory('aaa');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDirectoryIsFile()
    {
        $validate = new Validate();
        $validate->directory(__FILE__);
    }

    public function testFilePath()
    {
        $validate = new Validate();
        $this->assertEquals(__FILE__, $validate->filePath(__FILE__));
        $this->assertEquals(__DIR__.'/../README.md', $validate->filePath(__DIR__.'/../README.md'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFilePathRealPathFail()
    {
        $validate = new Validate();
        $validate->filePath('aaa.txt');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFilePathIsDirectory()
    {
        $validate = new Validate();
        $validate->filePath(__DIR__);
    }

    /**
     * @param string $branch
     *
     * @dataProvider moodleBranchProvider
     */
    public function testMoodleBranch($branch)
    {
        $validate = new Validate();
        $this->assertEquals($branch, $validate->moodleBranch($branch), "Validate that $branch is valid");
    }

    /**
     * @param string $branch
     *
     * @dataProvider invalidMoodleBranchProvider
     * @expectedException \InvalidArgumentException
     */
    public function testMoodleBranchInvalid($branch)
    {
        $validate = new Validate();
        $this->assertEquals($branch, $validate->moodleBranch($branch), "Validate that $branch is NOT valid");
    }

    public function moodleBranchProvider()
    {
        return [
            ['master'],
            ['MOODLE_27_STABLE'],
            ['MOODLE_28_STABLE'],
            ['MOODLE_29_STABLE'],
        ];
    }

    public function invalidMoodleBranchProvider()
    {
        return [
            ['master_bar'],
            ['MOODLE_27_STABLE_STUFF'],
            ['THIS_MOODLE_28_STABLE'],
            ['MOODLE_299_STABLE'],
            ['random'],
        ];
    }
}
