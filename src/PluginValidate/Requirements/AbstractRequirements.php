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
use Moodlerooms\MoodlePluginCI\PluginValidate\Plugin;

/**
 * Abstract plugin requirements.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractRequirements
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * The major Moodle version, EG: 29, 30, 31.
     *
     * @var int
     */
    protected $moodleVersion;

    /**
     * @param Plugin $plugin        Details about the plugin
     * @param int    $moodleVersion The major Moodle version, EG: 29, 30, 31
     */
    public function __construct(Plugin $plugin, $moodleVersion)
    {
        $this->plugin        = $plugin;
        $this->moodleVersion = $moodleVersion;
    }

    /**
     * An array of required files, paths are relative to the plugin directory.
     *
     * @return array
     */
    abstract public function getRequiredFiles();

    /**
     * Required plugin functions.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredFunctions();

    /**
     * Required plugin classes.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredClasses();

    /**
     * Required plugin string definitions.
     *
     * @return FileTokens
     */
    abstract public function getRequiredStrings();

    /**
     * Required plugin capability definitions.
     *
     * @return FileTokens
     */
    abstract public function getRequiredCapabilities();

    /**
     * Required plugin database tables.
     *
     * @return FileTokens
     */
    abstract public function getRequiredTables();

    /**
     * Required plugin database table prefix.
     *
     * @return FileTokens
     */
    abstract public function getRequiredTablePrefix();
}
