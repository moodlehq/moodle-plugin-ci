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
     * Wait this many microseconds for Selenium server to start/stop.
     *
     * @var int
     */
    public $seleniumWaitTime = 5000000;

    /**
     * @var Process[]
     */
    private $servers = [];

    protected function configure()
    {
        parent::configure();

        $jar = getenv('MOODLE_SELENIUM_JAR') !== false ? getenv('MOODLE_SELENIUM_JAR') : null;

        $this->setName('behat')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Behat profile to use', 'default')
            ->addOption('suite', null, InputOption::VALUE_REQUIRED, 'Behat suite to use (Moodle theme)', 'default')
            ->addOption('start-servers', null, InputOption::VALUE_NONE, 'Start Selenium and PHP servers')
            ->addOption('jar', null, InputOption::VALUE_REQUIRED, 'Path to Selenium Jar file', $jar)
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

        $servers && $this->startServerProcesses($input->getOption('jar'), $input);

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

    private function startServerProcesses($seleniumJarFile, InputInterface $input)
    {
        if (!is_file($seleniumJarFile)) {
            throw new \InvalidArgumentException(sprintf('Invalid Selenium Jar file path: %s', $seleniumJarFile));
        }
        $cmd = sprintf('xvfb-run -a --server-args="-screen 0 1024x768x24" java -jar %s', $seleniumJarFile);
        if ($input->getOption('profile') === 'chrome') {
            $driver = '/usr/lib/chromium-browser/chromedriver';
            if (!file_exists($driver)) {
                throw new \RuntimeException('chromedriver not found, please install it, see help docs');
            }
            $cmd .= ' -Dwebdriver.chrome.driver='.$driver;
        }

        $selenium = new Process($cmd);
        $selenium->setTimeout(0);
        $selenium->disableOutput();
        $selenium->start();

        $web = new Process('php -S localhost:8000', $this->moodle->directory);
        $web->setTimeout(0);
        $web->disableOutput();
        $web->start();

        $this->servers = [$selenium, $web];

        // Need to wait for Selenium to start up.  Not really sure how long that takes.
        usleep($this->seleniumWaitTime);
    }

    private function stopServerProcesses()
    {
        $curl = 'curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer';
        $this->execute->run(new Process($curl, null, null, null, 120));

        // Wait for Selenium to shutdown.
        usleep($this->seleniumWaitTime);

        foreach ($this->servers as $process) {
            $process->stop();
        }
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
}
