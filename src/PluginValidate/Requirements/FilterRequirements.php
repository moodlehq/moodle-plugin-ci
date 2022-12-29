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
        return array_merge(parent::getRequiredFiles(), [
            'filter.php',
        ]);
    }

    public function getRequiredClasses(): array
    {
        return [
            FileTokens::create('filter.php')->mustHave('filter_'.$this->plugin->name),
        ];
    }

    public function getRequiredStrings(): FileTokens
    {
        return FileTokens::create($this->getLangFile())->mustHave('filtername');
    }
}
