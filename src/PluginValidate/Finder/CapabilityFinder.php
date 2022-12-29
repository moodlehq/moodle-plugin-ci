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

use PhpParser\Node\Expr\Array_;

/**
 * Finds Moodle capabilities in a db/access.php file.
 */
class CapabilityFinder extends AbstractParserFinder
{
    public function getType(): string
    {
        return 'capability';
    }

    public function findTokens($file, FileTokens $fileTokens): void
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
