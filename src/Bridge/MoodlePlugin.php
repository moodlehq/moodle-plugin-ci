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

namespace Moodlerooms\MoodlePluginCI\Bridge;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Parser;
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
    public $directory;

    /**
     * @var Moodle
     */
    protected $moodle;

    /**
     * Cached component string.
     *
     * @var string
     */
    protected $component;

    /**
     * @param string $directory Absolute path to a Moodle plugin
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Loads the contents of a plugin file.
     *
     * @param string $relativePath Relative file path
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function loadFile($relativePath)
    {
        $file = $this->directory.'/'.$relativePath;
        if (!file_exists($file)) {
            throw new \RuntimeException("Failed to find the plugin's '$relativePath' file.");
        }

        return file_get_contents($file);
    }

    /**
     * Parse a plugin file.
     *
     * @param string $relativePath Relative file path
     *
     * @return \PhpParser\Node[]
     *
     * @throws \Exception
     */
    protected function parseFile($relativePath)
    {
        // This looks overkill and it is, but works very well.
        $parser = new Parser(new Lexer());

        try {
            return $parser->parse($this->loadFile($relativePath));
        } catch (Error $e) {
            throw new \RuntimeException("Failed to parse $relativePath file due to parse error: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get a plugin's component name.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getComponent()
    {
        // Simple cache.
        if (!empty($this->component)) {
            return $this->component;
        }

        $statements = $this->parseFile('version.php');

        // We are looking for statements that look like this:
        // $plugin->component = 'local_travis';
        // $module->component = 'local_travis';
        foreach ($statements as $statement) {
            // Looking for an assignment statement.
            if ($statement instanceof Assign) {
                $variable   = $statement->var; // Left side of equals.
                $expression = $statement->expr; // Right side of equals.

                // Looking for "$anything->component" being set to a string.
                if ($variable instanceof PropertyFetch && $variable->name === 'component' && $expression instanceof String_) {
                    $this->component = $expression->value;
                    break;
                }
            }
        }

        if (empty($this->component)) {
            throw new \RuntimeException('The plugin must define the component in the version.php file.');
        }

        return $this->component;
    }

    /**
     * Determine if the plugin has any PHPUnit tests.
     *
     * @return bool
     */
    public function hasUnitTests()
    {
        $finder = new Finder();
        $result = $finder->files()->in($this->directory)->path('tests')->name('*_test.php')->count();

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
        $result = $finder->files()->in($this->directory)->path('tests/behat')->name('*.feature')->count();

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
        $thirdPartyXML = $this->directory.'/thirdpartylibs.xml';

        if (!is_file($thirdPartyXML)) {
            return $paths;
        }
        $xml = simplexml_load_file($thirdPartyXML);
        foreach ($xml->xpath('/libraries/library/location') as $location) {
            $location = (string) trim($location, '/');

            // Accept only correct paths from XML files.
            if (file_exists(dirname($this->directory.'/'.$location))) {
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
        $ignoreFile = $this->directory.'/.travis-ignore.yml';

        if (!is_file($ignoreFile)) {
            return [];
        }

        return Yaml::parse($ignoreFile);
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
            $files[] = $file->getRealpath();
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
