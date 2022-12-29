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

namespace MoodlePluginCI\Installer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Plugin config dumper.
 */
class ConfigDumper
{
    private array $values = [];

    /**
     * @return bool
     */
    public function hasConfig(): bool
    {
        return !empty($this->values);
    }

    /**
     * @param string       $section
     * @param string       $name
     * @param string|array $value
     */
    public function addSection(string $section, string $name, $value): void
    {
        if (empty($value)) {
            return;
        }
        if (empty($this->values[$section])) {
            $this->values[$section] = [];
        }
        $this->values[$section][$name] = $value;
    }

    /**
     * @param string $toFile Write to this file
     */
    public function dump(string $toFile): void
    {
        if (empty($this->values)) {
            return;
        }

        $dump = Yaml::dump($this->values);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($toFile, $dump);
    }
}
