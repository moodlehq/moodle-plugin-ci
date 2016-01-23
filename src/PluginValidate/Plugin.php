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

namespace Moodlerooms\MoodlePluginCI\PluginValidate;

/**
 * Plugin information.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Plugin
{
    /**
     * The plugin component, EG: mod_forum.
     *
     * @var string
     */
    public $component;

    /**
     * The plugin type, EG: mod in mod_forum.
     *
     * @var string
     */
    public $type;

    /**
     * The plugin name, EG: forum in mod_forum.
     *
     * @var string
     */
    public $name;

    /**
     * Absolute path to the plugin directory.
     *
     * @var string
     */
    public $directory;

    /**
     * @param string $component
     * @param string $type
     * @param string $name
     * @param string $directory
     */
    public function __construct($component, $type, $name, $directory)
    {
        $this->component = $component;
        $this->type      = $type;
        $this->name      = $name;
        $this->directory = $directory;
    }
}
