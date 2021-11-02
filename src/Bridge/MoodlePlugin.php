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

namespace MoodlePluginCI\Bridge;

use MoodlePluginCI\Parser\CodeParser;
use MoodlePluginCI\Parser\StatementFilter;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Bridge to a Moodle plugin.
 *
 * Inspects the contents of a Moodle plugin
 * and uses Moodle API to get information about
 * the plugin.  Very important, the plugin may not
 * be installed into Moodle.
 */
class MoodlePlugin
{
    /**
     * Absolute path to a Moodle plugin.
     *
     * @var string
     */
    public $directory;

    /**
     * The context in which we are running.
     *
     * EG: this is the command name and it should only
     * be used for reading configs.
     *
     * @var string|null
     */
    public $context = '';

    /**
     * Cached component string.
     *
     * @var string
     */
    protected $component;

    /**
     * Cached dependencies.
     *
     * @var array
     */
    protected $dependencies;

    /**
     * Cached subplugin types.
     *
     * @var string[]
     */
    protected $subpluginTypes;

    /**
     * @param string $directory Absolute path to a Moodle plugin
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Get a plugin's component name.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getComponent()
    {
        // Simple cache.
        if (!empty($this->component)) {
            return $this->component;
        }

        $filter = new StatementFilter();
        $parser = new CodeParser();

        $notFound   = 'The plugin must define the $plugin->component in the version.php file.';
        $statements = $parser->parseFile($this->directory.'/version.php');

        try {
            $assign = $filter->findFirstPropertyFetchAssignment($statements, 'plugin', 'component', $notFound);
        } catch (\Exception $e) {
            $assign = $filter->findFirstPropertyFetchAssignment($statements, 'module', 'component', $notFound);
        }

        if (!$assign->expr instanceof String_) {
            throw new \RuntimeException('The $plugin->component must be assigned to a string in the version.php file.');
        }
        $this->component = $assign->expr->value;

        return $this->component;
    }

    /**
     * Get a plugin's dependencies.
     *
     * @return array
     */
    public function getDependencies()
    {
        // Simple cache.
        if (is_array($this->dependencies)) {
            return $this->dependencies;
        }
        $this->dependencies = [];

        $filter = new StatementFilter();
        $parser = new CodeParser();

        $statements = $parser->parseFile($this->directory.'/version.php');

        try {
            $assign = $filter->findFirstPropertyFetchAssignment($statements, 'plugin', 'dependencies');
        } catch (\Exception $e) {
            try {
                $assign = $filter->findFirstPropertyFetchAssignment($statements, 'module', 'dependencies');
            } catch (\Exception $e) {
                return $this->dependencies;
            }
        }

        if (!$assign->expr instanceof Array_) {
            throw new \RuntimeException('The $plugin->dependencies must be assigned to an array in the version.php file.');
        }
        $this->dependencies = $filter->arrayStringKeys($assign->expr);

        return $this->dependencies;
    }

    /**
     * Get a plugin's subplugin types.
     *
     * @return string[]
     */
    public function getSubpluginTypes()
    {
        // Simple cache.
        if (is_array($this->subpluginTypes)) {
            return $this->subpluginTypes;
        }
        $this->subpluginTypes = [];

        $subpluginsJsonLocation = $this->directory.'/db/subplugins.json';
        if (file_exists($subpluginsJsonLocation)) {
            $subpluginData = json_decode(file_get_contents($subpluginsJsonLocation));
            if ($subpluginData && property_exists($subpluginData, 'plugintypes')) {
                $this->subpluginTypes = array_keys((array) $subpluginData->plugintypes);
            }
        }

        return $this->subpluginTypes;
    }

    /**
     * Determine if the plugin has any PHPUnit tests.
     *
     * @return bool
     */
    public function hasUnitTests()
    {
        $result = Finder::create()->files()->in($this->directory)->path('tests')->name('*_test.php')->count();

        return $result !== 0;
    }

    /**
     * Determine if the plugin has any Behat features.
     *
     * @return bool
     */
    public function hasBehatFeatures()
    {
        $result = Finder::create()->files()->in($this->directory)->path('tests/behat')->name('*.feature')->count();

        return $result !== 0;
    }

    /**
     * Determine if the plugin has any files with a given name pattern.
     *
     * @param string $pattern File name pattern
     *
     * @return bool
     */
    public function hasFilesWithName($pattern)
    {
        $result = $this->getFiles(Finder::create()->name($pattern));

        return count($result) !== 0;
    }

    /**
     * Determine if the plugin has any Nodejs dependencies.
     *
     * @return bool
     */
    public function hasNodeDependencies()
    {
        return is_file($this->directory.'/package.json');
    }

    /**
     * Get paths to 3rd party libraries within the plugin.
     *
     * @return array
     */
    public function getThirdPartyLibraryPaths()
    {
        $xmlFile = $this->directory.'/thirdpartylibs.xml';
        if (!is_file($xmlFile)) {
            return [];
        }
        $vendors = new Vendors($xmlFile);

        return $vendors->getRelativeVendorPaths();
    }

    /**
     * Get ignore file information.
     *
     * @return array
     */
    public function getIgnores()
    {
        $configFile = $this->directory.'/.moodle-plugin-ci.yml';

        if (!is_file($configFile)) {
            return [];
        }

        $config = Yaml::parse(file_get_contents($configFile));

        // Search for context (AKA command) specific filter first.
        if (!empty($this->context) && array_key_exists('filter-'.$this->context, $config)) {
            return $config['filter-'.$this->context];
        }

        return array_key_exists('filter', $config) ? $config['filter'] : [];
    }

    /**
     * Get a list of plugin files.
     *
     * @param Finder $finder The finder to use, can be pre-configured
     *
     * @return array Of files
     */
    public function getFiles(Finder $finder)
    {
        $finder->files()->in($this->directory)->ignoreUnreadableDirs();

        // Ignore third party libraries.
        foreach ($this->getThirdPartyLibraryPaths() as $libPath) {
            $finder->notPath($libPath);
        }

        // Extra ignores for CI.
        $ignores = $this->getIgnores();

        if (!empty($ignores['notPaths'])) {
            foreach ($ignores['notPaths'] as $notPath) {
                $finder->notPath($notPath);
            }
        }
        if (!empty($ignores['notNames'])) {
            foreach ($ignores['notNames'] as $notName) {
                $finder->notName($notName);
            }
        }

        $files = [];
        foreach ($finder as $file) {
            /* @var \SplFileInfo $file */
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * Get a list of plugin files, with paths relative to the plugin itself.
     *
     * @param Finder $finder The finder to use, can be pre-configured
     *
     * @return array Of files
     */
    public function getRelativeFiles(Finder $finder)
    {
        $files = [];
        foreach ($this->getFiles($finder) as $file) {
            $files[] = str_replace($this->directory.'/', '', $file);
        }

        return $files;
    }
}
