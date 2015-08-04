<?php
/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Command;

use Moodlerooms\MoodlePluginCI\Command\IgnoreFileCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class IgnoreFileCommandTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/IgnoreFileCommandTest'.time();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    /**
     * Test executing the command.
     */
    public function testExecute()
    {
        $application = new Application();
        $application->add(new IgnoreFileCommand());

        $command       = $application->find('ignorefile');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'plugin'      => $this->tempDir,
            '--not-paths' => 'foo/bar,very/bad.php',
            '--not-names' => '*-m.js,bad.php',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir.'/.travis-ignore.yml');

        $expected = [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ];

        $parsed = Yaml::parse($this->tempDir.'/.travis-ignore.yml');

        $this->assertEquals($expected, $parsed);
    }

    /**
     * Test converting CSV to array.
     *
     * @param string $value
     * @param array $expected
     * @dataProvider csvToArrayProvider
     */
    public function testCsvToArray($value, array $expected)
    {
        $command = new IgnoreFileCommand();
        $this->assertEquals($expected, $command->csvToArray($value), "Converting this value: '$value'");
    }

    public function csvToArrayProvider()
    {
        return [
            [' , foo , bar ', ['foo', 'bar']],
            [' , ', []],
            [null, []],
        ];
    }
}
