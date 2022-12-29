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

namespace MoodlePluginCI\PluginValidate;

/**
 * Plugin information.
 */
class Plugin
{
    /**
     * The plugin component, EG: mod_forum.
     */
    public string $component;

    /**
     * The plugin type, EG: mod in mod_forum.
     */
    public string $type;

    /**
     * The plugin name, EG: forum in mod_forum.
     */
    public string $name;

    /**
     * Absolute path to the plugin directory.
     */
    public string $directory;

    /**
     * @param string $component
     * @param string $type
     * @param string $name
     * @param string $directory
     */
    public function __construct(string $component, string $type, string $name, string $directory)
    {
        $this->component = $component;
        $this->type      = $type;
        $this->name      = $name;
        $this->directory = $directory;
    }
}
