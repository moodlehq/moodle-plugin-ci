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

use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\ExceptionChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\GradeItemChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\PrivacyProviderChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\SearchAreaChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CachesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CapabilitiesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MessagesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MobileChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\SubpluginsChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\TagsChecker;

/**
 * Registry for string checkers.
 *
 * To add a new checker, simply add it to the getDefaultCheckers() method.
 */
class CheckersRegistry
{
    /**
     * Get all default string checkers.
     *
     * @return StringCheckerInterface[] array of checker instances
     */
    public static function getCheckers(): array
    {
        return array_merge(
            self::databaseFileCheckers(),
            self::classMethodCheckers()
        );
    }

    /**
     * Get all database file checkers.
     *
     * @return StringCheckerInterface[] array of checker instances
     */
    public static function databaseFileCheckers(): array
    {
        return [
            new CapabilitiesChecker(),
            new CachesChecker(),
            new MessagesChecker(),
            new MobileChecker(),
            new SubpluginsChecker(),
            new TagsChecker(),
        ];
    }

    /**
     * Get all class method checkers.
     *
     * @return StringCheckerInterface[] array of checker instances
     */
    public static function classMethodCheckers(): array
    {
        return [
            new ExceptionChecker(),
            new GradeItemChecker(),
            new PrivacyProviderChecker(),
            new SearchAreaChecker(),
        ];
    }
}
