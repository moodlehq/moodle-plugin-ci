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

/**
 * Interacts with Moodle thirdpartylibs.xml files.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Vendors
{
    /**
     * Absolute path to a thirdpartylibs.xml file.
     *
     * @var string
     */
    private $path;

    /**
     * The parsed thirdpartylibs.xml file.
     *
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * @param string $path Absolute path to a thirdpartylibs.xml file.
     */
    public function __construct($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException('Path does not exist: '.$path);
        }
        $this->path = $path;
        $this->xml  = simplexml_load_file($this->path);
    }

    /**
     * Returns all the third party library paths from the XML file.
     *
     * @return array
     */
    public function getVendorPaths()
    {
        $base  = dirname($this->path);
        $paths = [];
        foreach ($this->xml->xpath('/libraries/library/location') as $location) {
            $location = (string) trim($location, '/');
            $location = $base.'/'.$location;

            if (strpos($location, '*') !== false) {
                $locations = glob($location);
                if (empty($locations)) {
                    throw new \RuntimeException(sprintf('Failed to run glob on path: %s', $location));
                }
                $paths = array_merge($paths, $locations);
            } elseif (!file_exists($location)) {
                throw new \RuntimeException(sprintf('The %s contains a non-existent path: %s', $this->path, $location));
            } else {
                $paths[] = $location;
            }
        }

        return $paths;
    }

    /**
     * Returns all the third party library paths from the XML file.  The paths will be relative to the XML file.
     *
     * @return array
     */
    public function getRelativeVendorPaths()
    {
        $base = dirname($this->path).'/';

        return array_map(function ($path) use ($base) {
            return str_replace($base, '', $path);
        }, $this->getVendorPaths());
    }
}
