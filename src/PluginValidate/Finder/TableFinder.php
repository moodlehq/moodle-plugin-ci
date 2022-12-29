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
 * Finds tables in the db/install.xml.
 */
class TableFinder implements FinderInterface
{
    public function getType(): string
    {
        return 'table';
    }

    public function findTokens($file, FileTokens $fileTokens): void
    {
        foreach ($this->findTables($file) as $table) {
            $fileTokens->compare($table);
        }
    }

    /**
     * @param string $file
     *
     * @return array
     */
    protected function findTables(string $file): array
    {
        $tables = [];
        $xml    = simplexml_load_file($file);
        foreach ($xml->xpath('TABLES/TABLE') as $element) {
            if (isset($element['NAME'])) {
                $tables[] = (string) $element['NAME'];
            }
        }

        return $tables;
    }
}
