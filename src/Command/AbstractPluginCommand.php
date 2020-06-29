<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Command;

use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Plugin Command.
 *
 * This command interacts with a Moodle plugin.
 */
abstract class AbstractPluginCommand extends Command
{
    /**
     * @var MoodlePlugin
     */
    public $plugin;

    protected function configure()
    {
        $plugin = getenv('PLUGIN_DIR');
        $plugin = $plugin === false ? null : $plugin;
        $mode   = $plugin === null ? InputArgument::REQUIRED : InputArgument::OPTIONAL;
        $this->addArgument('plugin', $mode, 'Path to the plugin', $plugin);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$this->plugin) {
            $validate     = new Validate();
            $pluginDir    = realpath($validate->directory($input->getArgument('plugin')));
            $this->plugin = new MoodlePlugin($pluginDir);

            // This allows for command specific configs.
            $this->plugin->context = $this->getName();
        }
    }

    /**
     * @param string $message
     */
    protected function outputHeading(OutputInterface $output, $message)
    {
        $message = sprintf($message, $this->plugin->getComponent());
        $output->writeln(sprintf('<bg=green;fg=white;> RUN </> <fg=blue>%s</>', $message));
    }

    /**
     * @param string|null $message
     *
     * @return int
     */
    protected function outputSkip(OutputInterface $output, $message = null)
    {
        $message = $message ?: 'No relevant files found to process, free pass!';
        $output->writeln('<info>'.$message.'</info>');

        return 0;
    }
}
