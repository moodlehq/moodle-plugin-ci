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
            ->addOption(
                'standard',
                's',
                InputOption::VALUE_REQUIRED,
                'The name or path of the coding standard to use',
                'moodle'
            )->addOption(
                'exclude',
                'x',
                InputOption::VALUE_REQUIRED,
                'Comma separated list of sniff codes to exclude from checking',
                ''
            )->addOption(
                'max-warnings',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of warnings to trigger nonzero exit code - default: -1',
                -1
            )->addOption(
                'test-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Version or range of version to test with PHPCompatibility',
                0
            )->addOption(
                'todo-comment-regex',
                null,
                InputOption::VALUE_REQUIRED,
                'Regex to use to match TODO/@todo comments',
                ''
            )->addOption(
                'license-regex',
                null,
                InputOption::VALUE_REQUIRED,
                'Regex to use to match @license tags',
                ''
            );
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

        $filesystem  = new Filesystem();
        $pathToPHPCS = __DIR__ . '/../../vendor/squizlabs/php_codesniffer/bin/phpcs';
        $pathToConf  = __DIR__ . '/../../vendor/squizlabs/php_codesniffer/CodeSniffer.conf';
        $basicCMD    = ['php', $pathToPHPCS];
        // If we are running phpcs within a PHAR, the command is different, and we need also to copy the .conf file.
        // @codeCoverageIgnoreStart
        // (This is not executed when running tests, only when within a PHAR)
        if (\Phar::running() !== '') {
            // Invoke phpcs from the PHAR (via include, own params after --).
            $basicCMD = ['php', '-r', 'include "' . $pathToPHPCS . '";', '--'];
            // Copy the .conf file to the directory where the PHAR is running. That way phpcs will find it.
            $targetPathToConf = dirname(\Phar::running(false)) . '/CodeSniffer.conf';
            $filesystem->copy($pathToConf, $targetPathToConf, true);
        }
        // @codeCoverageIgnoreEnd

        $exclude = $input->getOption('exclude');

        $cmd     = array_merge($basicCMD, [
            '--standard=' . ($input->getOption('standard') ?: 'moodle'),
            '--extensions=php',
            '-p',
            '-w',
            '-s',
            '--no-cache',
            empty($exclude) ? '' : ('--exclude=' . $exclude),
            $output->isDecorated() ? '--colors' : '--no-colors',
            '--report-full',
            '--report-width=132',
            '--encoding=utf-8',
        ]);

        // If we aren't using the max-warnings option, then we can forget about warnings and tell phpcs
        // to ignore them for exit-code purposes (still they will be reported in the output).
        if ($input->getOption('max-warnings') < 0) {
            array_push($cmd, '--runtime-set', 'ignore_warnings_on_exit', '1');
        } else {
            // If we are using the max-warnings option, we need the summary report somewhere to get
            // the total number of errors and warnings from there.
            $cmd[] = '--report-json=' . $this->tempFile;
        }

        // Show PHPCompatibility backward-compatibility errors for a version or version range.
        $testVersion = $input->getOption('test-version');
        if (!empty($testVersion)) {
            array_push($cmd, '--runtime-set', 'testVersion', $testVersion);
        }

        // Set the regex to use to match TODO/@todo comments.
        // Note that the option defaults to an empty string,
        // meaning that no checks will be performed. Configure it
        // to a valid regex ('MDL-[0-9]+', 'https:', ...) to enable the checks.
        $todoCommentRegex = $input->getOption('todo-comment-regex');
        array_push($cmd, '--runtime-set', 'moodleTodoCommentRegex', $todoCommentRegex);

        // Set the regex to use to match @license tags.
        // Note that the option defaults to an empty string,
        // meaning that no checks will be performed. Configure it
        // to a valid regex ('GPL-3.0', 'https:', ...) to enable the checks.
        $licenseRegex = $input->getOption('license-regex');
        array_push($cmd, '--runtime-set', 'moodleLicenseRegex', $licenseRegex);

        // Add the files to process.
        foreach ($files as $file) {
            $cmd[] = $file;
        }

        $process = $this->execute->passThroughProcess(new Process($cmd, $this->plugin->directory, null, null, null));

        // If we are running phpcs within a PHAR, we need to remove the previously copied conf file.
        // @codeCoverageIgnoreStart
        // (This is not executed when running tests, only when within a PHAR)
        if (\Phar::running() !== '') {
            $targetPathToConf = dirname(\Phar::running(false)) . '/CodeSniffer.conf';
            $filesystem->remove($targetPathToConf);
        }
        // @codeCoverageIgnoreEnd

        // If we aren't using the max-warnings option, process exit code is enough for us.
        if ($input->getOption('max-warnings') < 0) {
            return $process->isSuccessful() ? 0 : 1;
        }

        // Arrived here, we are playing with max-warnings, so we have to decide the exit code
        // based on the existence of errors and the number of warnings compared with the threshold.

        // Verify that the summary file was created. If not, something went wrong with the execution.
        if (!file_exists($this->tempFile)) {
            return 1;
        }

        // Let's inspect the summary file to get the total number of errors and warnings.
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
