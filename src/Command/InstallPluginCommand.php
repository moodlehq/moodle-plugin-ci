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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installs a plugin into Moodle.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallPluginCommand extends Command {
    protected function configure() {
        $this->setName('installplugin')
            ->setDescription('Install a plugin into Moodle')
            ->addArgument('moodle', InputArgument::REQUIRED, 'Absolute path to Moodle')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Absolute path to the plugin that should be installed');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $moodle = $input->getArgument('moodle');
        $plugin = $input->getArgument('plugin');

        if (!is_dir($moodle)) {
            throw new \InvalidArgumentException('The moodle argument is not a path a directory');
        }
        if (!is_dir($plugin)) {
            throw new \InvalidArgumentException('The plugin argument is not a path a directory');
        }
        $moodlePlugin = new MoodlePlugin(new Moodle($moodle), $plugin);
        $moodlePlugin->installPluginIntoMoodle();

        $output->writeln('<info>Installed the plugin into Moodle.</info>');
    }
}