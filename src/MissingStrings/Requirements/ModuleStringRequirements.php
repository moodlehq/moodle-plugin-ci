<?php

declare(strict_types=1);

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\MissingStrings\Requirements;

/**
 * String requirements for activity module plugins (mod_*).
 *
 * Activity modules have additional required strings beyond the generic requirements.
 */
class ModuleStringRequirements extends GenericStringRequirements
{
    /**
     * Get required strings for activity modules.
     *
     * @return array Array of string keys that are required
     */
    public function getRequiredStrings(): array
    {
        return array_merge(parent::getRequiredStrings(), [
            'modulename',
            'modulenameplural',
        ]);
    }
}
