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

namespace Moodlerooms\MoodlePluginCI;

/**
 * Resolve the location of various coding standards.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class StandardResolver
{
    /**
     * Standards and their possible locations.
     *
     * @var array
     */
    private $standards = [];

    /**
     * @param array $standards Standards and their possible locations
     */
    public function __construct(array $standards = [])
    {
        $defaultStandards = [
            'moodle' => [
                __DIR__.'/../../../moodlerooms/moodle-coding-standard/moodle', // Global Composer install.
                __DIR__.'/../vendor/moodlerooms/moodle-coding-standard/moodle', // Local Composer install.
            ],
        ];

        $this->standards = $standards + $defaultStandards;
    }

    /**
     * Determine if a standard is known or not.
     *
     * @param string $name The standard name
     *
     * @return bool
     */
    public function hasStandard($name)
    {
        return array_key_exists($name, $this->standards);
    }

    /**
     * Find the location of a standard.
     *
     * @param string $name The standard name
     *
     * @return string
     */
    public function resolve($name)
    {
        if (!$this->hasStandard($name)) {
            throw new \InvalidArgumentException('Unknown coding standard: '.$name);
        }

        foreach ($this->standards[$name] as $location) {
            if (file_exists($location)) {
                return $location;
            }
        }

        throw new \RuntimeException(sprintf('Failed to find the \'%s\' coding standard, likely need to run Composer install', $name));
    }
}
