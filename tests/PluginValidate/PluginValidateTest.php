<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\PluginValidate;

use Moodlerooms\MoodlePluginCI\PluginValidate\Plugin;
use Moodlerooms\MoodlePluginCI\PluginValidate\PluginValidate;
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\GenericRequirements;
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\ModuleRequirements;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginValidateTest extends \PHPUnit_Framework_TestCase
{
    public function testVerifyRequirements()
    {
        $plugin       = new Plugin('local_travis', 'local', 'travis', __DIR__.'/../Fixture/moodle-local_travis');
        $requirements = new GenericRequirements($plugin, 29);
        $validate     = new PluginValidate($plugin, $requirements);
        $validate->verifyRequirements();

        $this->assertTrue($validate->isValid);
    }

    public function testVerifyRequirementsFail()
    {
        $plugin       = new Plugin('local_travis', 'local', 'travis', __DIR__.'/../Fixture/moodle-local_travis');
        $requirements = new ModuleRequirements($plugin, 29);  // Trick! It's not a module, should fail!
        $validate     = new PluginValidate($plugin, $requirements);
        $validate->verifyRequirements();

        $this->assertFalse($validate->isValid);
    }
}
