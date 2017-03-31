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

    /**
     * @var string
     */
    public $jarFile;

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
        $this->jarFile = $this->jarFile ?: $this->resolveJarFile();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Mustache Lint on %s');

        $files = $this->plugin->getRelativeFiles(Finder::create()->name('*.mustache'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $linter = realpath(__DIR__.'/../../vendor/moodlehq/moodle-local_ci/mustache_lint/mustache_lint.php');

        $code = 0;
        foreach ($files as $file) {
            $process = $this->execute->passThroughProcess(
                ProcessBuilder::create()
                    ->setPrefix('php')
                    ->add($linter)
                    ->add('--filename='.$this->plugin->directory.'/'.$file)
                    ->add('--validator='.$this->jarFile)
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

    public function resolveJarFile()
    {
        $process = $this->execute->mustRun('npm -g prefix');
        $file    = trim($process->getOutput()).'/lib/node_modules/vnu-jar/build/dist/vnu.jar';

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('Failed to find %s', $file));
        }

        return $file;
    }
}
