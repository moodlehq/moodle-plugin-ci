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

namespace MoodlePluginCI\MissingStrings\Checker;

use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Interface for string checkers.
 *
 * String checkers analyze specific files or code patterns to determine
 * what language strings are required by the plugin.
 */
interface StringCheckerInterface
{
    /**
     * Check for required strings in the plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return ValidationResult the result containing required strings
     */
    public function check(Plugin $plugin): ValidationResult;

    /**
     * Get the name of this checker for reporting purposes.
     *
     * @return string the checker name
     */
    public function getName(): string;

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if this checker should run for the plugin
     */
    public function appliesTo(Plugin $plugin): bool;
}
