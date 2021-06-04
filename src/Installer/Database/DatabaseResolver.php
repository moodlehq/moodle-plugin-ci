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
 * Database resolver.
 */
class DatabaseResolver
{
    /**
     * @param string      $type
     * @param string|null $name
     * @param string|null $user
     * @param string|null $pass
     * @param string|null $host
     * @param string|null $port
     *
     * @return AbstractDatabase
     */
    public function resolveDatabase($type, $name = null, $user = null, $pass = null, $host = null, $port = null)
    {
        $database = $this->resolveDatabaseType($type);

        if ($name !== null) {
            $database->name = $name;
        }
        if ($user !== null) {
            $database->user = $user;
        }
        if ($pass !== null) {
            $database->pass = $pass;
        }
        if ($host !== null) {
            $database->host = $host;
        }
        if ($port !== null) {
            $database->port = $port;
        }

        return $database;
    }

    /**
     * Resolve database class.
     *
     * @param string $type Database type
     *
     * @return AbstractDatabase
     */
    private function resolveDatabaseType($type)
    {
        foreach ($this->getDatabases() as $database) {
            if ($database->type === $type) {
                return $database;
            }
        }
        throw new \DomainException(sprintf('Unknown database type (%s). Please use mysqli, pgsql or mariadb.', $type));
    }

    /**
     * @return AbstractDatabase[]
     */
    private function getDatabases()
    {
        return [new MySQLDatabase(), new PostgresDatabase(), new MariaDBDatabase()];
    }
}
