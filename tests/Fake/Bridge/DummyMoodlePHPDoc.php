<?php

// This file is part of the Moodle Plugin CI package.
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

namespace MoodlePluginCI\Tests\Fake\Bridge;

/**
 * Dummy Moodle Fixture class to be able to run PHPDocCommand tests.
 *
 * @copyright  2023 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class DummyMoodlePHPDoc extends DummyMoodle
{
    public function normalizeComponent($component): array
    {
        return ['local', 'moodlecheck'];
    }

    public function getComponentInstallDirectory($component): string
    {
        return $this->directory . '/local/moodlecheck';
    }
}
