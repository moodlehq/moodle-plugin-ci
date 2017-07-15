<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Installer\Database;

/**
 * Postgres Database.
 */
class PostgresDatabase extends AbstractDatabase
{
    public $user = 'postgres';
    public $type = 'pgsql';

    public function getCreateDatabaseCommand()
    {
        $pass     = !empty($this->pass) ? 'env PGPASSWORD='.escapeshellarg($this->pass).' ' : '';
        $user     = escapeshellarg($this->user);
        $host     = escapeshellarg($this->host);
        $createDB = escapeshellarg(sprintf('CREATE DATABASE "%s";', $this->name));

        return sprintf('%spsql -c %s -U %s -h %s', $pass, $createDB, $user, $host);
    }
}
