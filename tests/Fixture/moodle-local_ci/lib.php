<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Just to give analysis tools something to work on.
 *
 * @package   local_ci
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add
 *
 * @param int $a A integer
 * @param int $b A integer
 * @return int
 */
function local_ci_add($a, $b) {
    // Let's add them.
    return $a + $b;
}

/**
 * Subtract
 *
 * @param int $a A integer
 * @param int $b A integer
 * @return int
 */
function local_ci_subtract($a, $b) {
    // Let's subtract them.
    return $a - $b;
}

/**
 * Math class.
 *
 * @package   local_ci
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_ci_math {
    /**
     * Add
     *
     * @param int $a A integer
     * @param int $b A integer
     * @return int
     */
    public function add($a, $b) {
        // Let's add them.
        return $a + $b;
    }
}
