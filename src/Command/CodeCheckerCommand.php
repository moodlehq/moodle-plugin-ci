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

use Moodlerooms\MoodlePluginCI\Bridge\CodeSnifferCLI;
use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
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
        $this->setName('codechecker')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Path to the plugin that should be installed')
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate = new Validate();
        $plugin   = realpath($validate->directory($input->getArgument('plugin')));
        $moodle   = realpath($validate->directory($input->getOption('moodle')));

        $moodlePlugin = new MoodlePlugin(new Moodle($moodle), $plugin);

        $finder = new Finder();
        $finder->files()->in($plugin)->notPath('yui/build')->name('*.php')->name('*.js');

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

        $cs = new \PHP_CodeSniffer();
        $cs->setCli(new CodeSnifferCLI([
            'reports'      => ['full' => null],
            'colors'       => true,
            'encoding'     => 'utf-8',
            'showProgress' => true,
            'reportWidth'  => 120,
        ]));

        $cs->process($files, $moodle.'/local/codechecker/moodle');
        $results = $cs->reporting->printReport('full', false, $cs->cli->getCommandLineValues(), null, 120);

        return $results['errors'] > 0 ? 1 : 0;
    }
}
