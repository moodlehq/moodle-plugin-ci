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

namespace MoodlePluginCI\Tests;

use MoodlePluginCI\StandardResolver;

class StandardResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testHasStandard(): void
    {
        $resolver = new StandardResolver();
        $this->assertTrue($resolver->hasStandard('moodle'));
        $this->assertFalse($resolver->hasStandard('foo'));

        $resolver = new StandardResolver(['foo' => []]);
        $this->assertTrue($resolver->hasStandard('moodle'));
        $this->assertTrue($resolver->hasStandard('foo'));
    }

    public function testResolve(): void
    {
        $resolver = new StandardResolver();
        $this->assertNotEmpty($resolver->resolve('moodle'));
    }

    public function testResolveUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $resolver = new StandardResolver();
        $resolver->resolve('foo');
    }

    public function testResolveNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $resolver = new StandardResolver(['moodle' => [__DIR__ . '/bad/location']]);
        $resolver->resolve('moodle');
    }
}
