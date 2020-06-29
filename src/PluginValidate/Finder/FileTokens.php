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
 * A list of tokens to find in a file.
 */
class FileTokens
{
    /**
     * @var Token[]
     */
    public $tokens = [];

    /**
     * @var string
     */
    public $file;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Factory method for quality of life.
     *
     * @param string $file
     *
     * @return FileTokens
     */
    public static function create($file)
    {
        return new self($file);
    }

    /**
     * Do we have any defined tokens?
     *
     * @return bool
     */
    public function hasTokens()
    {
        return !empty($this->tokens);
    }

    /**
     * @return FileTokens
     */
    public function addToken(Token $token)
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * Require that a the file has this single token.
     *
     * @param string $token
     *
     * @return FileTokens
     */
    public function mustHave($token)
    {
        return $this->addToken(new Token($token));
    }

    /**
     * Require that a the file has all of these tokens.
     *
     * @return FileTokens
     */
    public function mustHaveAll(array $tokens)
    {
        foreach ($tokens as $token) {
            $this->mustHave($token);
        }

        return $this;
    }

    /**
     * Require that a the file has any of the passed tokens.
     *
     * @return FileTokens
     */
    public function mustHaveAny(array $tokens)
    {
        return $this->addToken(new Token($tokens));
    }

    /**
     * Given some string, see if it matches any of our tokens.
     *
     * @param string $string
     */
    public function compare($string)
    {
        foreach ($this->tokens as $token) {
            if ($token->hasTokenBeenFound()) {
                continue;
            }
            $token->compare($string);
        }
    }

    /**
     * See if the beginning of the passed string matches any of our token(s).
     *
     * @param string $string
     */
    public function compareStart($string)
    {
        foreach ($this->tokens as $token) {
            if ($token->hasTokenBeenFound()) {
                continue;
            }
            $token->compareStart($string);
        }
    }

    /**
     * Have all of the tokens been found yet?
     *
     * @return bool
     */
    public function hasFoundAllTokens()
    {
        foreach ($this->tokens as $token) {
            if (!$token->hasTokenBeenFound()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reset found state on all tokens.
     */
    public function resetTokens()
    {
        foreach ($this->tokens as $token) {
            $token->reset();
        }
    }
}
