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
use MoodlePluginCI\PluginValidate\Requirements\BlockRequirements;
use MoodlePluginCI\PluginValidate\Requirements\GenericRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class RequirementsResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        // Note: do not test every single possible resolve here.  Instead, test if a requirements can resolve
        // in the WhateverRequirementsTest.php file.  That way each can make sure it can be resolved.

        $this->assertInstanceOf(
            BlockRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'block', '', ''), 29)
        );

        $this->assertInstanceOf(
            GenericRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'gibberish', '', ''), 29)
        );
    }
}
