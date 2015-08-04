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
use Moodlerooms\MoodlePluginCI\Properties\PluginProperties;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates a properties file for Phing with details about the plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginPropertiesCommand extends Command
{
    protected function configure()
    {
        $this->setName('pluginproperties')
            ->setDescription('Create a Phing properties file about a plugin')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Path to the plugin')
            ->addArgument('out', InputArgument::REQUIRED, 'Absolute path to the file to write to')
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate = new Validate();
        $plugin   = realpath($validate->directory($input->getArgument('plugin')));
        $moodle   = realpath($validate->directory($input->getOption('moodle')));
        $out      = $input->getArgument('out');

        $moodlePlugin     = new MoodlePlugin(new Moodle($moodle), $plugin);
        $pluginProperties = new PluginProperties();
        $properties       = $pluginProperties->getPropertiesFromPlugin($moodlePlugin);

        $fs = new Filesystem();
        $fs->dumpFile($out, $properties);

        $output->writeln(sprintf('<info>Created properties file at %s with the following content:</info>', $out));
        $output->writeln($properties);
    }
}
