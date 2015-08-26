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

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run PHPUnit tests.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PHPUnitCommand extends Command
{
    /**
     * @var Execute
     */
    public $execute;

    protected function configure()
    {
        // Install Command sets these in Travis CI.
        $plugin = getenv('PLUGIN_DIR') !== false ? getenv('PLUGIN_DIR') : null;
        $mode   = getenv('PLUGIN_DIR') !== false ? InputArgument::OPTIONAL : InputArgument::REQUIRED;
        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';

        $this->setName('phpunit')
            ->setDescription('Run PHPUnit on a plugin')
            ->addArgument('plugin', $mode, 'Path to the plugin', $plugin)
            ->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', $moodle);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->execute = $this->execute ?: new Execute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $pluginDir = realpath($validate->directory($input->getArgument('plugin')));
        $moodleDir = realpath($validate->directory($input->getOption('moodle')));
        $plugin    = new MoodlePlugin($pluginDir);

        if (!$plugin->hasUnitTests()) {
            throw new \InvalidArgumentException('The plugin does not have any PHPUnit tests to run: '.$pluginDir);
        }

        $output->writeln(sprintf('<bg=green;fg=white;> RUN </> <fg=blue>PHPUnit tests for %s</>', $plugin->getComponent()));

        $process = $this->execute->passThrough(
            sprintf('%s/vendor/bin/phpunit --testsuite %s_testsuite', $moodleDir, $plugin->getComponent()),
            $moodleDir
        );

        return $process->isSuccessful() ? 0 : 1;
    }
}
