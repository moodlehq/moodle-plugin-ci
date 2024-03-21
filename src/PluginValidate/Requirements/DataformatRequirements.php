<?php

/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2024 Marina Glancy
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\PluginValidate\Requirements;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * Dataformat plugin requirements.
 */
class DataformatRequirements extends GenericRequirements
{
    public function getRequiredStrings(): FileTokens
    {
        return FileTokens::create($this->getLangFile())->mustHave('dataformat');
    }
}
