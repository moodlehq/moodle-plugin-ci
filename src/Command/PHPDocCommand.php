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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use MoodlePluginCI\Bridge\MoodlePlugin;

class PHPDocCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('phpdoc')
            ->setDescription('Run Moodle PHPDoc Checker on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Moodle PHPDoc Checker on %s');

        // We need local_moodlecheck plugin to run this check.
        $pluginlocation  = __DIR__.'/../../vendor/moodlehq/moodle-local_moodlecheck';
        $plugin = new MoodlePlugin($pluginlocation);
        $directory = $this->moodle->getComponentInstallDirectory($plugin->getComponent());
        if (!is_dir($directory)) {
            // Copy plugin into Moodle if it does not exist.
            $filesystem = new Filesystem();
            $filesystem->mirror($plugin->directory, $directory);
        }

        $process = $this->execute->passThroughProcess(
            ProcessBuilder::create()
                ->setPrefix('php')
                ->add('local/moodlecheck/cli/moodlecheck.php')
                ->add('-p=' . $this->plugin->directory)
                ->add('-f=text')
                ->setTimeout(null)
                ->setWorkingDirectory($this->moodle->directory)
                ->getProcess()
        );

        if (isset($filesystem)) {
            // Remove plugin if we added it, so we leave things clean.
            $filesystem->remove($directory);
        }

        // moodlecheck.php does not return valid exit status,
        // We have to parse output to see if there are errors.
        $results = $process->getOutput();
        return (preg_match('/\s+Line/', $results)) ? 1 : 0;
    }
}
