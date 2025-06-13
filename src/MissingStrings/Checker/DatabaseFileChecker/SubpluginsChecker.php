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

namespace MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker;

use MoodlePluginCI\MissingStrings\Checker\StringContextTrait;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for subplugin type language strings in db/subplugins.json or db/subplugins.php.
 */
class SubpluginsChecker extends AbstractDatabaseChecker
{
    use StringContextTrait;

    /**
     * The pattern for subplugin type strings.
     *
     * @var string
     */
    private const SUBPLUGIN_TYPE_STRING_PATTERN = 'subplugintype_';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeStringUsageFinder();
    }

    /**
     * Get the database file path.
     */
    protected function getDatabaseFile(): string
    {
        // Prefer JSON format (Moodle 3.8+) over PHP format
        return 'db/subplugins.json';
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Subplugins';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if either subplugins file exists
     */
    public function appliesTo(Plugin $plugin): bool
    {
        $jsonFile = $plugin->directory . '/db/subplugins.json';
        $phpFile  = $plugin->directory . '/db/subplugins.php';

        return file_exists($jsonFile) || file_exists($phpFile);
    }

    /**
     * Check the plugin for required strings.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return \MoodlePluginCI\MissingStrings\ValidationResult the result of the check
     */
    public function check(Plugin $plugin): \MoodlePluginCI\MissingStrings\ValidationResult
    {
        $result = new \MoodlePluginCI\MissingStrings\ValidationResult();

        // Check for both JSON and PHP format files
        $jsonFile = $plugin->directory . '/db/subplugins.json';
        $phpFile  = $plugin->directory . '/db/subplugins.php';

        if (!file_exists($jsonFile) && !file_exists($phpFile)) {
            // No subplugins file exists - this is an error since check was called
            $result->addRawError('No subplugins file found (db/subplugins.json or db/subplugins.php)');

            return $result;
        }

        try {
            return $this->parseFile('', $plugin); // parseFile handles file detection internally
        } catch (\Exception $e) {
            $result->addRawError('Error parsing subplugins file: ' . $e->getMessage());

            return $result;
        }
    }

    /**
     * Parse the database file and extract required strings.
     *
     * @param string $filePath the full path to the database file
     * @param Plugin $plugin   the plugin being checked
     */
    protected function parseFile(string $filePath, Plugin $plugin): \MoodlePluginCI\MissingStrings\ValidationResult
    {
        $result = new \MoodlePluginCI\MissingStrings\ValidationResult();

        try {
            // Try JSON format first (preferred)
            $jsonFile = $plugin->directory . '/db/subplugins.json';
            $phpFile  = $plugin->directory . '/db/subplugins.php';

            $subpluginTypes = [];
            $actualFilePath = '';

            if (file_exists($jsonFile)) {
                $subpluginTypes = $this->parseJsonFile($jsonFile);
                $actualFilePath = $jsonFile;
            } elseif (file_exists($phpFile)) {
                $subpluginTypes = $this->parsePhpFile($phpFile);
                $actualFilePath = $phpFile;
            } else {
                $result->addRawError('No subplugins file found (db/subplugins.json or db/subplugins.php)');

                return $result;
            }

            // Generate required strings for each subplugin type
            foreach ($subpluginTypes as $typeName => $path) {
                // Each subplugin type requires two strings: singular and plural
                $singularKey = self::SUBPLUGIN_TYPE_STRING_PATTERN . $typeName;
                $pluralKey   = self::SUBPLUGIN_TYPE_STRING_PATTERN . $typeName . '_plural';

                $singularDescription = "Subplugin type: {$typeName} (singular)";
                $pluralDescription   = "Subplugin type: {$typeName} (plural)";

                // Use the trait helper method for string literal detection
                $this->addRequiredStringWithStringLiteral($result, $singularKey, $actualFilePath, $typeName, $singularDescription);
                $this->addRequiredStringWithStringLiteral($result, $pluralKey, $actualFilePath, $typeName, $pluralDescription);
            }
        } catch (\Exception $e) {
            $result->addRawError('Error parsing subplugins file: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Parse JSON subplugins file.
     *
     * @param string $filePath path to the JSON file
     *
     * @throws \Exception if the file cannot be parsed
     *
     * @return array array of subplugin types
     */
    private function parseJsonFile(string $filePath): array
    {
        $data = $this->loadJsonFile($filePath);

        // Support both new format (subplugintypes) and legacy format (plugintypes)
        if (isset($data['subplugintypes']) && is_array($data['subplugintypes'])) {
            return $data['subplugintypes'];
        }

        if (isset($data['plugintypes']) && is_array($data['plugintypes'])) {
            return $data['plugintypes'];
        }

        throw new \Exception('No valid subplugin types found in db/subplugins.json');
    }

    /**
     * Parse PHP subplugins file.
     *
     * @param string $filePath path to the PHP file
     *
     * @throws \Exception if the file cannot be parsed
     *
     * @return array array of subplugin types
     */
    private function parsePhpFile(string $filePath): array
    {
        $vars = $this->loadPhpFile($filePath);

        if (!isset($vars['subplugins']) || !is_array($vars['subplugins'])) {
            throw new \Exception('No valid $subplugins array found in db/subplugins.php');
        }

        return $vars['subplugins'];
    }
}
