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
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\AuthRequirements;
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AuthRequirementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthRequirements
     */
    private $requirements;

    protected function setUp()
    {
        $this->requirements = new AuthRequirements(new Plugin('auth_email', 'auth', 'email', ''), 29);
    }

    protected function tearDown()
    {
        $this->requirements = null;
    }

    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            'Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\AuthRequirements',
            $resolver->resolveRequirements(new Plugin('', 'auth', '', ''), 29)
        );
    }

    public function testGetRequiredFiles()
    {
        $files = $this->requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        $this->assertTrue(in_array('auth.php', $files));
        foreach ($files as $file) {
            $this->assertInternalType('string', $file);
        }
    }

    public function testGetRequiredClasses()
    {
        $classes = $this->requirements->getRequiredClasses();

        $this->assertNotEmpty($classes);
        foreach ($classes as $class) {
            $this->assertInstanceOf('Moodlerooms\MoodlePluginCI\PluginValidate\Finder\FileTokens', $class);
        }
    }
}
