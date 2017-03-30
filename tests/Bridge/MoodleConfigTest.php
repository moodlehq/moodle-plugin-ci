<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Bridge;

use Moodlerooms\MoodlePluginCI\Bridge\MoodleConfig;
use Moodlerooms\MoodlePluginCI\Installer\Database\MySQLDatabase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleConfigTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/MoodleConfigTest'.time();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testCreateContents()
    {
        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');

        $this->assertSame(file_get_contents(__DIR__.'/../Fixture/example-config.php'), $contents);
    }

    public function testInjectLineIntoConfig()
    {
        $before = <<<'EOT'
// Random lines 1
// Custom configuration lines are added here:
// Extra config.
// Random lines 2
EOT;

        $expected = <<<'EOT'
// Random lines 1
// Custom configuration lines are added here:
New Line
// Extra config.
// Random lines 2
EOT;

        $config   = new MoodleConfig();
        $contents = $config->injectLine($before, 'New Line');
        $this->assertSame($expected, $contents);
    }

    public function testInjectLineIntoConfigMissingPlaceholder()
    {
        $this->expectException(\RuntimeException::class);
        $config = new MoodleConfig();
        $config->injectLine('Bad param', 'New Line');
    }

    public function testRead()
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->tempDir.'/test.txt', 'Test');

        $config   = new MoodleConfig();
        $contents = $config->read($this->tempDir.'/test.txt');

        $this->assertSame('Test', $contents);
    }

    public function testReadFileNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new MoodleConfig();
        $config->read($this->tempDir.'/test.txt');
    }

    public function testReadFail()
    {
        $this->expectException(\RuntimeException::class);

        $fs = new Filesystem();
        $fs->dumpFile($this->tempDir.'/test.txt', 'Test');
        $fs->chmod($this->tempDir.'/test.txt', 0222);

        $config = new MoodleConfig();
        $config->read($this->tempDir.'/test.txt');
    }

    public function testDump()
    {
        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');
        $config->dump($this->tempDir.'/config.php', $contents);

        $this->assertFileEquals(__DIR__.'/../Fixture/example-config.php', $this->tempDir.'/config.php');
    }
}
