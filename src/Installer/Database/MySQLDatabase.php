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
    public $type = 'mysqli';

    public function getCreateDatabaseCommand()
    {
        $passOpt  = !empty($this->pass) ? ' --password='.escapeshellarg($this->pass) : '';
        $user     = escapeshellarg($this->user);
        $host     = escapeshellarg($this->host);
        $createDB = escapeshellarg(sprintf('CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', $this->name));

        return sprintf('mysql -u %s%s -h %s -e %s', $user, $passOpt, $host, $createDB);
    }
}
