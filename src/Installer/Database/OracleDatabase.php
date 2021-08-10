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
 * Oracle Database.
 */
class OracleDatabase extends AbstractDatabase
{
    public $type = 'oci';

    public function getCreateDatabaseCommand()
    {
        return "echo 'All done!'";
    }
}
