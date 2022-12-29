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
 * Finds classes in a file.
 */
class ClassFinder extends AbstractParserFinder
{
    public function getType(): string
    {
        return 'class';
    }

    public function findTokens($file, FileTokens $fileTokens): void
    {
        $statements = $this->parser->parseFile($file);

        foreach ($this->filter->filterClassNames($statements) as $className) {
            $fileTokens->compare($className);
        }
    }
}
