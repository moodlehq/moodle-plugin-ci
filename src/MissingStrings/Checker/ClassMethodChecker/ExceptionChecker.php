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

use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\MissingStrings\StringUsageFinder;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for exception message language strings.
 *
 * Analyzes PHP files to find custom exception classes and
 * moodle_exception throws that require language strings.
 */
class ExceptionChecker extends AbstractClassChecker
{
    /**
     * String usage finder utility.
     *
     * @var StringUsageFinder
     */
    private $usageFinder;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->usageFinder = new StringUsageFinder();
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Exception';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if exception classes or exception throws exist
     */
    public function appliesTo(Plugin $plugin): bool
    {
        // Check all PHP files for exception usage
        $phpFiles = $this->findClassFiles($plugin, '');

        foreach ($phpFiles as $filePath) {
            $content = file_get_contents($filePath);
            if (false === $content) {
                continue;
            }

            // Look for exception-related patterns
            if ($this->hasExceptionPatterns($content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze classes and files for exception-related string requirements.
     *
     * @param Plugin $plugin the plugin to analyze
     *
     * @return ValidationResult the result containing required strings
     */
    protected function analyzeClasses(Plugin $plugin): ValidationResult
    {
        $result = new ValidationResult();

        // Find all PHP files in the plugin
        $phpFiles = $this->findClassFiles($plugin, '');

        foreach ($phpFiles as $filePath) {
            try {
                // Analyze file content for exception patterns
                $this->analyzeFileForExceptions($filePath, $result, $plugin->component);

                // Also analyze class structure if it's a class file
                if (false !== strpos($filePath, '/classes/')) {
                    $classInfo = $this->parseClassFile($filePath);
                    $this->analyzeExceptionClass($classInfo, $result);
                }
            } catch (\Exception $e) {
                $result->addRawError("Error analyzing file {$filePath}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Check if content has exception-related patterns.
     *
     * @param string $content file content to check
     *
     * @return bool true if exception patterns found
     */
    private function hasExceptionPatterns(string $content): bool
    {
        $patterns = [
            '/throw\s+new\s+.*exception/i',
            '/extends\s+.*exception/i',
            '/moodle_exception/i',
            '/coding_exception/i',
            '/invalid_parameter_exception/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze a file for exception throws and custom exception classes.
     *
     * @param string           $filePath        path to the file to analyze
     * @param ValidationResult $result          result object to add strings to
     * @param string           $pluginComponent the component name of the current plugin
     */
    private function analyzeFileForExceptions(string $filePath, ValidationResult $result, string $pluginComponent): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            return;
        }

        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            $actualLineNumber = $lineNumber + 1;

            // moodle_exception with explicit component
            if (preg_match_all('/throw\s+new\s+moodle_exception\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                for ($i = 0; $i < count($matches[1]); ++$i) {
                    $errorCode = $matches[1][$i];
                    $component = $matches[2][$i];

                    // For module plugins, the language component is just the module name, not the full plugin component
                    $expectedComponent = $this->getLanguageComponent($pluginComponent);
                    if ($component === $expectedComponent || $component === $pluginComponent) {
                        $result->addRequiredString(
                            $errorCode,
                            new StringContext($filePath, $actualLineNumber, 'moodle_exception')
                        );
                    }
                }
            }

            // moodle_exception with only error code (defaults to 'error' component, skip)
            if (preg_match_all('/throw\s+new\s+moodle_exception\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $matches)) {
                // Skip: defaults to 'error' component, not current plugin
            }

            // moodle_exception with empty component (defaults to 'error' component, skip)
            if (preg_match_all('/throw\s+new\s+moodle_exception\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"][\'"]/', $line, $matches)) {
                // Skip: empty component defaults to 'error'
            }

            // moodle_exception with 'moodle' or 'core' component (defaults to 'error' component, skip)
            if (preg_match_all('/throw\s+new\s+moodle_exception\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"](?:moodle|core)[\'"]/', $line, $matches)) {
                // Skip: 'moodle'/'core' components default to 'error'
            }

            // Other exception types
            $exceptionTypes = [
                'coding_exception',
                'invalid_parameter_exception',
                'invalid_response_exception',
                'file_exception',
                'dml_exception',
            ];

            foreach ($exceptionTypes as $exceptionType) {
                $pattern = "/throw\\s+new\\s+{$exceptionType}\\s*\\(\\s*['\"]([^'\"]+)['\"]/";
                if (preg_match_all($pattern, $line, $matches)) {
                    foreach ($matches[1] as $errorMessage) {
                        if ($this->looksLikeStringKey($errorMessage)) {
                            $result->addRequiredString(
                                $errorMessage,
                                new StringContext($filePath, $actualLineNumber, $exceptionType)
                            );
                        }
                    }
                }
            }

            // print_error with explicit component
            if (preg_match_all('/print_error\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                for ($i = 0; $i < count($matches[1]); ++$i) {
                    $errorCode = $matches[1][$i];
                    $component = $matches[2][$i];

                    // For module plugins, the language component is just the module name, not the full plugin component
                    $expectedComponent = $this->getLanguageComponent($pluginComponent);
                    if (($component === $expectedComponent || $component === $pluginComponent) && $this->looksLikeStringKey($errorCode)) {
                        $result->addRequiredString(
                            $errorCode,
                            new StringContext($filePath, $actualLineNumber, 'print_error')
                        );
                    }
                }
            }

            // print_error with empty component (defaults to 'error' component, skip)
            if (preg_match_all('/print_error\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"][\'"]/', $line, $matches)) {
                // Skip: empty component defaults to 'error'
            }

            // print_error with 'moodle' or 'core' component (defaults to 'error' component, skip)
            if (preg_match_all('/print_error\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"](?:moodle|core)[\'"]/', $line, $matches)) {
                // Skip: 'moodle'/'core' components default to 'error'
            }

            // print_error with only error code (defaults to current component)
            if (preg_match_all('/print_error\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $matches)) {
                foreach ($matches[1] as $errorCode) {
                    if ($this->looksLikeStringKey($errorCode)) {
                        $result->addRequiredString(
                            $errorCode,
                            new StringContext($filePath, $actualLineNumber, 'print_error')
                        );
                    }
                }
            }
        }
    }

    /**
     * Analyze a class that might be a custom exception.
     *
     * @param array            $classInfo class information from parseClassFile()
     * @param ValidationResult $result    result object to add strings to
     */
    private function analyzeExceptionClass(array $classInfo, ValidationResult $result): void
    {
        // Check if class extends any exception class
        if ($classInfo['parent']
            && (false !== strpos($classInfo['parent'], 'exception')
             || false !== strpos($classInfo['parent'], 'Exception'))) {
            $className = $classInfo['name'];
            $filePath  = $classInfo['file'] ?? null;

            // Custom exception classes often need error message strings
            $baseClassName = str_replace('Exception', '', $className);
            if (is_string($baseClassName)) {
                $possibleStringKeys = [
                    strtolower($className),
                    'error_' . strtolower($className),
                    strtolower($baseClassName),
                ];
            } else {
                $possibleStringKeys = [
                    strtolower($className),
                    'error_' . strtolower($className),
                ];
            }

            foreach ($possibleStringKeys as $stringKey) {
                $context = new StringContext($filePath, null, "Error message for custom exception class {$className}");

                // Try to find the line where this string key might be used if we have a file path
                if ($filePath) {
                    $lineNumber = $this->usageFinder->findStringLiteralLine($filePath, $stringKey);
                    if (null !== $lineNumber) {
                        $context->setLine($lineNumber);
                    }
                }

                $result->addRequiredString($stringKey, $context);
            }
        }
    }

    /**
     * Check if a string looks like a language string key rather than a direct message.
     *
     * @param string $string the string to check
     *
     * @return bool true if it looks like a string key
     */
    private function looksLikeStringKey(string $string): bool
    {
        // String keys are typically:
        // - lowercase with underscores
        // - no spaces
        // - no punctuation except underscores and colons
        return preg_match('/^[a-z][a-z0-9_:]*$/', $string)
               && !preg_match('/\s/', $string)
               && strlen($string) > 3;
    }

    /**
     * Get the language component name for a plugin component.
     *
     * For module plugins (mod_forum), the language component is just the module name (forum).
     * For other plugin types, it's usually the full component name.
     *
     * @param string $pluginComponent The full plugin component (e.g., 'mod_forum')
     *
     * @return string The language component (e.g., 'forum')
     */
    private function getLanguageComponent(string $pluginComponent): string
    {
        // For module plugins, strip the 'mod_' prefix
        if (0 === strpos($pluginComponent, 'mod_')) {
            return substr($pluginComponent, 4); // Remove 'mod_' prefix
        }

        // For other plugin types, return the full component
        return $pluginComponent;
    }
}
