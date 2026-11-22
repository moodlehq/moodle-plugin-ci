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

use MoodlePluginCI\Bridge\Moodle;
use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Process\MoodleProcess;
use Symfony\Component\Process\Process;

/**
 * PHPUnit and Behat installer.
 */
class TestSuiteInstaller extends AbstractInstaller
{
    private Moodle $moodle;
    private MoodlePlugin $plugin;
    private Execute $execute;

    public function __construct(Moodle $moodle, MoodlePlugin $plugin, Execute $execute)
    {
        $this->moodle  = $moodle;
        $this->plugin  = $plugin;
        $this->execute = $execute;
    }

    /**
     * Find the correct Behat utility script in Moodle.
     *
     * @return string
     */
    private function getBehatUtility(): string
    {
        return $this->moodle->getPublicDirectory() . '/admin/tool/behat/cli/util_single_run.php';
    }

    public function install(): void
    {
        $this->getOutput()->step('Initialize test suite');

        $this->execute->mustRunAll(array_merge(
            $this->getBehatInstallProcesses(),
            $this->getUnitTestInstallProcesses()
        ));

        $this->getOutput()->step('Building configs');

        $this->execute->mustRunAll($this->getPostInstallProcesses());
    }

    public function stepCount(): int
    {
        return 2;
    }

    /**
     * Get all the processes to initialize Behat.
     *
     * @return Process[]
     */
    public function getBehatInstallProcesses(): array
    {
        if (!$this->plugin->hasBehatFeatures()) {
            return [];
        }

        // @see BehatCommand::execute()
        $this->addEnv('MOODLE_START_BEHAT_SERVERS', 'YES');

        return [
            new MoodleProcess([
                $this->getBehatUtility(),
                '--install',
            ]),
        ];
    }

    /**
     * Get all the processes to initialize PHPUnit.
     *
     * @return Process[]
     */
    public function getUnitTestInstallProcesses(): array
    {
        if (!$this->plugin->hasUnitTests()) {
            return [];
        }

        $this->getOutput()->debug('Initialize PHPUnit');

        return [
            new MoodleProcess([
                $this->moodle->getPublicDirectory() . '/admin/tool/phpunit/cli/util.php',
                '--install',
            ]),
        ];
    }

    /**
     * Get all the post install processes.
     *
     * @return Process[]
     */
    public function getPostInstallProcesses(): array
    {
        $processes = [];

        if ($this->plugin->hasBehatFeatures()) {
            $this->getOutput()->debug('Enabling Behat');
            $processes[] = new MoodleProcess([
                $this->getBehatUtility(),
                '--enable',
                '--add-core-features-to-theme',
            ]);
        }
        if ($this->plugin->hasUnitTests()) {
            $this->getOutput()->debug('Build PHPUnit config');
            $processes[] = new MoodleProcess([
                $this->moodle->getPublicDirectory() . '/admin/tool/phpunit/cli/util.php',
                '--buildconfig',
            ]);
            // Only create the PHPUnit config file (phpunit.xml) if it does not exist.
            // This is to avoid overwriting the file if it has been customized by the developer.
            if (!$this->plugin->hasPHPUnitConfig()) {
                $this->getOutput()->debug('Build PHPUnit component config');
                $processes[] = new MoodleProcess([
                    $this->moodle->getPublicDirectory() . '/admin/tool/phpunit/cli/util.php',
                    '--buildcomponentconfigs',
                ]);
            }
        }

        return $processes;
    }
}
