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
 * Installer collection.
 */
class InstallerCollection
{
    /**
     * @var AbstractInstaller[]
     */
    private array $installers = [];
    private InstallOutput $output;

    public function __construct(InstallOutput $output)
    {
        $this->output = $output;
    }

    /**
     * Add an installer.
     *
     * @param AbstractInstaller $installer
     */
    public function add(AbstractInstaller $installer): void
    {
        $installer->setOutput($this->output);
        $this->installers[] = $installer;
    }

    /**
     * @return AbstractInstaller[]
     */
    public function all(): array
    {
        return $this->installers;
    }

    /**
     * Merge the environment variables from all installers.
     *
     * @return array
     */
    public function mergeEnv(): array
    {
        $env = [];
        foreach ($this->installers as $installer) {
            $env = array_merge($env, $installer->getEnv());
        }

        return $env;
    }

    /**
     * Get the total number of steps from all installers.
     *
     * @return int
     */
    public function sumStepCount(): int
    {
        $sum = 0;
        foreach ($this->installers as $installer) {
            $sum += $installer->stepCount();
        }

        return $sum;
    }
}
