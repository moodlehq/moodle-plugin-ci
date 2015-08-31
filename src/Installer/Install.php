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

use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Manages the installation process.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Install
{
    /**
     * Run the entire install process.
     *
     * @param InstallerCollection $installers
     * @param EnvDumper           $envDumper
     */
    public function runInstallation(InstallerCollection $installers, EnvDumper $envDumper)
    {
        $logger = $installers->logger;
        $bar    = $installers->progressBar;

        $logger->info('Starting install');

        if ($bar instanceof ProgressBar) {
            $bar->setMessage('Starting install');
            $bar->start($installers->totalSteps() + 1);
        }

        foreach ($installers->all() as $installer) {
            $installer->install();
        }
        $envDumper->dump($installers->mergeEnv());

        $logger->info('Install completed');

        if ($bar instanceof ProgressBar) {
            $bar->setMessage('Install completed');
            $bar->finish();
        }
    }
}
