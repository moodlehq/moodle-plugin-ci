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
 * MariaDB Database.
 * Since it should be pretty like mysql, it is probably fine to just inherit everything from MySQLDatabase and only change the type.
 */
class MariaDBDatabase extends MySQLDatabase
{
    public $type = 'mariadb';
}
