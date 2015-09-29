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

namespace Moodlerooms\MoodlePluginCI\Tests;

use Moodlerooms\MoodlePluginCI\StandardResolver;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class StandardResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testHasStandard()
    {
        $resolver = new StandardResolver();
        $this->assertTrue($resolver->hasStandard('moodle'));
        $this->assertFalse($resolver->hasStandard('foo'));

        $resolver = new StandardResolver(['foo' => []]);
        $this->assertTrue($resolver->hasStandard('moodle'));
        $this->assertTrue($resolver->hasStandard('foo'));
    }

    public function testResolve()
    {
        $resolver = new StandardResolver();
        $this->assertNotEmpty($resolver->resolve('moodle'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolveUnknown()
    {
        $resolver = new StandardResolver();
        $resolver->resolve('foo');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testResolveNotFound()
    {
        $resolver = new StandardResolver(['moodle' => [__DIR__.'/bad/location']]);
        $resolver->resolve('moodle');
    }
}
