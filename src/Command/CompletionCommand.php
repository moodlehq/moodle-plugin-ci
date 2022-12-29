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

use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionInterface;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand as BaseCompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;

/**
 * Customized shell completion.
 */
class CompletionCommand extends BaseCompletionCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setHidden(true);
    }

    protected function configureCompletion(CompletionHandler $handler): void
    {
        // Completion for plugin argument/option to file system paths.
        $handler->addHandler(new ShellPathCompletion(
            CompletionInterface::ALL_COMMANDS,
            'plugin',
            CompletionInterface::ALL_TYPES
        ));

        // Completion for moodle option to file system paths.
        $handler->addHandler(new ShellPathCompletion(
            CompletionInterface::ALL_COMMANDS,
            'moodle',
            CompletionInterface::TYPE_OPTION
        ));
    }
}
