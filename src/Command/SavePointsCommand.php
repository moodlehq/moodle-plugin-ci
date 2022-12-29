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
use Symfony\Component\Process\Process;

class SavePointsCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('savepoints')
            ->setDescription('Check upgrade savepoints');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Check upgrade savepoints on %s');

        if (!is_file($this->plugin->directory.'/db/upgrade.php')) {
            return $this->outputSkip($output);
        }

        $filesystem    = new Filesystem();
        $upgradetester = __DIR__.'/../../vendor/moodlehq/moodle-local_ci/check_upgrade_savepoints/check_upgrade_savepoints.php';
        $filesystem->copy($upgradetester, $this->plugin->directory.'/check_upgrade_savepoints.php');

        $process = $this->execute->passThroughProcess(
            (new Process(['php', 'check_upgrade_savepoints.php']))
                ->setTimeout(null)
                ->setWorkingDirectory($this->plugin->directory)
        );

        $code    = 0;
        $results = $process->getOutput();
        if (strstr($results, 'WARN') || strstr($results, 'ERROR')) {
            $code = 1;
        }

        $filesystem->remove($this->plugin->directory.'/check_upgrade_savepoints.php');

        return $code;
    }
}
