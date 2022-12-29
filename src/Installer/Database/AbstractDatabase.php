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

namespace MoodlePluginCI\Installer\Database;

/**
 * Abstract Database.
 */
abstract class AbstractDatabase
{
    /**
     * Database username.
     */
    public string $user = 'root';

    /**
     * Database password.
     */
    public string $pass = '';

    /**
     * Database name.
     */
    public string $name = 'moodle';

    /**
     * Database host.
     */
    public string $host = 'localhost';

    /**
     * Database port.
     */
    public string $port = '';

    /**
     * Moodle database type.
     */
    public string $type;

    /**
     * Moodle database library.
     */
    public string $library = 'native';

    /**
     * Get database create command.  Suitable for executing on the CLI.
     *
     * @return string[]
     */
    abstract public function getCreateDatabaseCommand(): array;
}
