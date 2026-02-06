<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2024 Justus Dieckmann
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Installer\Database;

/**
 * MSSQL Database.
 */
class MSSQLDatabase extends AbstractDatabase
{
    public string $user = 'sa';
    public string $type = 'sqlsrv';

    public function getCreateDatabaseCommand(): array
    {
        $host = $this->host;
        if (!empty($this->port)) {
            $host .= ",$this->port";
        }

        return array_filter([
            'sqlcmd',
            '-U',
            $this->user,
            !empty($this->pass) ? '-P' : '',
            !empty($this->pass) ? $this->pass : '',
            '-S',
            $host,
            '-Q',
            sprintf('CREATE DATABASE %s COLLATE Latin1_General_CI_AI;', $this->name),
        ]);
    }
}
