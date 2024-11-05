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

namespace MoodlePluginCI\Parser;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;

/**
 * Filter parsed code.
 */
class StatementFilter
{
    /**
     * @param array $statements
     *
     * @return Function_[]
     */
    public function filterFunctions(array $statements): array
    {
        return array_filter($statements, function ($statement) {
            return $statement instanceof Function_;
        });
    }

    /**
     * Return class statements, does NOT return classes within namespaces!
     *
     * @param array $statements
     *
     * @return Class_[]
     */
    public function filterClasses(array $statements): array
    {
        return array_filter($statements, function ($statement) {
            return $statement instanceof Class_;
        });
    }

    /**
     * Returns class names, including those within namespaces (one level deep).
     *
     * @param array $statements
     *
     * @return string[]
     */
    public function filterClassNames(array $statements): array
    {
        $names = [];
        foreach ($this->filterClasses($statements) as $class) {
            if (!isset($class->name)) {
                continue;
            }
            $names[] = (string) $class->name;
        }

        foreach ($this->filterNamespaces($statements) as $namespace) {
            foreach ($this->filterClasses($namespace->stmts) as $class) {
                if (!isset($class->name)) {
                    continue;
                }
                $names[] = ($namespace->name ?? '') . '\\' . $class->name;
            }
        }

        return $names;
    }

    /**
     * @param array $statements
     *
     * @return Namespace_[]
     */
    public function filterNamespaces(array $statements): array
    {
        return array_filter($statements, function ($statement) {
            return $statement instanceof Namespace_;
        });
    }

    /**
     * Extract all the assignment expressions from the statements.
     *
     * @param Stmt[] $statements
     *
     * @return Assign[]
     */
    public function filterAssignments(array $statements): array
    {
        $assigns = [];
        foreach ($statements as $statement) {
            // Only expressions that are assigns.
            if ($statement instanceof Expression && $statement->expr instanceof Assign) {
                $assigns[] = $statement->expr;
            }
        }

        return $assigns;
    }

    /**
     * Extract all the function call expressions from the statements.
     *
     * @param Stmt[] $statements
     *
     * @return FuncCall[]
     */
    public function filterFunctionCalls(array $statements): array
    {
        $calls = [];
        foreach ($statements as $statement) {
            // Only expressions that are function calls.
            if ($statement instanceof Expression && $statement->expr instanceof FuncCall) {
                $calls[] = $statement->expr;
            }
        }

        return $calls;
    }

    /**
     * Find first variable assignment with a given name.
     *
     * @param array       $statements
     * @param string      $name
     * @param string|null $notFoundError
     *
     * @return Assign
     */
    public function findFirstVariableAssignment(array $statements, string $name, ?string $notFoundError = null): Assign
    {
        foreach ($this->filterAssignments($statements) as $assign) {
            if ($assign->var instanceof Variable && is_string($assign->var->name)) {
                if ($assign->var->name === $name) {
                    return $assign;
                }
            }
        }

        throw new \RuntimeException($notFoundError ?: sprintf('Variable assignment $%s not found', $name));
    }

    /**
     * Find first property fetch assignment with a given name.
     *
     * EG: Find $foo->bar = something.
     *
     * @param array       $statements    PHP statements
     * @param string      $variable      The variable name, EG: foo in $foo->bar
     * @param string      $property      The property name, EG: bar in $foo->bar
     * @param string|null $notFoundError Use this error when not found
     *
     * @return Assign
     */
    public function findFirstPropertyFetchAssignment(array $statements, string $variable, string $property, ?string $notFoundError = null): Assign
    {
        foreach ($this->filterAssignments($statements) as $assign) {
            if ($assign->var instanceof PropertyFetch && $assign->var->name instanceof Identifier) {
                $propName = $assign->var->name->name;
                $var      = $assign->var->var;
                if ($var instanceof Variable && is_string($var->name)) {
                    if ($var->name === $variable && $propName === $property) {
                        return $assign;
                    }
                }
            }
        }

        throw new \RuntimeException($notFoundError ?: sprintf('Variable assignment $%s->%s not found', $variable, $property));
    }

    /**
     * Given an array, find all the string keys.
     *
     * @param Array_ $array
     *
     * @return array
     */
    public function arrayStringKeys(Array_ $array): array
    {
        $keys = [];
        foreach ($array->items as $item) {
            if (isset($item->key) && $item->key instanceof String_) {
                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }
}
