<?php
/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge;

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;

/**
 * Must override to avoid using Moodle API.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class DummyMoodle extends Moodle
{
    public function requireConfig()
    {
        // Define things to make our tests work without actually having Moodle around.
        if (!defined('MOODLE_INTERNAL')) {
            define('MOODLE_INTERNAL', true);
        }
        if (!defined('MATURITY_STABLE')) {
            define('MATURITY_STABLE', 200);
        }
    }
}
