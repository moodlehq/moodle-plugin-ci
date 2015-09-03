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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Moodle plugin installer.
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
     * @var array
     */
    private $notPaths;

    /**
     * @var array
     */
    private $notNames;

    /**
     * @param Moodle       $moodle
     * @param MoodlePlugin $plugin
     * @param array        $notPaths
     * @param array        $notNames
     */
    public function __construct(Moodle $moodle, MoodlePlugin $plugin, array $notPaths = [], array $notNames = [])
    {
        $this->moodle   = $moodle;
        $this->plugin   = $plugin;
        $this->notPaths = $notPaths;
        $this->notNames = $notNames;
    }

    public function install()
    {
        $this->getOutput()->step('Install '.$this->plugin->getComponent());

        $installDir = $this->installPluginIntoMoodle();
        $this->createIgnoreFile($installDir.'/.travis-ignore.yml');

        // Update plugin so other installers use the installed path.
        $this->plugin->directory = $installDir;

        $this->addEnv('PLUGIN_DIR', $installDir);
    }

    /**
     * Install the plugin into Moodle.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function installPluginIntoMoodle()
    {
        $directory = $this->moodle->getComponentInstallDirectory($this->plugin->getComponent());

        if (is_dir($directory)) {
            throw new \RuntimeException('Plugin is already installed in standard Moodle');
        }

        $this->getOutput()->info(sprintf('Copying plugin from %s to %s', $this->plugin->directory, $directory));

        // Install the plugin.
        $filesystem = new Filesystem();
        $filesystem->mirror($this->plugin->directory, $directory);

        return $directory;
    }

    /**
     * Create an ignore file.
     *
     * @param string $filename The file to create
     */
    public function createIgnoreFile($filename)
    {
        if (file_exists($filename)) {
            $this->getOutput()->debug('Ignore file already exists in plugin, skipping creation of ignore file.');

            return;
        }

        $ignores = [];
        if (!empty($this->notPaths)) {
            $ignores['notPaths'] = $this->notPaths;
        }
        if (!empty($this->notNames)) {
            $ignores['notNames'] = $this->notNames;
        }
        if (empty($ignores)) {
            $this->getOutput()->debug('No file ignores to write out, skipping creation of ignore file.');

            return;
        }

        $dump = Yaml::dump($ignores);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filename, $dump);

        $this->getOutput()->debug('Created ignore file at '.$filename);
    }

    public function stepCount()
    {
        return 1;
    }
}
