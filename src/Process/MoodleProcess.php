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

use Symfony\Component\Process\Process;

/**
 * A process that runs a Moodle CLI script.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleProcess extends Process
{
    /**
     * @param string         $script  Passed to php binary
     * @param string|null    $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env     The environment variables or null to inherit
     * @param int|float|null $timeout The timeout in seconds or null to disable
     */
    public function __construct($script, $cwd = null, array $env = null, $timeout = null)
    {
        // By telling PHP to log errors without having a log file, PHP will write
        // errors to STDERR in a specific format (each line is prefixed with PHP).
        $commandline = sprintf('php -d log_errors=1 -d error_log=NULL %s', $script);

        parent::__construct($commandline, $cwd, $env, null, $timeout);
    }

    public function isSuccessful()
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

    public function mustRun($callback = null)
    {
        parent::mustRun($callback);

        // Check for problems with output.
        $this->checkOutputForProblems();

        return $this;
    }

    /**
     * Checks to make sure that there are no problems with the output.
     *
     * Problems would include PHP errors or Moodle debugging messages.
     */
    public function checkOutputForProblems()
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
    public function hasDebuggingMessages($output)
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
    public function hasPhpErrorMessages($output)
    {
        // Looks for something like the following which is a debug message and the start of the debug trace:
        // PHP Notice:  Undefined index: bat in /path/to/file.php on line 30
        return preg_match('/PHP [\w\s]+:/s', $output) !== 0;
    }
}
