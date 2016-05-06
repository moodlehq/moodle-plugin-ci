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

namespace Moodlerooms\MoodlePluginCI\Tests\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\ConfigDumper;
use Moodlerooms\MoodlePluginCI\Installer\PluginInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginInstallerTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir().'/moodle-plugin-ci/PluginInstallerTest'.time();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testInstall()
    {
        $fixture   = __DIR__.'/../Fixture/moodle-local_travis';
        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installer->install();

        $this->assertEquals($installer->stepCount(), $installer->getOutput()->getStepCount());

        $installDir = $this->tempDir.'/local/travis';

        $this->assertEquals($installDir, $plugin->directory, 'Plugin directory should be absolute path after install');
        $this->assertEquals(['PLUGIN_DIR' => $installDir], $installer->getEnv());
    }

    public function testInstallPluginIntoMoodle()
    {
        $fixture    = realpath(__DIR__.'/../Fixture/moodle-local_travis');
        $plugin     = new MoodlePlugin($fixture);
        $installer  = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installDir = $installer->installPluginIntoMoodle($plugin);

        $this->assertTrue(is_dir($installDir));

        $finder = new Finder();
        $finder->files()->in($fixture);

        /* @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $path = str_replace($fixture, $this->tempDir.'/local/travis', $file->getPathname());

            $this->assertFileExists($path);
            $this->assertFileEquals($file->getPathname(), $path);
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInstallPluginIntoMoodleAlreadyExists()
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->tempDir.'/local/travis');

        $fixture   = realpath(__DIR__.'/../Fixture/moodle-local_travis');
        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installer->installPluginIntoMoodle($plugin);
    }

    public function testCreateIgnoreFile()
    {
        $filename = $this->tempDir.'/.moodle-plugin-ci.yml';
        $expected = ['filter' => [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ]];

        $dumper = new ConfigDumper();
        $dumper->addSection('filter', 'notPaths', ['foo/bar', 'very/bad.php']);
        $dumper->addSection('filter', 'notNames', ['*-m.js', 'bad.php']);

        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), new MoodlePlugin($this->tempDir), '', $dumper);
        $installer->createConfigFile($filename);

        $this->assertFileExists($filename);
        $this->assertEquals($expected, Yaml::parse($filename));
    }

    public function testScanForPlugins()
    {
        $fixture = __DIR__.'/../Fixture/moodle-local_travis';

        $fs = new Filesystem();
        $fs->mirror($fixture, $this->tempDir.'/moodle-local_travis');

        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, $this->tempDir, new ConfigDumper());

        $plugins = $installer->scanForPlugins();
        $this->assertInstanceOf('Moodlerooms\MoodlePluginCI\Bridge\MoodlePluginCollection', $plugins);
        $this->assertCount(1, $plugins);
    }
}
