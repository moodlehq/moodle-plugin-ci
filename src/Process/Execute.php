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
    protected OutputInterface $output;
    protected ProcessHelper $helper;

    /**
     * Sleep for .2 seconds to avoid race conditions in Moodle scripts when running them in parallel.
     *
     * Example failure, making cache directories.
     */
    public int $parallelWaitTime = 200000;

    /**
     * Constructor.
     *
     * @param OutputInterface|null $output
     * @param ProcessHelper|null   $helper
     */
    public function __construct(?OutputInterface $output = null, ?ProcessHelper $helper = null)
    {
        $this->setOutput($output);
        $this->setHelper($helper);
    }

    /**
     * Output setter.
     *
     * @param OutputInterface|null $output
     */
    public function setOutput(?OutputInterface $output): void
    {
        $this->output = $output ?? new NullOutput();
    }

    /**
     * Process helper setter.
     *
     * @param ProcessHelper|null $helper
     */
    public function setHelper(?ProcessHelper $helper): void
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
    public function setNodeEnv(Process $process): Process
    {
        if (getenv('RUNTIME_NVM_BIN')) {
            // Concatenate RUNTIME_NVM_BIN with PATH, so the correct version of
            // npm binary is used within process.
            $env = ['PATH' => (getenv('RUNTIME_NVM_BIN') ?: '').':'.(getenv('PATH') ?: '')];
            $process->setEnv($env);
        }

        return $process;
    }

    /**
     * @param string[]|Process $cmd   An instance of Process or the command to run and its arguments listed as different entities
     * @param string|null      $error An error message that must be displayed if something went wrong
     *
     * @return Process
     */
    public function run($cmd, ?string $error = null): Process
    {
        if (!($cmd instanceof Process)) {
            $cmd = new Process($cmd);
        }
        $this->setNodeEnv($cmd);

        return $this->helper->run($this->output, $cmd, $error);
    }

    /**
     * @param string[]|Process $cmd   An instance of Process or the command to run and its arguments listed as different entities
     * @param string|null      $error An error message that must be displayed if something went wrong
     *
     * @return Process
     */
    public function mustRun($cmd, ?string $error = null): Process
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
    public function runAll(array $processes): void
    {
        if ($this->output->isVeryVerbose()) {
            // If verbose, then do not run in parallel, so we get sane debug output.
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
    public function mustRunAll(array $processes): void
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
     * @param string[]       $commandline The command to run and its arguments listed as different entities
     * @param string|null    $cwd         The working directory or null to use the working dir of the current PHP process
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     *
     * @return Process
     */
    public function passThrough(array $commandline, ?string $cwd = null, ?float $timeout = null): Process
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
    public function passThroughProcess(Process $process): Process
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
