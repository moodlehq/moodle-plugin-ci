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

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * A process that runs a Moodle CLI script.
 */
class MoodleProcess extends Process
{
    /**
     * @param array       $command The command to run and its arguments listed as separate entries
     * @param string|null $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null  $env     The environment variables or null to inherit
     * @param ?int        $timeout The timeout in seconds or null (default) to disable
     */
    public function __construct(array $command, ?string $cwd = null, ?array $env = null, ?int $timeout = null)
    {
        // Let's find our beloved php.
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinary       = $phpBinaryFinder->find();
        // By telling PHP to log errors without having a log file, PHP will write
        // errors to STDERR in a specific format (each line is prefixed with PHP).
        $cmd = array_merge(
            [
                $phpBinary,
                '-d',
                'log_errors=1',
                '-d',
                'error_log=null',
            ],
            $command,
        );

        parent::__construct($cmd, $cwd, $env, null, $timeout);
    }

    public function isSuccessful(): bool
    {
        $isSuccessful = parent::isSuccessful();

        // If successful, ensure there was no error output.
        if ($isSuccessful) {
            try {
                $this->checkOutputForProblems();
            } catch (\Exception $e) {
                $isSuccessful = false;
            }
        }

        return $isSuccessful;
    }

    public function mustRun(?callable $callback = null, array $env = []): static
    {
        parent::mustRun($callback, $env);

        // Check for problems with output.
        $this->checkOutputForProblems();

        return $this;
    }

    /**
     * Checks to make sure that there are no problems with the output.
     *
     * Problems would include PHP errors or Moodle debugging messages.
     */
    public function checkOutputForProblems(): void
    {
        if (!$this->isStarted()) {
            throw new \LogicException(sprintf('Process must be started before calling %s.', __FUNCTION__));
        }
        if ($this->isOutputDisabled()) {
            throw new \LogicException('Output has been disabled, cannot verify if Moodle script ran without problems');
        }
        if ($this->hasPhpErrorMessages($this->getErrorOutput())) {
            throw new MoodlePhpException($this);
        }
        if ($this->hasDebuggingMessages($this->getOutput())) {
            throw new MoodleDebugException($this);
        }
    }

    /**
     * Search output for Moodle debugging messages.
     *
     * @param string $output Output content to check
     *
     * @return bool
     */
    public function hasDebuggingMessages(string $output): bool
    {
        // Looks for something like the following which is a debug message and the start of the debug trace:
        // ++ Some message ++
        // * line
        return preg_match("/\\+\\+ .* \\+\\+\n\\* line/", $output) !== 0;
    }

    /**
     * Search output for PHP errors.
     *
     * @param string $output Output content to check
     *
     * @return bool
     */
    public function hasPhpErrorMessages(string $output): bool
    {
        // Looks for something like the following which is a debug message and the start of the debug trace:
        // PHP Notice:  Undefined index: bat in /path/to/file.php on line 30
        return preg_match('/PHP [\w\s]+:/', $output) !== 0;
    }
}
