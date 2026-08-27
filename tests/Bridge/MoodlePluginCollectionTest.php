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

namespace MoodlePluginCI\Tests\Bridge;

use MoodlePluginCI\Bridge\MoodlePluginCollection;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;

class MoodlePluginCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testSortByDependencies(): void
    {
        $plugin1               = new DummyMoodlePlugin('');
        $plugin1->component    = 'mod_1';
        $plugin1->dependencies = ['mod_2', 'mod_3'];

        $plugin2               = new DummyMoodlePlugin('');
        $plugin2->component    = 'mod_2';
        $plugin2->dependencies = ['mod_3'];

        $plugin3            = new DummyMoodlePlugin('');
        $plugin3->component = 'mod_3';

        $plugins = new MoodlePluginCollection();
        $plugins->add($plugin1);
        $plugins->add($plugin3);
        $plugins->add($plugin2);

        $sorted = $plugins->sortByDependencies();
        $this->assertInstanceOf(MoodlePluginCollection::class, $sorted);
        $this->assertNotSame($plugins, $sorted);
        $this->assertCount(3, $sorted);

        $all = $sorted->all();
        $this->assertSame($plugin3, $all[0]);
        $this->assertSame($plugin2, $all[1]);
        $this->assertSame($plugin1, $all[2]);
    }

    /**
     * Moodle plugins can have circular dependencies, ensure we do not error when this happens.
     */
    public function testSortByDependenciesCircularError(): void
    {
        $plugin1               = new DummyMoodlePlugin('');
        $plugin1->component    = 'mod_1';
        $plugin1->dependencies = ['mod_2', 'mod_3'];

        $plugin2               = new DummyMoodlePlugin('');
        $plugin2->component    = 'mod_2';
        $plugin2->dependencies = ['mod_1'];

        $plugin3            = new DummyMoodlePlugin('');
        $plugin3->component = 'mod_3';

        $plugins = new MoodlePluginCollection();
        $plugins->add($plugin1);
        $plugins->add($plugin3);
        $plugins->add($plugin2);

        $sorted = $plugins->sortByDependencies();
        $all    = $sorted->all();

        $this->assertCount(3, $all);
        $this->assertSame($plugin2, $all[0]);
        $this->assertSame($plugin3, $all[1]);
        $this->assertSame($plugin1, $all[2]);
    }

    public function testSortByDependenciesWithSubplugins(): void
    {
        $plugin1                 = new DummyMoodlePlugin('');
        $plugin1->component      = 'mod_1';
        $plugin1->subpluginTypes = ['subplugin'];

        $plugin2               = new DummyMoodlePlugin('');
        $plugin2->component    = 'subplugin_1';

        $plugins = new MoodlePluginCollection();
        $plugins->add($plugin2);
        $plugins->add($plugin1);

        $sorted = $plugins->sortByDependencies();
        $all    = $sorted->all();
        $this->assertCount(2, $all);
        $this->assertSame($plugin1, $all[0]);
        $this->assertSame($plugin2, $all[1]);
    }
}
