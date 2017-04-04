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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run PHP Code Beautifier and Fixer on a plugin.
 */
class CodeFixerCommand extends CodeCheckerCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('phpcbf')
            ->setDescription('Run Code Beautifier and Fixer on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Code Beautifier and Fixer on %s');

        $files = $this->plugin->getRelativeFiles($this->finder);
        if (count($files) === 0) {
            return $this->outputSkip($output, 'No files found to process.');
        }

        $colors = $output->isDecorated() ? '--colors' : '';
        $phpCBF = realpath(__DIR__.'/../../vendor/moodlehq/moodle-local_codechecker/pear/PHP/scripts/phpcbf');

        $command = sprintf('%s --encoding=utf-8 %s --standard=%s %s', $phpCBF, $colors, $this->standard, implode(' ', $files));
        $process = $this->execute->passThrough($command, $this->plugin->directory);

        return $process->isSuccessful() ? 0 : 1;
    }
}
