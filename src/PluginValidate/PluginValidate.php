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

namespace MoodlePluginCI\PluginValidate;

use MoodlePluginCI\PluginValidate\Finder\BehatTagFinder;
use MoodlePluginCI\PluginValidate\Finder\CapabilityFinder;
use MoodlePluginCI\PluginValidate\Finder\ClassFinder;
use MoodlePluginCI\PluginValidate\Finder\FileTokens;
use MoodlePluginCI\PluginValidate\Finder\FinderInterface;
use MoodlePluginCI\PluginValidate\Finder\FunctionFinder;
use MoodlePluginCI\PluginValidate\Finder\LangFinder;
use MoodlePluginCI\PluginValidate\Finder\TableFinder;
use MoodlePluginCI\PluginValidate\Finder\TablePrefixFinder;
use MoodlePluginCI\PluginValidate\Requirements\AbstractRequirements;

/**
 * Validates a plugin against a set of requirements.
 */
class PluginValidate
{
    /**
     * Results from validation.
     *
     * @var array
     */
    public $messages = [];

    /**
     * If the plugin is valid or not.
     *
     * @var bool
     */
    public $isValid = true;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var AbstractRequirements
     */
    private $requirements;

    public function __construct(Plugin $plugin, AbstractRequirements $requirements)
    {
        $this->plugin       = $plugin;
        $this->requirements = $requirements;
    }

    /**
     * @param string $message
     */
    public function addError($message)
    {
        $this->messages[] = sprintf('<fg=red>X %s</>', $message);
        $this->isValid    = false;
    }

    /**
     * @param string $message
     */
    public function addSuccess($message)
    {
        $this->messages[] = sprintf('<info>></info> %s', $message);
    }

    /**
     * @param string $message
     */
    public function addWarning($message)
    {
        $this->messages[] = sprintf('<comment>!</comment> %s', $message);
    }

    /**
     * Add messages about finding or not finding tokens in a file.
     *
     * @param string $type
     */
    public function addMessagesFromTokens($type, FileTokens $fileTokens)
    {
        foreach ($fileTokens->tokens as $token) {
            if ($token->hasTokenBeenFound()) {
                $this->addSuccess(sprintf('In %s, found %s %s', $fileTokens->file, $type, implode(' OR ', $token->tokens)));
            } else {
                $this->addError(sprintf('In %s, failed to find %s %s', $fileTokens->file, $type, implode(' OR ', $token->tokens)));
            }
        }
    }

    /**
     * Run verification of a plugin.
     */
    public function verifyRequirements()
    {
        $this->findRequiredFiles($this->requirements->getRequiredFiles());
        $this->findRequiredTokens(new FunctionFinder(), $this->requirements->getRequiredFunctions());
        $this->findRequiredTokens(new ClassFinder(), $this->requirements->getRequiredClasses());
        $this->findRequiredTokens(new LangFinder(), [$this->requirements->getRequiredStrings()]);
        $this->findRequiredTokens(new CapabilityFinder(), [$this->requirements->getRequiredCapabilities()]);
        $this->findRequiredTokens(new TableFinder(), [$this->requirements->getRequiredTables()]);
        $this->findRequiredTokens(new TablePrefixFinder(), [$this->requirements->getRequiredTablePrefix()]);
        $this->findRequiredTokens(new BehatTagFinder(), $this->requirements->getRequiredBehatTags());
    }

    /**
     * Ensure a list of files exists.
     */
    public function findRequiredFiles(array $files)
    {
        foreach ($files as $file) {
            if (file_exists($this->plugin->directory.'/'.$file)) {
                $this->addSuccess(sprintf('Found required file: %s', $file));
            } else {
                $this->addError(sprintf('Failed to find required file: %s', $file));
            }
        }
    }

    /**
     * Find required tokens in a file.
     *
     * @param FileTokens[] $tokenCollection
     */
    public function findRequiredTokens(FinderInterface $finder, array $tokenCollection)
    {
        foreach ($tokenCollection as $fileTokens) {
            if (!$fileTokens->hasTokens()) {
                continue;
            }
            $file = $this->plugin->directory.'/'.$fileTokens->file;

            if (!file_exists($file)) {
                $this->addWarning(sprintf('Skipping validation of missing or optional file: %s', $fileTokens->file));
                continue;
            }

            try {
                $finder->findTokens($file, $fileTokens);
                $this->addMessagesFromTokens($finder->getType(), $fileTokens);
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }
        }
    }
}
