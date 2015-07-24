<?php
/**
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
use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
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
        $this->setName('mess')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Path to the plugin that should be installed')
            ->addArgument('rules', InputArgument::REQUIRED, 'Path to PHP Mess Detector rule set')
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate = new Validate();
        $plugin   = realpath($validate->directory($input->getArgument('plugin')));
        $rules    = realpath($validate->filePath($input->getArgument('rules')));
        $moodle   = realpath($validate->directory($input->getOption('moodle')));

        $moodlePlugin = new MoodlePlugin(new Moodle($moodle), $plugin);

        $finder = new Finder();
        $finder->files()->in($plugin)->name('*.php');

        foreach ($moodlePlugin->getThirdPartyLibraryPaths() as $libPath) {
            $finder->notPath($libPath);
        }

        $files = [];
        foreach ($finder as $file) {
            /** @var \SplFileInfo $file */
            $files[] = $file->getRealpath();
        }
        if (empty($files)) {
            $output->writeln('<error>Failed to find any files to process.</error>');

            return 0;
        }

        $renderer = new MessDetectorRenderer($output, $moodle);
        $renderer->setWriter(new StreamWriter(STDOUT));

        $ruleSetFactory = new RuleSetFactory();
        $ruleSetFactory->setMinimumPriority(5);

        $md = new PHPMD();
        $md->processFiles(implode(',', $files), $rules, [$renderer], $ruleSetFactory);

        return 0;
    }
}
