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

use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Requirements resolver.
 */
class RequirementsResolver
{
    /**
     * Find the requirements for a given plugin type.
     *
     * @param int $moodleVersion
     *
     * @return AbstractRequirements
     */
    public function resolveRequirements(Plugin $plugin, $moodleVersion)
    {
        $map = [
            'auth'       => new AuthRequirements($plugin, $moodleVersion),
            'block'      => new BlockRequirements($plugin, $moodleVersion),
            'filter'     => new FilterRequirements($plugin, $moodleVersion),
            'format'     => new FormatRequirements($plugin, $moodleVersion),
            'mod'        => new ModuleRequirements($plugin, $moodleVersion),
            'qtype'      => new QuestionRequirements($plugin, $moodleVersion),
            'repository' => new RepositoryRequirements($plugin, $moodleVersion),
            'theme'      => new ThemeRequirements($plugin, $moodleVersion),
        ];

        if (array_key_exists($plugin->type, $map)) {
            return $map[$plugin->type];
        }

        return new GenericRequirements($plugin, $moodleVersion);
    }
}
