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

use MoodlePluginCI\Bridge\Moodle;
use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Installer\ConfigDumper;
use MoodlePluginCI\Installer\Database\DatabaseResolver;
use MoodlePluginCI\Installer\EnvDumper;
use MoodlePluginCI\Installer\Install;
use MoodlePluginCI\Installer\InstallerCollection;
use MoodlePluginCI\Installer\InstallerFactory;
use MoodlePluginCI\Installer\InstallOutput;
use MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install command.
 */
class InstallCommand extends Command
{
    use ExecuteTrait;

    /**
     * @var Install
     */
    public $install;

    /**
     * @var InstallerCollection
     */
    public $installers;

    /**
     * @var InstallerFactory
     */
    public $factory;

    /**
     * @var string
     */
    private $envFile;

    /**
     * @param string $envFile
     */
    public function __construct($envFile)
    {
        parent::__construct();
        $this->envFile = $envFile;
    }

    protected function configure()
    {
        // Travis CI configures some things by environment variables, default to those if available.
        $type   = getenv('DB') !== false ? getenv('DB') : null;
        $repo   = getenv('MOODLE_REPO') !== false ? getenv('MOODLE_REPO') : 'git://github.com/moodle/moodle.git';
        $branch = getenv('MOODLE_BRANCH') !== false ? getenv('MOODLE_BRANCH') : null;
        $plugin = getenv('TRAVIS_BUILD_DIR') !== false ? getenv('TRAVIS_BUILD_DIR') : null;
        $paths  = getenv('IGNORE_PATHS') !== false ? getenv('IGNORE_PATHS') : null;
        $names  = getenv('IGNORE_NAMES') !== false ? getenv('IGNORE_NAMES') : null;
        $extra  = getenv('EXTRA_PLUGINS_DIR') !== false ? getenv('EXTRA_PLUGINS_DIR') : null;

        $this->setName('install')
            ->setDescription('Install everything required for CI testing')
            ->addOption('moodle', null, InputOption::VALUE_REQUIRED, 'Clone Moodle to this directory', 'moodle')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'Directory create for Moodle data files', 'moodledata')
            ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'Moodle repository to clone', $repo)
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Moodle git branch to clone, EG: MOODLE_29_STABLE', $branch)
            ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'Path to Moodle plugin', $plugin)
            ->addOption('db-type', null, InputOption::VALUE_REQUIRED, 'Database type, mysqli, pgsql or mariadb', $type)
            ->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('db-pass', null, InputOption::VALUE_REQUIRED, 'Database pass', '')
            ->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'Database name', 'moodle')
            ->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost')
            ->addOption('not-paths', null, InputOption::VALUE_REQUIRED, 'CSV of file paths to exclude', $paths)
            ->addOption('not-names', null, InputOption::VALUE_REQUIRED, 'CSV of file names to exclude', $names)
            ->addOption('extra-plugins', null, InputOption::VALUE_REQUIRED, 'Directory of extra plugins to install', $extra)
            ->addOption('no-init', null, InputOption::VALUE_NONE, 'Prevent PHPUnit and Behat initialization');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeExecute($output, $this->getHelper('process'));

        $installOutput    = $this->initializeInstallOutput($output);
        $this->install    = $this->install ?: new Install($installOutput);
        $this->factory    = $this->factory ?: $this->initializeInstallerFactory($input);
        $this->installers = $this->installers ?: new InstallerCollection($installOutput);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->factory->addInstallers($this->installers);
        $this->install->runInstallation($this->installers);

        $envDumper = new EnvDumper();
        $envDumper->dump($this->installers->mergeEnv(), $this->envFile);

        // Progress bar does not end with a newline.
        $output->writeln('');
    }

    /**
     * @return InstallOutput
     */
    public function initializeInstallOutput(OutputInterface $output)
    {
        $progressBar = null;
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            // Low verbosity, use progress bar.
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% [%message%]');
        }

        return new InstallOutput(new ConsoleLogger($output), $progressBar);
    }

    /**
     * Create a new installer factory from input options.
     *
     * @return InstallerFactory
     */
    public function initializeInstallerFactory(InputInterface $input)
    {
        $validate   = new Validate();
        $resolver   = new DatabaseResolver();
        $pluginDir  = realpath($validate->directory($input->getOption('plugin')));
        $pluginsDir = $input->getOption('extra-plugins');

        if (!empty($pluginsDir)) {
            $pluginsDir = realpath($validate->directory($pluginsDir));
        }

        $factory             = new InstallerFactory();
        $factory->moodle     = new Moodle($input->getOption('moodle'));
        $factory->plugin     = new MoodlePlugin($pluginDir);
        $factory->execute    = $this->execute;
        $factory->repo       = $validate->gitUrl($input->getOption('repo'));
        $factory->branch     = $validate->gitBranch($input->getOption('branch'));
        $factory->dataDir    = $input->getOption('data');
        $factory->dumper     = $this->initializePluginConfigDumper($input);
        $factory->pluginsDir = $pluginsDir;
        $factory->noInit     = $input->getOption('no-init');
        $factory->database   = $resolver->resolveDatabase(
            $input->getOption('db-type'),
            $input->getOption('db-name'),
            $input->getOption('db-user'),
            $input->getOption('db-pass'),
            $input->getOption('db-host')
        );

        return $factory;
    }

    /**
     * @return ConfigDumper
     */
    public function initializePluginConfigDumper(InputInterface $input)
    {
        $dumper = new ConfigDumper();
        $dumper->addSection('filter', 'notPaths', $this->csvToArray($input->getOption('not-paths')));
        $dumper->addSection('filter', 'notNames', $this->csvToArray($input->getOption('not-names')));

        foreach ($this->getApplication()->all() as $command) {
            if (!$command instanceof AbstractPluginCommand) {
                continue;
            }

            $prefix   = strtoupper($command->getName());
            $envPaths = $prefix.'_IGNORE_PATHS';
            $envNames = $prefix.'_IGNORE_NAMES';

            $paths = getenv($envPaths) !== false ? getenv($envPaths) : null;
            $names = getenv($envNames) !== false ? getenv($envNames) : null;

            if (!empty($paths)) {
                $dumper->addSection('filter-'.$command->getName(), 'notPaths', $this->csvToArray($paths));
            }
            if (!empty($names)) {
                $dumper->addSection('filter-'.$command->getName(), 'notNames', $this->csvToArray($names));
            }
        }

        return $dumper;
    }

    /**
     * Convert a CSV string to an array.
     *
     * Remove empties and surrounding spaces.
     *
     * @param string|null $value
     *
     * @return array
     */
    public function csvToArray($value)
    {
        if ($value === null) {
            return [];
        }

        $result = explode(',', $value);
        $result = array_map('trim', $result);
        $result = array_filter($result);

        return array_values($result);
    }
}
