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
 * Checker for search area language strings.
 *
 * Analyzes classes in classes/search/ directory to determine
 * what search:{classname} language strings are required.
 */
class SearchAreaChecker extends AbstractClassChecker
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
        return 'Search Area';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if search area classes exist
     */
    public function appliesTo(Plugin $plugin): bool
    {
        $searchFiles = $this->findClassFiles($plugin, 'classes/search');

        return !empty($searchFiles);
    }

    /**
     * Analyze search area classes and extract required strings.
     *
     * @param Plugin $plugin the plugin to analyze
     *
     * @return ValidationResult the result containing required strings
     */
    protected function analyzeClasses(Plugin $plugin): ValidationResult
    {
        $result = new ValidationResult();

        // Look for search area classes in classes/search/
        $searchFiles = $this->findClassFiles($plugin, 'classes/search');

        foreach ($searchFiles as $filePath) {
            try {
                $classInfo = $this->parseClassFile($filePath);

                // Check if class extends core_search\base or its subclasses
                if ($this->extendsClass($classInfo, 'core_search\\base')
                    || $this->extendsClass($classInfo, 'core_search\\base_mod')
                    || $this->extendsClass($classInfo, 'core_search\\base_activity')
                    || $this->extendsClass($classInfo, 'base')
                    || $this->extendsClass($classInfo, 'base_mod')
                    || $this->extendsClass($classInfo, 'base_activity')) {
                    $this->analyzeSearchAreaClass($classInfo, $result, $filePath);
                }
            } catch (\Exception $e) {
                $result->addRawError("Error analyzing search class {$filePath}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Analyze a specific search area class for required strings.
     *
     * @param array            $classInfo class information from parseClassFile()
     * @param ValidationResult $result    result object to add strings to
     * @param string           $filePath  the path to the search class file
     */
    private function analyzeSearchAreaClass(array $classInfo, ValidationResult $result, string $filePath): void
    {
        $className = $classInfo['name'];

        // The pattern is simply search:{classname}
        // Example: classes/search/post.php requires search:post string
        $stringKey   = 'search:' . strtolower($className);
        $description = "Search area display name for {$className} search class";
        $pattern     = '/class\s+' . preg_quote($className, '/') . '\s/';

        // Use the trait helper method for custom pattern detection
        $this->addRequiredStringWithCustomPattern($result, $stringKey, $filePath, $className, $pattern, $description);
    }
}
