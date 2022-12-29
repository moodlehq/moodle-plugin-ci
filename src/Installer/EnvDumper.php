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

/**
 * Environment variable dumper.
 */
class EnvDumper
{
    /**
     * @param array  $values The values to write out
     * @param string $toFile Write to this file
     */
    public function dump(array $values, string $toFile): void
    {
        if (empty($values)) {
            return;
        }
        $content = '';
        foreach ($values as $name => $value) {
            $content .= sprintf('%s=%s', $name, $value).PHP_EOL;
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile($toFile, $content);
    }
}
