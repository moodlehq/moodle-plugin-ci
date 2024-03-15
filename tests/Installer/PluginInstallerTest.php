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

namespace MoodlePluginCI\Tests\Installer;

use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Bridge\MoodlePluginCollection;
use MoodlePluginCI\Installer\ConfigDumper;
use MoodlePluginCI\Installer\PluginInstaller;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\FilesystemTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class PluginInstallerTest extends FilesystemTestCase
{
    public function testInstall(): void
    {
        $fixture   = __DIR__ . '/../Fixture/moodle-local_ci';
        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installer->install();

        $this->assertSame($installer->stepCount(), $installer->getOutput()->getStepCount());

        $installDir = $this->tempDir . '/local/travis';

        $this->assertSame($installDir, $plugin->directory, 'Plugin directory should be absolute path after install');
        $this->assertSame(['PLUGIN_DIR' => $installDir], $installer->getEnv());
    }

    public function testInstallPluginIntoMoodle(): void
    {
        $fixture    = realpath(__DIR__ . '/../Fixture/moodle-local_ci');
        $plugin     = new MoodlePlugin($fixture);
        $installer  = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installDir = $installer->installPluginIntoMoodle($plugin);

        $this->assertTrue(is_dir($installDir));

        $finder = new Finder();
        $finder->files()->in($fixture);

        /* @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $path = str_replace($fixture, $this->tempDir . '/local/travis', $file->getPathname());

            $this->assertFileExists($path);
            $this->assertFileEquals($file->getPathname(), $path);
        }
    }

    public function testInstallPluginIntoMoodleAlreadyExists(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->fs->mkdir($this->tempDir . '/local/travis');

        $fixture   = realpath(__DIR__ . '/../Fixture/moodle-local_ci');
        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, '', new ConfigDumper());
        $installer->installPluginIntoMoodle($plugin);
    }

    public function testCreateIgnoreFile(): void
    {
        $filename = $this->tempDir . '/.moodle-plugin-ci.yml';
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
        $this->assertSame($expected, Yaml::parse(file_get_contents($filename)));
    }

    public function testScanForPlugins(): void
    {
        $fixture = __DIR__ . '/../Fixture/moodle-local_ci';

        $this->fs->mirror($fixture, $this->tempDir . '/moodle-local_ci');

        $plugin    = new MoodlePlugin($fixture);
        $installer = new PluginInstaller(new DummyMoodle($this->tempDir), $plugin, $this->tempDir, new ConfigDumper());

        $plugins = $installer->scanForPlugins();
        $this->assertInstanceOf(MoodlePluginCollection::class, $plugins);
        $this->assertCount(1, $plugins);
    }
}
