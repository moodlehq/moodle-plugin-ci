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

use MoodlePluginCI\MissingStrings\Checker\StringContextTrait;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for grade item language strings.
 *
 * Analyzes classes that implement grade item mappings (typically classes/grades/gradeitems.php)
 * to determine what gradeitem: language strings are required.
 */
class GradeItemChecker extends AbstractClassChecker
{
    use StringContextTrait;

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
        return 'Grade Item';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if grade item mapping classes exist
     */
    public function appliesTo(Plugin $plugin): bool
    {
        // Look for classes/grades/gradeitems.php file
        $gradeitemsFile = $plugin->directory . '/classes/grades/gradeitems.php';
        if (file_exists($gradeitemsFile)) {
            return true;
        }

        // Look for any class that implements itemnumber_mapping interface
        $phpFiles = $this->findClassFiles($plugin, '');
        foreach ($phpFiles as $filePath) {
            $content = file_get_contents($filePath);
            if (false === $content) {
                continue;
            }

            if ($this->hasGradeItemMapping($content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze classes for grade item mapping requirements.
     *
     * @param Plugin $plugin the plugin to analyze
     *
     * @return ValidationResult the result containing required strings
     */
    protected function analyzeClasses(Plugin $plugin): ValidationResult
    {
        $result = new ValidationResult();

        // Check the standard gradeitems.php file first
        $gradeitemsFile = $plugin->directory . '/classes/grades/gradeitems.php';
        if (file_exists($gradeitemsFile)) {
            $this->analyzeGradeItemsFile($gradeitemsFile, $result);
        }

        // Check all PHP files for grade item mapping implementations
        $phpFiles = $this->findClassFiles($plugin, '');
        foreach ($phpFiles as $filePath) {
            if ($filePath === $gradeitemsFile) {
                continue; // Already analyzed above
            }

            try {
                $this->analyzeFileForGradeItemMapping($filePath, $result);
            } catch (\Exception $e) {
                $result->addRawError("Error analyzing file {$filePath}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Check if content has grade item mapping patterns.
     *
     * @param string $content file content to check
     *
     * @return bool true if grade item mapping patterns found
     */
    private function hasGradeItemMapping(string $content): bool
    {
        return false !== strpos($content, 'itemnumber_mapping')
               || false !== strpos($content, 'advancedgrading_mapping')
               || false !== strpos($content, 'get_itemname_mapping_for_component')
               || false !== strpos($content, 'get_advancedgrading_itemnames');
    }

    /**
     * Analyze a gradeitems.php file for required strings.
     *
     * @param string           $filePath Path to the gradeitems.php file.
     * @param ValidationResult $result   result object to add strings to
     */
    private function analyzeGradeItemsFile(string $filePath, ValidationResult $result): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            $result->addRawError("Could not read file: {$filePath}");

            return;
        }

        // Extract item names from get_itemname_mapping_for_component method
        $itemNames = $this->extractItemNamesFromMapping($content);

        foreach ($itemNames as $itemName) {
            if (!empty($itemName)) { // Skip empty item names (itemnumber 0 often has empty name)
                // Add grade_{itemname}_name string requirement
                $gradeStringKey   = "grade_{$itemName}_name";
                $gradeDescription = "Grade item display name for '{$itemName}' from get_itemname_mapping_for_component()";
                $this->addRequiredStringWithStringLiteral($result, $gradeStringKey, $filePath, $itemName, $gradeDescription);
            }
        }

        // Extract item names from get_advancedgrading_itemnames method
        $advancedItemNames = $this->extractAdvancedGradingItemNames($content);

        foreach ($advancedItemNames as $itemName) {
            if (!empty($itemName)) {
                $stringKey   = "gradeitem:{$itemName}";
                $description = "Advanced grading item name for '{$itemName}' from get_advancedgrading_itemnames()";
                $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $itemName, $description);
            }
        }
    }

    /**
     * Analyze a file for grade item mapping implementations.
     *
     * @param string           $filePath path to the file to analyze
     * @param ValidationResult $result   result object to add strings to
     */
    private function analyzeFileForGradeItemMapping(string $filePath, ValidationResult $result): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            return;
        }

        if (!$this->hasGradeItemMapping($content)) {
            return;
        }

        // Extract item names from any get_itemname_mapping_for_component method
        $itemNames = $this->extractItemNamesFromMapping($content);

        foreach ($itemNames as $itemName) {
            if (!empty($itemName)) {
                // Add grade_{itemname}_name string requirement
                $gradeStringKey   = "grade_{$itemName}_name";
                $gradeDescription = "Grade item display name for '{$itemName}' from grade item mapping";
                $this->addRequiredStringWithStringLiteral($result, $gradeStringKey, $filePath, $itemName, $gradeDescription);
            }
        }

        // Extract item names from get_advancedgrading_itemnames method
        $advancedItemNames = $this->extractAdvancedGradingItemNames($content);

        foreach ($advancedItemNames as $itemName) {
            if (!empty($itemName)) {
                $stringKey   = "gradeitem:{$itemName}";
                $description = "Advanced grading item name for '{$itemName}' from get_advancedgrading_itemnames()";
                $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $itemName, $description);
            }
        }
    }

    /**
     * Extract item names from get_itemname_mapping_for_component method.
     *
     * @param string $content file content to analyze
     *
     * @return array array of item names found in the mapping
     */
    private function extractItemNamesFromMapping(string $content): array
    {
        $itemNames = [];

        // Look for get_itemname_mapping_for_component method and extract the return array
        // Match the method and its return statement more simply
        if (preg_match('/function\s+get_itemname_mapping_for_component\s*\(\s*\)\s*:\s*array\s*\{.*?return\s*\[(.*?)\];/s', $content, $matches)) {
            $arrayContent = $matches[1];

            // Extract quoted strings from the array content
            // This matches patterns like: 0 => 'submissions', 1 => 'grading', etc.
            // Use a more precise regex to match key => "value" patterns
            if (preg_match_all('/=>\s*[\'"]([^\'"]*)[\'"]/', $arrayContent, $stringMatches)) {
                $itemNames = array_merge($itemNames, $stringMatches[1]);
            }
        }

        return array_unique(array_filter($itemNames)); // Remove duplicates and empty values
    }

    /**
     * Extract item names from get_advancedgrading_itemnames method.
     *
     * @param string $content file content to analyze
     *
     * @return array array of item names found in the advanced grading method
     */
    private function extractAdvancedGradingItemNames(string $content): array
    {
        $itemNames = [];

        // Look for get_advancedgrading_itemnames method and extract the return array
        if (preg_match('/function\s+get_advancedgrading_itemnames\s*\(\s*\)\s*:\s*array\s*\{.*?return\s*\[(.*?)\];/s', $content, $matches)) {
            $arrayContent = $matches[1];

            // Extract quoted strings from the array content
            // This matches patterns like: 'forum', 'submissions', etc.
            // Match standalone quoted strings in the array
            if (preg_match_all('/^\s*[\'"]([^\'"]+)[\'"]\s*,?\s*$/m', $arrayContent, $stringMatches)) {
                $itemNames = array_merge($itemNames, $stringMatches[1]);
            }
        }

        return array_unique(array_filter($itemNames)); // Remove duplicates and empty values
    }
}
