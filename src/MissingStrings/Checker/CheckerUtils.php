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

namespace MoodlePluginCI\MissingStrings\Checker;

use MoodlePluginCI\PluginValidate\Plugin;

// Define PHP 8.0+ token constants for PHP 7.4 compatibility
if (!defined('T_NAME_QUALIFIED')) {
    define('T_NAME_QUALIFIED', -1);
}
if (!defined('T_NAME_FULLY_QUALIFIED')) {
    define('T_NAME_FULLY_QUALIFIED', -2);
}

/**
 * Utility class providing common functionality for all checkers.
 */
class CheckerUtils
{
    /**
     * Load and parse a PHP file safely with specific variable extraction.
     *
     * @param string $filePath     Path to the PHP file
     * @param string $variableName Name of the variable to extract (e.g., 'capabilities', 'caches')
     *
     * @return array|null Extracted variable data or null if file doesn't exist or has errors
     */
    public static function loadPhpFile(string $filePath, string $variableName = 'data'): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        // Define constants before including the file.
        self::defineConstantsFromFile($filePath);

        try {
            // Initialize the expected variable
            $$variableName = [];

            // Include the file - skip complex constant parsing for now
            include $filePath;

            // Return the specific variable
            return $$variableName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse a PHP file and define any constants that are referenced but not defined.
     *
     * @param string $filePath Path to the PHP file
     */
    private static function defineConstantsFromFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            return;
        }

        $tokens    = self::parsePhpTokens($content);
        $constants = self::extractConstantsFromTokens($tokens);

