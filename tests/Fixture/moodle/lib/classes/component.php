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
 * Minimal core_component core class.
 *
 * This is a minimal fixture of the Moodle core_component class, just to be able
 * to use it in the tests, only including the methods that we are going to need to
 * invoke.
 *
 * Note that we haven't been able to use Mockery for this, because the code to be
 * tested is using Reflection on this class, so we cannot aliases/overload or
 * use similar strategies to mock it.
 */
class core_component {

    public static function get_component_directory($component) {
        return '/path/to/' . str_replace('_', '/', $component);
    }

    public static function normalize_component($component) {
        return explode('_', $component, 2);
    }

    protected static function fetch_plugintypes() {
        return [
            ['mod' => '/path/to/mod', 'local' =>  '/path/to/local'],
            [], // We don't need this.
            [], // We don't need this.
        ];
    }
}
