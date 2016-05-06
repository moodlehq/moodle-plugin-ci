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

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Bridge\MoodleConfig;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\Database\AbstractDatabase;
use Moodlerooms\MoodlePluginCI\Process\Execute;

/**
 * Installer Factory.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallerFactory
{
    /**
     * @var Moodle
     */
    public $moodle;

    /**
     * @var MoodlePlugin
     */
    public $plugin;

    /**
     * @var Execute
     */
    public $execute;

    /**
     * @var AbstractDatabase
     */
    public $database;

    /**
     * @var string
     */
    public $branch;

    /**
     * @var string
     */
    public $dataDir;

    /**
     * @var ConfigDumper
     */
    public $dumper;

    /**
     * @var string
     */
    public $pluginsDir;

    /**
     * Given a big bag of install options, add installers to the collection.
     *
     * @param InstallerCollection $installers Installers will be added to this.
     */
    public function addInstallers(InstallerCollection $installers)
    {
        $installers->add(new MoodleInstaller($this->execute, $this->database, $this->moodle, new MoodleConfig(), $this->branch, $this->dataDir));
        $installers->add(new PluginInstaller($this->moodle, $this->plugin, $this->pluginsDir, $this->dumper));
        $installers->add(new VendorInstaller($this->moodle, $this->plugin, $this->execute));

        if ($this->plugin->hasBehatFeatures() || $this->plugin->hasUnitTests()) {
            $installers->add(new TestSuiteInstaller($this->moodle, $this->plugin, $this->execute));
        }
    }
}
