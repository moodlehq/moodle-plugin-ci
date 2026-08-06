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
use MoodlePluginCI\PluginValidate\PluginValidate;
use MoodlePluginCI\PluginValidate\Requirements\GenericRequirements;
use MoodlePluginCI\PluginValidate\Requirements\ModuleRequirements;

class PluginValidateTest extends \PHPUnit\Framework\TestCase
{
    public function testVerifyRequirements(): void
    {
        $plugin       = new Plugin('local_ci', 'local', 'travis', __DIR__ . '/../Fixture/moodle-local_ci');
        $requirements = new GenericRequirements($plugin, 29);
        $validate     = new PluginValidate($plugin, $requirements);
        $validate->verifyRequirements();

        $this->assertTrue($validate->isValid);
    }

    public function testVerifyRequirementsFail(): void
    {
        $plugin       = new Plugin('local_ci', 'local', 'travis', __DIR__ . '/../Fixture/moodle-local_ci');
        $requirements = new ModuleRequirements($plugin, 29);  // Trick! It's not a module, should fail!
        $validate     = new PluginValidate($plugin, $requirements);
        $validate->verifyRequirements();

        $this->assertFalse($validate->isValid);
    }
}
