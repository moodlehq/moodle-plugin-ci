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

namespace MoodlePluginCI\Tests\Fake\Process;

use MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

class DummyExecute extends Execute
{
    public function run($cmd, $error = null)
    {
        if ($cmd instanceof Process) {
            // Get the command line from process.
            $cmd = $cmd->getCommandLine();
        }
        $cmd = new DummyProcess($cmd);

        return $this->helper->run($this->output, $cmd, $error);
    }

    public function mustRun($cmd, $error = null)
    {
        if ($cmd instanceof Process) {
            // Get the command line from process.
            $cmd = $cmd->getCommandLine();
        }
        $cmd = new DummyProcess($cmd);

        return $this->helper->mustRun($this->output, $cmd, $error);
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
