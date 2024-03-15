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

use MoodlePluginCI\Installer\Database\MySQLDatabase;

class MySQLDatabaseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCreateDatabaseCommand(): void
    {
        $database       = new MySQLDatabase();
        $database->name = 'TestName';
        $database->user = 'TestUser';
        $database->pass = 'TestPass';
        $database->host = 'TestHost';

        $expected = 'mysql -u TestUser --password=TestPass -h TestHost -e CREATE DATABASE `TestName` ' .
            'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
        $this->assertSame($expected, implode(' ', $database->getCreateDatabaseCommand()));
    }
}
