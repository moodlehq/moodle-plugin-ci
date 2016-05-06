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

namespace Moodlerooms\MoodlePluginCI\Bridge;

use MJS\TopSort\Implementations\StringSort;

/**
 * A collection of Moodle plugins.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodlePluginCollection implements \Countable
{
    /**
     * @var MoodlePlugin[]
     */
    private $items = [];

    public function add(MoodlePlugin $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return MoodlePlugin[]
     */
    public function all()
    {
        return $this->items;
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * @return MoodlePluginCollection
     */
    public function sortByDependencies()
    {
        $elements = [];
        foreach ($this->items as $item) {
            $elements[$item->getComponent()] = [];
        }

        // Loop through a second time, only adding dependencies that exist in our list.
        foreach ($this->items as $item) {
            $dependencies = $item->getDependencies();
            foreach ($dependencies as $dependency) {
                if (array_key_exists($dependency, $elements)) {
                    $elements[$item->getComponent()][] = $dependency;
                }
            }
        }

        $sorter  = new StringSort($elements);
        $results = $sorter->sort();

        $sorted = new self();
        foreach ($results as $result) {
            foreach ($this->items as $item) {
                if ($result === $item->getComponent()) {
                    $sorted->add($item);
                    break;
                }
            }
        }

        if ($this->count() !== $sorted->count()) {
            throw new \LogicException('The sorted list of plugins does not match the size of original list');
        }

        return $sorted;
    }
}
