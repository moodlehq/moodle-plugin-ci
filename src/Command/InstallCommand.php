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

    public Install $install;
    public InstallerCollection $installers;
    public InstallerFactory $factory;
    private string $envFile;

    /**
     * @param string $envFile
     */
    public function __construct(string $envFile)
    {
        parent::__construct();
        $this->envFile = $envFile;
    }

    protected function configure(): void
    {
        // Travis CI configures some things by environment variables, default to those if available.
        $type   = getenv('DB') !== false ? getenv('DB') : null;
        $repo   = getenv('MOODLE_REPO') !== false ? getenv('MOODLE_REPO') : 'https://github.com/moodle/moodle.git';
        $branch = getenv('MOODLE_BRANCH') !== false ? getenv('MOODLE_BRANCH') : null;
        $plugin = getenv('TRAVIS_BUILD_DIR') !== false ? getenv('TRAVIS_BUILD_DIR') : null;
        $paths  = getenv('IGNORE_PATHS') !== false ? getenv('IGNORE_PATHS') : null;
        $names  = getenv('IGNORE_NAMES') !== false ? getenv('IGNORE_NAMES') : null;
        $extra  = getenv('EXTRA_PLUGINS_DIR') !== false ? getenv('EXTRA_PLUGINS_DIR') : null;
        $node   = getenv('NODE_VERSION') !== false ? getenv('NODE_VERSION') : null;

        // As there is not only Travis CI, it can also be passed a generic environment variable.
        if (null === $plugin) {
            $plugin = getenv('CI_BUILD_DIR') !== false ? getenv('CI_BUILD_DIR') : null;
        }

        // Add more options mapped to environment variables.
        $dbUser = getenv('DB_USER') !== false ? getenv('DB_USER') : null;
        $dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $dbName = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'moodle';
        $dbHost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
        $dbPort = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '';

        $this->setName('install')
            ->setDescription('Install everything required for CI testing')
            ->addOption('moodle', null, InputOption::VALUE_REQUIRED, 'Clone Moodle to this directory', 'moodle')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'Directory create for Moodle data files', 'moodledata')
            ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'Moodle repository to clone', $repo)
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Moodle git branch to clone, EG: MOODLE_29_STABLE', $branch)
            ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'Path to Moodle plugin', $plugin)
            ->addOption('db-type', null, InputOption::VALUE_REQUIRED, 'Database type, mysqli, pgsql, mariadb or sqlsrv', $type)
            ->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'Database user', $dbUser)
            ->addOption('db-pass', null, InputOption::VALUE_REQUIRED, 'Database pass', $dbPass)
            ->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'Database name', $dbName)
            ->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'Database host', $dbHost)
            ->addOption('db-port', null, InputOption::VALUE_REQUIRED, 'Database port', $dbPort)
            ->addOption('not-paths', null, InputOption::VALUE_REQUIRED, 'CSV of file paths to exclude', $paths)
            ->addOption('not-names', null, InputOption::VALUE_REQUIRED, 'CSV of file names to exclude', $names)
            ->addOption('extra-plugins', null, InputOption::VALUE_REQUIRED, 'Directory of extra plugins to install', $extra)
            ->addOption('no-init', null, InputOption::VALUE_NONE, 'Prevent PHPUnit and Behat initialization')
            ->addOption('no-plugin-node', null, InputOption::VALUE_NONE, 'Prevent Node.js plugin dependencies installation')
            ->addOption('node-version', null, InputOption::VALUE_REQUIRED, 'Node.js version to use for nvm install (this will override one defined in .nvmrc)', $node);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeExecute($output, $this->getHelper('process'));

        $installOutput    = $this->initializeInstallOutput($output);
        $this->install    = $this->install ?? new Install($installOutput);
        $this->factory    = $this->factory ?? $this->initializeInstallerFactory($input);
        $this->installers = $this->installers ?? new InstallerCollection($installOutput);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->factory->addInstallers($this->installers);
        $this->install->runInstallation($this->installers);

        $envDumper = new EnvDumper();
        $envDumper->dump($this->installers->mergeEnv(), $this->envFile);

        // Progress bar does not end with a newline.
        $output->writeln('');

        return 0;
    }

    /**
     * @param OutputInterface $output
     *
     * @return InstallOutput
     */
    public function initializeInstallOutput(OutputInterface $output): InstallOutput
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
     * @param InputInterface $input
     *
     * @return InstallerFactory
     */
    public function initializeInstallerFactory(InputInterface $input): InstallerFactory
    {
        $validate   = new Validate();
        $resolver   = new DatabaseResolver();
        $pluginDir  = realpath($validate->directory($input->getOption('plugin')));
        $pluginsDir = $input->getOption('extra-plugins');

        if (!empty($pluginsDir)) {
            $pluginsDir = realpath($validate->directory($pluginsDir));
        }

        $factory               = new InstallerFactory();
        $factory->moodle       = new Moodle($input->getOption('moodle'));
        $factory->plugin       = new MoodlePlugin($pluginDir);
        $factory->execute      = $this->execute;
        $factory->repo         = $validate->gitUrl($input->getOption('repo'));
        $factory->branch       = $validate->gitBranch($input->getOption('branch'));
        $factory->dataDir      = $input->getOption('data');
        $factory->dumper       = $this->initializePluginConfigDumper($input);
        $factory->pluginsDir   = $pluginsDir;
        $factory->noInit       = $input->getOption('no-init');
        $factory->noPluginNode = $input->getOption('no-plugin-node');
        $factory->nodeVer      = $input->getOption('node-version');
        $factory->database     = $resolver->resolveDatabase(
            $input->getOption('db-type'),
            $input->getOption('db-name'),
            $input->getOption('db-user'),
            $input->getOption('db-pass'),
            $input->getOption('db-host'),
            $input->getOption('db-port')
        );

        return $factory;
    }

    /**
     * @param InputInterface $input
     *
     * @return ConfigDumper
     */
    public function initializePluginConfigDumper(InputInterface $input): ConfigDumper
    {
        $dumper = new ConfigDumper();
        $dumper->addSection('filter', 'notPaths', $this->csvToArray($input->getOption('not-paths')));
        $dumper->addSection('filter', 'notNames', $this->csvToArray($input->getOption('not-names')));

        $application = $this->getApplication();
        if (!isset($application)) {
            return $dumper;
        }

        foreach ($application->all() as $command) {
            if (!$command instanceof AbstractPluginCommand) {
                continue;
            }

            $commandName = $command->getName() ?? '';
            $prefix      = strtoupper($commandName);
            $envPaths    = $prefix . '_IGNORE_PATHS';
            $envNames    = $prefix . '_IGNORE_NAMES';

            $paths = getenv($envPaths) !== false ? getenv($envPaths) : null;
            $names = getenv($envNames) !== false ? getenv($envNames) : null;

            if (!empty($paths)) {
                $dumper->addSection('filter-' . $commandName, 'notPaths', $this->csvToArray($paths));
            }
            if (!empty($names)) {
                $dumper->addSection('filter-' . $commandName, 'notNames', $this->csvToArray($names));
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
    public function csvToArray(?string $value): array
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
