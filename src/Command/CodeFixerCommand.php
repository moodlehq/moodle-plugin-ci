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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Run PHP Code Beautifier and Fixer on a plugin.
 */
class CodeFixerCommand extends CodeCheckerCommand
{
    protected function configure(): void
    {
        AbstractPluginCommand::configure();

        $this->setName('phpcbf')
            ->setDescription('Run Code Beautifier and Fixer on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_REQUIRED, 'The name or path of the coding standard to use', 'moodle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Code Beautifier and Fixer on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $cmd = [
            'php', __DIR__.'/../../vendor/squizlabs/php_codesniffer/bin/phpcbf',
            '--standard='.($input->getOption('standard') ?: 'moodle'),
            '--extensions=php',
            '-p',
            '-w',
            '-s',
            '--no-cache',
            $output->isDecorated() ? '--colors' : '--no-colors',
            '--report-full',
            '--report-width=132',
            '--encoding=utf-8',
        ];

        // Add the files to process.
        foreach ($files as $file) {
            $cmd[] = $file;
        }

        $this->execute->passThroughProcess(new Process($cmd, $this->plugin->directory, null, null, null));

        return 0;
    }
}
