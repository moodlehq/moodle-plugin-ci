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

$finder = Symfony\CS\Finder::create()
    ->exclude('tests/Fixture')
    ->exclude('moodle')
    ->exclude('moodledata')
    ->name('moodle-plugin-ci')
    ->in(__DIR__);

return Symfony\CS\Config::create()
    ->setUsingCache(true)
    ->fixers([
        'align_equals',
        'align_double_arrow',
        'ordered_use',
        'short_array_syntax',
        '-psr0',
    ])
    ->finder($finder);