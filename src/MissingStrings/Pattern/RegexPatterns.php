<?php

declare(strict_types=1);

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\MissingStrings\Pattern;

/**
 * Centralized repository for regex patterns used in string checkers.
 *
 * This class provides standardized regex patterns to ensure consistency
 * across different checkers and reduce code duplication.
 */
class RegexPatterns
{
    /**
     * Pattern for add_database_table calls with optional table description.
     * Matches: add_database_table('table', ['field' => 'string'], 'description').
     */
    public static function addDatabaseTable(): string
    {
        return '/add_database_table\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[(.*?)\](?:\s*,\s*[\'"]([^\'"]+)[\'"])?\s*\)/s';
    }

    /**
     * Pattern for add_external_location_link calls (simple format).
     * Matches: add_external_location_link('service', 'privacy:metadata:service', 'url').
     */
    public static function addExternalLocationLinkSimple(): string
    {
        return '/add_external_location_link\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/';
    }

    /**
     * Pattern for add_external_location_link calls (array format).
     * Matches: add_external_location_link('service', ['field' => 'string'], 'privacy:metadata:service').
     */
    public static function addExternalLocationLinkArray(): string
    {
        return '/add_external_location_link\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[(.*?)\]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/s';
    }

    /**
     * Pattern for add_subsystem_link calls.
     * Matches: add_subsystem_link('subsystem', [], 'privacy:metadata:subsystem').
     */
    public static function addSubsystemLink(): string
    {
        return '/add_subsystem_link\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,.*?[\'"]([^\'"]+)[\'"]\)/';
    }

    /**
     * Pattern for link_subsystem calls.
     * Matches: link_subsystem('subsystem', 'privacy:metadata:subsystem').
     */
    public static function linkSubsystem(): string
    {
        return '/link_subsystem\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)/';
    }

    /**
     * Pattern for add_user_preference calls.
     * Matches: add_user_preference('preference', 'privacy:metadata:preference').
     */
    public static function addUserPreference(): string
    {
        return '/add_user_preference\s*\(\s*[^,]+,\s*[\'"]([^\'"]+)[\'"]\)/';
    }

    /**
     * Pattern for field mappings within arrays.
     * Matches: 'field' => 'privacy:metadata:field'.
     */
    public static function fieldMapping(): string
    {
        return '/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/';
    }

    /**
     * Pattern for return statements with string literals.
     * Matches: return 'privacy:metadata:string';.
     */
    public static function returnStatement(): string
    {
        return '/return\s+[\'"]([^\'"]+)[\'"];/';
    }

    /**
     * Pattern for get_string() function calls.
     * Matches: get_string('identifier', 'component') and get_string('identifier', 'component', $param).
     */
    public static function getString(): string
    {
        return '/get_string\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/';
    }

    /**
     * Pattern for JavaScript getString() calls.
     * Matches: getString('identifier', 'component').
     */
    public static function jsGetString(): string
    {
        return '/getString\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/';
    }

    /**
     * Pattern for JavaScript getStrings() calls.
     * Matches: getStrings([{key: 'identifier', component: 'component'}]).
     */
    public static function jsGetStrings(): string
    {
        return '/getStrings\s*\(\s*\[(.*?)\]\s*\)/s';
    }

    /**
     * Pattern for Prefetch.prefetchString() calls.
     * Matches: Prefetch.prefetchString('identifier', 'component').
     */
    public static function prefetchString(): string
    {
        return '/Prefetch\.prefetchString\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*[,\)]/';
    }

    /**
     * Pattern for Prefetch.prefetchStrings() calls.
     * Matches: Prefetch.prefetchStrings([{key: 'identifier', component: 'component'}]).
     */
    public static function prefetchStrings(): string
    {
        return '/Prefetch\.prefetchStrings\s*\(\s*\[(.*?)\]\s*\)/s';
    }
}
