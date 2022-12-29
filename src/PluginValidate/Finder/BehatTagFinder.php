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
 * Finds tags in tests/behat/*.feature files.
 */
class BehatTagFinder implements FinderInterface
{
    public function getType(): string
    {
        return 'Behat tag';
    }

    public function findTokens($file, FileTokens $fileTokens): void
    {
        foreach (file($file) as $line) {
            if (strpos($line, 'Feature:') !== false) {
                break; // Stop looking after we found the feature definition line.
            }
            if (preg_match_all('/@\w+/', $line, $maches) === 0) {
                continue;
            }
            foreach ($maches[0] as $match) {
                $fileTokens->compare($match);
            }
        }
    }
}
