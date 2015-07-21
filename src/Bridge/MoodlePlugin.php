<?php
/**
 * This file is part of the Moodle Plugin Travis CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodleTravisPlugin\Bridge;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Bridge to a Moodle plugin
 *
 * Inspects the contents of a Moodle plugin
 * and uses Moodle API to get information about
 * the plugin.  Very important, the plugin is not
 * installed into Moodle.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePlugin {
    /**
     * Absolute path to a Moodle plugin
     *
     * @var string
     */
    private $pathToPlugin;

    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @param Moodle $moodle
     * @param string $pathToPlugin Absolute path to a Moodle plugin
     */
    public function __construct(Moodle $moodle, $pathToPlugin) {
        $this->moodle       = $moodle;
        $this->pathToPlugin = $pathToPlugin;
    }

    /**
     * Loads the contents of a plugin's version.php
     *
     * @return \stdClass
     * @throws \Exception
     */
    protected function loadVersionFile() {
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
        require($versionFile);

        return $plugin;
    }

    /**
     * Get a plugin's component name.
     *
     * @return string
     * @throws \Exception
     */
    public function getComponent() {
        $plugin = $this->loadVersionFile();

        if (empty($plugin->component)) {
            throw new \Exception('The plugin must define the component in the version.php file.');
        }

        return $plugin->component;
    }

    /**
     * Get the absolute install directory path within Moodle
     *
     * @return string Absolute path, EG: /path/to/mod/forum
     */
    public function getInstallDirectory() {
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
     * Install the plugin into Moodle
     */
    public function installPluginIntoMoodle() {
        $directory = $this->getInstallDirectory();

        if (is_dir($directory)) {
            throw new \RuntimeException('Plugin is already installed in standard Moodle');
        }

        // Install the plugin.
        $fs = new Filesystem();
        $fs->mirror($this->pathToPlugin, $directory);
    }

    /**
     * Get the relative install directory path within Moodle
     *
     * @return string Relative path, EG: mod/forum
     */
    public function getRelativeInstallDirectory() {
        $path = $this->getInstallDirectory();

        return str_replace($this->moodle->pathToMoodle.'/', '', $path);
    }

    /**
     * Determine if the plugin has any PHPUnit tests
     *
     * @return bool
     */
    public function hasUnitTests() {
        $finder = new Finder();
        $result = $finder->files()->in($this->pathToPlugin)->path('tests')->name('*_test.php')->count();

        return ($result !== 0);
    }

    /**
     * Determine if the plugin has any Behat features
     *
     * @return bool
     */
    public function hasBehatFeatures() {
        $finder = new Finder();
        $result = $finder->files()->in($this->pathToPlugin)->path('tests/behat')->name('*.feature')->count();

        return ($result !== 0);
    }
}
