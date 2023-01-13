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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Upload code coverage to Coveralls.
 */
class CoverallsUploadCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('coveralls-upload')
            ->setDescription('Upload code coverage to Coveralls')
            ->addOption('coverage-file', null, InputOption::VALUE_REQUIRED, 'Location of the Clover XML file to upload', './coverage.xml');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coverage = realpath($input->getOption('coverage-file'));
        if ($coverage === false) {
            $message = sprintf('Did not find coverage file at <info>%s</info>', $input->getOption('coverage-file'));
            $output->writeln($message);

            return 0;
        }

        $filesystem = new Filesystem();

        // Only if it has not been installed before.
        if (!$filesystem->exists($this->plugin->directory . '/coveralls')) {
            $cmd = [
                'composer',
                'create-project',
                '-n',
                '--no-dev',
                '--prefer-dist',
                'php-coveralls/php-coveralls',
                'coveralls',
                '^2',
            ];
            $process = new Process($cmd, $this->plugin->directory);
            $this->execute->mustRun($process);
        }

        // Yes, this is a hack, but it's the only way to get the coverage file into the right place
        // for the coveralls command to find it.
        $filesystem->copy($coverage, $this->plugin->directory . '/build/logs/clover.xml');

        $cmd = [
            'coveralls/bin/php-coveralls',
            '-v',
        ];
        $process = $this->execute->passThrough($cmd, $this->plugin->directory);

        return $process->isSuccessful() ? 0 : 1;
    }
}
