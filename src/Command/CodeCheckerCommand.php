<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Command;

use Moodlerooms\MoodlePluginCI\Bridge\CodeSnifferCLI;
use Moodlerooms\MoodlePluginCI\StandardResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run Moodle Code Checker on a plugin.
 */
class CodeCheckerCommand extends AbstractPluginCommand
{
    /**
     * The coding standard to use.
     *
     * @var string
     */
    protected $standard;

    /**
     * Used to find the files to process.
     *
     * @var Finder
     */
    protected $finder;

    protected function configure()
    {
        parent::configure();

        $this->setName('codechecker')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_REQUIRED, 'The name or path of the coding standard to use', 'moodle');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // Resolve the coding standard.
        $resolver       = new StandardResolver();
        $this->standard = $input->getOption('standard');
        if ($resolver->hasStandard($this->standard)) {
            $this->standard = $resolver->resolve($this->standard);
        }

        $this->finder = Finder::create()->name('*.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Moodle Code Checker on %s');

        $files = $this->plugin->getFiles($this->finder);
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        // Must define this before the sniffer due to odd code inclusion resulting in sniffer being included twice.
        $cli = new CodeSnifferCLI([
            'reports'      => ['full' => null],
            'colors'       => $output->isDecorated(),
            'encoding'     => 'utf-8',
            'showProgress' => true,
            'reportWidth'  => 120,
        ]);

        \PHP_CodeSniffer::setConfigData('testVersion', PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, true);

        $sniffer = new \PHP_CodeSniffer();
        $sniffer->setCli($cli);
        $sniffer->process($files, $this->standard);
        $results = $sniffer->reporting->printReport('full', false, $sniffer->cli->getCommandLineValues(), null, 120);

        return $results['errors'] > 0 ? 1 : 0;
    }
}
