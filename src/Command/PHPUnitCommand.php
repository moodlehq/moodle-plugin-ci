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

    protected function configure()
    {
        parent::configure();

        $this->setName('phpunit')
            ->setDescription('Run PHPUnit on a plugin')
            ->addOption('coverage-text', null, InputOption::VALUE_NONE, 'Generate and print code coverage report in text format')
            ->addOption('coverage-clover', null, InputOption::VALUE_NONE, 'Generate code coverage report in Clover XML format')
            ->addOption('coverage-pcov', null, InputOption::VALUE_NONE, 'Use the pcov extension to calculate code coverage')
            ->addOption('coverage-xdebug', null, InputOption::VALUE_NONE, 'Use the xdebug extension to calculate code coverage')
            ->addOption('coverage-phpdbg', null, InputOption::VALUE_NONE, 'Use the phpdbg binary to calculate code coverage')
            ->addOption('fail-on-incomplete', null, InputOption::VALUE_NONE, 'Treat incomplete tests as failures')
            ->addOption('fail-on-risky', null, InputOption::VALUE_NONE, 'Treat risky tests as failures')
            ->addOption('fail-on-skipped', null, InputOption::VALUE_NONE, 'Treat skipped tests as failures')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Treat tests with warnings as failures');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
        if ($this->supportsCoverage() && $input->getOption('coverage-text')) {
            $options[] = [
                '--coverage-text',
            ];
        }
        if ($this->supportsCoverage() && $input->getOption('coverage-clover')) {
            $options[] = [
                '--coverage-clover',
                getcwd() . '/coverage.xml',
            ];
        }
        if ($input->getOption('verbose')) {
            $options[] = [
                '--verbose',
            ];
        }
        foreach (['fail-on-incomplete', 'fail-on-risky', 'fail-on-skipped', 'fail-on-warning'] as $option) {
            if ($input->getOption($option)) {
                $options[] = [
                    '--' . $option,
                ];
            }
        }
        if (is_file($this->plugin->directory . '/phpunit.xml')) {
            $options[] = [
                '--configuration',
                $this->plugin->directory,
            ];
        } else {
            $options[] = [
                '--testsuite',
                $this->plugin->getComponent(),
            ];
        }

        return array_merge(...$options); // Merge all options into a single array.
    }

    /**
     * Use phpdbg if we are generating code coverage.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string[]
     */
    private function resolveBinary(InputInterface $input, OutputInterface $output): array
    {
        if (!$this->supportsCoverage()) {
            return [];
        }
        if (!$input->getOption('coverage-text') && !$input->getOption('coverage-clover')) {
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
            case 'phpdbg':
                return [
                    'phpdbg',
                    '-d',
                    'memory_limit=-1',
                    '-qrr',
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
     * Only allow coverage when using PHP7.
     *
     * @return bool
     */
    private function supportsCoverage()
    {
        return version_compare(PHP_VERSION, '7.0.0', '>=');
    }

    /**
     * Given the current environment and options return the code coverage driver to use.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string one of pcov, xdebug, phpdbg
     */
    private function resolveCoverageDriver(InputInterface $input, OutputInterface $output)
    {
        // Let's see if any of the coverage drivers has been forced via command line options.
        if ($input->getOption('coverage-pcov')) {
            // Before accepting it, perform some checks and report.
            if (!extension_loaded('pcov')) {
                $output->writeln('<error>PHP pcov extension not available.</error>');

                return '';
            }
            if ($this->moodle->getBranch() < 310) {
                $output->writeln('<error>PHP pcov coverage only can be used with Moodle 3.10 and up.</error>');

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
        } elseif ($input->getOption('coverage-phpdbg')) {
            return 'phpdbg';
        }

        // Arrived here, let's find the best (pcov => xdebug => phpdbg) available driver.

        if (extension_loaded('pcov') && $this->moodle->getBranch() >= 310) {
            // If pcov is available and we are using Moodle 3.10 (PHPUnit 8.5) and up, let's use it.
            return 'pcov';
        }

        if (extension_loaded('xdebug')) {
            // If xdebug is available, let's use it.
            return 'xdebug';
        }

        return 'phpdbg'; // Fallback to phpdbg (bundled with php 7.0 and up) if none of the above are available.
    }
}
