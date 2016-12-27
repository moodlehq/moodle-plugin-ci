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
     * Validate git branch name.
     *
     * @param string $branch
     *
     * @return string
     */
    public function gitBranch($branch)
    {
        $options = ['options' => ['regexp' => '/^[a-zA-Z0-9\/\+\._-]+$/']];
        if (filter_var($branch, FILTER_VALIDATE_REGEXP, $options) === false) {
            throw new \InvalidArgumentException(sprintf("Invalid characters found in git branch name '%s'. Use only letters, numbers, underscore, hyphen and forward slashes.", $branch));
        }

        return $branch;
    }

    /**
     * Validate git URL.
     *
     * @param string $url
     *
     * @return string
     */
    public function gitUrl($url)
    {
        // Source/credit: https://github.com/jonschlinkert/is-git-url/blob/master/index.js
        $options = ['options' => ['regexp' => '/(?:git|ssh|https?|git@[\w\.]+):(?:\/\/)?[\w\.@:\/~_-]+\.git(?:\/?|\#[\d\w\.\-_]+?)$/']];
        if (filter_var($url, FILTER_VALIDATE_REGEXP, $options) === false) {
            throw new \InvalidArgumentException(sprintf('Invalid URL: %s', $url));
        }

        return $url;
    }
}
