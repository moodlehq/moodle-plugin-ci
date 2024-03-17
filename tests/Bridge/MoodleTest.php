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
use MoodlePluginCI\Tests\FilesystemTestCase;

/**
 * Test the \MoodlePluginCI\Bridge\Moodle class.
 *
 * Note that various unit tests are run in a separate process to avoid
 * conflicts with global state when including Moodle's config.php or
 * core_component (mocked) class.
 *
 * @covers \MoodlePluginCI\Bridge\Moodle
 */
class MoodleTest extends FilesystemTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetConfigExists(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $this->fs->copy(__DIR__ . '/../Fixture/tiny-config.php', $this->tempDir . '/config.php');
        $moodle = new Moodle($this->tempDir);

        $this->assertSame('exists', $moodle->getConfig('exists'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetConfigNotExists(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $this->fs->copy(__DIR__ . '/../Fixture/tiny-config.php', $this->tempDir . '/config.php');
        $moodle = new Moodle($this->tempDir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to find $CFG->notexists in Moodle config file');
        $moodle->getConfig('notexists');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetConfigNoConfigFile(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $moodle = new Moodle($this->tempDir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to find Moodle config file');
        $moodle->getConfig('wwwroot');
    }

    /**
     * @runInSeparateProcess
     */
    public function testComponentInstallDirectoryExists(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $this->fs->copy(__DIR__ . '/../Fixture/tiny-config.php', $this->tempDir . '/config.php');
        $moodle = new Moodle($this->tempDir);

        // Initially we tried to mock the core_component class with Mockery, and everything
        // worked fine, but the use of Reflection that we have in the code to be tested.
        // Hence, we have changed to load a minimal core_component (Fixture/moodle/lib/classes/component.php)
        // class that will be used instead of the (core) original.
        require_once $this->tempDir . '/lib/classes/component.php';

        $this->assertSame('/path/to/mod/test', $moodle->getComponentInstallDirectory('mod_test'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testComponentInstallDirectoryNotExists(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $this->fs->copy(__DIR__ . '/../Fixture/tiny-config.php', $this->tempDir . '/config.php');
        $moodle = new Moodle($this->tempDir);

        // Initially we tried to mock the core_component class with Mockery, and everything
        // worked fine, but the use of Reflection that we have in the code to be tested.
        // Hence, we have changed to load a minimal core_component (Fixture/moodle/lib/classes/component.php)
        // class that will be used instead of the (core) original.
        require_once $this->tempDir . '/lib/classes/component.php';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The component unknown_test has an unknown plugin type of unknown');
        $moodle->getComponentInstallDirectory('unknown_test');
    }

    public function testGetBranchCorrect(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);
        $moodle = new Moodle($this->tempDir);

        $this->assertSame(39, $moodle->getBranch());
    }

    public function testGetBranchIncorrect(): void
    {
        $this->fs->mirror(__DIR__ . '/../Fixture/moodle', $this->tempDir);

        // Let's edit the version.php file to convert the correct (string) branch into incorrect (integer) branch.
        $contents = file_get_contents($this->tempDir . '/version.php');
        $contents = preg_replace("/'39'/", '39', $contents);
        file_put_contents($this->tempDir . '/version.php', $contents);

        $moodle = new Moodle($this->tempDir);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to find Moodle branch version');
        $moodle->getBranch();
    }
}
