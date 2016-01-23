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
 * Filter plugin requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FilterRequirements extends GenericRequirements
{
    public function getRequiredFiles()
    {
        return array_merge(parent::getRequiredFiles(), [
            'filter.php',
        ]);
    }

    public function getRequiredClasses()
    {
        return [
            FileTokens::create('filter.php')->mustHave('filter_'.$this->plugin->name),
        ];
    }

    public function getRequiredStrings()
    {
        return FileTokens::create($this->getLangFile())->mustHave('filtername');
    }
}
