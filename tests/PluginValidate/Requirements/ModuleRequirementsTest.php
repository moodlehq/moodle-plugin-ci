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
use MoodlePluginCI\PluginValidate\Requirements\ModuleRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class ModuleRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            ModuleRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'mod', '', ''), 29)
        );
    }

    public function testGetRequiredFiles(): void
    {
        $requirements = new ModuleRequirements(new Plugin('mod_forum', 'mod', 'forum', ''), 29);
        $files        = $requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredFunctions(): void
    {
        $requirements = new ModuleRequirements(new Plugin('mod_forum', 'mod', 'forum', ''), 29);
        $functions    = $requirements->getRequiredFunctions();

        $this->assertNotEmpty($functions);
        foreach ($functions as $function) {
            $this->assertInstanceOf(FileTokens::class, $function);
        }
    }

    public function testGetRequiredStrings(): void
    {
        $requirements = new ModuleRequirements(new Plugin('mod_forum', 'mod', 'forum', ''), 29);
        $fileToken    = $requirements->getRequiredStrings();
        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('lang/en/forum.php', $fileToken->file);
    }

    public function testGetRequiredCapabilities(): void
    {
        $requirements = new ModuleRequirements(new Plugin('mod_forum', 'mod', 'forum', ''), 29);
        $fileToken    = $requirements->getRequiredCapabilities();
        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('db/access.php', $fileToken->file);
    }
}
