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
    public array $tokens = [];

    /**
     * File to find tokens in.
     *
     * @var string
     */
    public string $file;

    /**
     * Not found error hint.
     *
     * @var string
     */
    public string $hint = '';

    /**
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * Factory method for quality of life.
     *
     * @param string $file
     *
     * @return self
     */
    public static function create(string $file): self
    {
        return new self($file);
    }

    /**
     * Do we have any defined tokens?
     *
     * @return bool
     */
    public function hasTokens(): bool
    {
        return !empty($this->tokens);
    }

    /**
     * Do we have any hint?
     *
     * @return bool
     */
    public function hasHint(): bool
    {
        return !empty($this->hint);
    }

    /**
     * @param Token $token
     *
     * @return self
     */
    public function addToken(Token $token): self
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * Require that the file has this single token.
     *
     * @param string $token
     *
     * @return self
     */
    public function mustHave(string $token): self
    {
        return $this->addToken(new Token($token));
    }

    /**
     * Require that the file has all of these tokens.
     *
     * @param array $tokens
     *
     * @return self
     */
    public function mustHaveAll(array $tokens): self
    {
        foreach ($tokens as $token) {
            $this->mustHave($token);
        }

        return $this;
    }

    /**
     * Require that the file has any of the passed tokens.
     *
     * @param array $tokens
     *
     * @return self
     */
    public function mustHaveAny(array $tokens): self
    {
        return $this->addToken(new Token($tokens));
    }

    /**
     * Given some string, see if it matches any of our tokens.
     *
     * @param string $string
     */
    public function compare(string $string): void
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
    public function compareStart(string $string): void
    {
        foreach ($this->tokens as $token) {
            if ($token->hasTokenBeenFound()) {
                continue;
            }
            $token->compareStart($string);
        }
    }

    /**
     * Have all the tokens been found yet?
     *
     * @return bool
     */
    public function hasFoundAllTokens(): bool
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
    public function resetTokens(): void
    {
        foreach ($this->tokens as $token) {
            $token->reset();
        }
    }

    /**
     * Not found error additional information guiding user how to fix it (optional).
     *
     * @param string $hint
     *
     * @return self
     */
    public function notFoundHint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }
}
