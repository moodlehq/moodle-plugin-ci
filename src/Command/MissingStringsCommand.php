<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Command;

use MoodlePluginCI\MissingStrings\StringValidator;
use MoodlePluginCI\MissingStrings\ValidationConfig;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Find missing language strings in a plugin.
 */
class MissingStringsCommand extends AbstractMoodleCommand
{
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('missingstrings')
            ->setAliases(['missing-strings'])
            ->setDescription('Find missing language strings in a plugin')
            ->addOption(
                'lang',
                'l',
                InputOption::VALUE_REQUIRED,
                'Language to validate against',
                'en'
            )
            ->addOption(
                'strict',
                null,
                InputOption::VALUE_NONE,
                'Strict mode - treat warnings as errors'
            )
            ->addOption(
                'unused',
                'u',
                InputOption::VALUE_NONE,
                'Report unused strings as warnings'
            )
            ->addOption(
                'exclude-patterns',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of string patterns to exclude from validation',
                ''
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'Enable debug mode for detailed error information'
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input  the input interface
     * @param OutputInterface $output the output interface
     *
     * @return int the exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Checking for missing language strings in %s');

        // Create configuration from command line options
        $config = ValidationConfig::fromOptions([
            'lang'             => $input->getOption('lang'),
            'strict'           => $input->getOption('strict'),
            'unused'           => $input->getOption('unused'),
            'exclude-patterns' => $input->getOption('exclude-patterns'),
            'debug'            => $input->getOption('debug'),
        ]);

        // Convert MoodlePlugin to Plugin object
        list($type, $name) = $this->moodle->normalizeComponent($this->plugin->getComponent());
        $plugin            = new Plugin($this->plugin->getComponent(), $type, $name, $this->plugin->directory);

        $validator = new StringValidator(
            $plugin,
            $this->moodle,
            $config
        );

        $result = $validator->validate();

        // Show only errors and warnings
        foreach ($result->getMessages() as $message) {
            $output->writeln($message);
        }

        // Show summary statistics
        $this->outputSummary($output, $result);

        // Show debug information if debug mode is enabled
        if ($config->isDebugEnabled()) {
            $this->outputDebugInformation($output, $result);
        }

        return $result->isValid() ? 0 : 1;
    }

    /**
     * Output summary statistics.
     *
     * @param OutputInterface  $output the output interface
     * @param ValidationResult $result the validation result
     */
    private function outputSummary(OutputInterface $output, ValidationResult $result): void
    {
        $output->writeln('');
        $output->writeln('<comment>Summary:</comment>');

        $summary = $result->getSummary();

        if ($summary['errors'] > 0) {
            $output->writeln(sprintf('- <fg=red>Errors: %d</>', $summary['errors']));
        }

        if ($summary['warnings'] > 0) {
            $output->writeln(sprintf('- <comment>Warnings: %d</comment>', $summary['warnings']));
        }

        if ($summary['total_issues'] === 0) {
            $output->writeln('- <info>No issues found</info>');
        } else {
            $output->writeln(sprintf('- <comment>Total issues: %d</comment>', $summary['total_issues']));
        }

        $output->writeln('');

        if ($summary['is_valid']) {
            $output->writeln('<info>✓ All language strings are valid</info>');
        } else {
            $output->writeln('<error>✗ Language string validation failed</error>');
        }
    }

    /**
     * Output debug performance information.
     *
     * @param OutputInterface  $output the output interface
     * @param ValidationResult $result the validation result
     */
    private function outputDebugInformation(OutputInterface $output, ValidationResult $result): void
    {
        $debugData = $result->getDebugData();

        $output->writeln('');
        $output->writeln('<comment>Debug Performance Information:</comment>');

        // Overall timing
        if ($debugData['processing_time'] > 0) {
            $output->writeln(sprintf('- <info>Total processing time: %.3f seconds</info>', $debugData['processing_time']));
        }

        // Plugin counts
        $totalPlugins = $debugData['plugin_count'] + $debugData['subplugin_count'];
        $output->writeln(sprintf('- <info>Plugins processed: %d</info>', $totalPlugins));
        if ($debugData['subplugin_count'] > 0) {
            $output->writeln(sprintf('  - Main: %d, Subplugins: %d', $debugData['plugin_count'], $debugData['subplugin_count']));
        }

        // Total files count
        if (!empty($debugData['file_counts'])) {
            $totalFiles = $debugData['file_counts']['total_files'] ?? 0;
            if ($totalFiles > 0) {
                $output->writeln(sprintf('- <info>Files processed: %d</info>', $totalFiles));
            }
        }

        // String processing metrics
        if (!empty($debugData['string_counts'])) {
            $output->writeln('- <info>String processing metrics:</info>');
            foreach ($debugData['string_counts'] as $type => $count) {
                if ($count > 0) {
                    /** @var string $type */
                    $displayName = str_replace('_', ' ', $type);
                    $output->writeln(sprintf('  - %s: %d', ucfirst($displayName), $count));
                }
            }
        }

        $output->writeln('');
    }
}
