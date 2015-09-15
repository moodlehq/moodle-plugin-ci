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

namespace Moodlerooms\MoodlePluginCI\Bridge;

/**
 * Bridge to Code Sniffer CLI.
 *
 * This gets around a problem where the report
 * does not print when you do not use Code Sniffer
 * via its own CLI script.
 *
 * This was inspired by moodle-local_codechecker.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CodeSnifferCLI extends \PHP_CodeSniffer_CLI
{
    private $overrideCommandLineValues = [];

    public function __construct($override = [])
    {
        $this->errorSeverity   = 1;
        $this->warningSeverity = 1;

        $this->overrideCommandLineValues = $override;
    }

    /**
     * This is the override magic, set defaults to how we like.
     *
     * @return array
     */
    public function getCommandLineValues()
    {
        return array_merge(
            $this->getDefaults(),
            $this->overrideCommandLineValues
        );
    }
}
