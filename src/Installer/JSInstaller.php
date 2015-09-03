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

use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

/**
 * Javascript installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class JSInstaller extends AbstractInstaller
{
    /**
     * @var Execute
     */
    private $execute;

    public function __construct(Execute $execute)
    {
        $this->execute = $execute;
    }

    public function install()
    {
        $this->getOutput()->step('Install NPM packages');
        $process = new Process('npm install -g jshint csslint shifter@0.4.6');
        $process->setTimeout(null);

        $this->execute->mustRun($process);
    }

    public function stepCount()
    {
        return 1;
    }
}
