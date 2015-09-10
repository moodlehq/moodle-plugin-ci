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

namespace Moodlerooms\MoodlePluginCI\Process;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Runs a process and generates output if necessary.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Execute
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProcessHelper
     */
    private $helper;

    public function __construct(OutputInterface $output, ProcessHelper $helper)
    {
        $this->output = $output;
        $this->helper = $helper;
    }

    /**
     * @param string|array|Process $cmd   An instance of Process or an array of arguments to escape and run or a command to run
     * @param string|null          $error An error message that must be displayed if something went wrong
     *
     * @return Process
     */
    public function run($cmd, $error = null)
    {
        return $this->helper->run($this->output, $cmd, $error);
    }

    /**
     * @param string|array|Process $cmd   An instance of Process or an array of arguments to escape and run or a command to run
     * @param string|null          $error An error message that must be displayed if something went wrong
     *
     * @return Process
     */
    public function mustRun($cmd, $error = null)
    {
        return $this->helper->mustRun($this->output, $cmd, $error);
    }

    /**
     * @param Process[] $processes
     */
    public function runAll($processes)
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
            // If verbose, then do not run in parallel so we get sane debug output.
            array_map([$this, 'run'], $processes);

            return;
        }
        foreach ($processes as $process) {
            $process->start();
        }
        foreach ($processes as $process) {
            $process->wait();
        }
    }

    /**
     * @param Process[] $processes
     */
    public function mustRunAll($processes)
    {
        $this->runAll($processes);

        foreach ($processes as $process) {
            if ($process instanceof MoodleProcess) {
                $process->checkOutputForProblems();
            }
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }

    /**
     * Run a command and send output, unaltered, immediately.
     *
     * @param string         $commandline The command line to run
     * @param string|null    $cwd         The working directory or null to use the working dir of the current PHP process
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     *
     * @return Process
     */
    public function passThrough($commandline, $cwd = null, $timeout = null)
    {
        return $this->passThroughProcess(new Process($commandline, $cwd, null, null, $timeout));
    }

    /**
     * Run a process and send output, unaltered, immediately.
     *
     * @param Process $process
     *
     * @return Process
     */
    public function passThroughProcess(Process $process)
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
            $this->output->writeln(sprintf('<bg=blue;fg=white;> RUN </> <fg=blue>%s</>', $process->getCommandLine()));
        }
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process;
    }
}
