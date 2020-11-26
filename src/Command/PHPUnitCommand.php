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
            ->addOption('coverage-clover', null, InputOption::VALUE_NONE, 'Generate code coverage report in Clover XML format');
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

        $colors  = $output->isDecorated() ? '--colors="always"' : '';
        $binary  = $this->resolveBinary($input);
        $options = $this->resolveOptions($input);
        $process = $this->execute->passThrough(
            sprintf('%s%s/vendor/bin/phpunit %s %s', $binary, $this->moodle->directory, $colors, $options),
            $this->moodle->directory
        );

        return $process->isSuccessful() ? 0 : 1;
    }

    /**
     * Resolve options for PHPUnit command.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function resolveOptions(InputInterface $input)
    {
        $options = [];
        if ($this->supportsCoverage() && $input->getOption('coverage-text')) {
            $options[] = '--coverage-text';
        }
        if ($this->supportsCoverage() && $input->getOption('coverage-clover')) {
            $options[] = sprintf('--coverage-clover %s/coverage.xml', getcwd());
        }
        if (is_file($this->plugin->directory.'/phpunit.xml')) {
            $options[] = sprintf('--configuration %s', $this->plugin->directory);
        } else {
            $options[] = sprintf('--testsuite %s_testsuite', $this->plugin->getComponent());
        }

        return implode(' ', $options);
    }

    /**
     * Use phpdbg if we are generating code coverage.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function resolveBinary(InputInterface $input)
    {
        if (!$this->supportsCoverage()) {
            return '';
        }
        if (!$input->getOption('coverage-text') && !$input->getOption('coverage-clover')) {
            return '';
        }

        return 'phpdbg -d memory_limit=-1 -qrr ';
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
}
