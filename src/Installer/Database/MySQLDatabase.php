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

namespace Moodlerooms\MoodlePluginCI\Installer\Database;

/**
 * MySQL Database.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MySQLDatabase extends AbstractDatabase
{
    public $type = 'mysqli';

    public function getCreateDatabaseCommand()
    {
        $passOpt  = !empty($this->pass) ? ' --password='.escapeshellarg($this->pass) : '';
        $user     = escapeshellarg($this->user);
        $host     = escapeshellarg($this->host);
        $createDB = escapeshellarg(sprintf('CREATE DATABASE `%s` DEFAULT CHARACTER SET UTF8 COLLATE utf8_general_ci;', $this->name));

        return sprintf('mysql -u %s%s -h %s -e %s', $user, $passOpt, $host, $createDB);
    }
}
