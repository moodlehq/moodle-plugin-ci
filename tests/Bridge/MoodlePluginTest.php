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

namespace Moodlerooms\MoodlePluginCI\Tests\Bridge;

use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
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
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals('local_travis', $plugin->getComponent());
    }

    public function getRelativeInstallDirectory()
    {
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals('local/travis', $plugin->getRelativeInstallDirectory());
    }

    public function testInstallPluginIntoMoodle()
    {
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $plugin->installPluginIntoMoodle();

        $this->assertFileExists($this->moodleDir.'/local/travis/version.php');
    }

    public function testHasUnitTests()
    {
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertTrue($plugin->hasUnitTests());
    }

    public function testNoUnitTests()
    {
        // Remove the only unit test file.
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/phpunit_test.php');

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertFalse($plugin->hasUnitTests());
    }

    public function testHasBehatFeatures()
    {
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertTrue($plugin->hasBehatFeatures());
    }

    public function testNoBehatFeatures()
    {
        // Remove the only unit test file.
        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat/login.feature');

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertFalse($plugin->hasBehatFeatures());
    }

    public function testGetThirdPartyLibraryPaths()
    {
        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals(['vendor.php', 'vendor'], $plugin->getThirdPartyLibraryPaths());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetThirdPartyLibraryPathsError()
    {
        // Overwrite third party libs XML with a broken one.
        $fs = new Filesystem();
        $fs->copy(__DIR__.'/../Fixture/broken-thirdpartylibs.xml', $this->pluginDir.'/thirdpartylibs.xml', true);

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $plugin->getThirdPartyLibraryPaths();
    }

    public function testGetIgnores()
    {
        $expected = [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.travis-ignore.yml', Yaml::dump($expected));

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals($expected, $plugin->getIgnores());
    }

    public function testGetFiles()
    {

        // Create an ignore file for better testing
        $ignores = ['notNames' => ['version.php'], 'notPaths' => ['test']];

        $fs = new Filesystem();
        $fs->dumpFile($this->pluginDir.'/.travis-ignore.yml', Yaml::dump($ignores));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals([$this->pluginDir.'/lib.php'], $plugin->getFiles($finder));
    }

    public function testGetRelativeFiles()
    {
        $finder = new Finder();
        $finder->name('*.php')->sortByName();

        $plugin = new DummyMoodlePlugin(new DummyMoodle($this->moodleDir), $this->pluginDir);
        $this->assertEquals(['lib.php', 'tests/phpunit_test.php', 'version.php'], $plugin->getRelativeFiles($finder));
    }
}
