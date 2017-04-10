<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

class SavePointsCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('savepoints')
            ->setDescription('Check upgrade savepoints');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Check upgrade savepoints on %s');

        if (!is_file($this->plugin->directory.'/db/upgrade.php')) {
            return $this->outputSkip($output);
        }

        $fs            = new Filesystem();
        $upgradetester = realpath(__DIR__.'/../../vendor/moodlehq/moodle-local_ci/check_upgrade_savepoints/check_upgrade_savepoints.php');
        $fs->copy($upgradetester, $this->plugin->directory.'/check_upgrade_savepoints.php');

        $process = $this->execute->passThroughProcess(
            ProcessBuilder::create()
                ->setPrefix('php')
                ->add('check_upgrade_savepoints.php')
                ->setTimeout(null)
                ->setWorkingDirectory($this->plugin->directory)
                ->getProcess()
        );

        $code    = 0;
        $results = $process->getOutput();
        if (strstr($results, 'WARN') || strstr($results, 'ERROR')) {
            $code = 1;
        }

        $fs->remove($this->plugin->directory.'/check_upgrade_savepoints.php');

        return $code;
    }
}
