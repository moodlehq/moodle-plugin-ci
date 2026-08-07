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

use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Resolves string requirements based on plugin type.
 *
 * Acts as a factory to return the appropriate requirements class
 * for different plugin types.
 */
class StringRequirementsResolver
{
    /**
     * Resolve requirements for a plugin.
     *
     * @param Plugin $plugin        The plugin to resolve requirements for
     * @param int    $moodleVersion The Moodle version
     *
     * @return AbstractStringRequirements The requirements for the plugin
     */
    public function resolve(Plugin $plugin, int $moodleVersion): AbstractStringRequirements
    {
        switch ($plugin->type) {
            case 'mod':
                return new ModuleStringRequirements($plugin, $moodleVersion);
            default:
                // Use generic requirements for all other plugin types
                return new GenericStringRequirements($plugin, $moodleVersion);
        }
    }
}
