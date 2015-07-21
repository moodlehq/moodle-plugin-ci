<?php
/**
 * This file is part of the Moodle Plugin Travis CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodleTravisPlugin\Bridge;

/**
 * Bridge to Moodle
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Moodle {
    /**
     * Absolute path to Moodle directory
     *
     * @var string
     */
    public $pathToMoodle;

    /**
     * @param string $pathToMoodle Absolute path to Moodle directory
     */
    public function __construct($pathToMoodle) {
        $this->pathToMoodle = $pathToMoodle;
    }

    /**
     * Load's Moodle config so we can use Moodle APIs
     */
    public function requireConfig() {
        if (!defined('CLI_SCRIPT')) {
            define('CLI_SCRIPT', true);
        }
        if (!defined('IGNORE_COMPONENT_CACHE')) {
            define('IGNORE_COMPONENT_CACHE', true);
        }
        if (!defined('ABORT_AFTER_CONFIG')) {
            // Need this since Moodle will not be fully installed.
            define('ABORT_AFTER_CONFIG', true);
        }
        $path = $this->pathToMoodle.'/config.php';

        if (!is_file($path)) {
            throw new \InvalidArgumentException('Failed to find Moodle config file');
        }

        /** @noinspection PhpIncludeInspection */
        require_once($path);
    }
}
