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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Extra Moodle plugins installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ExtraPluginsInstaller extends AbstractPluginInstaller
{
    /**
     * @var string
     */
    private $extraPluginsDir;

    /**
     * @param Moodle $moodle
     * @param string $extraPluginsDir
     */
    public function __construct(Moodle $moodle, $extraPluginsDir)
    {
        parent::__construct($moodle);
        $this->extraPluginsDir = $extraPluginsDir;
    }

    public function install()
    {
        $this->getOutput()->step('Install extra plugins');

        foreach ($this->scanForPlugins() as $plugin) {
            $this->installPluginIntoMoodle($plugin);
        }
    }

    /**
     * @return MoodlePlugin[]
     */
    public function scanForPlugins()
    {
        $plugins = [];

        /** @var SplFileInfo[] $files */
        $files = Finder::create()->directories()->in($this->extraPluginsDir)->depth(0);
        foreach ($files as $file) {
            $plugins[] = new MoodlePlugin($file->getRealPath());
        }

        return $plugins;
    }
}
