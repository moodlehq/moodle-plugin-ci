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
class AddPluginCommand extends Command
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
        $this->setName('add-plugin')
            ->setDescription('Queue up an additional plugin to be installed in the test site')
            ->addArgument('project', InputArgument::OPTIONAL, 'GitHub project, EG: moodlehq/moodle-local_hub, can\'t be used with --clone option')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'The branch to checkout in plugin repo (if non-default)', null)
            ->addOption('clone', 'c', InputOption::VALUE_REQUIRED, 'Git clone URL, can\'t be used with --project option')
            ->addOption('storage', null, InputOption::VALUE_REQUIRED, 'Plugin storage directory', 'moodle-plugin-ci-plugins');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $validate   = new Validate();
        $filesystem = new Filesystem();
        $project    = $input->getArgument('project');
        $branch     = $input->getOption('branch');
        $clone      = $input->getOption('clone');
        $storage    = $input->getOption('storage');

        if (!empty($project) && !empty($clone)) {
            throw new \InvalidArgumentException('Cannot use both the project argument and the --clone option');
        }
        if (!is_string($storage)) {
            throw new \InvalidArgumentException('The storage option must be a string');
        }
        if (!empty($project)) {
            $cloneUrl = sprintf('https://github.com/%s.git', $project);
        } elseif (!empty($clone)) {
            $cloneUrl = $clone;
        } else {
            throw new \RuntimeException('Must use the project argument or --clone option');
        }

        $filesystem->mkdir($storage);
        $storageDir = realpath($validate->directory($storage));

        $branchCmd = [];
        if (null !== $branch) {
            $branchCmd = [
                '--branch',
                $branch,
            ];
        }

        $cloneCmd = array_merge(
            [
                'git',
                'clone',
                '--depth',
                '1',
            ],
            $branchCmd,
            [
                $cloneUrl,
            ]
        );
        $process  = $this->execute->mustRun(new Process($cloneCmd, $storageDir, null, null, null));

        $dumper = new EnvDumper();
        $dumper->dump(['EXTRA_PLUGINS_DIR' => $storageDir], $this->envFile);

        return $process->isSuccessful() ? 0 : 1;
    }
}
