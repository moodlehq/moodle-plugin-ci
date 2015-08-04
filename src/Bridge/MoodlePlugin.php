<?php
/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Bridge;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Bridge to a Moodle plugin.
 *
 * Inspects the contents of a Moodle plugin
 * and uses Moodle API to get information about
 * the plugin.  Very important, the plugin may not
 * be installed into Moodle.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePlugin
{
    /**
     * Absolute path to a Moodle plugin.
     *
     * @var string
     */
    protected $pathToPlugin;

    /**
     * @var Moodle
     */
    protected $moodle;

    /**
     * @param Moodle $moodle
     * @param string $pathToPlugin Absolute path to a Moodle plugin
     */
    public function __construct(Moodle $moodle, $pathToPlugin)
    {
        $this->moodle       = $moodle;
        $this->pathToPlugin = $pathToPlugin;
    }

    /**
     * Loads the contents of a plugin's version.php.
     *
     * @return \stdClass
     * @throws \Exception
     */
    protected function loadVersionFile()
    {
        // Need config because of MOODLE_INTERNAL, etc.
        $this->moodle->requireConfig();

        $plugin = new \stdClass();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $module = $plugin; // Some define $module in version file.

        $versionFile = $this->pathToPlugin.'/version.php';
        if (!file_exists($versionFile)) {
            throw new \Exception('Failed to find the plugin version.php file.  All plugins should have this file.');
        }

        /** @noinspection PhpIncludeInspection */
        require $versionFile;

        return $plugin;
    }

    /**
     * Get a plugin's component name.
     *
     * @return string
     * @throws \Exception
     */
    public function getComponent()
    {
        $plugin = $this->loadVersionFile();

        if (empty($plugin->component)) {
            throw new \Exception('The plugin must define the component in the version.php file.');
        }

        return $plugin->component;
    }

    /**
     * Get the absolute install directory path within Moodle.
     *
     * @return string Absolute path, EG: /path/to/mod/forum
     */
    public function getInstallDirectory()
    {
        $this->moodle->requireConfig();

        $component = $this->getComponent();

        /** @noinspection PhpUndefinedClassInspection */
        list($type, $name) = \core_component::normalize_component($component);
        /** @noinspection PhpUndefinedClassInspection */
        $types = \core_component::get_plugin_types();

        if (!array_key_exists($type, $types)) {
            throw new \InvalidArgumentException(sprintf('The component %s has an unknown plugin type of %s', $component, $type));
        }

        return $types[$type].'/'.$name;
    }

    /**
     * Get the relative install directory path within Moodle.
     *
     * @return string Relative path, EG: mod/forum
     */
    public function getRelativeInstallDirectory()
    {
        $path = $this->getInstallDirectory();

        return str_replace($this->moodle->pathToMoodle.'/', '', $path);
    }

    /**
     * Install the plugin into Moodle.
     */
    public function installPluginIntoMoodle()
    {
        $directory = $this->getInstallDirectory();

        if (is_dir($directory)) {
            throw new \RuntimeException('Plugin is already installed in standard Moodle');
        }

        // Install the plugin.
        $fs = new Filesystem();
        $fs->mirror($this->pathToPlugin, $directory);
    }

    /**
     * Determine if the plugin has any PHPUnit tests.
     *
     * @return bool
     */
    public function hasUnitTests()
    {
        $finder = new Finder();
        $result = $finder->files()->in($this->pathToPlugin)->path('tests')->name('*_test.php')->count();

        return ($result !== 0);
    }

    /**
     * Determine if the plugin has any Behat features.
     *
     * @return bool
     */
    public function hasBehatFeatures()
    {
        $finder = new Finder();
        $result = $finder->files()->in($this->pathToPlugin)->path('tests/behat')->name('*.feature')->count();

        return ($result !== 0);
    }

    /**
     * Get paths to 3rd party libraries within the plugin.
     *
     * @return array
     */
    public function getThirdPartyLibraryPaths()
    {
        $paths         = [];
        $thirdPartyXML = $this->pathToPlugin.'/thirdpartylibs.xml';

        if (!is_file($thirdPartyXML)) {
            return $paths;
        }
        $xml = simplexml_load_file($thirdPartyXML);
        foreach ($xml->xpath('/libraries/library/location') as $location) {
            $location = (string) trim($location, '/');

            // Accept only correct paths from XML files.
            if (file_exists(dirname($this->pathToPlugin.'/'.$location))) {
                $paths[] = $location;
            } else {
                throw new \RuntimeException('The plugin thirdpartylibs.xml contains a non-existent path: '.$location);
            }
        }

        return $paths;
    }

    /**
     * Get ignore file information.
     *
     * @return array
     */
    public function getIgnores()
    {
        $ignoreFile = $this->pathToPlugin.'/.travis-ignore.yml';

        if (!is_file($ignoreFile)) {
            return [];
        }

        return Yaml::parse($ignoreFile);
    }

    /**
     * Get a list of plugin files.
     *
     * @param Finder $finder The finder to use, can be pre-configured
     * @return array Of files
     */
    public function getFiles(Finder $finder)
    {
        $finder->files()->in($this->pathToPlugin)->ignoreUnreadableDirs();

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
            /** @var \SplFileInfo $file */
            $files[] = $file->getRealpath();
        }

        return $files;
    }

    /**
     * Get a list of plugin files, with paths relative to the plugin itself.
     *
     * @param Finder $finder The finder to use, can be pre-configured
     * @return array Of files
     */
    public function getRelativeFiles(Finder $finder)
    {
        $files = [];
        foreach ($this->getFiles($finder) as $file) {
            $files[] = str_replace($this->pathToPlugin.'/', '', $file);
        }

        return $files;
    }
}
