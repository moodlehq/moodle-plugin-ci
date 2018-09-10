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

use MoodlePluginCI\Installer\Database\DatabaseResolver;

class DatabaseResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $resolver = new DatabaseResolver();

        $this->assertInstanceOf(
            'MoodlePluginCI\Installer\Database\MySQLDatabase',
            $resolver->resolveDatabase('mysqli')
        );
        $this->assertInstanceOf(
            'MoodlePluginCI\Installer\Database\PostgresDatabase',
            $resolver->resolveDatabase('pgsql')
        );
        $this->assertInstanceOf(
            'MoodlePluginCI\Installer\Database\MariaDBDatabase',
            $resolver->resolveDatabase('mariadb')
        );
    }

    public function testTypeError()
    {
        $this->expectException(\DomainException::class);
        $resolver = new DatabaseResolver();
        $resolver->resolveDatabase('foo');
    }

    public function testOptions()
    {
        $resolver = new DatabaseResolver();

        $name = 'TestName';
        $user = 'TestUser';
        $pass = 'TestPass';
        $host = 'TestHost';

        $database = $resolver->resolveDatabase('mysqli', $name, $user, $pass, $host);

        $this->assertInstanceOf(
            'MoodlePluginCI\Installer\Database\MySQLDatabase',
            $database
        );

        $this->assertSame($name, $database->name);
        $this->assertSame($user, $database->user);
        $this->assertSame($pass, $database->pass);
        $this->assertSame($host, $database->host);
    }
}
