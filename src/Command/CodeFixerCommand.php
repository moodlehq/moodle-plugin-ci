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
use Symfony\Component\Process\ProcessBuilder;

/**
 * Run PHP Code Beautifier and Fixer on a plugin.
 */
class CodeFixerCommand extends CodeCheckerCommand
{
    protected function configure()
    {
        AbstractPluginCommand::configure();

        $this->setName('phpcbf')
            ->setDescription('Run Code Beautifier and Fixer on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_REQUIRED, 'The name or path of the coding standard to use', 'moodle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Code Beautifier and Fixer on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $builder = ProcessBuilder::create()
            ->setPrefix('php')
            ->add(__DIR__.'/../../vendor/squizlabs/php_codesniffer/bin/phpcbf')
            ->add('--standard='.($input->getOption('standard') ?: 'moodle'))
            ->add('--extensions=php')
            ->add('-p')
            ->add('-w')
            ->add('-s')
            ->add('--no-cache')
            ->add($output->isDecorated() ? '--colors' : '--no-colors')
            ->add('--report-full')
            ->add('--report-width=132')
            ->add('--encoding=utf-8')
            ->setWorkingDirectory($this->plugin->directory)
            ->setTimeout(null);

        // Add the files to process.
        foreach ($files as $file) {
            $builder->add($file);
        }

        $this->execute->passThroughProcess($builder->getProcess());

        return 0;
    }
}
