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
 * Generic plugin requirements.
 *
 * This is used by default, so update wisely.
 */
class GenericRequirements extends AbstractRequirements
{
    protected function getLangFile(): string
    {
        return 'lang/en/' . $this->plugin->component . '.php';
    }

    public function getRequiredFiles(): array
    {
        return [
            'version.php',
            $this->getLangFile(),
        ];
    }

    public function getRequiredFunctions(): array
    {
        return [
            FileTokens::create('db/upgrade.php')->mustHave('xmldb_' . $this->plugin->component . '_upgrade'),
        ];
    }

    public function getRequiredClasses(): array
    {
        return [];
    }

    public function getRequiredFunctionCalls(): array
    {
        return [];
    }

    public function getRequiredStrings(): FileTokens
    {
        return FileTokens::create($this->getLangFile())->mustHave('pluginname');
    }

    public function getRequiredCapabilities(): FileTokens
    {
        return FileTokens::create('db/access.php'); // None.
    }

    public function getRequiredTables(): FileTokens
    {
        return FileTokens::create('db/install.xml'); // None.
    }

    public function getRequiredTablePrefix(): FileTokens
    {
        return FileTokens::create('db/install.xml')->mustHave($this->plugin->component);
    }

    public function getRequiredBehatTags(): array
    {
        return $this->behatTagsFactory(['@' . $this->plugin->type, '@' . $this->plugin->component]);
    }
}
