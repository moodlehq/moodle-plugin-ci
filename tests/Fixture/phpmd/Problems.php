<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2024 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This fixture class is used to test the PHPMD command.
 *
 * It contains 2 PHPMD naming violations and 1 parsing error.
 */
class Problems {
    const testConst = 1;
    protected $reallyVeryLongVariableName = true;
}

class unfinishedFixtureClass {
