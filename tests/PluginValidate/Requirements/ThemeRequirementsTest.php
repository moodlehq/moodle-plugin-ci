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
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;
use Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\ThemeRequirements;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ThemeRequirementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeRequirements
     */
    private $requirements;

    protected function setUp()
    {
        $this->requirements = new ThemeRequirements(new Plugin('theme_base', 'theme', 'base', ''), 29);
    }

    protected function tearDown()
    {
        $this->requirements = null;
    }

    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            'Moodlerooms\MoodlePluginCI\PluginValidate\Requirements\ThemeRequirements',
            $resolver->resolveRequirements(new Plugin('', 'theme', '', ''), 29)
        );
    }

    public function testGetRequiredFiles()
    {
        $files = $this->requirements->getRequiredFiles();

        $this->assertNotEmpty($files);
        $this->assertTrue(in_array('config.php', $files));
        foreach ($files as $file) {
            $this->assertInternalType('string', $file);
        }
    }
}
