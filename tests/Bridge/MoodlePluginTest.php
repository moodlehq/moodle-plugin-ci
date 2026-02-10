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

    public function testGetSubpluginTypes()
    {
        $plugintypes = ['subplugin' => 'some/plugin/dir'];
        file_put_contents($this->pluginDir . '/db/subplugins.json', json_encode(['plugintypes' => $plugintypes]));
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertSame(array_keys($plugintypes), $plugin->getSubpluginTypes());
    }

    public function testHasUnitTests()
    {
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertTrue($plugin->hasUnitTests());
    }

    public function testHasPhpUnitConfig()
    {
        // Our plugins doesn't have a phpunit.xml file.
        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertFalse($plugin->hasPhpUnitConfig());

        // Let's create one.
        $this->fs->dumpFile($this->pluginDir . '/phpunit.xml', '');
        $this->assertTrue($plugin->hasPhpUnitConfig());
    }

    public function testNoUnitTests()
    {
        // Remove the only unit test file.
        $this->fs->remove($this->pluginDir . '/tests/lib_test.php');

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
        $this->fs->remove($this->pluginDir . '/tests/behat/login.feature');

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
        $this->fs->copy(__DIR__ . '/../Fixture/broken-thirdpartylibs.xml', $this->pluginDir . '/thirdpartylibs.xml', true);

        $plugin = new MoodlePlugin($this->pluginDir);
        $plugin->getThirdPartyLibraryPaths();
    }

    public function testGetIgnores()
    {
        $expected = ['filter' => [
            'notPaths' => ['foo/bar', 'very/bad.php'],
            'notNames' => ['*-m.js', 'bad.php'],
        ]];

        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($expected));

        $plugin = new MoodlePlugin($this->pluginDir);
        $this->assertSame($expected['filter'], $plugin->getIgnores());
    }

    public function testGetFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin   = new MoodlePlugin($this->pluginDir);
        $files    = $plugin->getFiles($finder);
        $expected = [
            $this->pluginDir . '/classes/math.php',
            $this->pluginDir . '/classes/output/mobile.php',
            $this->pluginDir . '/db/access.php',
            $this->pluginDir . '/db/mobile.php',
            $this->pluginDir . '/db/upgrade.php',
            $this->pluginDir . '/lang/en/local_ci.php',
            $this->pluginDir . '/lib.php',
            $this->pluginDir . '/tests/lib_test.php',
            $this->pluginDir . '/version.php',
        ];

        sort($files);

        $this->assertSame($expected, $files);
    }

    public function testGetFilesWithSubdirectoryNotPaths()
    {
        // Create a subplugin directory with its own config.
        $subDir = $this->pluginDir . '/subtype/mysub';
        $this->fs->mkdir($subDir . '/vendor');
        $this->fs->dumpFile($subDir . '/lib.php', '<?php // Subplugin lib.');
        $this->fs->dumpFile($subDir . '/vendor/dep.php', '<?php // Vendor file to exclude.');

        // Subplugin config excludes 'vendor' path.
        $subConfig = ['filter' => ['notPaths' => ['vendor']]];
        $this->fs->dumpFile($subDir . '/.moodle-plugin-ci.yml', Yaml::dump($subConfig));

        // Main plugin config excludes 'ignore' path and 'ignore_name.php' name.
        $mainConfig = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($mainConfig));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin = new MoodlePlugin($this->pluginDir);
        $files  = $plugin->getFiles($finder);

        // The subplugin's lib.php should be present.
        $this->assertContains(realpath($subDir . '/lib.php'), $files);

        // The subplugin's vendor/dep.php should be excluded by the subplugin config.
        $this->assertNotContains(realpath($subDir . '/vendor/dep.php'), $files);
    }

    public function testGetFilesWithSubdirectoryContextFilter()
    {
        $subDir = $this->pluginDir . '/subtype/mysub';
        $this->fs->mkdir($subDir);
        $this->fs->dumpFile($subDir . '/excluded.php', '<?php // Should be excluded.');
        $this->fs->dumpFile($subDir . '/included.php', '<?php // Should be included.');

        // Context-specific filter for 'phpcs' command.
        $subConfig = [
            'filter'       => ['notPaths' => ['nonexistent']],
            'filter-phpcs' => ['notNames' => ['excluded.php']],
        ];
        $this->fs->dumpFile($subDir . '/.moodle-plugin-ci.yml', Yaml::dump($subConfig));

        // Main plugin config excludes 'ignore' path and 'ignore_name.php' name.
        $mainConfig = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($mainConfig));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin          = new MoodlePlugin($this->pluginDir);
        $plugin->context = 'phpcs';
        $files           = $plugin->getFiles($finder);

        $this->assertNotContains(realpath($subDir . '/excluded.php'), $files);
        $this->assertContains(realpath($subDir . '/included.php'), $files);
    }

    public function testGetFilesWithMultipleSubdirectoryConfigs()
    {
        $sub1Dir = $this->pluginDir . '/subtype1/sub1';
        $sub2Dir = $this->pluginDir . '/subtype2/sub2';
        $this->fs->mkdir($sub1Dir . '/generated');
        $this->fs->mkdir($sub2Dir . '/tmp');
        $this->fs->dumpFile($sub1Dir . '/lib.php', '<?php // Sub1 lib.');
        $this->fs->dumpFile($sub1Dir . '/generated/out.php', '<?php // Generated.');
        $this->fs->dumpFile($sub2Dir . '/lib.php', '<?php // Sub2 lib.');
        $this->fs->dumpFile($sub2Dir . '/tmp/cache.php', '<?php // Cached.');

        $this->fs->dumpFile($sub1Dir . '/.moodle-plugin-ci.yml',
            Yaml::dump(['filter' => ['notPaths' => ['generated']]]));
        $this->fs->dumpFile($sub2Dir . '/.moodle-plugin-ci.yml',
            Yaml::dump(['filter' => ['notPaths' => ['tmp']]]));

        // Main plugin config excludes 'ignore' path and 'ignore_name.php' name.
        $mainConfig = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($mainConfig));

        $finder = new Finder();
        $finder->name('*.php');

        $plugin = new MoodlePlugin($this->pluginDir);
        $files  = $plugin->getFiles($finder);

        $this->assertNotContains(realpath($sub1Dir . '/generated/out.php'), $files);
        $this->assertNotContains(realpath($sub2Dir . '/tmp/cache.php'), $files);
        $this->assertContains(realpath($sub1Dir . '/lib.php'), $files);
        $this->assertContains(realpath($sub2Dir . '/lib.php'), $files);
    }

    public function testGetRelativeFiles()
    {
        // Ignore some files for better testing.
        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];

        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($config));

        $finder = new Finder();
        $finder->name('*.php')->sortByName();

        $plugin   = new MoodlePlugin($this->pluginDir);
        $expected = [
            'classes/math.php',
            'classes/output/mobile.php',
            'db/access.php',
            'db/mobile.php',
            'db/upgrade.php',
            'lang/en/local_ci.php',
            'lib.php',
            'tests/lib_test.php',
            'version.php',
        ];
        $this->assertSame($expected, $plugin->getRelativeFiles($finder));
    }
}
