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

use MoodlePluginCI\Installer\EnvDumper;
use MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Add a dependent plugin to be installed.
 */
class AddOfflinePluginCommand extends Command
{
    use ExecuteTrait;
    private string $envFile;

    public function __construct(string $envFile)
    {
        parent::__construct();
        $this->envFile = $envFile;
    }

    protected function configure(): void
    {
        $this->setName('add-offline-plugin')
            ->setDescription('Copies and installs the plugin from an offline source')
            ->addArgument('projectname', InputArgument::OPTIONAL, 'Name of your repository, not a path, if not set, the basepath is used')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'The branch to checkout in plugin folder when a git repo is present', null)
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'The path to the plugin folder')
            ->addOption('storage', null, InputOption::VALUE_REQUIRED, 'Plugin storage directory', 'moodle-plugin-ci-plugins');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $validate       = new Validate();
        $filesystem     = new Filesystem();
        $branch         = trim($input->getOption('branch'));
        $source         = rtrim(trim($input->getOption('source')), DIRECTORY_SEPARATOR);
        $storageDir     = rtrim(trim($input->getOption('storage')), DIRECTORY_SEPARATOR);
        $projectname    = trim($input->getArgument('projectname'));

        $source = realpath($validate->directory($source));

        if (!empty($branch)) {

            $process = new Process(explode(' ', 'git status'), $source, null, null, null);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException('There is no valid git repository in your source folder!');
            }

            $process = new Process(explode(' ', "git rev-parse --verify $branch"), $source, null, null, null);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException("Your branch $branch does not exist in this repository");
            }
        }

        if (empty($projectname)) {
            $projectname = basename($source);
        }

        $filesystem->mkdir($storageDir);
        $storageDir = realpath($validate->directory($storageDir));
        $filesystem->mkdir("$storageDir/$projectname");
        $filesystem->mirror($source, "$storageDir/$projectname/");
        $retval = true;

        if (!empty($branch)) {
            $process = new Process(['git', 'checkout', $branch], "$storageDir/$projectname", null, null, null);
            $process->run();
            $retval = $process->isSuccessful();
        }

        $dumper = new EnvDumper();
        $dumper->dump(['EXTRA_PLUGINS_DIR' => $storageDir], $this->envFile);

        return $retval ? 0 : 1;
    }
}
