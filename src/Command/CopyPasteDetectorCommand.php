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

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Validate;
use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Log\Text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run PHP Copy/Paste Detector on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CopyPasteDetectorCommand extends Command
{
    protected function configure()
    {
        $this->setName('copypaste')
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

        $detector = new Detector(new DefaultStrategy());
        $clones   = $detector->copyPasteDetection($files);

        $printer = new Text();
        $printer->printResult($output, $clones);
        $output->writeln(\PHP_Timer::resourceUsage());

        return count($clones) > 0 ? 1 : 0;
    }
}
