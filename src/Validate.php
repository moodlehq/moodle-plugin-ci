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
 * Validation of user input.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Validate
{
    /**
     * @param string $path
     *
     * @return string
     */
    private function realPath($path)
    {
        $result = realpath($path);
        if ($result === false) {
            throw new \InvalidArgumentException(sprintf('Failed to run realpath(\'%s\')', $path));
        }

        return $result;
    }

    /**
     * Validate a directory path.
     *
     * @param string $path
     *
     * @return string
     */
    public function directory($path)
    {
        $dir = $this->realPath($path);
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The path is not a directory: %s', $dir));
        }

        return $path;
    }

    /**
     * Validate a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function filePath($path)
    {
        $file = $this->realPath($path);
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('The path is not a file: %s', $file));
        }

        return $path;
    }

    /**
     * Validate Moodle branch name.
     *
     * @param string $branch
     *
     * @return string
     */
    public function moodleBranch($branch)
    {
        if ($branch !== 'master' && preg_match('/^MOODLE_\d\d_STABLE$/', $branch) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid Moodle branch: %s', $branch));
        }

        return $branch;
    }
}
