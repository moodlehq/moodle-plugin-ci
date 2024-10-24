<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2024 Moodle Pty Ltd <support@moodle.com>
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\PluginValidate\Finder;

use PhpParser\Node\Name;

/**
 * Finds function call.
 */
class FunctionCallFinder extends AbstractParserFinder
{
    public function getType(): string
    {
        return 'function call';
    }

    public function findTokens($file, FileTokens $fileTokens): void
    {
        $statements = $this->parser->parseFile($file);

        foreach ($this->filter->filterFunctionCalls($statements) as $funccall) {
            if ($funccall->name instanceof Name) {
                $fileTokens->compare((string) $funccall->name);
            }
        }
    }
}
