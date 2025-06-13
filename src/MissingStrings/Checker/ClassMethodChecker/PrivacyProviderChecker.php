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

namespace MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker;

use MoodlePluginCI\MissingStrings\Checker\CheckerUtils;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\Checker\StringContextTrait;
use MoodlePluginCI\MissingStrings\Pattern\RegexPatterns;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for privacy provider language strings.
 *
 * Analyzes classes/privacy/provider.php to determine which privacy
 * interfaces are implemented and what language strings are required.
 */
class PrivacyProviderChecker implements StringCheckerInterface
{
    use StringContextTrait;

    private const PRIVACY_METADATA_STRING = 'privacy:metadata';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeStringUsageFinder();
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Privacy Provider';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if privacy provider file exists
     */
    public function appliesTo(Plugin $plugin): bool
    {
        return file_exists($plugin->directory . '/classes/privacy/provider.php');
    }

    /**
     * Check the plugin for required strings.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return ValidationResult the result of the check
     */
    public function check(Plugin $plugin): ValidationResult
    {
        $result = new ValidationResult();

        $providerFile = $plugin->directory . '/classes/privacy/provider.php';
        if (!file_exists($providerFile)) {
            return $result;
        }

        // Check if it's actually a file (not a directory)
        if (!is_file($providerFile)) {
            $result->addRawError('Error analyzing privacy provider: path exists but is not a readable file');

            return $result;
        }

        try {
            $content = file_get_contents($providerFile);
            if (false === $content || empty($content)) {
                $result->addRawError('Could not read privacy provider file');

                return $result;
            }

            $tokens  = CheckerUtils::parsePhpTokens($content);
            $classes = CheckerUtils::extractClassInfo($tokens);

            foreach ($classes as $classInfo) {
                $this->analyzePrivacyClass($classInfo, $content, $plugin, $result, $providerFile);
            }
        } catch (\Exception $e) {
            $result->addRawError('Error analyzing privacy provider: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Analyze a privacy provider class.
     *
     * @param array            $classInfo Class information from CheckerUtils::extractClassInfo()
     * @param string           $content   File content for method analysis
     * @param Plugin           $plugin    The plugin being analyzed
     * @param ValidationResult $result    The result to add strings to
     * @param string           $filePath  The path to the provider file
     */
    private function analyzePrivacyClass(array $classInfo, string $content, Plugin $plugin, ValidationResult $result, string $filePath): void
    {
        // Check for null provider (no data stored)
        if (CheckerUtils::implementsInterface($classInfo, 'null_provider')) {
            // Find the actual string returned from get_reason() method
            $reasonStrings = $this->findGetReasonStrings($content, $filePath);

            if (empty($reasonStrings)) {
                // Fallback to default if no strings found
                $reasonStrings[self::PRIVACY_METADATA_STRING] = 'Privacy metadata for null provider (default)';
            }

            foreach ($reasonStrings as $stringKey => $description) {
                $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $stringKey, $description);
            }
        }

        // Check for metadata provider (data stored)
        if (CheckerUtils::implementsInterface($classInfo, 'metadata\\provider')) {
            // Metadata providers only need specific field/preference description strings.
            $metadataStrings = $this->analyzeGetMetadataMethod($content, $plugin, $filePath);
            foreach ($metadataStrings as $stringKey => $description) {
                // Use the trait helper method for string literal detection
                $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $stringKey, $description);
            }
        }

        // Check for request provider (handles data requests)
        if (CheckerUtils::implementsInterface($classInfo, 'request\\provider')) {
            // These providers typically need additional strings for data export/deletion
            // but the specific strings depend on implementation
        }
    }

    /**
     * Analyze the get_metadata method to find required metadata strings.
     *
     * @param string $content  file content
     * @param Plugin $plugin   the plugin being analyzed
     * @param string $filePath the path to the provider file
     *
     * @return array array of string keys and their descriptions
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function analyzeGetMetadataMethod(string $content, Plugin $plugin, string $filePath): array
    {
        $strings = [];

        // Look for add_database_table calls
        // Pattern: add_database_table('table_name', ['field' => 'privacy:metadata:field'], 'privacy:metadata:table')
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::addDatabaseTable(), $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); ++$i) {
                $tableName        = $matches[1][$i];
                $fieldsArray      = $matches[2][$i];
                $tableDescription = isset($matches[3][$i]) && !empty($matches[3][$i]) ? $matches[3][$i] : null;

                // Extract field mappings
                // @psalm-suppress ArgumentTypeCoercion
                if (preg_match_all(RegexPatterns::fieldMapping(), $fieldsArray, $fieldMatches)) {
                    for ($j = 0; $j < count($fieldMatches[1]); ++$j) {
                        $fieldName = $fieldMatches[1][$j];
                        $stringKey = $fieldMatches[2][$j];

                        // Only include strings for this plugin component
                        if (0 === strpos($stringKey, self::PRIVACY_METADATA_STRING . ':')) {
                            $strings[$stringKey] = "Privacy metadata for table '{$tableName}', field '{$fieldName}'";
                        }
                    }
                }

                // Add table description string if provided
                if ($tableDescription && 0 === strpos($tableDescription, self::PRIVACY_METADATA_STRING . ':')) {
                    $strings[$tableDescription] = "Privacy metadata for table '{$tableName}'";
                }
            }
        }

        // Look for add_external_location_link calls
        // Pattern 1: add_external_location_link('service', 'privacy:metadata:service', 'link')
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::addExternalLocationLinkSimple(), $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); ++$i) {
                $serviceName = $matches[1][$i];
                $stringKey   = $matches[2][$i];

                if (0 === strpos($stringKey, self::PRIVACY_METADATA_STRING . ':')) {
                    $strings[$stringKey] = "Privacy metadata for external service '{$serviceName}'";
                }
            }
        }

        // Pattern 2: add_external_location_link('service', ['field' => 'privacy:metadata:field'], 'privacy:metadata:service')
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::addExternalLocationLinkArray(), $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); ++$i) {
                $serviceName        = $matches[1][$i];
                $fieldsArray        = $matches[2][$i];
                $serviceDescription = $matches[3][$i];

                // Extract field mappings from the array
                // @psalm-suppress ArgumentTypeCoercion
                if (preg_match_all(RegexPatterns::fieldMapping(), $fieldsArray, $fieldMatches)) {
                    for ($j = 0; $j < count($fieldMatches[1]); ++$j) {
                        $fieldName = $fieldMatches[1][$j];
                        $stringKey = $fieldMatches[2][$j];

                        // Only include strings for this plugin component
                        if (0 === strpos($stringKey, self::PRIVACY_METADATA_STRING . ':')) {
                            $strings[$stringKey] = "Privacy metadata for external service '{$serviceName}', field '{$fieldName}'";
                        }
                    }
                }

                // Add service description string
                if ($serviceDescription && 0 === strpos($serviceDescription, self::PRIVACY_METADATA_STRING . ':')) {
                    $strings[$serviceDescription] = "Privacy metadata for external service '{$serviceName}'";
                }
            }
        }

        // Look for add_subsystem_link calls
        // Pattern: add_subsystem_link('subsystem', [], 'privacy:metadata:subsystem')
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::addSubsystemLink(), $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); ++$i) {
                $subsystemName = $matches[1][$i];
                $stringKey     = $matches[2][$i];

                if (0 === strpos($stringKey, self::PRIVACY_METADATA_STRING . ':')) {
                    $strings[$stringKey] = "Privacy metadata for subsystem '{$subsystemName}'";
                }
            }
        }

        // Look for link_subsystem calls (alternative method)
        // Pattern: link_subsystem('subsystem', 'privacy:metadata:subsystem')
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::linkSubsystem(), $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); ++$i) {
                $subsystemName = $matches[1][$i];
                $stringKey     = $matches[2][$i];

                if (0 === strpos($stringKey, self::PRIVACY_METADATA_STRING . ':')) {
                    $strings[$stringKey] = "Privacy metadata for linked subsystem '{$subsystemName}'";
                }
            }
        }

        // Look for add_user_preference calls
        // Pattern: add_user_preference('preference_name', 'privacy:preference:name') or 'privacy:metadata:preference:name'
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::addUserPreference(), $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); ++$i) {
                $stringKey = $matches[1][$i];

                // Accept both privacy:metadata: and privacy:preference: strings
                if (0 === strpos($stringKey, 'privacy:')) {
                    $strings[$stringKey] = 'Privacy metadata for user preference';
                }
            }
        }

        return $strings;
    }

    /**
     * Find strings returned from get_reason() method in null providers.
     *
     * @param string $content  file content
     * @param string $filePath the path to the provider file
     *
     * @return array array of string keys and their descriptions
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function findGetReasonStrings(string $content, string $filePath): array
    {
        $strings = [];

        // Look for return statements in get_reason method
        // This approach is more robust - find any return statement with string literals
        // @psalm-suppress ArgumentTypeCoercion
        if (preg_match_all(RegexPatterns::returnStatement(), $content, $returnMatches)) {
            foreach ($returnMatches[1] as $stringKey) {
                // Only include privacy-related strings
                if (0 === strpos($stringKey, 'privacy:')) {
                    $strings[$stringKey] = 'Privacy metadata string returned from get_reason() method';
                }
            }
        }

        return $strings;
    }
}