        // Define constants before including the file
        foreach ($constants as $constantName) {
            if (!defined($constantName)) {
                define($constantName, '1');
            }
        }
    }

    /**
     * Extract constant names from PHP tokens.
     *
     * @param array $tokens PHP tokens
     *
     * @return array Array of constant names found in the code
     */
    private static function extractConstantsFromTokens(array $tokens): array
    {
        $constants  = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i])) {
                // Handle T_STRING tokens (bare constants)
                if (T_STRING === $tokens[$i][0]) {
                    $tokenValue = $tokens[$i][1];

                    // Check if this looks like a constant (all uppercase with underscores)
                    if (self::looksLikeConstant($tokenValue)) {
                        $constants[] = $tokenValue;
                    }
                }
                // Handle T_CONSTANT_ENCAPSED_STRING tokens (quoted strings)
                elseif (T_CONSTANT_ENCAPSED_STRING === $tokens[$i][0]) {
                    $stringValue = trim($tokens[$i][1], '"\'');

                    // Check if this looks like a constant and might be used in defined() calls
                    if (self::looksLikeConstant($stringValue)) {
                        // Look back to see if this is part of a defined() call
                        $isDefinedCall = false;
                        for ($j = $i - 1; $j >= max(0, $i - 5); --$j) {
                            if (is_array($tokens[$j]) && T_STRING === $tokens[$j][0] && 'defined' === $tokens[$j][1]) {
                                $isDefinedCall = true;
                                break;
                            }
                        }

                        if ($isDefinedCall) {
                            $constants[] = $stringValue;
                        }
                    }
                }
            }
        }

        return array_unique($constants);
    }

    /**
     * Check if a string looks like a PHP constant.
     *
     * @param string $name The string to check
     *
     * @return bool True if it looks like a constant
     */
    private static function looksLikeConstant(string $name): bool
    {
        // Constants are typically all uppercase with underscores
        // and don't start with numbers
        return 1 === preg_match('/^[A-Z][A-Z0-9_]*$/', $name);
    }

    /**
     * Load and parse a JSON file safely.
     *
     * @param string $filePath Path to the JSON file
     *
     * @return array|null Parsed data or null if file doesn't exist or has errors
     */
    public static function loadJsonFile(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if (false === $content) {
            return null;
        }

        $data = json_decode($content, true);

        return JSON_ERROR_NONE === json_last_error() ? $data : null;
    }

    /**
     * Find PHP files in a directory recursively.
     *
     * @param string $directory Directory to search
     * @param string $pattern   Optional filename pattern (e.g., '*.php')
     *
     * @return array Array of file paths
     */
    public static function findPhpFiles(string $directory, string $pattern = '*.php'): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Parse PHP tokens from file content.
     *
     * @param string $content PHP file content
     *
     * @return array Array of tokens
     */
    public static function parsePhpTokens(string $content): array
    {
        return token_get_all($content);
    }

    /**
     * Extract class information from PHP tokens.
     *
     * @param array $tokens PHP tokens
     *
     * @return array Class information: ['name' => string, 'interfaces' => array, 'parent' => string|null, 'methods' => array]
     */
    public static function extractClassInfo(array $tokens): array
    {
        $classes    = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i]) && T_CLASS === $tokens[$i][0]) {
                $classInfo = self::parseClassDeclaration($tokens, $i);
                if ($classInfo) {
                    $classes[] = $classInfo;
                }
            }
        }

        return $classes;
    }

    /**
     * Parse a class declaration from tokens.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex Index where class token was found
     *
     * @return array|null Class information or null if parsing failed
     */
    private static function parseClassDeclaration(array $tokens, int $startIndex): ?array
    {
        $tokenCount  = count($tokens);
        $className   = null;
        $parentClass = null;
        $interfaces  = [];
        $methods     = [];

        // Find class name
        for ($i = $startIndex + 1; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i]) && T_STRING === $tokens[$i][0]) {
                $className = $tokens[$i][1];
                break;
            }
        }

        if (!$className) {
            return null;
        }

        // Find extends and implements
        for ($i = $startIndex; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i])) {
                if (T_EXTENDS === $tokens[$i][0]) {
                    $parentClass = self::getNextStringToken($tokens, $i);
                } elseif (T_IMPLEMENTS === $tokens[$i][0]) {
                    $interfaces = self::getImplementedInterfaces($tokens, $i);
                } elseif (T_FUNCTION === $tokens[$i][0]) {
                    $methodName = self::getNextStringToken($tokens, $i);
                    if ($methodName) {
                        $methods[] = $methodName;
                    }
                }
            }

            // Stop at class opening brace
            if ('{' === $tokens[$i]) {
                break;
            }
        }

        return [
            'name'       => $className,
            'parent'     => $parentClass,
            'interfaces' => $interfaces,
            'methods'    => $methods,
        ];
    }

    /**
     * Get the next string token after a given position.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex Starting index
     *
     * @return string|null Next string token or null
     */
    private static function getNextStringToken(array $tokens, int $startIndex): ?string
    {
        $tokenCount = count($tokens);

        for ($i = $startIndex + 1; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i])) {
                // Handle different token types for class/interface names
                $tokenType = $tokens[$i][0];
                if (T_STRING === $tokenType) {
                    return $tokens[$i][1];
                }

                // Handle PHP 8.0+ tokens if they exist and are not our fallback values
                if (defined('T_NAME_QUALIFIED') && T_NAME_QUALIFIED !== -1 && T_NAME_QUALIFIED === $tokenType) {
                    return $tokens[$i][1];
                }
                if (defined('T_NAME_FULLY_QUALIFIED') && T_NAME_FULLY_QUALIFIED !== -2 && T_NAME_FULLY_QUALIFIED === $tokenType) {
                    return $tokens[$i][1];
                }
            }
        }

        return null;
    }

    /**
     * Get implemented interfaces from tokens.
     *
     * @param array $tokens     PHP tokens
     * @param int   $startIndex Starting index (implements token)
     *
     * @return array Array of interface names
     */
    private static function getImplementedInterfaces(array $tokens, int $startIndex): array
    {
        $interfaces = [];
        $tokenCount = count($tokens);

        for ($i = $startIndex + 1; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i])) {
                // Handle different token types for interface names
                $tokenType = $tokens[$i][0];
                if (T_STRING === $tokenType) {
                    $interfaces[] = $tokens[$i][1];
                }

                // Handle PHP 8.0+ tokens if they exist and are not our fallback values
                if (defined('T_NAME_QUALIFIED') && T_NAME_QUALIFIED !== -1 && T_NAME_QUALIFIED === $tokenType) {
                    $interfaces[] = $tokens[$i][1];
                }
                if (defined('T_NAME_FULLY_QUALIFIED') && T_NAME_FULLY_QUALIFIED !== -2 && T_NAME_FULLY_QUALIFIED === $tokenType) {
                    $interfaces[] = $tokens[$i][1];
                }
            } elseif ('{' === $tokens[$i]) {
                break;
            }
        }

        return $interfaces;
    }

    /**
     * Check if a class implements a specific interface.
     *
     * @param array  $classInfo     Class information from extractClassInfo()
     * @param string $interfaceName Interface name to check (can be partial)
     *
     * @return bool True if class implements the interface
     */
    public static function implementsInterface(array $classInfo, string $interfaceName): bool
    {
        $interfaces = $classInfo['interfaces'] ?? [];

        // Handle individual interface matching (for single tokens)
        foreach ($interfaces as $implementedInterface) {
            if (false !== strpos($implementedInterface, $interfaceName)) {
                return true;
            }
        }

        // Handle namespaced interface matching (for tokenized interfaces)
        // Join all interface tokens with backslashes to reconstruct the full interface name
        $fullInterfaceName = '\\' . implode('\\', $interfaces);
        if (false !== strpos($fullInterfaceName, $interfaceName)) {
            return true;
        }

        // Also check for pattern matching in the token sequence
        // For example, to find "metadata\provider" in ["core_privacy", "local", "metadata", "provider"]
        $interfaceParts = preg_split('/[\\\\\/]/', $interfaceName);
        if (count($interfaceParts) > 1) {
            // Look for consecutive matches in the interface tokens
            $targetCount    = count($interfaceParts);
            $interfaceCount = count($interfaces);

            for ($i = 0; $i <= $interfaceCount - $targetCount; ++$i) {
                $matches = true;
                for ($j = 0; $j < $targetCount; ++$j) {
                    if ($interfaces[$i + $j] !== $interfaceParts[$j]) {
                        $matches = false;
                        break;
                    }
                }
                if ($matches) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a class extends a specific parent class.
     *
     * @param array  $classInfo  Class information from extractClassInfo()
     * @param string $parentName Parent class name to check
     *
     * @return bool True if class extends the parent
     */
    public static function extendsClass(array $classInfo, string $parentName): bool
    {
        return ($classInfo['parent'] ?? null) === $parentName;
    }

    /**
     * Check if a class has a specific method.
     *
     * @param array  $classInfo  Class information from extractClassInfo()
     * @param string $methodName Method name to check
     *
     * @return bool True if class has the method
     */
    public static function hasMethod(array $classInfo, string $methodName): bool
    {
        return in_array($methodName, $classInfo['methods'] ?? [], true);
    }

    /**
     * Get the database file path for a plugin.
     *
     * @param Plugin $plugin   The plugin
     * @param string $filename Database filename (e.g., 'access.php', 'caches.php')
     *
     * @return string Full path to the database file
     */
    public static function getDatabaseFilePath(Plugin $plugin, string $filename): string
    {
        return $plugin->directory . '/db/' . $filename;
    }

    /**
     * Check if a plugin has a specific database file.
     *
     * @param Plugin $plugin   The plugin
     * @param string $filename Database filename
     *
     * @return bool True if the file exists
     */
    public static function hasDatabaseFile(Plugin $plugin, string $filename): bool
    {
        return file_exists(self::getDatabaseFilePath($plugin, $filename));
    }

    /**
     * Remove component prefix from a string key.
     *
     * @param string $key       String key that might have component prefix
     * @param string $component Component name to remove
     *
     * @return string Key without component prefix
     */
    public static function removeComponentPrefix(string $key, string $component): string
    {
        $prefix = $component . ':';
        if (0 === strpos($key, $prefix)) {
            return substr($key, strlen($prefix));
        }

        return $key;
    }

    /**
     * Normalize a string key by removing common prefixes and suffixes.
     *
     * @param string $key String key to normalize
     *
     * @return string Normalized key
     */
    public static function normalizeStringKey(string $key): string
    {
        // Remove common prefixes
        $prefixes = ['mod_', 'block_', 'local_', 'admin_', 'core_'];
        foreach ($prefixes as $prefix) {
            if (0 === strpos($key, $prefix)) {
                $key = substr($key, strlen($prefix));
                break;
            }
        }

        return $key;
    }
}
