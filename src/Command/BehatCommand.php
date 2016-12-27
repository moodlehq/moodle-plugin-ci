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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Run Behat tests.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
            ->addOption('start-servers', null, InputOption::VALUE_NONE, 'Start Selenium and PHP servers')
            ->addOption('jar', null, InputOption::VALUE_REQUIRED, 'Path to Selenium Jar file', $jar)
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

        $servers && $this->startServerProcesses($input->getOption('jar'));

        $colors = '';
        if ($output->isDecorated()) {
            $colors = $this->moodle->getBranch() >= 31 ? '--colors' : '--ansi';
        }
        $config = $this->moodle->getBehatDataDirectory().'/behat/behat.yml';
        if (!file_exists($config)) {
            throw new \RuntimeException('Behat config file not found.  Behat must not have been installed.');
        }

        $process = $this->execute->passThrough(
            sprintf('%s/vendor/bin/behat %s --config %s --tags @%s', $this->moodle->directory, $colors, $config, $this->plugin->getComponent()),
            $this->moodle->directory
        );

        $servers && $this->stopServerProcesses();

        return $process->isSuccessful() ? 0 : 1;
    }

    private function startServerProcesses($seleniumJarFile)
    {
        if (!is_file($seleniumJarFile)) {
            throw new \InvalidArgumentException(sprintf('Invalid Selenium Jar file path: %s', $seleniumJarFile));
        }
        $selenium = new Process(sprintf('xvfb-run -a --server-args="-screen 0 1024x768x24" java -jar %s', $seleniumJarFile));
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
}
