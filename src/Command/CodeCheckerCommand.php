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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Run Moodle CodeSniffer standard on a plugin.
 */
class CodeCheckerCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    /**
     * Path to the temp file where the json report results will be stored.
     */
    protected string $tempFile;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('phpcs')
            ->setAliases(['codechecker'])
            ->setDescription('Run Moodle CodeSniffer standard on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_REQUIRED, 'The name or path of the coding standard to use', 'moodle')
            ->addOption('max-warnings', null, InputOption::VALUE_REQUIRED,
                'Number of warnings to trigger nonzero exit code - default: -1', -1);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
        $this->tempFile = sys_get_temp_dir() . '/moodle-plugin-ci-code-checker-summary-' . time();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Moodle CodeSniffer standard on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $cmd = [
            'php', __DIR__ . '/../../vendor/squizlabs/php_codesniffer/bin/phpcs',
            '--standard=' . ($input->getOption('standard') ?: 'moodle'),
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

        // If we aren't using the max-warnings option, then we can forget about warnings and tell phpcs
        // to ignore them for exit-code purposes (still they will be reported in the output).
        if ($input->getOption('max-warnings') < 0) {
            array_push($cmd, '--runtime-set', 'ignore_warnings_on_exit', '1');
        } else {
            // If we are using the max-warnings option, we need the summary report somewhere to get
            // the total number of errors and warnings from there.
            $cmd[] = '--report-json=' . $this->tempFile;
        }

        // Add the files to process.
        foreach ($files as $file) {
            $cmd[] = $file;
        }

        $process = $this->execute->passThroughProcess(new Process($cmd, $this->plugin->directory, null, null, null));

        // If we aren't using the max-warnings option, process exit code is enough for us.
        if ($input->getOption('max-warnings') < 0) {
            return $process->isSuccessful() ? 0 : 1;
        }

        // Arrived here, we are playing with max-warnings, so we have to decide the exit code
        // based on the existence of errors and the number of warnings compared with the threshold.
        $totalErrors   = 0;
        $totalWarnings = 0;
        $jsonFile      = trim(file_get_contents($this->tempFile));
        if ($json = json_decode($jsonFile, false)) {
            $totalErrors   = (int) $json->totals->errors;
            $totalWarnings = (int) $json->totals->warnings;
        }
        (new Filesystem())->remove($this->tempFile);  // Remove the temporal summary file.

        // With errors or warnings over the max-warnings threshold, fail the command.
        return ($totalErrors > 0 || ($totalWarnings > $input->getOption('max-warnings'))) ? 1 : 0;
    }
}
