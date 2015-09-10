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
 * Throw when a process has PHP error messages.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePhpException extends \RuntimeException
{
    public function __construct(Process $process)
    {
        $error = sprintf('PHP error message was detected when running this command:'.PHP_EOL.'  %s'.PHP_EOL.
            'Moodle scripts should run without any PHP errors.',
            $process->getCommandLine()
        );

        if (!$process->isOutputDisabled()) {
            $error .= sprintf("\n\nError Output\n============\n%s", $process->getErrorOutput());
        }

        parent::__construct($error);
    }
}
