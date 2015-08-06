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

namespace Moodlerooms\MoodlePluginCI\Tests\Command;

use Moodlerooms\MoodlePluginCI\Command\InstallCommand;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $value
     * @param array  $expected
     * @dataProvider csvToArrayProvider
     */
    public function testCsvToArray($value, array $expected)
    {
        $command = new InstallCommand();
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
