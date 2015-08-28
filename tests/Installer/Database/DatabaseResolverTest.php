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

namespace Moodlerooms\MoodlePluginCI\Tests\Installer\Database;

use Moodlerooms\MoodlePluginCI\Installer\Database\DatabaseResolver;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class DatabaseResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $resolver = new DatabaseResolver();

        $this->assertInstanceOf(
            'Moodlerooms\MoodlePluginCI\Installer\Database\MySQLDatabase',
            $resolver->resolveDatabase('mysqli')
        );
        $this->assertInstanceOf(
            'Moodlerooms\MoodlePluginCI\Installer\Database\PostgresDatabase',
            $resolver->resolveDatabase('pgsql')
        );
    }

    /**
     * @expectedException \DomainException
     */
    public function testTypeError()
    {
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
            'Moodlerooms\MoodlePluginCI\Installer\Database\MySQLDatabase',
            $database
        );

        $this->assertEquals($name, $database->name);
        $this->assertEquals($user, $database->user);
        $this->assertEquals($pass, $database->pass);
        $this->assertEquals($host, $database->host);
    }
}
