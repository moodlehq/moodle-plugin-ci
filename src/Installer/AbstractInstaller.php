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

namespace MoodlePluginCI\Installer;

/**
 * Abstract Installer.
 */
abstract class AbstractInstaller
{
    private ?InstallOutput $output = null;

    /**
     * Environment variables to write out.
     */
    private array $env = [];

    /**
     * @param InstallOutput $output
     */
    public function setOutput(InstallOutput $output): void
    {
        $this->output = $output;
    }

    /**
     * @return InstallOutput
     */
    public function getOutput(): InstallOutput
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
    public function getEnv(): array
    {
        return $this->env;
    }

    /**
     * Add a variable to write to the environment.
     *
     * @param string $name
     * @param string $value
     */
    public function addEnv(string $name, string $value): void
    {
        $this->env[$name] = $value;
    }

    /**
     * Run install.
     */
    abstract public function install(): void;

    /**
     * Get the number of steps this installer will perform.
     *
     * @return int
     */
    abstract public function stepCount(): int;
}
