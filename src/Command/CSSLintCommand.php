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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Run CSS Lint on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CSSLintCommand extends Command
{
    protected function configure()
    {
        $this->setName('csslint')
            ->setDescription('Run CSSLint on a plugin')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Path to the plugin')
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate = new Validate();
        $plugin   = realpath($validate->directory($input->getArgument('plugin')));
        $moodle   = realpath($validate->directory($input->getOption('moodle')));

        $moodlePlugin = new MoodlePlugin(new Moodle($moodle), $plugin);

        $finder = new Finder();
        $finder->name('*.css')->notName('*-min.css');

        $files = $moodlePlugin->getRelativeFiles($finder);

        if (empty($files)) {
            $output->writeln('<error>Failed to find any files to process.</error>');

            return 0;
        }

        $process = new Process('csslint '.implode(' ', $files), $moodlePlugin->getInstallDirectory());
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return $process->isSuccessful() ? 0 : 1;
    }
}
