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

use JakubOnderka\PhpParallelLint\Manager;
use JakubOnderka\PhpParallelLint\Settings;
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
 * Run PHP Lint on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PHPLintCommand extends Command
{
    protected function configure()
    {
        $this->setName('phplint')
            ->setDescription('Run PHP Lint on a plugin')
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
        $finder->name('*.php');

        $files = $moodlePlugin->getFiles($finder);

        if (empty($files)) {
            $output->writeln('<error>Failed to find any files to process.</error>');

            return 0;
        }

        $settings = new Settings();
        $settings->addPaths($files);

        $manager = new Manager();
        $result  = $manager->run($settings);

        return $result->hasError() ? 1 : 0;
    }
}
