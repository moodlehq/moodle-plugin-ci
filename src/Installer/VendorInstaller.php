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
    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @var MoodlePlugin
     */
    private $plugin;

    /**
     * @var Execute
     */
    private $execute;

    public function __construct(Moodle $moodle, MoodlePlugin $plugin, Execute $execute)
    {
        $this->moodle  = $moodle;
        $this->plugin  = $plugin;
        $this->execute = $execute;
    }

    public function install()
    {
        if ($this->canInstallNode()) {
            $this->getOutput()->step('Installing Node.js version specified in .nvmrc');
            $nvmDir  = getenv('NVM_DIR');
            $cmd     = ". $nvmDir/nvm.sh && nvm install && nvm use && echo \"NVM_BIN=\$NVM_BIN\"";
            $process = $this->execute->passThrough($cmd, $this->moodle->directory);
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

        $this->getOutput()->step('Install global dependencies');

        $processes = [];
        if ($this->plugin->hasUnitTests() || $this->plugin->hasBehatFeatures()) {
            $processes[] = new Process('composer install --no-interaction --prefer-dist', $this->moodle->directory, null, null, null);
        }
        $processes[] = new Process('npm install -g --no-progress grunt', null, null, null, null);

        $this->execute->mustRunAll($processes);

        $this->getOutput()->step('Install npm dependencies');

        $this->execute->mustRun(new Process('npm install --no-progress', $this->moodle->directory, null, null, null));
        if ($this->plugin->hasNodeDependencies()) {
            $this->execute->mustRun(new Process('npm install --no-progress', $this->plugin->directory, null, null, null));
        }

        $this->execute->mustRun(new Process('grunt ignorefiles', $this->moodle->directory, null, null, null));
    }

    public function stepCount()
    {
        return ($this->canInstallNode()) ? 3 : 2;
    }

    /**
     * Check if we have everything needed to proceed with Node.js installation step.
     *
     * @return bool
     */
    public function canInstallNode()
    {
        // TODO: Check if currently installed Node.js is matching.
        if (is_file($this->moodle->directory.'/.nvmrc')) {
            $reqversion = file_get_contents($this->moodle->directory.'/.nvmrc');

            return getenv('NVM_DIR') && getenv('NVM_BIN') && strpos(getenv('NVM_BIN'), $reqversion) === false;
        }

        return false;
    }
}
