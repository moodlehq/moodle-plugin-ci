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

namespace Moodlerooms\MoodlePluginCI\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

/**
 * PHPUnit and Behat installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TestSuiteInstaller extends AbstractInstaller
{
    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @var MoodlePlugin
     */
    private $plugin;

    /**
     * @var Execute
     */
    private $execute;

    public function __construct(Moodle $moodle, MoodlePlugin $plugin, Execute $execute)
    {
        $this->moodle  = $moodle;
        $this->plugin  = $plugin;
        $this->execute = $execute;
    }

    /**
     * Find the correct Behat utility script in Moodle.
     *
     * @return string
     */
    private function getBehatUtility()
    {
        // Moodle 2.9 or later use this one.
        $behatUtility = $this->moodle->directory.'/admin/tool/behat/cli/util_single_run.php';
        if (!file_exists($behatUtility)) {
            // Moodle 2.8 or earlier use this one.
            $behatUtility = $this->moodle->directory.'/admin/tool/behat/cli/util.php';
        }

        return $behatUtility;
    }

    public function install()
    {
        $this->getOutput()->step('Initialize test suite');

        $this->execute->mustRunAll(array_merge(
            $this->getBehatInstallProcesses(),
            $this->getUnitTestInstallProcesses()
        ));

        $this->getOutput()->step('Building configs');

        $this->execute->mustRunAll(array_merge(
            $this->getPostBehatInstallProcesses(),
            $this->getPostUnitTestInstallProcesses()
        ));
    }

    public function stepCount()
    {
        return 2;
    }

    /**
     * Get all the processes to initialize Behat.
     *
     * @return Process[]
     */
    public function getBehatInstallProcesses()
    {
        if (!$this->plugin->hasBehatFeatures()) {
            return [];
        }

        $this->getOutput()->debug('Download Selenium, start servers and initialize Behat');

        $jar    = $this->moodle->directory.'/selenium.jar';
        $curl   = sprintf('curl -o %s http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar', $jar);
        $binDir = realpath(__DIR__.'/../../bin');

        return [
            new Process($curl, null, null, null, 120),
            new Process(sprintf('%s/start-selenium %s', $binDir, $jar)),
            new Process(sprintf('%s/start-phantom-js', $binDir)),
            new Process(sprintf('%s/start-web-server', $binDir), $this->moodle->directory),
            new Process(sprintf('php %s --install', $this->getBehatUtility()), null, null, null, null),
        ];
    }

    /**
     * Get all the post install processes for Behat.
     *
     * @return Process[]
     */
    public function getPostBehatInstallProcesses()
    {
        if (!$this->plugin->hasBehatFeatures()) {
            return [];
        }

        $this->getOutput()->debug('Enabling Behat');

        return [new Process(sprintf('php %s --enable', $this->getBehatUtility()))];
    }

    /**
     * Get all the processes to initialize PHPUnit.
     *
     * @return Process[]
     */
    public function getUnitTestInstallProcesses()
    {
        if (!$this->plugin->hasUnitTests()) {
            return [];
        }

        $this->getOutput()->debug('Initialize PHPUnit');

        $process = new Process(sprintf('php %s/admin/tool/phpunit/cli/util.php --install', $this->moodle->directory));
        $process->setTimeout(null);

        return [$process];
    }

    /**
     * Get all the post install processes for PHPUnit.
     *
     * @return Process[]
     */
    public function getPostUnitTestInstallProcesses()
    {
        if (!$this->plugin->hasUnitTests()) {
            return [];
        }

        $this->getOutput()->debug('Build PHPUnit config');

        return [new Process(sprintf('php %s/admin/tool/phpunit/cli/util.php --buildconfig', $this->moodle->directory))];
    }
}
