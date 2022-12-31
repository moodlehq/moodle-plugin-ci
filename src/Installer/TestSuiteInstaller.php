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

namespace MoodlePluginCI\Installer;

use MoodlePluginCI\Bridge\Moodle;
use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Process\MoodleProcess;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * PHPUnit and Behat installer.
 */
class TestSuiteInstaller extends AbstractInstaller
{
    private Moodle$moodle;
    private MoodlePlugin $plugin;
    private Execute $execute;

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
    private function getBehatUtility(): string
    {
        return $this->moodle->directory . '/admin/tool/behat/cli/util_single_run.php';
    }

    public function install(): void
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

    public function stepCount(): int
    {
        return 2;
    }

    /**
     * Get all the processes to initialize Behat.
     *
     * @return Process[]
     */
    public function getBehatInstallProcesses(): array
    {
        if (!$this->plugin->hasBehatFeatures()) {
            return [];
        }

        // @see BehatCommand::execute()
        $this->addEnv('MOODLE_START_BEHAT_SERVERS', 'YES');

        return [
            new MoodleProcess([
                $this->getBehatUtility(),
                '--install',
            ]),
        ];
    }

    /**
     * Get all the processes to initialize PHPUnit.
     *
     * @return Process[]
     */
    public function getUnitTestInstallProcesses(): array
    {
        if (!$this->plugin->hasUnitTests()) {
            return [];
        }

        $this->getOutput()->debug('Initialize PHPUnit');

        return [
            new MoodleProcess([
                $this->moodle->directory . '/admin/tool/phpunit/cli/util.php',
                '--install',
            ]),
        ];
    }

    /**
     * Get all the post install processes.
     *
     * @return Process[]
     */
    public function getPostInstallProcesses(): array
    {
        $processes = [];

        if ($this->plugin->hasBehatFeatures()) {
            $this->getOutput()->debug('Enabling Behat');
            $processes[] = new MoodleProcess([
                $this->getBehatUtility(),
                '--enable',
                '--add-core-features-to-theme',
            ]);
        }
        if ($this->plugin->hasUnitTests()) {
            $this->getOutput()->debug('Build PHPUnit config');
            $processes[] = new MoodleProcess([
                $this->moodle->directory . '/admin/tool/phpunit/cli/util.php',
                '--buildconfig',
            ]);
            $processes[] = new MoodleProcess([
                $this->moodle->directory . '/admin/tool/phpunit/cli/util.php',
                '--buildcomponentconfigs',
            ]);
        }

        return $processes;
    }

    /**
     * Inject filter XML into the plugin's PHPUnit configuration file.
     */
    public function injectPHPUnitFilter(): void
    {
        $config = $this->plugin->directory . '/phpunit.xml';
        if (!is_file($config)) {
            return;
        }

        // If the plugin already has a tests/coverage.php file, then phpunit.xml filter/coverage
        // section is already configured following it. Nothing to do here.
        $coverage = $this->plugin->directory . '/tests/coverage.php';
        // If the file exists and we are Moodle >= 3.7.
        // TODO: Remove the branch condition when 3.6 becomes unsupported by moodle-local-ci.
        if ($this->moodle->getBranch() >= 37 && is_readable($coverage)) {
            return;
        }

        $files     = $this->getCoverageFiles();
        $filterXml = $this->getFilterXml($files);
        $subject   = file_get_contents($config);
        $count     = 0;

        // Replace existing filter.
        $contents = preg_replace('/<coverage>(.|\n)*<\/coverage>/m', trim($filterXml), $subject, 1, $count);
        // TODO: Remove this when 3.10 becomes unsupported by moodle-local-ci.
        if ($this->moodle->getBranch() < 311) {
            $contents = preg_replace('/<filter>(.|\n)*<\/filter>/m', trim($filterXml), $subject, 1, $count);
        }

        // Or if no existing filter, inject the filter.
        if ($count === 0) {
            $contents  = str_replace('</phpunit>', $filterXml . '</phpunit>', $subject, $count);
        }

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
    private function getCoverageFiles(): array
    {
        $finder = Finder::create()
            ->name('*.php')
            ->notName('coverage.php')
            ->notName('*_test.php')
            ->notName('version.php')
            ->notName('settings.php')
            ->notPath('lang')
            ->notPath('vendor')
            ->sortByName();

        $this->plugin->context = 'phpunit'; // Bit of a hack, but ensure we respect PHPUnit ignores.

        $files = $this->plugin->getRelativeFiles($finder);

        $this->plugin->context = ''; // Revert.

        return $this->removeDbFiles($this->plugin->directory . '/db', $files);
    }

    /**
     * Remove DB files that should not or cannot be covered by unit tests.
     *
     * @param string $dbPath
     * @param array  $files
     *
     * @return array
     */
    private function removeDbFiles(string $dbPath, array $files): array
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
            $key = array_search('db/' . $dbFile->getRelativePathname(), $files, true);

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
    private function getFilterXml(array $files): string
    {
        // Default (Moodle 3.11 and above) template (PHPUnit 9.5 and up).
        $template = <<<'XML'
    <coverage>
        <include>
            {{includes}}
        </include>
    </coverage>
XML;
        // Before Moodle 3.11 (PHPUnit < 9.5), it was filter & white-list.
        // TODO: Remove this when 3.10 becomes unsupported by moodle-local-ci.
        if ($this->moodle->getBranch() < 311) {
            $template = <<<'XML'
    <filter>
        <whitelist addUncoveredFilesFromWhitelist="true">
            {{includes}}
        </whitelist>
    </filter>
XML;
        }

        // Let's add the found php files to become code-coverage analysed.
        $includes = [];
        foreach ($files as $file) {
            $includes[] = sprintf('<file>%s</file>', $file);
        }
        $includes = implode("\n            ", $includes);

        return str_replace('{{includes}}', $includes, $template);
    }
}
