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

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('tests/fixture/moodle-local_travis')
    ->name('helper') // This covers bin/helper.
    ->in(__DIR__);

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
return Symfony\CS\Config\Config::create()
    ->fixers([
        'align_equals',
        'align_double_arrow',
        'ordered_use',
        'short_array_syntax',
        '-phpdoc_params',
        '-elseif',
        '-phpdoc_separation',
        '-phpdoc_to_comment',
        '-blankline_after_open_tag',
    ])
    ->finder($finder);