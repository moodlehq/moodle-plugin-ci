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

use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

// Define PHP 8.0+ token constants for PHP 7.4 compatibility
if (!defined('T_NAME_QUALIFIED')) {
    define('T_NAME_QUALIFIED', -1);
}
if (!defined('T_NAME_FULLY_QUALIFIED')) {
    define('T_NAME_FULLY_QUALIFIED', -2);
}

/**
 * Abstract base class for class method checkers.
 *
 * Provides common functionality for analyzing PHP class files
 * to detect string requirements based on implemented interfaces,
 * extended classes, and method implementations.
 */
abstract class AbstractClassChecker implements StringCheckerInterface
{
    /**
     * Analyze classes in the plugin and extract required strings.
     *
     * @param Plugin $plugin the plugin to analyze
     *
     * @return ValidationResult the result containing required strings
     */
    abstract protected function analyzeClasses(Plugin $plugin): ValidationResult;

    /**
     * Check for required strings in the plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return ValidationResult the result containing required strings
     */
    public function check(Plugin $plugin): ValidationResult
    {
        try {
            return $this->analyzeClasses($plugin);
        } catch (\Exception $e) {
            $result = new ValidationResult();
            $result->addRawError('Error analyzing classes: ' . $e->getMessage());

            return $result;
        }
    }

    /**
     * Find PHP class files in the plugin.
     *
     * @param Plugin $plugin       the plugin to search
     * @param string $subdirectory Optional subdirectory to search in (e.g., 'classes/privacy').
     *
     * @return array array of file paths
     */
    protected function findClassFiles(Plugin $plugin, string $subdirectory = 'classes'): array
    {
        $classesDir = $plugin->directory . '/' . $subdirectory;
        if (!is_dir($classesDir)) {
            return [];
        }

        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($classesDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && 'php' === $file->getExtension()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Parse a PHP file and extract basic class information.
     *
     * @param string $filePath path to the PHP file
     *
     * @throws \Exception if the file cannot be parsed
     *
     * @return array class information including name, interfaces, parent class, methods
     */
    protected function parseClassFile(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \Exception("File is not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \Exception("Cannot read file: {$filePath}");
        }

        $tokens    = token_get_all($content);
        $classInfo = [
            'name'       => null,
            'namespace'  => null,
            'interfaces' => [],
            'parent'     => null,
            'methods'    => [],
            'file'       => $filePath,
        ];

        $this->parseTokens($tokens, $classInfo);

        return $classInfo;
    }

    /**
     * Parse PHP tokens to extract class information.
     *
     * @param array $tokens     PHP tokens from token_get_all()
     * @param array &$classInfo Class information array to populate
     */
    private function parseTokens(array $tokens, array &$classInfo): void
    {
        $tokenCount       = count($tokens);
        $currentNamespace = '';

        for ($i = 0; $i < $tokenCount; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            switch ($token[0]) {
                case T_NAMESPACE:
                    $currentNamespace       = $this->extractNamespace($tokens, $i);
                    $classInfo['namespace'] = $currentNamespace;
                    break;
                case T_CLASS:
                    $this->parseClassDeclaration($tokens, $i, $classInfo);
                    break;
                case T_FUNCTION:
                    if ($classInfo['name']) { // Only parse methods if we're inside a class
                        $methodName = $this->extractMethodName($tokens, $i);
                        if ($methodName) {
                            $classInfo['methods'][] = $methodName;
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Extract namespace from tokens.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex index of T_NAMESPACE token
     *
     * @return string the namespace name
     */
    private function extractNamespace(array $tokens, int $startIndex): string
    {
        $namespace = '';
        $i         = $startIndex + 1;

        while ($i < count($tokens)) {
            $token = $tokens[$i];

            if (is_array($token)) {
                if (T_STRING === $token[0] || T_NAME_QUALIFIED === $token[0]) {
                    $namespace .= $token[1];
                } elseif (T_NS_SEPARATOR === $token[0]) {
                    $namespace .= '\\';
                }
            } elseif (';' === $token || '{' === $token) {
                break;
            }

            ++$i;
        }

        return trim($namespace);
    }

    /**
     * Parse class declaration to extract name, parent, and interfaces.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex index of T_CLASS token
     * @param array &$classInfo Class information array to populate
     */
    private function parseClassDeclaration(array $tokens, int $startIndex, array &$classInfo): void
    {
        $i             = $startIndex + 1;
        $mode          = 'name'; // name, extends, implements
        $currentParent = '';

        while ($i < count($tokens)) {
            $token = $tokens[$i];

            if ('{' === $token) {
                break;
            }

            if (is_array($token)) {
                switch ($token[0]) {
                    case T_STRING:
                    case T_NAME_QUALIFIED:
                    case T_NAME_FULLY_QUALIFIED:
                        if ('name' === $mode && !$classInfo['name']) {
                            $classInfo['name'] = $token[1];
                        } elseif ('extends' === $mode) {
                            $currentParent .= $token[1];
                        } elseif ('implements' === $mode) {
                            $classInfo['interfaces'][] = $token[1];
                        }
                        break;
                    case T_NS_SEPARATOR:
                        if ('extends' === $mode) {
                            $currentParent .= '\\';
                        }
                        break;
                    case T_EXTENDS:
                        $mode          = 'extends';
                        $currentParent = '';
                        break;
                    case T_IMPLEMENTS:
                        if ('extends' === $mode && $currentParent) {
                            $classInfo['parent'] = $currentParent;
                        }
                        $mode = 'implements';
                        break;
                }
            } elseif (',' === $token && 'implements' === $mode && $currentParent) {
                // Handle multiple interfaces
                continue;
            }

            ++$i;
        }

        // Handle case where class ends without implements clause
        if ('extends' === $mode && $currentParent) {
            $classInfo['parent'] = $currentParent;
        }
    }

    /**
     * Extract method name from tokens.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex index of T_FUNCTION token
     *
     * @return string|null the method name or null if not found
     */
    private function extractMethodName(array $tokens, int $startIndex): ?string
    {
        $i = $startIndex + 1;

        while ($i < count($tokens)) {
            $token = $tokens[$i];

            if (is_array($token) && T_STRING === $token[0]) {
                return $token[1];
            }

            if ('(' === $token) {
                break;
            }

            ++$i;
        }

        return null;
    }

    /**
     * Check if a class implements a specific interface.
     *
     * @param array  $classInfo class information from parseClassFile()
     * @param string $interface interface name to check (can be partial)
     *
     * @return bool true if the interface is implemented
     */
    protected function implementsInterface(array $classInfo, string $interface): bool
    {
        foreach ($classInfo['interfaces'] as $implementedInterface) {
            if (false !== strpos($implementedInterface, $interface)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a class has a specific method.
     *
     * @param array  $classInfo  class information from parseClassFile()
     * @param string $methodName method name to check
     *
     * @return bool true if the method exists
     */
    protected function hasMethod(array $classInfo, string $methodName): bool
    {
        return in_array($methodName, $classInfo['methods'], true);
    }

    /**
     * Check if a class extends a specific parent class.
     *
     * @param array  $classInfo   class information from parseClassFile()
     * @param string $parentClass parent class name to check (can be partial)
     *
     * @return bool true if the class extends the parent
     */
    protected function extendsClass(array $classInfo, string $parentClass): bool
    {
        return $classInfo['parent'] && false !== strpos($classInfo['parent'], $parentClass);
    }
}
