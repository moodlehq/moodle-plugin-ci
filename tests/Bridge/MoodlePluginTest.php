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

namespace Moodlerooms\MoodlePluginCI\Tests\Bridge;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePluginTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;
    private $pluginDir;
    private $moodleDir;

    protected function setUp()
    {
        $this->tempDir   = sys_get_temp_dir().'/moodle-plugin-ci/BridgeMoodlePluginTest'.time();
        $this->pluginDir = $this->tempDir.'/moodle-local_travis';
        $this->moodleDir = $this->tempDir.'/moodle';

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
        $fs->mkdir($this->moodleDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);

        // Paths we get later from PHP use the "real" path.
        $this->tempDir   = realpath($this->tempDir);
        $this->pluginDir = realpath($this->pluginDir);
        $this->moodleDir = realpath($this->moodleDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testGetComponent()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertEquals('local_travis', $plugin->getComponent());
    }

    public function testGetDependencies()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertEquals(['mod_forum'], $plugin->getDependencies());
    }

    public function testHasUnitTests()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertTrue($plugin->hasUnitTests());
    }

    public function testNoUnitTests()
    {
        // Remove the only unit test file.
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/lib_test.php');

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertFalse($plugin->hasUnitTests());
    }

    public function testHasBehatFeatures()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertTrue($plugin->hasBehatFeatures());
    }

    public function testNoBehatFeatures()
    {
        // Remove the only unit test file.
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat/login.feature');

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertFalse($plugin->hasBehatFeatures());
    }

    public function testGetThirdPartyLibraryPaths()
    {
        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = ['vendor.php', 'vendor', 'vendor_glob1.php', 'vendor_glob2.php'];
        $this->assertEquals($expected, $plugin->getThirdPartyLibraryPaths());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetThirdPartyLibraryPathsError()
    {
        // Overwrite third party libs XML with a broken one.
        $fs = new Filesystem();
        $fs->copy(__DIR__.'/../Fixture/broken-thirdpartylibs.xml', $this->pluginDir.'/thirdpartylibs.xml', true);

        $plugin = new MoodlePlugin($this->pluginDir);
        $plugin->getThirdPartyLibraryPaths();
    }

    public function testGetIgnores()
    {
        $expected = ['filter' => [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ]];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($expected));

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertEquals($expected['filter'], $plugin->getIgnores());
    }

    public function testGetFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = [
            $this->pluginDir.'/classes/math.php',
            $this->pluginDir.'/db/access.php',
            $this->pluginDir.'/lang/en/local_travis.php',
            $this->pluginDir.'/lib.php',
            $this->pluginDir.'/tests/lib_test.php',
            $this->pluginDir.'/version.php',
        ];
        $this->assertEquals($expected, $plugin->getFiles($finder));
    }

    public function testGetRelativeFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php')->sortByName();

        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = [
            'classes/math.php',
            'db/access.php',
            'lang/en/local_travis.php',
            'lib.php',
            'tests/lib_test.php',
            'version.php',
        ];
        $this->assertEquals($expected, $plugin->getRelativeFiles($finder));
    }
}
