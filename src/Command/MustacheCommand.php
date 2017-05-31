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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Lints mustache template files.
 */
class MustacheCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('mustache')
            ->setDescription('Run Mustache Lint on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Mustache Lint on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.mustache'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $linter  = realpath(__DIR__.'/../../vendor/moodlehq/moodle-local_ci/mustache_lint/mustache_lint.php');
        $jarFile = realpath(__DIR__.'/../../vendor/moodlehq/moodle-local_ci/node_modules/vnu-jar/build/dist/vnu.jar');

        $code = 0;
        foreach ($files as $file) {
            $process = $this->execute->passThroughProcess(
                ProcessBuilder::create()
                    ->setPrefix('php')
                    ->add($linter)
                    ->add('--filename='.$file)
                    ->add('--validator='.$jarFile)
                    ->add('--basename='.$this->moodle->directory)
                    ->setTimeout(null)
                    ->getProcess()
            );

            if (!$process->isSuccessful()) {
                $code = 1;
            }
        }

        return $code;
    }
}
