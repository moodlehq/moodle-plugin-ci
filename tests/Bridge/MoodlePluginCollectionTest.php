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

namespace Moodlerooms\MoodlePluginCI\Tests\Bridge;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePluginCollection;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePluginCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testSortByDependencies()
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
        $this->assertInstanceOf('Moodlerooms\MoodlePluginCI\Bridge\MoodlePluginCollection', $sorted);
        $this->assertNotSame($plugins, $sorted);
        $this->assertCount(3, $sorted);

        $all = $sorted->all();
        $this->assertSame($plugin3, $all[0]);
        $this->assertSame($plugin2, $all[1]);
        $this->assertSame($plugin1, $all[2]);
    }
}
