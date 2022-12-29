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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Run Behat tests.
 */
class BehatCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    /**
     * Selenium legacy Firefox image.
     */
    private string $seleniumLegacyFirefoxImage = 'selenium/standalone-firefox:2.53.1';

    /**
     * Selenium standalone Firefox image.
     */
    private string $seleniumFirefoxImage = 'selenium/standalone-firefox:4';

    /**
     * Selenium standalone Chrome image.
     */
    private string $seleniumChromeImage = 'selenium/standalone-chrome:4';

    /**
     * Wait this many microseconds for Selenium server to start/stop.
     */
    private int $seleniumWaitTime = 5000000;

    private Process $webserver;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('behat')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Behat profile to use', 'default')
            ->addOption('suite', null, InputOption::VALUE_REQUIRED, 'Behat suite to use (Moodle theme)', 'default')
            ->addOption('start-servers', null, InputOption::VALUE_NONE, 'Start Selenium and PHP servers')
            ->addOption('auto-rerun', null, InputOption::VALUE_REQUIRED, 'Number of times to rerun failures', 2)
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Print contents of Behat failure HTML files')
            ->setDescription('Run Behat on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Behat features for %s');

        if (!$this->plugin->hasBehatFeatures()) {
            return $this->outputSkip($output, 'No Behat features to run, free pass!');
        }
        // This env var is set during install and forces server startup.
        $servers = getenv('MOODLE_START_BEHAT_SERVERS') === 'YES';
        if (!$servers) {
            $servers = $input->getOption('start-servers');
        }

        $servers && $this->startServerProcesses($input);

        $cmd = [
            'php', 'admin/tool/behat/cli/run.php',
            '--tags=@'.$this->plugin->getComponent(),
            '--profile='.$input->getOption('profile'),
            '--suite='.$input->getOption('suite'),
            '--auto-rerun='.$input->getOption('auto-rerun'),
            '--verbose',
            '-vvv',
        ];

        if ($output->isDecorated()) {
            $cmd[] = '--colors';
        }

        $process = $this->execute->passThroughProcess(new Process($cmd, $this->moodle->directory, null, null, null));

        $servers && $this->stopServerProcesses();

        if ($input->getOption('dump')) {
            $this->dumpFailures($output);
        }

        return $process->isSuccessful() ? 0 : 1;
    }

    /**
     * @param InputInterface $input
     */
    private function startServerProcesses(InputInterface $input): void
    {
        // Test we have docker cli.
        $process = $this->execute->run('docker -v');
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Docker is not available, can\'t start Selenium server');
        }

        // Start docker container using desired image.
        if ($input->getOption('profile') === 'chrome') {
            $image = $this->seleniumChromeImage;
        } elseif ($this->usesLegacyPhpWebdriver()) {
            $image = $this->seleniumLegacyFirefoxImage;
        } else {
            $image = $this->seleniumFirefoxImage;
        }

        $cmd = [
            'docker',
            'run',
            '-d',
            '--rm',
            '--name=selenium',
            '--publish=4444:4444',
            '--shm-size=2g',
            '-v',
            $this->moodle->directory . ':' . $this->moodle->directory,
            $image,
        ];
        $docker = $this->execute->passThrough($cmd);
        if (!$docker->isSuccessful()) {
            throw new \RuntimeException('Can\'t start Selenium server');
        }

        // Start web server.
        $cmd = [
            'php',
            '-S',
            'localhost:8000',
        ];
        $web = new Process($cmd, $this->moodle->directory);
        $web->setTimeout(0);
        $web->disableOutput();
        $web->start();
        $this->webserver = $web;

        // Need to wait for Selenium to start up. Not really sure how long that takes.
        usleep($this->seleniumWaitTime);
    }

    private function stopServerProcesses(): void
    {
        // Stop docker. This will also destroy container.
        $this->execute->mustRun('docker stop selenium');
        // Stop webserver.
        $this->webserver->stop();
    }

    private function dumpFailures(OutputInterface $output): void
    {
        $dumpDir = $this->moodle->getConfig('behat_faildump_path');
        if (is_dir($dumpDir)) {
            $files = Finder::create()->name('*.html')->in($dumpDir)->getIterator();
            foreach ($files as $file) {
                $output->writeln([
                    sprintf('<comment>===== %s =====</comment>', $file->getFilename()),
                    $file->getContents(),
                    sprintf('<comment>===== %s =====</comment>', $file->getFilename()),
                ]);
            }
        }
    }

    private function usesLegacyPhpWebdriver(): bool
    {
        // The `instaclick/php-webdriver` dependency requires use of a legacy version of Firefox.
        $composerlock = "{$this->moodle->directory}/composer.lock";

        return strpos(file_get_contents($composerlock), 'instaclick/php-webdriver') !== false;
    }
}
