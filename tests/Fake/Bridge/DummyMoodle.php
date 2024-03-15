<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\Fake\Bridge;

use MoodlePluginCI\Bridge\Moodle;

/**
 * Must override to avoid using Moodle API.
 */
class DummyMoodle extends Moodle
{
    public int $branch = 33;

    public function requireConfig(): void
    {
        // Don't do anything.
    }

    public function normalizeComponent($component): array
    {
        return ['local', 'travis'];
    }

    public function getComponentInstallDirectory($component): string
    {
        return $this->directory . '/local/travis';
    }

    public function getBranch(): int
    {
        return $this->branch;
    }
}
