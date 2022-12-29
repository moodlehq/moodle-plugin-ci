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

namespace MoodlePluginCI\PluginValidate\Requirements;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;

/**
 * Question plugin requirements.
 */
class QuestionRequirements extends GenericRequirements
{
    public function getRequiredTablePrefix(): FileTokens
    {
        return FileTokens::create('db/install.xml')->mustHaveAny(['qtype_', 'question_']);
    }
}
