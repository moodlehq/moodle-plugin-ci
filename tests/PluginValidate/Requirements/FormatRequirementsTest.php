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
use MoodlePluginCI\PluginValidate\Requirements\FormatRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class FormatRequirementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormatRequirements
     */
    private $requirements;

    protected function setUp()
    {
        $this->requirements = new FormatRequirements(new Plugin('format_weeks', 'format', 'weeks', ''), 29);
    }

    protected function tearDown()
    {
        $this->requirements = null;
    }

    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            'MoodlePluginCI\PluginValidate\Requirements\FormatRequirements',
            $resolver->resolveRequirements(new Plugin('', 'format', '', ''), 29)
        );
    }

    public function testGetRequiredFiles()
    {
        $files = $this->requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        $this->assertTrue(in_array('format.php', $files, true));
        foreach ($files as $file) {
            $this->assertInternalType('string', $file);
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
}
