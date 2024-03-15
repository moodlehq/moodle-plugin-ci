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

use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;
use MoodlePluginCI\PluginValidate\Requirements\ThemeRequirements;

class ThemeRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            ThemeRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'theme', '', ''), 29)
        );
    }

    public function testGetRequiredFiles(): void
    {
        $requirements = new ThemeRequirements(new Plugin('theme_base', 'theme', 'base', ''), 29);
        $files        = $requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        $this->assertTrue(in_array('config.php', $files, true));
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }
}
