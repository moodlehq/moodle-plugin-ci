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
use MoodlePluginCI\Bridge\MoodleConfig;
use MoodlePluginCI\Installer\Database\AbstractDatabase;
use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Validate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Installer.
 */
class MoodleInstaller extends AbstractInstaller
{
    private Execute $execute;
    private AbstractDatabase $database;
    private Moodle $moodle;
    private MoodleConfig $config;
    private string $repo;
    private string $branch;
    private string $dataDir;

    /**
     * @param Execute          $execute
     * @param AbstractDatabase $database
     * @param Moodle           $moodle
     * @param MoodleConfig     $config
     * @param string           $repo
     * @param string           $branch
     * @param string           $dataDir
     */
    public function __construct(Execute $execute, AbstractDatabase $database, Moodle $moodle, MoodleConfig $config,
        string $repo, string $branch, string $dataDir)
    {
        $this->execute  = $execute;
        $this->database = $database;
        $this->moodle   = $moodle;
        $this->config   = $config;
        $this->repo     = $repo;
        $this->branch   = $branch;
        $this->dataDir  = $dataDir;
    }

    public function install(): void
    {
        $this->getOutput()->step('Cloning Moodle');

        $cmd = [
            'git', 'clone',
            '--depth=1',
            '--branch', $this->branch,
            $this->repo,
            $this->moodle->directory,
        ];

        $this->execute->mustRun(new Process($cmd, null, null, null, null));

        // Expand the path to Moodle so all other installers use absolute path.
        $this->moodle->directory = $this->expandPath($this->moodle->directory);

        // If there are submodules, we clean up empty directories, since we
        // don't initialise them properly anyway.
        if (is_file($this->moodle->directory . '/.gitmodules')) {
            $process = Process::fromShellCommandline(sprintf('git config -f %s --get-regexp \'^submodule\..*\.path$\' ' .
                '| awk \'{ print $2 }\' | xargs -i rmdir "%s/{}"',
                $this->moodle->directory . '/.gitmodules', $this->moodle->directory));
            $process->setTimeout(null);
            $this->execute->mustRun($process);
        }

        $this->getOutput()->step('Moodle assets');

        $this->getOutput()->debug('Creating Moodle data directories');

        $dirs = [$this->dataDir, $this->dataDir . '/phpu_moodledata', $this->dataDir . '/behat_moodledata', $this->dataDir . '/behat_dump'];

        $filesystem = new Filesystem();
        $filesystem->mkdir($dirs);
        $filesystem->chmod($dirs, 0777);

        $this->getOutput()->debug('Create Moodle database');
        $this->execute->mustRun($this->database->getCreateDatabaseCommand());

        $this->getOutput()->debug('Creating Moodle\'s config file');
        $contents = $this->config->createContents($this->database, $this->expandPath($this->dataDir));
        $this->config->dump($this->moodle->directory . '/config.php', $contents);

        $this->addEnv('MOODLE_DIR', $this->moodle->directory);
    }

    /**
     * Converts a path to an absolute path.
     *
     * @param string $path
     *
     * @return string
     */
    public function expandPath(string $path): string
    {
        $validate = new Validate();

        return realpath($validate->directory($path));
    }

    public function stepCount(): int
    {
        return 2;
    }
}
