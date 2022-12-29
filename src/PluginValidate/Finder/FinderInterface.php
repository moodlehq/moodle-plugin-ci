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
 * Finder interface.
 *
 * Finds tokens in a file.
 */
interface FinderInterface
{
    /**
     * Get type - used in reporting.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Find tokens in a given file.
     *
     * @param string     $file
     * @param FileTokens $fileTokens
     */
    public function findTokens(string $file, FileTokens $fileTokens): void;
}
