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

namespace MoodlePluginCI\Tests\Bridge;

use MoodlePluginCI\Bridge\MoodlePlugin;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class MoodlePluginTest extends MoodleTestCase
{
    public function testGetComponent()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertSame('local_ci', $plugin->getComponent());
    }

    public function testGetDependencies()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertSame(['mod_forum'], $plugin->getDependencies());
    }

    public function testHasUnitTests()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertTrue($plugin->hasUnitTests());
    }

    public function testNoUnitTests()
    {
        // Remove the only unit test file.
        $this->fs->remove($this->pluginDir.'/tests/lib_test.php');

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
        $this->fs->remove($this->pluginDir.'/tests/behat/login.feature');

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertFalse($plugin->hasBehatFeatures());
    }

    public function testGetThirdPartyLibraryPaths()
    {
        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = ['vendor.php', 'vendor', 'vendor_glob1.php', 'vendor_glob2.php'];
        $this->assertSame($expected, $plugin->getThirdPartyLibraryPaths());
    }

    public function testGetThirdPartyLibraryPathsError()
    {
        $this->expectException(\RuntimeException::class);

        // Overwrite third party libs XML with a broken one.
        $this->fs->copy(__DIR__.'/../Fixture/broken-thirdpartylibs.xml', $this->pluginDir.'/thirdpartylibs.xml', true);

        $plugin = new MoodlePlugin($this->pluginDir);
        $plugin->getThirdPartyLibraryPaths();
    }

    public function testGetIgnores()
    {
        $expected = ['filter' => [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ]];

        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($expected));

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertSame($expected['filter'], $plugin->getIgnores());
    }

    public function testGetFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin   = new MoodlePlugin($this->pluginDir);
        $files    = $plugin->getFiles($finder);
        $expected = [
            $this->pluginDir.'/classes/math.php',
            $this->pluginDir.'/db/access.php',
            $this->pluginDir.'/db/upgrade.php',
            $this->pluginDir.'/lang/en/local_ci.php',
            $this->pluginDir.'/lib.php',
            $this->pluginDir.'/tests/lib_test.php',
            $this->pluginDir.'/version.php',
        ];

        sort($files);

        $this->assertSame($expected, $files);
    }

    public function testGetRelativeFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php')->sortByName();

        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = [
            'classes/math.php',
            'db/access.php',
            'db/upgrade.php',
            'lang/en/local_ci.php',
            'lib.php',
            'tests/lib_test.php',
            'version.php',
        ];
        $this->assertSame($expected, $plugin->getRelativeFiles($finder));
    }
}
