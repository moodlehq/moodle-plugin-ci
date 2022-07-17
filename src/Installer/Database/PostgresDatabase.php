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
    public $user = 'postgres';
    public $type = 'pgsql';

    public function getCreateDatabaseCommand()
    {
        // Travis changed the PostgreSQL package for version 11 and up so, instead of
        // using the "postgres" user it now uses the "travis" one. And the port is
        // 5433 instead of 5432. We use the existence of the PGVER environmental
        // variable to decide which defaults to use.
        //
        // More yet, the connection via "localhost" (local net) now requires login and
        // password, it used to be trust/peer auth mode (not requiring password). If we want to
        // keep localhost (+ port) working, then we need to edit the  pg_hba.conf file
        // to trust/peer the local connections and then restart the database.
        //
        // So, at the end, we are going to use socket connections (host = '')
        // that is perfectly ok for Travis (non dockered database). Only if they
        // haven't been configured another way manually (user, host, port).
        if ($this->user === 'postgres' && getenv('PGVER') && is_numeric(getenv('PGVER')) && getenv('PGVER') >= 11) {
            $this->user = 'travis';
            if ($this->port === '') { // Only if the port is not set.
                if ($this->host === 'localhost') {
                    $this->host = ''; // Use sockets or we'll need to edit pg_hba.conf and restart the server. Only if not set.
                    $this->port = '5433'; // We also need the port to find the correct socket file.
                }
            }
        }
        $pass     = !empty($this->pass) ? 'env PGPASSWORD='.escapeshellarg($this->pass).' ' : '';
        $user     = escapeshellarg($this->user);
        $host     = escapeshellarg($this->host);
        $port     = !empty($this->port) ? ' --port '.escapeshellarg($this->port) : '';
        $createDB = escapeshellarg(sprintf('CREATE DATABASE "%s";', $this->name));

        return sprintf('%spsql -c %s -U %s -h %s%s', $pass, $createDB, $user, $host, $port);
    }
}
