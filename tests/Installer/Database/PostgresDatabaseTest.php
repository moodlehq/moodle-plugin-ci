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

use MoodlePluginCI\Installer\Database\PostgresDatabase;

class PostgresDatabaseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCreateDatabaseCommand(): void
    {
        $database       = new PostgresDatabase();
        $database->name = 'TestName';
        $database->user = 'TestUser';
        $database->pass = 'TestPass';
        $database->host = 'TestHost';

        $expected = 'env PGPASSWORD=TestPass psql -c CREATE DATABASE "TestName"; -U TestUser -d postgres -h TestHost';
        $this->assertSame($expected, implode(' ', $database->getCreateDatabaseCommand()));
    }
}
