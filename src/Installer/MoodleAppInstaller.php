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

use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Validate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Moodle App Installer.
 */
class MoodleAppInstaller extends AbstractInstaller
{
    private Execute $execute;
    private string $pluginsDir;

    public function __construct(Execute $execute, string $pluginsDir)
    {
        $this->execute    = $execute;
        $this->pluginsDir = $pluginsDir;
    }

    public function install(): void
    {
        $this->addEnv('MOODLE_APP', 'true');

        // Launch docker image.
        $this->getOutput()->step('Launch Moodle App docker image');

        $image = getenv('MOODLE_APP_DOCKER_IMAGE') ?: 'moodlehq/moodleapp:latest-test';
        $port  = getenv('MOODLE_APP_PORT') ?: '443';

        $this->execute->mustRun([
            'docker',
            'run',
            '-d',
            '--rm',
            '--name=moodleapp',
            '-p',
            "8100:$port",
            $image,
        ]);

        // Clone plugin.
        $this->getOutput()->step('Clone Moodle App Behat plugin');

        $pluginProject    = getenv('MOODLE_APP_BEHAT_PLUGIN_PROJECT') ?: 'moodlehq/moodle-local_moodleappbehat';
        $pluginRepository = getenv('MOODLE_APP_BEHAT_PLUGIN_REPOSITORY') ?: sprintf('https://github.com/%s.git', $pluginProject);
        $pluginBranch     = getenv('MOODLE_APP_BEHAT_PLUGIN_BRANCH') ?: 'latest';
        $filesystem       = new Filesystem();
        $validate         = new Validate();
        $command          = [
            'git',
            'clone',
            '--depth',
            '1',
            '--branch',
            $pluginBranch,
            $pluginRepository,
        ];

        $filesystem->mkdir($this->pluginsDir);
        $storageDir = realpath($validate->directory($this->pluginsDir));
        $this->execute->mustRun(new Process($command, $storageDir, null, null, null));
    }

    public function stepCount(): int
    {
        return 2;
    }
}
