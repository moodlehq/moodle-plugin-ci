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

use Moodlerooms\MoodlePluginCI\Installer\EnvDumper;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Add a dependent plugin to be installed.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AddPluginCommand extends Command
{
    use ExecuteTrait;

    /**
     * @var string
     */
    private $envFile;

    public function __construct($envFile)
    {
        parent::__construct();
        $this->envFile = $envFile;
    }

    protected function configure()
    {
        $this->setName('add-plugin')
            ->setDescription('Queue up an additional plugin to be installed in the test site')
            ->addArgument('project', InputArgument::OPTIONAL, 'GitHub project, EG: moodlehq/moodle-local_hub')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'The branch to checkout within the plugin', 'master')
            ->addOption('clone', 'c', InputOption::VALUE_REQUIRED, 'Git clone URL')
            ->addOption('storage', null, InputOption::VALUE_REQUIRED, 'Plugin storage directory', 'moodle-plugin-ci-plugins');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
        if (!empty($project)) {
            $cloneUrl = sprintf('https://github.com/%s.git', $project);
        } elseif (!empty($clone)) {
            $cloneUrl = $clone;
        } else {
            throw new \RuntimeException('Must use the project argument or --clone option');
        }

        $filesystem->mkdir($storage);
        $storageDir = realpath($validate->directory($storage));

        $process = new Process(sprintf('git clone --depth 1 --branch %s %s', $branch, $cloneUrl), $storageDir);
        $this->execute->mustRun($process);

        $dumper = new EnvDumper();
        $dumper->dump(['EXTRA_PLUGINS_DIR' => $storageDir], $this->envFile);
    }
}
