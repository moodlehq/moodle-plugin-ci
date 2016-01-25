<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Command;

use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Runs all of the testing commands in parallel.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $bin    = realpath(__DIR__.'/../../bin/moodle-plugin-ci');
        $plugin = $this->plugin->directory;
        $moodle = $this->moodle->directory;

        return [
            'phplint'     => new Process(sprintf('%s phplint --ansi %s', $bin, $plugin)),
            'phpcpd'      => new Process(sprintf('%s phpcpd --ansi %s', $bin, $plugin)),
            'phpmd'       => new Process(sprintf('%s phpmd --ansi -m %s %s', $bin, $moodle, $plugin)),
            'codechecker' => new Process(sprintf('%s codechecker --ansi %s', $bin, $plugin)),
            'csslint'     => new Process(sprintf('%s csslint --ansi %s', $bin, $plugin)),
            'shifter'     => new Process(sprintf('%s shifter --ansi %s', $bin, $plugin)),
            'jshint'      => new Process(sprintf('%s jshint --ansi %s', $bin, $plugin)),
            'validate'    => new Process(sprintf('%s validate --ansi -m %s %s', $bin, $moodle, $plugin)),
            'phpunit'     => new Process(sprintf('%s phpunit --ansi -m %s %s', $bin, $moodle, $plugin)),
            'behat'       => new Process(sprintf('%s behat --ansi -m %s %s', $bin, $moodle, $plugin)),
        ];
    }

    /**
     * Run the processes in parallel.
     *
     * @param OutputInterface $output
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
     * @param InputInterface  $input
     * @param OutputInterface $output
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
