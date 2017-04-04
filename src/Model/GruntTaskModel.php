<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Model;

class GruntTaskModel
{
    /**
     * @var string
     */
    public $taskName;

    /**
     * @var string
     */
    public $workingDirectory;

    /**
     * @var string
     */
    public $buildDirectory;

    /**
     * @param string $taskName
     * @param string $workingDirectory
     * @param string $buildDirectory
     */
    public function __construct($taskName, $workingDirectory, $buildDirectory = '')
    {
        $this->taskName         = $taskName;
        $this->workingDirectory = $workingDirectory;
        $this->buildDirectory   = $buildDirectory;
    }
}
