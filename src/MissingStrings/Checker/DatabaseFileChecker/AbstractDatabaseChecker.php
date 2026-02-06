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

use MoodlePluginCI\MissingStrings\Checker\CheckerUtils;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Abstract base class for database file checkers.
 *
 * Provides common functionality for checking database definition files
 * like db/access.php, db/caches.php, db/messages.php, etc.
 */
abstract class AbstractDatabaseChecker implements StringCheckerInterface
{
    /**
     * Get the database file path relative to plugin directory.
     *
     * @return string The relative file path (e.g., 'db/access.php').
     */
    abstract protected function getDatabaseFile(): string;

    /**
     * Parse the database file and extract required strings.
     *
     * @param string $filePath the full path to the database file
     * @param Plugin $plugin   the plugin being checked
     *
     * @return ValidationResult the result containing required strings
     */
    abstract protected function parseFile(string $filePath, Plugin $plugin): ValidationResult;

    /**
     * Check for required strings in the plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return ValidationResult the result containing required strings
     */
    public function check(Plugin $plugin): ValidationResult
    {
        $result   = new ValidationResult();
        $filePath = $plugin->directory . '/' . $this->getDatabaseFile();

        if (!file_exists($filePath)) {
            // File doesn't exist, no strings required
            return $result;
        }

        try {
            return $this->parseFile($filePath, $plugin);
        } catch (\Exception $e) {
            $result->addRawError("Error parsing {$this->getDatabaseFile()}: " . $e->getMessage());

            return $result;
        }
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if the database file exists
     */
    public function appliesTo(Plugin $plugin): bool
    {
        $filePath = $plugin->directory . '/' . $this->getDatabaseFile();

        return file_exists($filePath);
    }

    /**
     * Load and evaluate a PHP database file safely.
     *
     * @param string $filePath the path to the PHP file
     *
     * @throws \Exception if the file cannot be loaded or parsed
     *
     * @return array the extracted data array
     *
     * @psalm-suppress UnresolvableInclude
     */
    protected function loadPhpFile(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \Exception("File is not readable: {$filePath}");
        }

        // Use CheckerUtils to load the file with proper constant definitions
        $data = CheckerUtils::loadPhpFile($filePath, 'data');
        if (null === $data) {
            throw new \Exception("Cannot load or parse file: {$filePath}");
        }

        // Capture any variables defined in the file
        $originalVars = get_defined_vars();

        // Include the file again to get all variables
        // @psalm-suppress UnresolvableInclude
        include $filePath;

        // Get new variables defined by the file
        $newVars = array_diff_key(get_defined_vars(), $originalVars);

        // Remove our own variables
        unset($newVars['originalVars'], $newVars['filePath'], $newVars['data']);

        return $newVars;
    }

    /**
     * Load a JSON file safely.
     *
     * @param string $filePath the path to the JSON file
     *
     * @throws \Exception if the file cannot be loaded or parsed
     *
     * @return array the decoded JSON data
     */
    protected function loadJsonFile(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \Exception("File is not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \Exception("Cannot read file: {$filePath}");
        }

        $data = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Invalid JSON in {$filePath}: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Generate a string key based on a pattern and value.
     *
     * @param string $pattern The pattern (e.g., 'capability_{name}').
     * @param string $value   the value to substitute
     *
     * @return string the generated string key
     */
    protected function generateStringKey(string $pattern, string $value): string
    {
        return str_replace('{name}', $value, $pattern);
    }

    /**
     * Clean a string key by removing plugin prefix if present.
     *
     * @param string $key       the string key
     * @param string $component the plugin component
     *
     * @return string the cleaned key
     */
    protected function cleanStringKey(string $key, string $component): string
    {
        // Remove component prefix if present (e.g., 'mod_forum:addnews' -> 'addnews')
        $prefix = $component . ':';
        if (0 === strpos($key, $prefix)) {
            return substr($key, strlen($prefix));
        }

        return $key;
    }

    /**
     * Validate that a required array key exists.
     *
     * @param array  $data    the data array
     * @param string $key     the required key
     * @param string $context context for error messages
     *
     * @throws \Exception if the key is missing
     */
    protected function requireKey(array $data, string $key, string $context = ''): void
    {
        if (!array_key_exists($key, $data)) {
            $message = "Missing required key '{$key}'";
            if ($context) {
                $message .= " in {$context}";
            }
            throw new \Exception($message);
        }
    }
}
