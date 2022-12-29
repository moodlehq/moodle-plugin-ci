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
 * Repository requirements.
 */
class RepositoryRequirements extends GenericRequirements
{
    public function getRequiredFiles(): array
    {
        return array_merge(parent::getRequiredFiles(), [
            'lib.php',
            'db/access.php',
        ]);
    }

    public function getRequiredClasses(): array
    {
        return [
            FileTokens::create('lib.php')->mustHave($this->plugin->component),
        ];
    }

    public function getRequiredStrings(): FileTokens
    {
        return parent::getRequiredStrings()->mustHave($this->plugin->name.':view');
    }

    public function getRequiredCapabilities(): FileTokens
    {
        return FileTokens::create('db/access.php')->mustHave('repository/'.$this->plugin->name.':view');
    }
}
