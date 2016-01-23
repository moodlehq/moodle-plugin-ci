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

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Scalar\String_;

/**
 * Finds Moodle language strings in a file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class LangFinder extends AbstractParserFinder
{
    public function getType()
    {
        return 'language';
    }

    public function findTokens($file, FileTokens $fileTokens)
    {
        $statements = $this->parser->parseFile($file);

        foreach ($this->filter->filterAssignments($statements) as $assign) {
            // Looking for a assignment to an array key, EG: $string['something'].
            if ($assign->var instanceof ArrayDimFetch) {
                // Grab the array index.
                $arrayIndex = $assign->var->dim;
                if ($arrayIndex instanceof String_) {
                    $fileTokens->compare($arrayIndex->value);
                }
            }
        }
    }
}
