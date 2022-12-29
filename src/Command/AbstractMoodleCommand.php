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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Moodle Command.
 *
 * This command interacts with Moodle and a plugin.
 */
abstract class AbstractMoodleCommand extends AbstractPluginCommand
{
    use MoodleOptionTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->addMoodleOption($this);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeMoodle($input);
    }
}
