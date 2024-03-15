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

namespace MoodlePluginCI\Tests\PluginValidate\Requirements;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\Requirements\RepositoryRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class RepositoryRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            RepositoryRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'repository', '', ''), 29)
        );
    }

    public function testGetRequiredFiles(): void
    {
        $requirements = new RepositoryRequirements(new Plugin('repository_upload', 'repository', 'upload', ''), 29);
        $files        = $requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredClasses(): void
    {
        $requirements = new RepositoryRequirements(new Plugin('repository_upload', 'repository', 'upload', ''), 29);
        $classes      = $requirements->getRequiredClasses();

        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf(FileTokens::class, $class);
        }
    }

    public function testGetRequiredStrings(): void
    {
        $requirements = new RepositoryRequirements(new Plugin('repository_upload', 'repository', 'upload', ''), 29);
        $fileToken    = $requirements->getRequiredStrings();

        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('lang/en/repository_upload.php', $fileToken->file);
    }

    public function testGetRequiredCapabilities(): void
    {
        $requirements = new RepositoryRequirements(new Plugin('repository_upload', 'repository', 'upload', ''), 29);
        $fileToken    = $requirements->getRequiredCapabilities();

        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('db/access.php', $fileToken->file);
    }

    public function testPluginAndMoodleAreSet(): void
    {
        // Let's check, via reflection, that the plugin and the moodle version are always set,
        // no matter that we aren't using them for anything within the RepositoryRequirements class.
        $requirements   = new RepositoryRequirements(new Plugin('repository_upload', 'repository', 'upload', ''), 1234);
        $reflectedClass = new \ReflectionClass($requirements);

        $this->assertSame('upload', $reflectedClass->getProperty('plugin')->getValue($requirements)->name);
        $this->assertSame(1234, $reflectedClass->getProperty('moodleVersion')->getValue($requirements));
    }
}
