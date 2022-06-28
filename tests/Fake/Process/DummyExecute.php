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
    private function getMockProcess($cmd)
    {
        $process = \Mockery::mock('Symfony\Component\Process\Process');
        $process->shouldReceive(
            'setTimeout',
            'run',
            'wait',
            'stop',
            'getExitCode',
            'getExitCodeText',
            'getWorkingDirectory',
            'isOutputDisabled',
            'getErrorOutput'
        );

        $process->shouldReceive('isSuccessful')->andReturn(true);
        $process->shouldReceive('getOutput')->andReturn('');
        $process->shouldReceive('getCommandLine')->andReturn($cmd);

        return $process;
    }

    public function run($cmd, string $error = null): Process
    {
        if ($cmd instanceof Process) {
            // Get the command line from process.
            $cmd = $cmd->getCommandLine();
        }

        return $this->helper->run($this->output, $this->getMockProcess($cmd), $error);
    }

    public function mustRun($cmd, string $error = null): Process
    {
        if ($cmd instanceof Process) {
            // Get the command line from process.
            $cmd = $cmd->getCommandLine();
        }

        return $this->helper->mustRun($this->output, $this->getMockProcess($cmd), $error);
    }

    public function runAll(array $processes): void
    {
        // Do nothing.
    }

    public function mustRunAll(array $processes): void
    {
        // Do nothing.
    }

    public function passThrough(array $commandline, ?string $cwd = null, ?float $timeout = null): Process
    {
        return $this->passThroughProcess($this->getMockProcess($commandline));
    }

    public function passThroughProcess(Process $process): Process
    {
        if ($process instanceof \Mockery\MockInterface) {
            return $process;
        }

        return $this->getMockProcess($process->getCommandLine());
    }
}
