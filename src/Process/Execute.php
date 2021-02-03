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

namespace MoodlePluginCI\Process;

use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Runs a process and generates output if necessary.
 */
class Execute
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ProcessHelper
     */
    protected $helper;

    /**
     * Sleep for .2 seconds to avoid race conditions in Moodle scripts when running them in parallel.
     *
     * Example failure, making cache directories.
     *
     * @var int
     */
    public $parallelWaitTime = 200000;

    /**
     * TODO: Add nullable type declaration for params when we switch to php 7.1.
     *
     * @param OutputInterface|null $output
     * @param ProcessHelper|null   $helper
     */
    public function __construct($output = null, $helper = null)
    {
        $this->setOutput($output);
        $this->setHelper($helper);
    }

    /**
     * Output setter.
     * TODO: Add nullable type declaration for param when we switch to php 7.1.
     *
     * @param OutputInterface|null $output
     */
    public function setOutput($output)
    {
        $this->output = $output ?? new NullOutput();
    }

    /**
     * Process helper setter.
     * TODO: Add nullable type declaration for param when we switch to php 7.1.
     *
     * @param ProcessHelper|null $helper
     */
    public function setHelper($helper)
    {
        if (empty($helper)) {
            $helper = new ProcessHelper();
            // Looks like $helper->run is not possible without DebugFormatterHelper.
            $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));
        }
        $this->helper = $helper;
    }

    /**
     * Sets Node.js environment for process.
     *
     * We call 'nvm use' as part of install routine, but we can't export env
     * variable containing path to required version npm binary to make it
     * available in each script run (CI step). To overcome that limitation,
     * we store this path in RUNTIME_NVM_BIN custom variable (that install step
     * dumps into .env file) and use it to substitute Node.js environment
     * in processes we execute.
     *
     * @param Process $process An instance of Process
     *
     * @return Process
     */
    public function setNodeEnv(Process $process)
    {
        if (getenv('RUNTIME_NVM_BIN')) {
            // Concatinate RUNTIME_NVM_BIN with PATH, so the correct version of
            // npm binary is used within process.
            $env = ['PATH' => getenv('RUNTIME_NVM_BIN').':'.getenv('PATH')];
            $process->setEnv($env);
            // Make sure we have all system env vars available too.
            // TODO: Env vars are inherited by default in Symfony 4, next line
            // can be removed after upgrade.
            $process->inheritEnvironmentVariables(true);
        }

        return $process;
    }

    /**
     * @param string|array|Process $cmd   An instance of Process or an array of arguments to escape and run or a command to run
     * @param string|null          $error An error message that must be displayed if something went wrong
     *
     * @return Process
     */
    public function run($cmd, $error = null)
    {
        if (!($cmd instanceof Process)) {
            $cmd = new Process($cmd);
        }
        $this->setNodeEnv($cmd);

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
        if (!($cmd instanceof Process)) {
            $cmd = new Process($cmd);
        }
        $this->setNodeEnv($cmd);

        return $this->helper->mustRun($this->output, $cmd, $error);
    }

    /**
     * @param Process[] $processes
     */
    public function runAll($processes)
    {
        if ($this->output->isVeryVerbose()) {
            // If verbose, then do not run in parallel so we get sane debug output.
            array_map([$this, 'run'], $processes);

            return;
        }
        foreach ($processes as $process) {
            $this->setNodeEnv($process)->start();
            usleep($this->parallelWaitTime);
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
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln(sprintf('<bg=blue;fg=white;> RUN </> <fg=blue>%s</>', $process->getCommandLine()));
        }
        $this->setNodeEnv($process)->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process;
    }
}
