<?php
/**
 * This file is part of the Moodle Plugin Travis CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodleTravisPlugin\Command;

use Moodlerooms\MoodleTravisPlugin\Bridge\Moodle;
use Moodlerooms\MoodleTravisPlugin\Bridge\MoodlePlugin;
use Moodlerooms\MoodleTravisPlugin\Properties\PluginProperties;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates a properties file for Phing with details about the plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginPropertiesCommand extends Command {
    protected function configure() {
        $this->setName('pluginproperties')
            ->setDescription('Create a Phing properties file about a plugin')
            ->addArgument('moodle', InputArgument::REQUIRED, 'Absolute path to Moodle')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Absolute path to the plugin that should be installed')
            ->addArgument('out', InputArgument::REQUIRED, 'Absolute path to the file to write to');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $moodle = $input->getArgument('moodle');
        $plugin = $input->getArgument('plugin');
        $out    = $input->getArgument('out');

        if (!is_dir($moodle)) {
            throw new \InvalidArgumentException('The moodle argument is not a path a directory');
        }
        if (!is_dir($plugin)) {
            throw new \InvalidArgumentException('The plugin argument is not a path a directory');
        }
        $moodlePlugin     = new MoodlePlugin(new Moodle($moodle), $plugin);
        $pluginProperties = new PluginProperties();
        $properties       = $pluginProperties->getPropertiesFromPlugin($moodlePlugin);

        $fs = new Filesystem();
        $fs->dumpFile($out, $properties);

        $output->writeln(sprintf('<info>Created properties file at %s with the following content:</info>', $out));
        $output->writeln($properties);
    }
}