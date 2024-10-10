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
use MoodlePluginCI\PluginValidate\Plugin;
use Symfony\Component\Finder\Finder;

/**
 * Abstract plugin requirements.
 */
abstract class AbstractRequirements
{
    /**
     * Plugin to be validated.
     */
    protected Plugin $plugin;

    /**
     * The major Moodle version, EG: 29, 30, 31.
     */
    protected int $moodleVersion;

    /**
     * @param Plugin $plugin        Details about the plugin
     * @param int    $moodleVersion The major Moodle version, EG: 29, 30, 31
     */
    public function __construct(Plugin $plugin, int $moodleVersion)
    {
        $this->plugin        = $plugin;
        $this->moodleVersion = $moodleVersion;
    }

    /**
     * Factory method for generating FileTokens instances for all feature files in a plugin.
     *
     * @param array $tags
     *
     * @return FileTokens[]
     */
    protected function behatTagsFactory(array $tags): array
    {
        $fileTokens = [];

        try {
            $files = Finder::create()->files()->in($this->plugin->directory)->path('tests/behat')->name('*.feature')->getIterator();
            foreach ($files as $file) {
                $fileTokens[] = FileTokens::create($file->getRelativePathname())->mustHaveAll($tags);
            }
        } catch (\Exception $e) {
            // Nothing to do.
        }

        return $fileTokens;
    }

    /**
     * Helper method to check file existence.
     *
     * @param string $file
     *
     * @return bool
     */
    protected function fileExists(string $file): bool
    {
        return file_exists($this->plugin->directory . '/' . $file);
    }

    /**
     * Required function calls.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredFunctionCalls(): array;

    /**
     * An array of required files, paths are relative to the plugin directory.
     *
     * @return array
     */
    abstract public function getRequiredFiles(): array;

    /**
     * Required plugin functions.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredFunctions(): array;

    /**
     * Required plugin classes.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredClasses(): array;

    /**
     * Required plugin string definitions.
     *
     * @return FileTokens
     */
    abstract public function getRequiredStrings(): FileTokens;

    /**
     * Required plugin capability definitions.
     *
     * @return FileTokens
     */
    abstract public function getRequiredCapabilities(): FileTokens;

    /**
     * Required plugin database tables.
     *
     * @return FileTokens
     */
    abstract public function getRequiredTables(): FileTokens;

    /**
     * Required plugin database table prefix.
     *
     * @return FileTokens
     */
    abstract public function getRequiredTablePrefix(): FileTokens;

    /**
     * Required Behat tags for feature files.
     *
     * @return FileTokens[]
     */
    abstract public function getRequiredBehatTags(): array;
}
