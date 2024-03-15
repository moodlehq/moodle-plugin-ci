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
use MoodlePluginCI\Installer\Database\MariaDBDatabase;
use MoodlePluginCI\Installer\Database\MySQLDatabase;
use MoodlePluginCI\Installer\Database\PostgresDatabase;

class DatabaseResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testType(): void
    {
        $resolver = new DatabaseResolver();

        $this->assertInstanceOf(
            MySQLDatabase::class,
            $resolver->resolveDatabase('mysqli')
        );
        $this->assertInstanceOf(
            PostgresDatabase::class,
            $resolver->resolveDatabase('pgsql')
        );
        $this->assertInstanceOf(
            MariaDBDatabase::class,
            $resolver->resolveDatabase('mariadb')
        );
    }

    public function testTypeError(): void
    {
        $this->expectException(\DomainException::class);
        $resolver = new DatabaseResolver();
        $resolver->resolveDatabase('foo');
    }

    public function testOptions(): void
    {
        $resolver = new DatabaseResolver();

        $name = 'TestName';
        $user = 'TestUser';
        $pass = 'TestPass';
        $host = 'TestHost';
        $port = 'TestPort';

        $database = $resolver->resolveDatabase('mysqli', $name, $user, $pass, $host, $port);

        $this->assertInstanceOf(
            MySQLDatabase::class,
            $database
        );

        $this->assertSame($name, $database->name);
        $this->assertSame($user, $database->user);
        $this->assertSame($pass, $database->pass);
        $this->assertSame($host, $database->host);
    }
}
