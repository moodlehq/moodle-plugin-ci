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
 * Runs all the testing commands in parallel.
 */
class ParallelCommand extends AbstractMoodleCommand
{
    /**
     * This is an array of arrays of processes, so that we can run them in parallel.
     *
     * @var Process[][]
     */
    public array $processes = [];

    protected function configure(): void
    {
        parent::configure();

        $this->setName('parallel')
            ->setDescription('Run all of the tests and analysis against a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->processes = $this->processes ?: $this->initializeProcesses();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'All checks in parallel on %s (output will show below)');

        $this->runProcesses($output);

        return $this->reportOnProcesses($input, $output);
    }

    /**
     * @return Process[][]
     */
    public function initializeProcesses(): array
    {
        $bin    = ['php', $_SERVER['PHP_SELF'] ?? 'moodle-plugin-ci'];
        $plugin = $this->plugin->directory;
        $moodle = $this->moodle->directory;

        // Note that we cannot run them 100% in parallel, because some of them install and remove
        // code from the moodle checkout, and that may cause problems to other processes. Hence, we
        // have them grouped into parallel-safe groups.
        return [
            [
                // The 'savepoints' command installs and removes local/plugin/check_upgrade_savepoints.php.
                'savepoints'  => new Process(array_merge($bin, ['savepoints', '--ansi', $plugin])),
                // The 'phpdoc' command installs and removes local/moodlecheck.
                'phpdoc'      => new Process(array_merge($bin, ['phpdoc', '--ansi', $plugin])),
            ],
            [
                'phplint'     => new Process(array_merge($bin, ['phplint', '--ansi', $plugin])),
                'phpmd'       => new Process(array_merge($bin, ['phpmd', '--ansi', '-m', $moodle, $plugin])),
                'codechecker' => new Process(array_merge($bin, ['codechecker', '--ansi', $plugin])),
                'validate'    => new Process(array_merge($bin, ['validate', '--ansi', '-m', $moodle, $plugin])),
                'mustache'    => new Process(array_merge($bin, ['mustache', '--ansi', '-m', $moodle, $plugin])),
                'grunt'       => new Process(array_merge($bin, ['grunt', '--ansi', '-m', $moodle, $plugin])),
                'phpunit'     => new Process(array_merge($bin, ['phpunit', '--ansi', '-m', $moodle, $plugin])),
                'behat'       => new Process(array_merge($bin, ['behat', '--ansi', '-m', $moodle, $plugin])),
            ],
        ];
    }

    /**
     * Run the processes in parallel.
     *
     * @param OutputInterface $output
     */
    private function runProcesses(OutputInterface $output): void
    {
        $progress = new ProgressIndicator($output);
        $progress->start('Starting...');

        // Start all the processes, in groups of parallel-safe processes.
        foreach ($this->processes as $processGroup) {
            foreach ($processGroup as $name => $process) {
                $process->start();
                $progress->advance();
            }
            // Wait until the group is done before starting with the next group.
            foreach ($processGroup as $name => $process) {
                $progress->setMessage(sprintf('Waiting for moodle-plugin-ci %s...', $name));
                while ($process->isRunning()) {
                    $progress->advance();
                }
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
    private function reportOnProcesses(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $result = 0;

        // Report the output of all the processes, in groups of parallel-safe processes.
        foreach ($this->processes as $processGroup) {
            foreach ($processGroup as $name => $process) {
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
        }

        return $result;
    }
}
