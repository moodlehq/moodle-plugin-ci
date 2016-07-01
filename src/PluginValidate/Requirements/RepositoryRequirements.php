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
 * Repository requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class RepositoryRequirements extends GenericRequirements
{
    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'lib.php',
            'db/access.php',
        ]);
    }

    public function getRequiredClasses()
    {
        return [
            FileTokens::create('lib.php')->mustHave($this->plugin->component),
        ];
    }

    public function getRequiredStrings()
    {
        return parent::getRequiredStrings()->mustHave($this->plugin->name.':view');
    }

    public function getRequiredCapabilities()
    {
        return FileTokens::create('db/access.php')->mustHave('repository/'.$this->plugin->name.':view');
    }
}
