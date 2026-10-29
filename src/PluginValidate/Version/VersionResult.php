<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2026 Catalyst IT Australia
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\PluginValidate\Version;

class VersionResult
{
    /** @var string[] */
    public array $messages;

    public bool $isValid;

    /**
     * @param string[] $messages
     */
    public function __construct(array $messages, bool $isValid)
    {
        $this->messages = $messages;
        $this->isValid  = $isValid;
    }
}
