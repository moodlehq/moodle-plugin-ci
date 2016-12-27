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
     * @dataProvider gitBranchProvider
     */
    public function testGitBranch($branch)
    {
        $validate = new Validate();
        $this->assertEquals($branch, $validate->gitBranch($branch), "Validate that $branch is valid");
    }

    /**
     * @param string $branch
     *
     * @dataProvider invalidGitBranchProvider
     * @expectedException \InvalidArgumentException
     */
    public function testGitBranchInvalid($branch)
    {
        $validate = new Validate();
        $validate->gitBranch($branch);
    }

    /**
     * @param string $url
     *
     * @dataProvider urlProvider
     */
    public function testUrl($url)
    {
        $validate = new Validate();
        $this->assertEquals($url, $validate->gitUrl($url), "Validate that $url is valid");
    }

    /**
     * @param string $url
     *
     * @dataProvider invalidUrlProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidUrl($url)
    {
        $validate = new Validate();
        $validate->gitUrl($url);
    }

    public function gitBranchProvider()
    {
        return [
            ['master'],
            ['MOODLE_27_STABLE'],
            ['MOODLE_28_STABLE'],
            ['MOODLE_29_STABLE'],
            ['v3.2.0'], // We allow tags.
        ];
    }

    public function invalidGitBranchProvider()
    {
        return [
            ['bad!'],
            ['stuff#'],
        ];
    }

    public function urlProvider()
    {
        return [
            ['git@github.com:moodle/moodle.git'],
            ['https://github.com/moodle/moodle.git'],
        ];
    }

    public function invalidUrlProvider()
    {
        return [
            ['foo/bar'],
            ['baz'],
            ['http://google.com'],
        ];
    }
}
