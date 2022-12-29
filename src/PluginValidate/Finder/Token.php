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

namespace MoodlePluginCI\PluginValidate\Finder;

/**
 * A token (just a string really).
 */
class Token
{
    /**
     * If the token was found or not.
     */
    private bool $found = false;

    /**
     * If there are multiple tokens, it means that we are looking for
     * any of the tokens, EG: token 1 OR token 2.
     */
    public array $tokens;

    /**
     * @param string|array $token
     */
    public function __construct($token)
    {
        if (!is_array($token)) {
            $token = [$token];
        }
        $this->tokens = $token;
    }

    /**
     * Reset token found state.
     */
    public function reset(): void
    {
        $this->found = false;
    }

    /**
     * @return bool
     */
    public function hasTokenBeenFound(): bool
    {
        return $this->found;
    }

    /**
     * See if the passed string matches our token(s).
     *
     * @param string $string
     */
    public function compare(string $string): void
    {
        foreach ($this->tokens as $token) {
            if (strcasecmp($token, $string) === 0) {
                $this->found = true;
            }
        }
    }

    /**
     * See if the beginning of the passed string matches our token(s).
     *
     * @param string $string
     */
    public function compareStart(string $string): void
    {
        $lowerString = strtolower($string);
        foreach ($this->tokens as $token) {
            if (strpos($lowerString, strtolower($token)) === 0) {
                $this->found = true;
            }
        }
    }
}
