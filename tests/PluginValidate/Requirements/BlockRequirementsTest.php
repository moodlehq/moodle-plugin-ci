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

namespace MoodlePluginCI\Tests\PluginValidate;

use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\Requirements\BlockRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class BlockRequirementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockRequirements
     */
    private $requirements;

    protected function setUp()
    {
        $this->requirements = new BlockRequirements(new Plugin('block_html', 'block', 'html', ''), 29);
    }

    protected function tearDown()
    {
        $this->requirements = null;
    }

    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            'MoodlePluginCI\PluginValidate\Requirements\BlockRequirements',
            $resolver->resolveRequirements(new Plugin('', 'block', '', ''), 29)
        );
    }

    public function testGetRequiredFiles()
    {
        $files = $this->requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertInternalType('string', $file);
        }
    }

    public function testGetRequiredFunctions()
    {
        $functions = $this->requirements->getRequiredFunctions();

        $this->assertNotEmpty($functions);
        foreach ($functions as $function) {
            $this->assertInstanceOf('MoodlePluginCI\PluginValidate\Finder\FileTokens', $function);
        }
    }

    public function testGetRequiredClasses()
    {
        $classes = $this->requirements->getRequiredClasses();

        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf('MoodlePluginCI\PluginValidate\Finder\FileTokens', $class);
        }
    }

    public function testGetRequiredStrings()
    {
        $fileToken = $this->requirements->getRequiredStrings();
        $this->assertInstanceOf('MoodlePluginCI\PluginValidate\Finder\FileTokens', $fileToken);
        $this->assertSame('lang/en/block_html.php', $fileToken->file);
    }

    public function testGetRequiredCapabilities()
    {
        $fileToken = $this->requirements->getRequiredCapabilities();
        $this->assertInstanceOf('MoodlePluginCI\PluginValidate\Finder\FileTokens', $fileToken);
        $this->assertSame('db/access.php', $fileToken->file);
    }
}
