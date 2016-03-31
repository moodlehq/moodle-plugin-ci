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
use Moodlerooms\MoodlePluginCI\Process\MoodleProcess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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

    /**
     * The location where the selenium.jar file is stored.
     *
     * @return string
     */
    private function getSeleniumJarPath()
    {
        return $this->moodle->directory.'/selenium.jar';
    }

    public function install()
    {
        $this->getOutput()->step('Initialize test suite');

        $this->execute->mustRunAll(array_merge(
            $this->getBehatInstallProcesses(),
            $this->getUnitTestInstallProcesses()
        ));

        $this->getOutput()->step('Building configs');

        $this->execute->mustRunAll($this->getPostInstallProcesses());

        $this->injectPHPUnitFilter();
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

        $curl = sprintf(
            'curl -o %s http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar',
            $this->getSeleniumJarPath()
        );

        return [
            new Process($curl, null, null, null, 120),
            new MoodleProcess(sprintf('%s --install', $this->getBehatUtility())),
        ];
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

        return [new MoodleProcess(sprintf('%s/admin/tool/phpunit/cli/util.php --install', $this->moodle->directory))];
    }

    /**
     * Get all the post install processes.
     *
     * @return Process[]
     */
    public function getPostInstallProcesses()
    {
        $processes = [];

        if ($this->plugin->hasBehatFeatures()) {
            $this->getOutput()->debug('Enabling Behat');

            $binDir = realpath(__DIR__.'/../../bin');

            $processes[] = new Process(sprintf('%s/start-selenium %s', $binDir, $this->getSeleniumJarPath()));
            $processes[] = new Process(sprintf('%s/start-web-server', $binDir), $this->moodle->directory);
            $processes[] = new MoodleProcess(sprintf('%s --enable', $this->getBehatUtility()));
        }
        if ($this->plugin->hasUnitTests()) {
            $this->getOutput()->debug('Build PHPUnit config');
            $processes[] = new MoodleProcess(sprintf('%s/admin/tool/phpunit/cli/util.php --buildconfig', $this->moodle->directory));
            $processes[] = new MoodleProcess(sprintf('%s/admin/tool/phpunit/cli/util.php --buildcomponentconfigs', $this->moodle->directory));
        }

        return $processes;
    }

    /**
     * Inject filter XML into the plugin's PHPUnit configuration file.
     */
    private function injectPHPUnitFilter()
    {
        $config = $this->plugin->directory.'/phpunit.xml';
        if (!is_file($config)) {
            return;
        }

        $files     = $this->getCoverageFiles();
        $filterXml = $this->getFilterXml($files);
        $contents  = str_replace('</phpunit>', $filterXml.'</phpunit>', file_get_contents($config), $count);

        if ($count !== 1) {
            throw new \RuntimeException('Failed to inject settings into plugin phpunit.xml file');
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile($config, $contents);
    }

    /**
     * Get all files we want to add to code coverage.
     *
     * @return array
     */
    private function getCoverageFiles()
    {
        $finder = Finder::create()
            ->name('*.php')
            ->notName('*_test.php')
            ->notName('version.php')
            ->notName('settings.php')
            ->notPath('lang')
            ->notPath('vendor');

        $files = $this->plugin->getFiles($finder);

        return $this->removeDbFiles($this->plugin->directory.'/db', $files);
    }

    /**
     * Remove DB files that should not or cannot be covered by unit tests.
     *
     * @param string $dbPath
     * @param array  $files
     *
     * @return array
     */
    private function removeDbFiles($dbPath, array $files)
    {
        if (!is_dir($dbPath)) {
            return $files;
        }
        /* @var SplFileInfo[] $dbFiles */
        $dbFiles = Finder::create()->files()->in($dbPath)->name('*.php')
            ->notName('caches.php')
            ->notName('events.php')
            ->notName('upgradelib.php');

        foreach ($dbFiles as $dbFile) {
            $key = array_search($dbFile->getRealPath(), $files);

            if ($key !== false) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Given a list of files, create the filter XML used by PHPUnit for code coverage.
     *
     * @param array $files
     *
     * @return string
     */
    private function getFilterXml(array $files)
    {
        $includes = [];
        foreach ($files as $file) {
            $includes[] = sprintf('<file>%s</file>', $file);
        }
        $includes = implode("\n            ", $includes);

        return <<<XML
    <filter>
        <whitelist addUncoveredFilesFromWhitelist="true">
            $includes
        </whitelist>
    </filter>

XML;
    }
}
