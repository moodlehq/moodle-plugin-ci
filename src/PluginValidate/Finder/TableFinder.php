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

namespace Moodlerooms\MoodlePluginCI\PluginValidate\Finder;

/**
 * Finds tables in the db/install.xml.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TableFinder implements FinderInterface
{
    public function getType()
    {
        return 'table';
    }

    public function findTokens($file, FileTokens $fileTokens)
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
    protected function findTables($file)
    {
        $tables = [];
        $xml    = simplexml_load_file($file);
        foreach ($xml->xpath('TABLES/TABLE') as $element) {
            if (!is_null($element['NAME'])) {
                $tables[] = (string) $element['NAME'];
            }
        }

        return $tables;
    }
}
