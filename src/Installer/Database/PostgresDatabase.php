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
 * Postgres Database.
 */
class PostgresDatabase extends AbstractDatabase
{
    public string $user = 'postgres';
    public string $type = 'pgsql';

    public function getCreateDatabaseCommand(): array
    {
        $passcmd = [];
        if (!empty($this->pass)) {
            $passcmd = [
                'env',
                'PGPASSWORD=' . $this->pass,
            ];
        }

        $hostcmd = [];
        if (!empty($this->host)) {
            $hostcmd = [
                '-h',
                $this->host,
            ];
        }

        $portcmd = [];
        if (!empty($this->port)) {
            $portcmd = [
                '--port',
                $this->port,
            ];
        }

        $cmd = array_merge(
            $passcmd,
            [
                'psql',
                '-c',
                sprintf('CREATE DATABASE "%s";', $this->name),
                '-U',
                $this->user,
                '-d',
                'postgres',
            ],
            $hostcmd,
            $portcmd
        );

        return array_filter($cmd); // Remove empties.
    }
}
