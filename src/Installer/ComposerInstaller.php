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
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

/**
 * Composer installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ComposerInstaller extends AbstractInstaller
{
    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @var Execute
     */
    private $execute;

    public function __construct(Moodle $moodle, Execute $execute)
    {
        $this->moodle  = $moodle;
        $this->execute = $execute;
    }

    public function install()
    {
        $this->output->step('Composer install');

        $process = new Process('composer install -n', $this->moodle->directory);
        $process->setTimeout(null);

        $this->execute->mustRun($process);
    }

    public function stepCount()
    {
        return 1;
    }
}
