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

namespace Moodlerooms\MoodlePluginCI\Tests\Installer;

use Moodlerooms\MoodlePluginCI\Installer\EnvDumper;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class EnvDumperTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/EnvDumperTest'.time();

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testDump()
    {
        $toFile = $this->tempDir.'/.env';
        $dumper = new EnvDumper();
        $dumper->dump(['TEST' => 'value', 'FOO' => 'bar'], $toFile);

        $expected = 'TEST=value'.PHP_EOL.'FOO=bar'.PHP_EOL;

        $this->assertFileExists($toFile);
        $this->assertEquals($expected, file_get_contents($toFile));
    }

    public function testNoDump()
    {
        $toFile = $this->tempDir.'/.env';
        $dumper = new EnvDumper();
        $dumper->dump([], $toFile);

        $this->assertFileNotExists($toFile);
    }
}
