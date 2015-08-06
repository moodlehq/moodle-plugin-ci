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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Shift YUI modules on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ShifterCommand extends Command
{
    /**
     * @var Execute
     */
    public $execute;

    protected function configure()
    {
        // Install Command sets this in Travis CI.
        $plugin = getenv('PLUGIN_DIR') !== false ? getenv('PLUGIN_DIR') : null;
        $mode   = getenv('PLUGIN_DIR') !== false ? InputArgument::OPTIONAL : InputArgument::REQUIRED;

        $this->setName('shifter')
            ->setDescription('Shift YUI modules in a plugin')
            ->addArgument('plugin', $mode, 'Path to the plugin', $plugin);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->execute = $this->execute ?: new Execute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $pluginDir = realpath($validate->directory($input->getArgument('plugin')));
        $plugin    = new MoodlePlugin($pluginDir);

        if (!is_dir($pluginDir.'/yui/src')) {
            $output->writeln('<error>Plugin does not have a yui/src directory to process.</error>');

            return 0;
        }
        if (!is_dir($pluginDir.'/yui/build')) {
            throw new \RuntimeException('The yui/build directory does not exist, plugin YUI modules need to be re-shifted.');
        }

        $output->writeln("<bg=green;fg=white;> RUN </> <fg=blue>Shifter on {$plugin->getComponent()}</>");

        $process = new Process('shifter --walk --lint-stderr --build-dir ../buildci', $pluginDir.'/yui/src');
        $this->execute->mustRun($process);

        if (!is_dir($pluginDir.'/yui/buildci')) {
            throw new \RuntimeException('Shifter failed to make the yui/buildci directory.');
        }

        $process = $this->execute->mustRun("diff -r $pluginDir/yui/build $pluginDir/yui/buildci");
        $out     = trim($process->getOutput());

        $fs = new Filesystem();
        $fs->remove($pluginDir.'/yui/buildci');

        if ($out !== '') {
            $output->writeln('<error>The plugin YUI modules need to be re-shifted</error>');

            return 1;
        }

        $output->writeln('<info>The plugin YUI modules appear to be shifted correctly!</info>');

        return 0;
    }
}
