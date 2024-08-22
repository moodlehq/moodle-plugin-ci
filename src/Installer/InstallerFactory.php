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
use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Installer\Database\AbstractDatabase;
use MoodlePluginCI\Process\Execute;

/**
 * Installer Factory.
 */
class InstallerFactory
{
    public Moodle $moodle;
    public MoodlePlugin $plugin;
    public Execute $execute;
    public AbstractDatabase $database;
    public string $repo;
    public string $branch;
    public string $dataDir;
    public ConfigDumper $dumper;
    public ?string $pluginsDir;
    public bool $noInit;
    public bool $noNvm;
    public bool $noPluginNode;
    public ?string $nodeVer;

    /**
     * Given a big bag of install options, add installers to the collection.
     *
     * @param InstallerCollection $installers Installers will be added to this
     */
    public function addInstallers(InstallerCollection $installers): void
    {
        $installers->add(new MoodleInstaller($this->execute, $this->database, $this->moodle, new MoodleConfig(), $this->repo, $this->branch, $this->dataDir));

        if (getenv('MOODLE_APP')) {
            $this->pluginsDir = $this->pluginsDir ?? 'moodle-plugin-ci-plugins';

            $installers->add(new MoodleAppInstaller($this->execute, $this->pluginsDir));
        }

        $installers->add(new PluginInstaller($this->moodle, $this->plugin, $this->pluginsDir, $this->dumper));
        $installers->add(new VendorInstaller($this->moodle, $this->plugin, $this->execute, $this->noPluginNode, $this->nodeVer, $this->noNvm));

        if ($this->noInit) {
            return;
        }
        if ($this->plugin->hasBehatFeatures() || $this->plugin->hasUnitTests()) {
            $installers->add(new TestSuiteInstaller($this->moodle, $this->plugin, $this->execute));
        }
    }
}
