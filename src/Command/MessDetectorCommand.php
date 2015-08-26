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
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Validate;
use PHPMD\PHPMD;
use PHPMD\RuleSetFactory;
use PHPMD\Writer\StreamWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
class MessDetectorCommand extends Command
{
    protected function configure()
    {
        // Install Command sets these in Travis CI.
        $plugin = getenv('PLUGIN_DIR') !== false ? getenv('PLUGIN_DIR') : null;
        $mode   = getenv('PLUGIN_DIR') !== false ? InputArgument::OPTIONAL : InputArgument::REQUIRED;
        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';
        $rules  = realpath(__DIR__.'/../../res/config/phpmd.xml');

        $this->setName('phpmd')
            ->setDescription('Run PHP Mess Detector on a plugin')
            ->addArgument('plugin', $mode, 'Path to the plugin', $plugin)
            ->addOption('rules', 'r', InputOption::VALUE_OPTIONAL, 'Path to PHP Mess Detector rule set', $rules)
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', $moodle);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $pluginDir = realpath($validate->directory($input->getArgument('plugin')));
        $moodleDir = realpath($validate->directory($input->getOption('moodle')));
        $rules     = realpath($validate->filePath($input->getOption('rules')));
        $plugin    = new MoodlePlugin($pluginDir);

        $finder = new Finder();
        $finder->name('*.php');

        $files = $plugin->getFiles($finder);

        if (empty($files)) {
            $output->writeln('<error>Failed to find any files to process.</error>');

            return 0;
        }

        $output->writeln(sprintf('<bg=green;fg=white;> RUN </> <fg=blue>PHP Mess Detector on %s</>', $plugin->getComponent()));

        $renderer = new MessDetectorRenderer($output, $moodleDir);
        $renderer->setWriter(new StreamWriter(STDOUT));

        $ruleSetFactory = new RuleSetFactory();
        $ruleSetFactory->setMinimumPriority(5);

        $messDetector = new PHPMD();
        $messDetector->processFiles(implode(',', $files), $rules, [$renderer], $ruleSetFactory);

        return 0;
    }
}
