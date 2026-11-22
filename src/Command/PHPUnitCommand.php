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

/**
 * Run PHPUnit tests.
 */
class PHPUnitCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('phpunit')
            ->setDescription('Run PHPUnit on a plugin')
            ->addOption(
                'configuration',
                'c',
                InputOption::VALUE_REQUIRED,
                'PHPUnit configuration XML file (relative to plugin directory)'
            )
            ->addOption(
                'testsuite',
                null,
                InputOption::VALUE_REQUIRED,
                'PHPUnit testsuite option to use (must exist in the configuration file being used)'
            )
            ->addOption('filter', null, InputOption::VALUE_REQUIRED, 'PHPUnit filter option to use')
            ->addOption('testdox', null, InputOption::VALUE_NONE, 'Enable testdox formatter')
            ->addOption('coverage-text', null, InputOption::VALUE_NONE, 'Generate and print code coverage report in text format')
            ->addOption('coverage-clover', null, InputOption::VALUE_NONE, 'Generate code coverage report in Clover XML format')
            ->addOption('coverage-cobertura', null, InputOption::VALUE_NONE, 'Generate code coverage report in Cobertura XML format')
            ->addOption('log-junit', null, InputOption::VALUE_NONE, 'Log test execution in JUnit XML format (junit.xml in the current working directory)')
            ->addOption('coverage-pcov', null, InputOption::VALUE_NONE, 'Use the pcov extension to calculate code coverage')
            ->addOption('coverage-xdebug', null, InputOption::VALUE_NONE, 'Use the xdebug extension to calculate code coverage')
            ->addOption('fail-on-incomplete', null, InputOption::VALUE_NONE, 'Treat incomplete tests as failures')
            ->addOption('fail-on-risky', null, InputOption::VALUE_NONE, 'Treat risky tests as failures')
            ->addOption('fail-on-skipped', null, InputOption::VALUE_NONE, 'Treat skipped tests as failures')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Treat tests with warnings as failures');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'PHPUnit tests for %s');

        if (!$this->plugin->hasUnitTests()) {
            return $this->outputSkip($output, 'No PHPUnit tests to run, free pass!');
        }

        $binary    = $this->resolveBinary($input, $output);
        $directory = [$this->moodle->directory . '/vendor/bin/phpunit'];
        $colors    = $output->isDecorated() ? ['--colors=always'] : [];
        $options   = $this->resolveOptions($input);
        $cmd       = array_merge(
            $binary,
            $directory,
            $colors,
            $options,
        );
        $process = $this->execute->passThrough($cmd, $this->moodle->directory);

        return $process->isSuccessful() ? 0 : 1;
    }

    /**
     * Resolve options for PHPUnit command.
     *
     * @param InputInterface $input
     *
     * @return string[]
     */
    private function resolveOptions(InputInterface $input): array
    {
        $options = [];

        if ($input->getOption('configuration')) {
            $options[] = [
                '--configuration',
                $this->plugin->directory . '/' . $input->getOption('configuration'),
            ];
        }

        if ($input->getOption('testsuite')) {
            $options[] = [
                '--testsuite',
                $input->getOption('testsuite'),
            ];
        }

        if ($input->getOption('filter')) {
            $options[] = [
                '--filter',
                $input->getOption('filter'),
            ];
        }

        if ($input->getOption('coverage-text')) {
            $options[] = [
                '--coverage-text',
            ];
        }
        if ($input->getOption('coverage-clover')) {
            $options[] = [
                '--coverage-clover',
                getcwd() . '/coverage.xml',
            ];
        }
        if ($input->getOption('coverage-cobertura')) {
            $options[] = [
                '--coverage-cobertura',
                getcwd() . '/cobertura.xml',
            ];
        }
        if ($input->getOption('log-junit')) {
            $options[] = [
                '--log-junit',
                getcwd() . '/junit.xml',
            ];
        }
        if ($input->getOption('verbose')) {
            if ($this->moodle->getBranch() <= 405) { // Only available with PHPUnit <= 9.x (Moodle <= 4.5).
                $options[] = [
                    '--verbose',
                ];
            } else {
                // @codeCoverageIgnoreStart
                if (!defined('PHPUNIT_TEST')) { // Only show deprecation warnings in non-test environments.
                    if (getenv('GITHUB_ACTIONS')) { // Only show deprecation annotations in GitHub Actions.
                        echo '::warning title=Deprecated PHPUnit --verbose option::The --verbose option was removed for ' .
                            ' PHPUnit 10, so it will be ignored by this run (Moodle >= 5.0)' . PHP_EOL;
                    }
                }
                // @codeCoverageIgnoreEnd
            }
        }
        foreach (['fail-on-incomplete', 'fail-on-risky', 'fail-on-skipped', 'fail-on-warning', 'testdox'] as $option) {
            if ($input->getOption($option)) {
                $options[] = [
                    '--' . $option,
                ];
            }
        }

        // Only can set configuration or testsuite here (auto) if the former has not been set via command line option.
        if (!$input->getOption('configuration')) {
            // Use default configuration (phpunit.xml) only if it exists.
            if (is_file($this->plugin->directory . '/phpunit.xml')) {
                $options[] = [
                    '--configuration',
                    $this->plugin->directory . '/phpunit.xml',
                ];
            } else {
                // Fallback to try to use the best testsuite potentially available.
                // Only can set automatic testsuite if it has not been passed via command line option.
                if (!$input->getOption('testsuite')) {
                    $options[] = [
                        '--testsuite',
                        $this->plugin->getComponent() . '_testsuite', // This is our best guess.
                    ];
                }
            }
        }

        return array_merge(...$options); // Merge all options into a single array.
    }

    /**
     * Resolve the PHP binary and settings to use if we are generating code coverage.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string[]
     */
    private function resolveBinary(InputInterface $input, OutputInterface $output): array
    {
        if (!$this->coverageRequested($input)) {
            return [];
        }

        // Depending on the coverage driver, selected return different values.
        switch ($this->resolveCoverageDriver($input, $output)) {
            case 'pcov': // Enable pcov, disable xdebug, just in case.
                return [
                    'php',
                    '-dxdebug.mode=off',
                    '-dpcov.enabled=1',
                    '-dpcov.directory=.',
                ];
            case 'xdebug': // Enable xdebug, disable pcov, just in case.
                return [
                    'php',
                    '-dpcov.enabled=0',
                    '-dxdebug.mode=coverage',
                ];
        }
        // No suitable coverage driver found, disabling all candidates.
        $output->writeln('<error>No suitable driver found, disabling code coverage.</error>');

        return [
            'php',
            '-dpcov.enabled=0',
            '-dxdebug.mode=off',
        ];
    }

    /**
     * Whether any of the coverage report options (the ones needing a coverage driver) has been requested.
     *
     * @param InputInterface $input
     *
     * @return bool
     */
    private function coverageRequested(InputInterface $input): bool
    {
        foreach (['coverage-text', 'coverage-clover', 'coverage-cobertura'] as $option) {
            if ($input->getOption($option)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Given the current environment and options return the code coverage driver to use.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string one of pcov, xdebug or empty string if none is available
     */
    private function resolveCoverageDriver(InputInterface $input, OutputInterface $output): string
    {
        // Let's see if any of the coverage drivers has been forced via command line options.
        if ($input->getOption('coverage-pcov')) {
            // Before accepting it, perform some checks and report.
            if (!extension_loaded('pcov')) {
                $output->writeln('<error>PHP pcov extension not available.</error>');

                return '';
            }

            return 'pcov';
        } elseif ($input->getOption('coverage-xdebug')) {
            // Before accepting it, perform some checks and report.
            if (!extension_loaded('xdebug')) {
                $output->writeln('<error>PHP xdebug extension not available.</error>');

                return '';
            }

            return 'xdebug';
        }

        // Arrived here, let's find the best (pcov => xdebug) available driver.

        if (extension_loaded('pcov')) {
            // If pcov is available, let's use it.
            return 'pcov';
        }

        if (extension_loaded('xdebug')) {
            // If xdebug is available, let's use it.
            return 'xdebug';
        }

        return ''; // No suitable driver available.
    }
}
