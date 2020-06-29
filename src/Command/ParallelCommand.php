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

use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Runs all of the testing commands in parallel.
 */
class ParallelCommand extends AbstractMoodleCommand
{
    /**
     * @var Process[]
     */
    public $processes;

    protected function configure()
    {
        parent::configure();

        $this->setName('parallel')
            ->setDescription('Run all of the tests and analysis against a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->processes = $this->processes ?: $this->initializeProcesses();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runProcesses($output);

        return $this->reportOnProcesses($input, $output);
    }

    /**
     * @return Process[]
     */
    public function initializeProcesses()
    {
        $bin    = 'php '.$_SERVER['PHP_SELF'];
        $plugin = $this->plugin->directory;
        $moodle = $this->moodle->directory;

        return [
            'phplint'     => new Process(sprintf('%s phplint --ansi %s', $bin, $plugin)),
            'phpcpd'      => new Process(sprintf('%s phpcpd --ansi %s', $bin, $plugin)),
            'phpmd'       => new Process(sprintf('%s phpmd --ansi -m %s %s', $bin, $moodle, $plugin)),
            'codechecker' => new Process(sprintf('%s codechecker --ansi %s', $bin, $plugin)),
            'phpdoc'      => new Process(sprintf('%s phpdoc --ansi %s', $bin, $plugin)),
            'validate'    => new Process(sprintf('%s validate --ansi -m %s %s', $bin, $moodle, $plugin)),
            'savepoints'  => new Process(sprintf('%s savepoints --ansi %s', $bin, $plugin)),
            'mustache'    => new Process(sprintf('%s mustache --ansi -m %s %s', $bin, $moodle, $plugin)),
            'grunt'       => new Process(sprintf('%s grunt --ansi -m %s %s', $bin, $moodle, $plugin)),
            'phpunit'     => new Process(sprintf('%s phpunit --ansi -m %s %s', $bin, $moodle, $plugin)),
            'behat'       => new Process(sprintf('%s behat --ansi -m %s %s', $bin, $moodle, $plugin)),
        ];
    }

    /**
     * Run the processes in parallel.
     */
    private function runProcesses(OutputInterface $output)
    {
        $progress = new ProgressIndicator($output);
        $progress->start('Starting...');

        // Start all of the processes.
        foreach ($this->processes as $process) {
            $process->start();
            $progress->advance();
        }

        // Wait for each to be done.
        foreach ($this->processes as $name => $process) {
            $progress->setMessage(sprintf('Waiting for moodle-plugin-ci %s...', $name));
            while ($process->isRunning()) {
                $progress->advance();
            }
        }
        $progress->finish('Done!');
    }

    /**
     * Report on the completed processes.
     *
     * @return int
     */
    private function reportOnProcesses(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $result = 0;
        foreach ($this->processes as $name => $process) {
            $style->newLine();

            echo $process->getOutput();

            if (!$process->isSuccessful()) {
                $result = 1;
                $style->error(sprintf('Command %s failed', $name));
            }
            $errorOutput = $process->getErrorOutput();
            if (!empty($errorOutput)) {
                $style->error(sprintf('Error output for %s command', $name));
                $style->writeln($errorOutput);
            }
        }

        return $result;
    }
}
