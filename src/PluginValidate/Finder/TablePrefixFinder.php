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

namespace MoodlePluginCI\PluginValidate\Finder;

/**
 * Finds table prefixes in the db/install.xml.
 */
class TablePrefixFinder extends TableFinder
{
    public function getType(): string
    {
        return 'table prefixes';
    }

    public function findTokens($file, FileTokens $fileTokens): void
    {
        $tables = $this->findTables($file);
        $total  = count($tables);
        for ($i = 0; $i < $total; ++$i) {
            $fileTokens->compareStart($tables[$i]);

            // This runs after every table except for the last one.
            if ($i !== $total - 1) {
                if (!$fileTokens->hasFoundAllTokens()) {
                    break; // Found an invalid table name, can stop.
                }
                // Current table name valid, reset tokens, so we can see if the next table is valid or not.
                $fileTokens->resetTokens();
            }
        }
    }
}
