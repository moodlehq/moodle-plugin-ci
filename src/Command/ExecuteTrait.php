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

namespace Moodlerooms\MoodlePluginCI\Command;

use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute trait.
 */
trait ExecuteTrait
{
    /**
     * @var Execute
     */
    public $execute;

    /**
     * Initialize the execute property if necessary.
     *
     * @param OutputInterface $output
     * @param ProcessHelper   $helper
     */
    protected function initializeExecute(OutputInterface $output, ProcessHelper $helper)
    {
        $this->execute = $this->execute ?: new Execute($output, $helper);
    }
}
