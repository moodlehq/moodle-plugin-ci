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
 * Generic plugin requirements.
 *
 * This is used by default, so update wisely.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GenericRequirements extends AbstractRequirements
{
    protected function getLangFile()
    {
        return 'lang/en/'.$this->plugin->component.'.php';
    }

    public function getRequiredFiles()
    {
        return [
            'version.php',
            $this->getLangFile(),
        ];
    }

    public function getRequiredFunctions()
    {
        return [
            FileTokens::create('db/upgrade.php')->mustHave('xmldb_'.$this->plugin->component.'_upgrade'),
        ];
    }

    public function getRequiredClasses()
    {
        return [];
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())->mustHave('pluginname');
    }

    public function getRequiredCapabilities()
    {
        return FileTokens::create('db/access.php'); // None.
    }

    public function getRequiredTables()
    {
        return FileTokens::create('db/install.xml'); // None.
    }

    public function getRequiredTablePrefix()
    {
        return FileTokens::create('db/install.xml')->mustHave($this->plugin->component);
    }
}
