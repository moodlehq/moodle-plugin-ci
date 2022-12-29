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
 * MySQL Database.
 */
class MySQLDatabase extends AbstractDatabase
{
    public string $type = 'mysqli';

    public function getCreateDatabaseCommand(): array
    {
        return array_filter([
            'mysql',
            '-u',
            $this->user,
            !empty($this->pass) ? '--password='.$this->pass : '',
            '-h',
            $this->host,
            !empty($this->port) ? '--port='.$this->port : '',
            '-e',
            sprintf('CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', $this->name),
        ]);
    }
}
