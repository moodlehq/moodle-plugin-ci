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
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class RequirementsResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        // Note: do not test every single possible resolve here.  Instead, test if a requirements can resolve
        // in the WhateverRequirementsTest.php file.  That way each can make sure it can be resolved.

        $this->assertInstanceOf(
            'MoodlePluginCI\PluginValidate\Requirements\BlockRequirements',
            $resolver->resolveRequirements(new Plugin('', 'block', '', ''), 29)
        );

        $this->assertInstanceOf(
            'MoodlePluginCI\PluginValidate\Requirements\GenericRequirements',
            $resolver->resolveRequirements(new Plugin('', 'gibberish', '', ''), 29)
        );
    }
}
