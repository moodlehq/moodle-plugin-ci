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
use MoodlePluginCI\PluginValidate\Requirements\BlockRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class BlockRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            BlockRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'block', '', ''), 29)
        );
    }

    public function testGetRequiredFiles(): void
    {
        $requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
        $files        = $requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredFunctions(): void
    {
        $requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
        $functions    = $requirements->getRequiredFunctions();

        $this->assertNotEmpty($functions);
        foreach ($functions as $function) {
            $this->assertInstanceOf(FileTokens::class, $function);
        }
    }

    public function testGetRequiredClasses(): void
    {
        $requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
        $classes      = $requirements->getRequiredClasses();

        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf(FileTokens::class, $class);
        }
    }

    public function testGetRequiredStrings(): void
    {
        $requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
        $fileToken    = $requirements->getRequiredStrings();
        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('lang/en/block_html.php', $fileToken->file);
    }

    public function testGetRequiredCapabilities(): void
    {
        $requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
        $fileToken    = $requirements->getRequiredCapabilities();
        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('db/access.php', $fileToken->file);
    }
}
