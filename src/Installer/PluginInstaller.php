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
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePluginCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Moodle plugins installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginInstaller extends AbstractInstaller
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
     * @var string
     */
    private $extraPluginsDir;

    /**
     * @var ConfigDumper
     */
    private $configDumper;

    /**
     * @param Moodle       $moodle
     * @param MoodlePlugin $plugin
     * @param string       $extraPluginsDir
     * @param ConfigDumper $configDumper
     */
    public function __construct(Moodle $moodle, MoodlePlugin $plugin, $extraPluginsDir, ConfigDumper $configDumper)
    {
        $this->moodle          = $moodle;
        $this->plugin          = $plugin;
        $this->extraPluginsDir = $extraPluginsDir;
        $this->configDumper    = $configDumper;
    }

    public function install()
    {
        $this->getOutput()->step('Install plugins');

        $plugins = $this->scanForPlugins();
        $plugins->add($this->plugin);
        $sorted = $plugins->sortByDependencies();

        foreach ($sorted->all() as $plugin) {
            $directory = $this->installPluginIntoMoodle($plugin);

            if ($plugin->getComponent() === $this->plugin->getComponent()) {
                $this->addEnv('PLUGIN_DIR', $directory);
                $this->createConfigFile($directory.'/.moodle-plugin-ci.yml');

                // Update plugin so other installers use the installed path.
                $this->plugin->directory = $directory;
            }
        }
    }

    /**
     * @return MoodlePluginCollection
     */
    public function scanForPlugins()
    {
        $plugins = new MoodlePluginCollection();

        if (empty($this->extraPluginsDir)) {
            return $plugins;
        }

        /** @var SplFileInfo[] $files */
        $files = Finder::create()->directories()->in($this->extraPluginsDir)->depth(0);
        foreach ($files as $file) {
            $plugins->add(new MoodlePlugin($file->getRealPath()));
        }

        return $plugins;
    }

    /**
     * Install the plugin into Moodle.
     *
     * @param MoodlePlugin $plugin
     *
     * @return string
     */
    public function installPluginIntoMoodle(MoodlePlugin $plugin)
    {
        $this->getOutput()->info(sprintf('Installing %s', $plugin->getComponent()));

        $directory = $this->moodle->getComponentInstallDirectory($plugin->getComponent());

        if (is_dir($directory)) {
            throw new \RuntimeException('Plugin is already installed in standard Moodle');
        }

        $this->getOutput()->info(sprintf('Copying plugin from %s to %s', $plugin->directory, $directory));

        // Install the plugin.
        $filesystem = new Filesystem();
        $filesystem->mirror($plugin->directory, $directory);

        return $directory;
    }

    /**
     * Create plugin config file.
     *
     * @param string $toFile
     */
    public function createConfigFile($toFile)
    {
        if (file_exists($toFile)) {
            $this->getOutput()->debug('Config file already exists in plugin, skipping creation of config file.');

            return;
        }
        if (!$this->configDumper->hasConfig()) {
            $this->getOutput()->debug('No config to write out, skipping creation of config file.');

            return;
        }
        $this->configDumper->dump($toFile);
        $this->getOutput()->debug('Created config file at '.$toFile);
    }

    public function stepCount()
    {
        return 1;
    }
}
