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

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run Behat tests.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BehatCommand extends Command
{
    /**
     * @var Execute
     */
    public $execute;

    /**
     * @var Moodle
     */
    public $moodle;

    /**
     * @var MoodlePlugin
     */
    public $plugin;

    protected function configure()
    {
        // Install Command sets these in Travis CI.
        $plugin = getenv('PLUGIN_DIR') !== false ? getenv('PLUGIN_DIR') : null;
        $mode   = getenv('PLUGIN_DIR') !== false ? InputArgument::OPTIONAL : InputArgument::REQUIRED;
        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';

        $this->setName('behat')
            ->setDescription('Run Behat on a plugin')
            ->addArgument('plugin', $mode, 'Path to the plugin', $plugin)
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', $moodle);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $pluginDir = realpath($validate->directory($input->getArgument('plugin')));
        $moodleDir = realpath($validate->directory($input->getOption('moodle')));

        $this->execute = $this->execute ?: new Execute($output, $this->getHelper('process'));
        $this->moodle  = $this->moodle ?: new Moodle($moodleDir);
        $this->plugin  = $this->plugin ?: new MoodlePlugin($pluginDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->moodle->getBehatDataDirectory().'/behat/behat.yml';

        if (!$this->plugin->hasBehatFeatures()) {
            throw new \InvalidArgumentException('The plugin does not have any Behat features to run: '.$this->plugin->directory);
        }
        if (!file_exists($config)) {
            throw new \RuntimeException('Behat config file not found.  Behat must not have been installed.');
        }

        $output->writeln(sprintf('<bg=green;fg=white;> RUN </> <fg=blue>Behat features for %s</>', $this->plugin->getComponent()));

        $process = $this->execute->passThrough(
            sprintf('%s/vendor/bin/behat --config %s --tags @%s', $this->moodle->directory, $config, $this->plugin->getComponent()),
            $this->moodle->directory
        );

        return $process->isSuccessful() ? 0 : 1;
    }
}
