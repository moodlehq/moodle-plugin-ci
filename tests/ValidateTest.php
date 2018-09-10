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

use MoodlePluginCI\Validate;

class ValidateTest extends \PHPUnit_Framework_TestCase
{
    public function testDirectory()
    {
        $validate = new Validate();
        $this->assertSame(__DIR__, $validate->directory(__DIR__));
        $this->assertSame(__DIR__.'/..', $validate->directory(__DIR__.'/..'));
    }

    public function testDirectoryRealPathFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validate = new Validate();
        $validate->directory('aaa');
    }

    public function testDirectoryIsFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validate = new Validate();
        $validate->directory(__FILE__);
    }

    public function testFilePath()
    {
        $validate = new Validate();
        $this->assertSame(__FILE__, $validate->filePath(__FILE__));
        $this->assertSame(__DIR__.'/../README.md', $validate->filePath(__DIR__.'/../README.md'));
    }

    public function testFilePathRealPathFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $validate = new Validate();
        $validate->filePath('aaa.txt');
    }

    public function testFilePathIsDirectory()
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $this->assertSame($branch, $validate->gitBranch($branch), "Validate that $branch is valid");
    }

    /**
     * @param string $branch
     *
     * @dataProvider invalidGitBranchProvider
     */
    public function testGitBranchInvalid($branch)
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $this->assertSame($url, $validate->gitUrl($url), "Validate that $url is valid");
    }

    /**
     * @param string $url
     *
     * @dataProvider invalidUrlProvider
     */
    public function testInvalidUrl($url)
    {
        $this->expectException(\InvalidArgumentException::class);
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
