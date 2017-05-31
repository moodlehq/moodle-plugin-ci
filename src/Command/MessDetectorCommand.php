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

use Moodlerooms\MoodlePluginCI\Bridge\MessDetectorRenderer;
use PHPMD\PHPMD;
use PHPMD\RuleSetFactory;
use PHPMD\Writer\StreamWriter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run Moodle Code Checker on a plugin.
 */
class MessDetectorCommand extends AbstractMoodleCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('phpmd')
            ->setDescription('Run PHP Mess Detector on a plugin')
            ->addOption('rules', 'r', InputOption::VALUE_REQUIRED, 'Path to PHP Mess Detector rule set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'PHP Mess Detector on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }
        $rules = $input->getOption('rules') ?: __DIR__.'/../../res/config/phpmd.xml';

        $renderer = new MessDetectorRenderer($output, $this->moodle->directory);
        $renderer->setWriter(new StreamWriter(STDOUT));

        $ruleSetFactory = new RuleSetFactory();
        $ruleSetFactory->setMinimumPriority(5);

        $messDetector = new PHPMD();
        $messDetector->processFiles(implode(',', $files), $rules, [$renderer], $ruleSetFactory);

        return 0;
    }
}
