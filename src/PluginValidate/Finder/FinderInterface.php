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

/**
 * Finder interface.
 *
 * Finds tokens in a file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface FinderInterface
{
    /**
     * Get type - used in reporting.
     *
     * @return string
     */
    public function getType();

    /**
     * Find tokens in a given file.
     *
     * @param string     $file
     * @param FileTokens $fileTokens
     */
    public function findTokens($file, FileTokens $fileTokens);
}
