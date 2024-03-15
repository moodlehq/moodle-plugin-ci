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
use MoodlePluginCI\PluginValidate\Requirements\FormatRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class FormatRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            FormatRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'format', '', ''), 29)
        );
    }

    public function testGetRequiredFiles(): void
    {
        $requirements = new FormatRequirements(new Plugin('format_weeks', 'format', 'weeks', ''), 29);
        $files        = $requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        $this->assertTrue(in_array('format.php', $files, true));
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredClasses(): void
    {
        $requirements = new FormatRequirements(new Plugin('format_weeks', 'format', 'weeks', ''), 29);
        $classes      = $requirements->getRequiredClasses();

        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf(FileTokens::class, $class);
        }
    }
}
