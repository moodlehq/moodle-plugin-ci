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
 * Manages the installation process.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Install
{
    /**
     * @var InstallOutput
     */
    private $output;

    public function __construct(InstallOutput $output)
    {
        $this->output = $output;
    }

    /**
     * Run the entire install process.
     *
     * @param InstallerCollection $installers
     */
    public function runInstallation(InstallerCollection $installers)
    {
        $this->output->start('Starting install', $installers->sumStepCount() + 1);

        foreach ($installers->all() as $installer) {
            $installer->install();
        }

        $this->output->end('Install completed');
    }
}
