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

namespace MoodlePluginCI\Tests\Installer;

use MoodlePluginCI\Installer\EnvDumper;
use MoodlePluginCI\Tests\FilesystemTestCase;

class EnvDumperTest extends FilesystemTestCase
{
    public function testDump(): void
    {
        $toFile = $this->tempDir . '/.env';
        $dumper = new EnvDumper();
        $dumper->dump(['TEST' => 'value', 'FOO' => 'bar'], $toFile);

        $expected = 'TEST=value' . PHP_EOL . 'FOO=bar' . PHP_EOL;

        $this->assertFileExists($toFile);
        $this->assertSame($expected, file_get_contents($toFile));
    }

    public function testNoDump(): void
    {
        $toFile = $this->tempDir . '/.env';
        $dumper = new EnvDumper();
        $dumper->dump([], $toFile);

        $this->assertFileDoesNotExist($toFile);
    }
}
