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

namespace MoodlePluginCI\Tests\Installer\Database;

use MoodlePluginCI\Installer\Database\OracleDatabase;

class OracleDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCreateDatabaseCommand()
    {
        $database       = new OracleDatabase();
        $database->name = 'TestName';
        $database->user = 'TestUser';
        $database->pass = 'TestPass';
        $database->host = 'TestHost';

        $expected = 'echo \'All done!\'';
        $this->assertSame($expected, $database->getCreateDatabaseCommand());
    }
}
