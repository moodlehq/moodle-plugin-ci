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

namespace Moodlerooms\MoodlePluginCI\Installer;

/**
 * Abstract Installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractInstaller
{
    /**
     * @var InstallOutput
     */
    protected $output;

    /**
     * Environment variables to write out.
     *
     * @var array
     */
    public $env = [];

    public function setInstallOutput(InstallOutput $output)
    {
        $this->output = $output;
    }

    /**
     * Run install.
     */
    abstract public function install();

    /**
     * Get the number of steps this installer will perform.
     *
     * @return int
     */
    abstract public function stepCount();

    /**
     * Add a variable to write to the environment.
     *
     * @param string $name
     * @param string $value
     */
    public function addEnv($name, $value)
    {
        $this->env[$name] = $value;
    }
}
