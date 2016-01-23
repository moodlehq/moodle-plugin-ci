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

use PhpParser\Node\Expr\Array_;

/**
 * Finds Moodle capabilities in a db/access.php file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CapabilityFinder extends AbstractParserFinder
{
    public function getType()
    {
        return 'capability';
    }

    public function findTokens($file, FileTokens $fileTokens)
    {
        $notFound   = sprintf('Failed to find $capabilities in %s file', $fileTokens->file);
        $statements = $this->parser->parseFile($file);
        $assign     = $this->filter->findFirstVariableAssignment($statements, 'capabilities', $notFound);

        if (!$assign->expr instanceof Array_) {
            throw new \RuntimeException(sprintf('The $capabilities variable is not set to an array in %s file', $fileTokens->file));
        }
        foreach ($this->filter->arrayStringKeys($assign->expr) as $key) {
            $fileTokens->compare($key);
        }
    }
}
