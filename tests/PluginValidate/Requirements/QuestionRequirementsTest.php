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
use MoodlePluginCI\PluginValidate\Requirements\QuestionRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class QuestionRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            QuestionRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'qtype', '', ''), 29)
        );
    }

    public function testGetRequiredPrefixes(): void
    {
        $requirements = new QuestionRequirements(new Plugin('qtype_calculated', 'qtype', 'calculated', ''), 29);
        $fileTokens   = $requirements->getRequiredTablePrefix();

        $this->assertInstanceOf(FileTokens::class, $fileTokens);
        $this->assertSame('db/install.xml', $fileTokens->file);
    }
}
