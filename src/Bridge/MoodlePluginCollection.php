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

namespace MoodlePluginCI\Bridge;

use MJS\TopSort\Implementations\StringSort;

/**
 * A collection of Moodle plugins.
 */
class MoodlePluginCollection implements \Countable
{
    /**
     * @var MoodlePlugin[]
     */
    private array $items = [];

    public function add(MoodlePlugin $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return MoodlePlugin[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function sortByDependencies(): self
    {
        $elements = [];
        foreach ($this->items as $item) {
            $elements[$item->getComponent()] = [];
        }

        $subpluginTypes = [];
        foreach ($this->items as $item) {
            foreach ($item->getSubpluginTypes() as $type) {
                $subpluginTypes[$type] = $item->getComponent();
            }
        }

        // Loop through a second time, only adding dependencies that exist in our list.
        foreach ($this->items as $item) {
            $dependencies = $item->getDependencies();
            foreach ($dependencies as $dependency) {
                if (array_key_exists($dependency, $elements)) {
                    $elements[$item->getComponent()][] = $dependency;
                }
            }

            // Add implied dependencies for subplugins.
            $type = strtok($item->getComponent(), '_');
            if (array_key_exists($type, $subpluginTypes)) {
                $elements[$item->getComponent()][] = $subpluginTypes[$type];
            }
        }

        $sorter  = new StringSort($elements, false);
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
