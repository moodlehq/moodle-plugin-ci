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

namespace MoodlePluginCI\PluginValidate\Requirements;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * Filter plugin requirements.
 */
class FilterRequirements extends GenericRequirements
{
    public function getRequiredFiles(): array
    {
        $files = [];
        if ($this->moodleVersion >= 405) {
            $files[] = 'classes/text_filter.php';
        } else {
            // This must exist in 4.5 if plugin supports older version, but we don't identify support range to validate it.
            $files[] = 'filter.php';
        }

        return array_merge(parent::getRequiredFiles(), $files);
    }

    public function getRequiredClasses(): array
    {
        if ($this->moodleVersion <= 404 && !$this->fileExists('classes/text_filter.php')) {
            // Plugin does not support 4.5, check class presence in filter.php
            return [
                FileTokens::create('filter.php')->mustHave('filter_' . $this->plugin->name),
            ];
        }

        return [
            FileTokens::create('classes/text_filter.php')->mustHave("filter_{$this->plugin->name}\\text_filter"),
        ];
    }

    public function getRequiredStrings(): FileTokens
    {
        return FileTokens::create($this->getLangFile())->mustHave('filtername');
    }

    public function getRequiredFunctionCalls(): array
    {
        if ($this->moodleVersion <= 404 && !$this->fileExists('classes/text_filter.php')) {
            return [];
        }

        return [
            FileTokens::create('filter.php')->mustHave('class_alias')->notFoundHint('https://moodledev.io/docs/4.5/devupdate#filter-plugins'),
        ];
    }
}
