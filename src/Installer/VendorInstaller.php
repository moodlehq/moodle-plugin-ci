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
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

/**
 * Vendor installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $this->getOutput()->step('Install dependencies');

        $processes = [];
        if ($this->plugin->hasUnitTests() || $this->plugin->hasBehatFeatures()) {
            $processes[] = new Process('composer install --no-interaction --prefer-dist', $this->moodle->directory, null, null, null);
        }
        $processes[] = new Process('npm install -g jshint csslint shifter@0.4.6', null, null, null, null);

        $this->execute->mustRunAll($processes);
    }

    public function stepCount()
    {
        return 1;
    }
}
