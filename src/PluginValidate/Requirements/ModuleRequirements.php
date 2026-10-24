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
 * Module requirements.
 */
class ModuleRequirements extends GenericRequirements
{
    protected function getLangFile(): string
    {
        return 'lang/en/' . $this->plugin->name . '.php';
    }

    public function getRequiredFiles(): array
    {
        return array_merge(parent::getRequiredFiles(), [
            'lib.php',
            'mod_form.php',
            'view.php',
            'index.php',
            'db/install.xml',
            'db/access.php',
        ]);
    }

    public function getRequiredFunctions(): array
    {
        return [
            FileTokens::create('lib.php')->mustHave($this->plugin->name . '_add_instance')->mustHave($this->plugin->name . '_update_instance')
                ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#libphp---library-functions'),
            FileTokens::create('db/upgrade.php')->mustHave('xmldb_' . $this->plugin->name . '_upgrade')
                ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#upgradephp---upgrade-steps'),
        ];
    }

    public function getRequiredStrings(): FileTokens
    {
        return FileTokens::create($this->getLangFile())
            ->mustHaveAny(['modulename', 'pluginname'])
            ->mustHave($this->plugin->name . ':addinstance')
            ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#langenmodnamephp---language-string-definitions');
    }

    public function getRequiredCapabilities(): FileTokens
    {
        return FileTokens::create('db/access.php')->mustHave('mod/' . $this->plugin->name . ':addinstance')
            ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#accessphp---capability-defaults');
    }

    public function getRequiredTables(): FileTokens
    {
        return FileTokens::create('db/install.xml')->mustHave($this->plugin->name)
            ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#installxml---database-installation');
    }

    public function getRequiredTablePrefix(): FileTokens
    {
        return FileTokens::create('db/install.xml')->mustHaveAny([$this->plugin->name, $this->plugin->component])
            ->notFoundHint('https://moodledev.io/docs/apis/plugintypes/mod#installxml---database-installation');
    }
}
