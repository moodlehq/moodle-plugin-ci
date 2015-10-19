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
class ShifterCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('shifter')
            ->setDescription('Shift YUI modules in a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Shifter on %s');

        $pluginDir = $this->plugin->directory;

        if (!is_dir($pluginDir.'/yui/src')) {
            return $this->outputSkip($output);
        }
        if (!is_dir($pluginDir.'/yui/build')) {
            throw new \RuntimeException('The yui/build directory does not exist, plugin YUI modules need to be re-shifted.');
        }

        $process = new Process('shifter --walk --lint-stderr --build-dir ../buildci', $pluginDir.'/yui/src');
        $this->execute->mustRun($process);

        if (!is_dir($pluginDir.'/yui/buildci')) {
            throw new \RuntimeException('Shifter failed to make the yui/buildci directory.');
        }

        $process = $this->execute->mustRun(sprintf('diff -r %1$s/yui/build %1$s/yui/buildci', $pluginDir));
        $out     = trim($process->getOutput());

        $filesystem = new Filesystem();
        $filesystem->remove($pluginDir.'/yui/buildci');

        if ($out !== '') {
            $output->writeln('<error>The plugin YUI modules need to be re-built.</error>');

            return 1;
        }

        $output->writeln('<info>The plugin YUI modules have been built correctly.</info>');

        return 0;
    }
}
