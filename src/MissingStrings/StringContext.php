<?php

declare(strict_types=1);

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\MissingStrings;

/**
 * Context information for required strings.
 *
 * Simple container for file, line, and optional description.
 */
class StringContext
{
    /**
     * The file where the string is used.
     */
    private ?string $file;

    /**
     * The line number where the string is used.
     */
    private ?int $line;

    /**
     * Optional description of the usage.
     */
    private ?string $description;

    /**
     * Constructor.
     *
     * @param string|null $file        the file name
     * @param int|null    $line        the line number
     * @param string|null $description optional description
     */
    public function __construct(?string $file = null, ?int $line = null, ?string $description = null)
    {
        $this->file        = $file;
        $this->line        = $line;
        $this->description = $description;
    }

    /**
     * Get the file name.
     *
     * @return string|null the file name
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Get the line number.
     *
     * @return int|null the line number
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * Get the description.
     *
     * @return string|null the description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the line number.
     *
     * @param int $line the line number
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    /**
     * Check if this context has file and line information.
     *
     * @return bool true if file and line are available
     */
    public function hasLocation(): bool
    {
        return !empty($this->file) && null !== $this->line;
    }

    /**
     * Convert to array format for error context.
     *
     * @return array array representation for error handlers
     */
    public function toArray(): array
    {
        $array = [];

        if ($this->hasLocation()) {
            $array['file'] = $this->file;
            $array['line'] = $this->line;
        }

        if (!empty($this->description)) {
            $array['context'] = $this->description;
        }

        return $array;
    }

    /**
     * Convert to string for display.
     *
     * @return string string representation
     */
    public function __toString(): string
    {
        $parts = [];

        if (!empty($this->description)) {
            $parts[] = $this->description;
        }

        if ($this->hasLocation()) {
            $parts[] = "in {$this->file}:{$this->line}";
        }

        return implode(' ', $parts);
    }
}
