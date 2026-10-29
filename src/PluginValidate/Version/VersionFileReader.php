<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2026 Catalyst IT Australia
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\PluginValidate\Version;

use MoodlePluginCI\Parser\CodeParser;
use MoodlePluginCI\Parser\StatementFilter;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;

/**
 * Reads the `$plugin->name = expr;` assignments from a version.php file.
 *
 * The file is parsed, never executed. Only top-level assignments are read, which matches the
 * format Moodle expects in version.php.
 */
class VersionFileReader
{
    /**
     * @param string $path Path to version.php
     *
     * @throws \Exception When the file cannot be read or parsed
     *
     * @return array{properties: array<string, Expr>, duplicates: string[]}
     *                                                                      properties maps property name to the value expression,
     *                                                                      duplicates lists property names assigned more than once
     */
    public function read(string $path): array
    {
        $parser     = new CodeParser();
        $filter     = new StatementFilter();
        $statements = $parser->parseFile($path);

        $properties = [];
        $duplicates = [];
        foreach ($filter->filterAssignments($statements) as $assign) {
            $name = $this->propertyName($assign->var);
            if ($name === null) {
                continue;
            }
            if (array_key_exists($name, $properties)) {
                $duplicates[] = $name;
            }
            $properties[$name] = $assign->expr;
        }

        return [
            'properties' => $properties,
            'duplicates' => array_values(array_unique($duplicates)),
        ];
    }

    /**
     * @param Expr $var Left hand side of an assignment
     *
     * @return string|null Property name when the left hand side is `$plugin->name`
     */
    private function propertyName(Expr $var): ?string
    {
        if (!$var instanceof PropertyFetch) {
            return null;
        }
        if (!$var->var instanceof Variable || $var->var->name !== 'plugin') {
            return null;
        }
        if (!$var->name instanceof Identifier) {
            return null;
        }

        return $var->name->toString();
    }
}
