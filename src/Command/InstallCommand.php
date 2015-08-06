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

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\BehatInstaller;
use Moodlerooms\MoodlePluginCI\Installer\ComposerInstaller;
use Moodlerooms\MoodlePluginCI\Installer\Database\DatabaseResolver;
use Moodlerooms\MoodlePluginCI\Installer\Installer;
use Moodlerooms\MoodlePluginCI\Installer\JSInstaller;
use Moodlerooms\MoodlePluginCI\Installer\MoodleInstaller;
use Moodlerooms\MoodlePluginCI\Installer\PHPUnitInstaller;
use Moodlerooms\MoodlePluginCI\Installer\PluginInstaller;
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install command.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallCommand extends Command
{
    protected function configure()
    {
        // Travis CI configures some things by environment variables, default to those if available.
        $type   = getenv('DB') !== false ? getenv('DB') : null;
        $branch = getenv('MOODLE_BRANCH') !== false ? getenv('MOODLE_BRANCH') : null;
        $plugin = getenv('TRAVIS_BUILD_DIR') !== false ? getenv('TRAVIS_BUILD_DIR') : null;
        $paths  = getenv('IGNORE_PATHS') !== false ? getenv('IGNORE_PATHS') : null;
        $names  = getenv('IGNORE_NAMES') !== false ? getenv('IGNORE_NAMES') : null;

        $this->setName('install')
            ->setDescription('Install everything required for CI testing')
            ->addOption('moodle', null, InputOption::VALUE_OPTIONAL, 'Clone Moodle to this directory', 'moodle')
            ->addOption('data', null, InputOption::VALUE_OPTIONAL, 'Directory create for Moodle data files', 'moodledata')
            ->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Moodle git branch to clone, EG: MOODLE_29_STABLE', $branch)
            ->addOption('plugin', null, InputOption::VALUE_OPTIONAL, 'Path to Moodle plugin', $plugin)
            ->addOption('db-type', null, InputOption::VALUE_OPTIONAL, 'Database type, mysqli or pgsql', $type)
            ->addOption('db-user', null, InputOption::VALUE_OPTIONAL, 'Database user')
            ->addOption('db-pass', null, InputOption::VALUE_OPTIONAL, 'Database pass', '')
            ->addOption('db-name', null, InputOption::VALUE_OPTIONAL, 'Database name', 'moodle')
            ->addOption('db-host', null, InputOption::VALUE_OPTIONAL, 'Database host', 'localhost')
            ->addOption('no-js', null, InputOption::VALUE_NONE, 'Do not install NPM packages')
            ->addOption('not-paths', null, InputOption::VALUE_OPTIONAL, 'CSV of file paths to exclude', $paths)
            ->addOption('not-names', null, InputOption::VALUE_OPTIONAL, 'CSV of file names to exclude', $names);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate  = new Validate();
        $moodleDir = $input->getOption('moodle');
        $dataDir   = $input->getOption('data');
        $branch    = $validate->moodleBranch($input->getOption('branch'));
        $pluginDir = realpath($validate->directory($input->getOption('plugin')));
        $notPaths  = $this->csvToArray($input->getOption('not-paths'));
        $notNames  = $this->csvToArray($input->getOption('not-names'));

        $progressBar = null;
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            // Low verbosity, use progress bar.
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% [%message%]');
        }

        $moodle    = new Moodle($moodleDir);
        $plugin    = new MoodlePlugin($pluginDir);
        $execute   = new Execute($output, $this->getHelper('process'));
        $logger    = new ConsoleLogger($output);
        $installer = new Installer($logger, $progressBar);
        $resolver  = new DatabaseResolver();
        $database  = $resolver->resolveDatabase(
            $input->getOption('db-type'),
            $input->getOption('db-name'),
            $input->getOption('db-user'),
            $input->getOption('db-pass'),
            $input->getOption('db-host')
        );

        $installer->addInstaller(new MoodleInstaller($execute, $database, $moodle, $branch, $dataDir));
        $installer->addInstaller(new PluginInstaller($moodle, $plugin, $notPaths, $notNames));

        if ($plugin->hasBehatFeatures() || $plugin->hasUnitTests()) {
            $installer->addInstaller(new ComposerInstaller($moodle, $execute));
        }
        if ($plugin->hasBehatFeatures()) {
            $installer->addInstaller(new BehatInstaller($moodle, $execute));
        }
        if ($plugin->hasUnitTests()) {
            $installer->addInstaller(new PHPUnitInstaller($moodle, $execute));
        }
        if ($input->getOption('no-js') === false) {
            $installer->addInstaller(new JSInstaller($execute));
        }

        $installer->runInstallation();

        $output->writeln('');
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
