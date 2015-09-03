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
    private $output;

    /**
     * Environment variables to write out.
     *
     * @var array
     */
    private $env = [];

    /**
     * @param InstallOutput $output
     */
    public function setOutput(InstallOutput $output)
    {
        $this->output = $output;
    }

    /**
     * @return InstallOutput
     */
    public function getOutput()
    {
        // Output is optional, if not set, use null output.
        if (!$this->output instanceof InstallOutput) {
            $this->output = new InstallOutput();
        }

        return $this->output;
    }

    /**
     * @return array
     */
    public function getEnv()
    {
        return $this->env;
    }

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
}
