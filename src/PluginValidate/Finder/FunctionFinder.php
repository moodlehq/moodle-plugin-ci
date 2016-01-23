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
 * Finds functions in a file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FunctionFinder extends AbstractParserFinder
{
    public function getType()
    {
        return 'function';
    }

    public function findTokens($file, FileTokens $fileTokens)
    {
        $statements = $this->parser->parseFile($file);

        foreach ($this->filter->filterFunctions($statements) as $function) {
            $fileTokens->compare($function->name);
        }
    }
}
