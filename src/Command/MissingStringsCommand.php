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
}
