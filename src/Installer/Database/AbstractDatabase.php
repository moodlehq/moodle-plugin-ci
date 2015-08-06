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
 * Abstract Database.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractDatabase
{
    /**
     * Database username.
     *
     * @var string
     */
    public $user = 'root';

    /**
     * Database password.
     *
     * @var string
     */
    public $pass = '';

    /**
     * Database name.
     *
     * @var string
     */
    public $name = 'moodle';

    /**
     * Database host.
     *
     * @var string
     */
    public $host = 'localhost';

    /**
     * Moodle database type.
     *
     * @var string
     */
    public $type;

    /**
     * Moodle database library.
     *
     * @var string
     */
    public $library = 'native';

    /**
     * Get database create command.  Suitable for executing on the CLI.
     *
     * @return string
     */
    abstract public function getCreateDatabaseCommand();
}
