<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Fake\Process;

use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

class DummyExecute extends Execute
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        // Do nothing.
    }

    public function run($cmd, $error = null)
    {
        return new DummyProcess('dummy');
    }

    public function mustRun($cmd, $error = null)
    {
        return new DummyProcess('dummy');
    }

    public function runAll($processes)
    {
        // Do nothing.
    }

    public function mustRunAll($processes)
    {
        // Do nothing.
    }

    public function passThrough($commandline, $cwd = null, $timeout = null)
    {
        return $this->passThroughProcess(new DummyProcess($commandline, $cwd, null, null, $timeout));
    }

    public function passThroughProcess(Process $process)
    {
        if ($process instanceof DummyProcess) {
            return $process;
        }

        return new DummyProcess($process->getCommandLine(), $process->getWorkingDirectory(), null, null, $process->getTimeout());
    }
}
