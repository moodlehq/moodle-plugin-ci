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
use MoodlePluginCI\PluginValidate\Requirements\FilterRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class FilterRequirementsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FilterRequirements
     */
    private $requirements;

    /**
     * @var FilterRequirements
     */
    private $requirements404;

    protected function setUp(): void
    {
        $this->requirements404 = new FilterRequirements(new Plugin('filter_activitynames', 'filter', 'activitynames', ''), 404);
        $this->requirements    = new FilterRequirements(new Plugin('filter_activitynames', 'filter', 'activitynames', ''), 405);
    }

    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            FilterRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'filter', '', ''), 404)
        );
    }

    public function testGetRequiredFiles404(): void
    {
        $files = $this->requirements404->getRequiredFiles();

        $this->assertTrue(in_array('filter.php', $files, true));
        $this->assertFalse(in_array('classes/text_filter.php', $files, true));
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredFiles(): void
    {
        $files = $this->requirements->getRequiredFiles();

        $this->assertFalse(in_array('filter.php', $files, true));
        $this->assertTrue(in_array('classes/text_filter.php', $files, true));
        foreach ($files as $file) {
            $this->assertIsString($file);
        }
    }

    public function testGetRequiredClasses404(): void
    {
        $requirements = $this->getMockBuilder(FilterRequirements::class)
            ->setConstructorArgs([new Plugin('filter_activitynames', 'filter', 'activitynames', ''), 404])
            ->onlyMethods(['fileExists'])
            ->getMock();
        // On first call fileExists return false, on second call return true.
        $requirements->method('fileExists')
            ->with($this->identicalTo('classes/text_filter.php'))
            ->willReturn(false, true);

        // If classes/text_filter.php does not exist, expect class presence in filter.php.
        $classes = $requirements->getRequiredClasses();
        $this->assertCount(1, $classes);
        $class = reset($classes);
        $this->assertInstanceOf(FileTokens::class, $class);
        $this->assertSame('filter.php', $class->file);

        // If classes/text_filter.php exists, expect class presence in it (4.5 plugin backward compatibility).
        $classes = $requirements->getRequiredClasses();
        $this->assertCount(1, $classes);
        $class = reset($classes);
        $this->assertInstanceOf(FileTokens::class, $class);
        $this->assertSame('classes/text_filter.php', $class->file);
    }

    public function testGetRequiredClasses(): void
    {
        $classes = $this->requirements->getRequiredClasses();

        $this->assertCount(1, $classes);
        $class = reset($classes);
        $this->assertInstanceOf(FileTokens::class, $class);
        $this->assertSame('classes/text_filter.php', $class->file);
    }

    public function testGetRequiredStrings(): void
    {
        $fileToken = $this->requirements->getRequiredStrings();

        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('lang/en/filter_activitynames.php', $fileToken->file);
    }

    public function testGetRequiredFunctionCalls404(): void
    {
        $requirements = $this->getMockBuilder(FilterRequirements::class)
            ->setConstructorArgs([new Plugin('filter_activitynames', 'filter', 'activitynames', ''), 404])
            ->onlyMethods(['fileExists'])
            ->getMock();
        // On first call fileExists return false, on second call return true.
        $requirements->method('fileExists')
            ->with($this->identicalTo('classes/text_filter.php'))
            ->willReturn(false, true);

        // If classes/text_filter.php does not exist, expect class alias is not needed in filter.php.
        $calls = $requirements->getRequiredFunctionCalls();
        $this->assertCount(0, $calls);

        // If classes/text_filter.php exists, expect class alias in filter.php (4.5 plugin backward compatibility).
        $calls = $requirements->getRequiredFunctionCalls();
        $this->assertCount(1, $calls);
        $call = reset($calls);
        $this->assertInstanceOf(FileTokens::class, $call);
        $this->assertSame('filter.php', $call->file);
    }

    public function testGetRequiredFunctionCalls(): void
    {
        $calls = $this->requirements->getRequiredFunctionCalls();

        $this->assertCount(1, $calls);
        $call = reset($calls);
        $this->assertInstanceOf(FileTokens::class, $call);
        $this->assertSame('filter.php', $call->file);
    }
}
