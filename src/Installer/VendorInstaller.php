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
use Symfony\Component\Process\Process;

/**
 * Vendor installer.
 */
class VendorInstaller extends AbstractInstaller
{
    private Moodle $moodle;
    private MoodlePlugin $plugin;
    private Execute $execute;
    public ?string $nodeVer;

    /**
     * Define legacy Node version to use when .nvmrc is absent (Moodle < 3.5).
     */
    private string $legacyNodeVersion = 'lts/carbon';

    /**
     * @param Moodle       $moodle
     * @param MoodlePlugin $plugin
     * @param Execute      $execute
     * @param string|null  $nodeVer
     */
    public function __construct(Moodle $moodle, MoodlePlugin $plugin, Execute $execute, ?string $nodeVer)
    {
        $this->moodle  = $moodle;
        $this->plugin  = $plugin;
        $this->execute = $execute;
        $this->nodeVer = $nodeVer;
    }

    public function install(): void
    {
        if ($this->canInstallNode()) {
            $this->getOutput()->step('Installing Node.js');
            $this->installNode();
        }

        $this->getOutput()->step('Install global dependencies');

        $processes = [];
        if ($this->plugin->hasUnitTests() || $this->plugin->hasBehatFeatures()) {
            $processes[] = Process::fromShellCommandline('composer install --no-interaction --prefer-dist',
                $this->moodle->directory, null, null, null);
        }
        $processes[] = Process::fromShellCommandline('npm install --no-progress grunt', null, null, null, null);

        $this->execute->mustRunAll($processes);

        $this->getOutput()->step('Install npm dependencies');

        $this->execute->mustRun(
            Process::fromShellCommandline('npm install --no-progress', $this->moodle->directory, null, null, null)
        );
        if ($this->plugin->hasNodeDependencies()) {
            $this->execute->mustRun(
                Process::fromShellCommandline('npm install --no-progress', $this->plugin->directory, null, null, null)
            );
        }

        $this->execute->mustRun(
            Process::fromShellCommandline('npx grunt ignorefiles', $this->moodle->directory, null, null, null)
        );
    }

    public function stepCount(): int
    {
        return ($this->canInstallNode()) ? 3 : 2;
    }

    /**
     * Check if we have nvm to proceed with Node.js installation step.
     *
     * @return bool
     */
    public function canInstallNode(): bool
    {
        return !empty(getenv('NVM_DIR'));
    }

    /**
     * Install Node.js.
     *
     * In order to figure out which version to install, first look for user
     * specified version (NODE_VERSION env variable or --node-version param passed
     * for install step). If there is none, use version from .nvmrc in Moodle
     * directory. If file does not exist, use legacy version (lts/carbon).
     */
    public function installNode(): void
    {
        if (!empty($this->nodeVer)) {
            // Use Node version specified by user.
            $reqversion = $this->nodeVer;
            file_put_contents($this->moodle->directory.'/.nvmrc', $reqversion);
        } elseif (!is_file($this->moodle->directory.'/.nvmrc')) {
            // Use legacy version. Since Moodle 3.5, all branches have the .nvmrc file.
            $reqversion = $this->legacyNodeVersion;
            file_put_contents($this->moodle->directory.'/.nvmrc', $reqversion);
        }

        $nvmDir  = getenv('NVM_DIR');
        $cmd     = ". $nvmDir/nvm.sh && nvm install && nvm use && echo \"NVM_BIN=\$NVM_BIN\"";

        $process = $this->execute->passThroughProcess(
            Process::fromShellCommandline($cmd, $this->moodle->directory, null, null, null)
        );
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Node.js installation failed.');
        }
        // Retrieve NVM_BIN from initialisation output, we will use it to
        // substitute right Node.js environment in all future process runs.
        // @see Execute::setNodeEnv()
        preg_match('/^NVM_BIN=(.+)$/m', trim($process->getOutput()), $matches);
        if (isset($matches[1]) && is_dir($matches[1])) {
            $this->addEnv('RUNTIME_NVM_BIN', $matches[1]);
            putenv('RUNTIME_NVM_BIN='.$matches[1]);
        } else {
            $this->getOutput()->debug('Can\'t retrieve NVM_BIN content from the command output.');
        }
    }
}
