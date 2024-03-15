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

namespace MoodlePluginCI\Command;

use MoodlePluginCI\Process\Execute;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute trait.
 */
trait ExecuteTrait
{
    public Execute $execute;

    /**
     * Initialize the "execute" property if necessary.
     *
     * @param OutputInterface $output
     * @param HelperInterface $helper
     */
    protected function initializeExecute(OutputInterface $output, HelperInterface $helper): void
    {
        // The helper must be a ProcessHelper. If it's not, pass null.
        // (note this is needed only because Command->getHelper() in Symfony 6.4 has PHPDoc for the helper
        // as HelperInterface instead of ProcessHelper, which is the actual type,
        // and static analysers (psalm, ...) need to be satisfied).
        if (!$helper instanceof ProcessHelper) {
            $helper = null;
        }

        if (isset($this->execute)) {
            // Define output and process helper.
            $this->execute->setOutput($output);
            $this->execute->setHelper($helper);
        } else {
            $this->execute = new Execute($output, $helper);
        }
    }
}
