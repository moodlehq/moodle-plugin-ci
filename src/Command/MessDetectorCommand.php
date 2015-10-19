<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Command;

use Moodlerooms\MoodlePluginCI\Bridge\MessDetectorRenderer;
use Moodlerooms\MoodlePluginCI\Validate;
use PHPMD\PHPMD;
use PHPMD\RuleSetFactory;
use PHPMD\Writer\StreamWriter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run Moodle Code Checker on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MessDetectorCommand extends AbstractMoodleCommand
{
    protected function configure()
    {
        parent::configure();

        $rules = realpath(__DIR__.'/../../res/config/phpmd.xml');

        $this->setName('phpmd')
            ->setDescription('Run PHP Mess Detector on a plugin')
            ->addOption('rules', 'r', InputOption::VALUE_REQUIRED, 'Path to PHP Mess Detector rule set', $rules);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'PHP Mess Detector on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }
        $validate = new Validate();
        $rules    = realpath($validate->filePath($input->getOption('rules')));

        $renderer = new MessDetectorRenderer($output, $this->moodle->directory);
        $renderer->setWriter(new StreamWriter(STDOUT));

        $ruleSetFactory = new RuleSetFactory();
        $ruleSetFactory->setMinimumPriority(5);

        $messDetector = new PHPMD();
        $messDetector->processFiles(implode(',', $files), $rules, [$renderer], $ruleSetFactory);
    }
}
