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

use Moodlerooms\MoodlePluginCI\Bridge\CodeSnifferCLI;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Validate;
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
class CodeCheckerCommand extends Command
{
    protected function configure()
    {
        // Install Command sets these in Travis CI.
        $plugin = getenv('PLUGIN_DIR') !== false ? getenv('PLUGIN_DIR') : null;
        $mode   = getenv('PLUGIN_DIR') !== false ? InputArgument::OPTIONAL : InputArgument::REQUIRED;
        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';

        $this->setName('codechecker')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addArgument('plugin', $mode, 'Path to the plugin', $plugin)
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', $moodle);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $pluginDir = realpath($validate->directory($input->getArgument('plugin')));
        $moodleDir = realpath($validate->directory($input->getOption('moodle')));
        $plugin    = new MoodlePlugin($pluginDir);

        $finder = new Finder();
        $finder->notPath('yui/build')->name('*.php')->name('*.js')->notName('*-min.js');

        $files = $plugin->getFiles($finder);

        if (empty($files)) {
            $output->writeln('<error>Failed to find any files to process.</error>');

            return 0;
        }

        $output->writeln(sprintf('<bg=green;fg=white;> RUN </> <fg=blue>Moodle Code Checker on %s</>', $plugin->getComponent()));

        $sniffer = new \PHP_CodeSniffer();
        $sniffer->setCli(new CodeSnifferCLI([
            'reports'      => ['full' => null],
            'colors'       => true,
            'encoding'     => 'utf-8',
            'showProgress' => true,
            'reportWidth'  => 120,
        ]));

        $sniffer->process($files, $moodleDir.'/local/codechecker/moodle');
        $results = $sniffer->reporting->printReport('full', false, $sniffer->cli->getCommandLineValues(), null, 120);

        return $results['errors'] > 0 ? 1 : 0;
    }
}
