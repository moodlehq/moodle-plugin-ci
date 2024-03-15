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

use Mockery\MockInterface;
use MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

class DummyExecute extends Execute
{
    public string $returnOutput = '';
    public array $allCmds       = [];
    public string $lastCmd      = ''; // We need this for assertions against the command run.

    private function getMockProcess(string $cmd): Process&MockInterface
    {
        /** @var Process&MockInterface $process */
        $process = \Mockery::mock(Process::class);

        // We only need the following right now. Add more as needed.
        $process->shouldReceive('run')->andReturn(0);
        $process->shouldReceive('isSuccessful')->andReturn(true);
        $process->shouldReceive('getOutput')->andReturn($this->returnOutput);
        $process->shouldReceive('getCommandLine')->andReturn($cmd);

        return $process;
    }

    public function run($cmd, ?string $error = null): Process
    {
        return $this->helper->run($this->output, $this->getMockProcess($this->getCommandLine($cmd)), $error);
    }

    public function mustRun($cmd, ?string $error = null): Process
    {
        return $this->helper->mustRun($this->output, $this->getMockProcess($this->getCommandLine($cmd)), $error);
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
        return $this->getMockProcess($this->getCommandLine($commandline));
    }

    public function passThroughProcess(Process $process): Process
    {
        return $this->getMockProcess($this->getCommandLine($process));
    }

    /**
     * Helper function to get the command line from a Process object or array.
     *
     * @param Process|array $cmd the command to run and its arguments listed as different entities
     *
     * @return string the command to run in a shell
     */
    private function getCommandLine(Process|array $cmd): string
    {
        if (is_array($cmd)) {
            $this->lastCmd = (new Process($cmd))->getCommandLine();
        } else {
            $this->lastCmd = $cmd->getCommandLine();
        }

        $this->allCmds[] = $this->lastCmd;

        return $this->lastCmd;
    }
}
