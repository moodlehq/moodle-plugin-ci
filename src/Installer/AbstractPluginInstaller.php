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

/**
 * Abstract Plugin Installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractPluginInstaller extends AbstractInstaller
{
    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @param Moodle $moodle
     */
    public function __construct(Moodle $moodle)
    {
        $this->moodle = $moodle;
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

    public function stepCount()
    {
        return 1;
    }
}
