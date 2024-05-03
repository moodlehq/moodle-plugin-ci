<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2024 Justus Dieckmann
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\Installer\Database;

use MoodlePluginCI\Installer\Database\MSSQLDatabase;

class MSSQLDatabaseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCreateDatabaseCommand()
    {
        $database       = new MSSQLDatabase();
        $database->name = 'TestName';
        $database->user = 'TestUser';
        $database->pass = 'TestPass';
        $database->host = 'TestHost';

        $expected = 'sqlcmd -U TestUser -P TestPass -S TestHost -Q CREATE DATABASE TestName COLLATE Latin1_General_CI_AI;';
        $this->assertSame($expected, implode(' ', $database->getCreateDatabaseCommand()));
    }
}
