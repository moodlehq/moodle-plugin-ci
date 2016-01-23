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

namespace Moodlerooms\MoodlePluginCI\PluginValidate\Requirements;

use Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * Module requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ModuleRequirements extends GenericRequirements
{
    protected function getLangFile()
    {
        return 'lang/en/'.$this->plugin->name.'.php';
    }

    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'lib.php',
            'view.php',
            'index.php',
            'db/install.xml',
            'db/access.php',
        ]);
    }

    public function getRequiredFunctions()
    {
        return [
            FileTokens::create('lib.php')->mustHave($this->plugin->name.'_add_instance')->mustHave($this->plugin->name.'_update_instance'),
            FileTokens::create('db/upgrade.php')->mustHave('xmldb_'.$this->plugin->name.'_upgrade'),
        ];
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())
            ->mustHaveAny(['modulename', 'pluginname'])
            ->mustHave($this->plugin->name.':addinstance');
    }

    public function getRequiredCapabilities()
    {
        return FileTokens::create('db/access.php')->mustHave('mod/'.$this->plugin->name.':addinstance');
    }

    public function getRequiredTables()
    {
        return FileTokens::create('db/install.xml')->mustHave($this->plugin->name);
    }

    public function getRequiredTablePrefix()
    {
        return FileTokens::create('db/install.xml')->mustHaveAny([$this->plugin->name, $this->plugin->component]);
    }
}
