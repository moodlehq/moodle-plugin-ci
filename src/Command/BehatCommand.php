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
use Symfony\Component\Process\ProcessBuilder;

/**
 * Run Behat tests.
 */
class BehatCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    /**
     * Selenium standalone Firefox image.
     *
     * @var string
     */
    private $seleniumLegacyFirefoxImage = 'selenium/standalone-firefox:2.53.1';

    /**
     * Selenium standalone Firefox image.
     *
     * @var string
     */
    private $seleniumFirefoxImage = 'selenium/standalone-firefox:3';

    /**
     * Selenium standalone Chrome image.
     *
     * @var string
     */
    private $seleniumChromeImage = 'selenium/standalone-chrome:3';

    /**
     * Wait this many microseconds for Selenium server to start/stop.
     *
     * @var int
     */
    private $seleniumWaitTime = 5000000;

    /**
     * @var Process
     */
    private $webserver;

    protected function configure()
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Behat features for %s');

        if (!$this->plugin->hasBehatFeatures()) {
            return $this->outputSkip($output, 'No Behat features to run, free pass!');
        }
        // This env var is set during install and forces server startup.
        $servers = getenv('MOODLE_START_BEHAT_SERVERS') === 'YES' ? true : false;
        if (!$servers) {
            $servers = $input->getOption('start-servers');
        }

        $servers && $this->startServerProcesses($input);

        $builder = ProcessBuilder::create()
            ->setPrefix('php')
            ->add('admin/tool/behat/cli/run.php')
            ->add('--tags=@'.$this->plugin->getComponent())
            ->add('--profile='.$input->getOption('profile'))
            ->add('--suite='.$input->getOption('suite'))
            ->add('--auto-rerun='.$input->getOption('auto-rerun'))
            ->add('--verbose')
            ->add('-vvv')
            ->setWorkingDirectory($this->moodle->directory)
            ->setTimeout(null);

        if ($output->isDecorated()) {
            $builder->add('--colors');
        }
        $process = $this->execute->passThroughProcess($builder->getProcess());

        $servers && $this->stopServerProcesses();

        if ($input->getOption('dump')) {
            $this->dumpFailures($output);
        }

        return $process->isSuccessful() ? 0 : 1;
    }

    /**
     * @param InputInterface $input
     */
    private function startServerProcesses(InputInterface $input)
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
        $cmd   = sprintf('docker run -d --rm --name=selenium --net=host --shm-size=2g -v %s:%s %s',
            $this->moodle->directory, $this->moodle->directory, $image);
        $docker = $this->execute->passThrough($cmd);
        if (!$docker->isSuccessful()) {
            throw new \RuntimeException('Can\'t start Selenium server');
        }

        // Start web server.
        $web = new Process('php -S localhost:8000', $this->moodle->directory);
        $web->setTimeout(0);
        $web->disableOutput();
        $web->start();
        $this->webserver = $web;

        // Need to wait for Selenium to start up. Not really sure how long that takes.
        usleep($this->seleniumWaitTime);
    }

    private function stopServerProcesses()
    {
        // Stop docker. This will also destroy container.
        $this->execute->mustRun('docker stop selenium');
        // Stop webserver.
        $this->webserver->stop();
    }

    private function dumpFailures(OutputInterface $output)
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
