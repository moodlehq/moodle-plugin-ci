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

namespace MoodlePluginCI\Tests\MissingStrings\TestBase;

use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MoodleTestCase;

/**
 * Base test case for Missing Strings tests.
 *
 * Provides common functionality for creating test fixtures, temporary files,
 * and specialized assertions for missing strings validation.
 */
abstract class MissingStringsTestCase extends MoodleTestCase
{
    /**
     * @var array Temporary files created during test execution
     */
    protected array $tempFiles = [];

    /**
     * @var array Temporary directories created during test execution
     */
    protected array $tempDirs = [];

    /**
     * Clean up temporary files and directories after each test.
     */
    protected function tearDown(): void
    {
        // Clean up temporary files
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Clean up temporary directories
        foreach (array_reverse($this->tempDirs) as $dir) {
            if (is_dir($dir)) {
                $this->removeDirectory($dir);
            }
        }

        $this->tempFiles = [];
        $this->tempDirs  = [];

        parent::tearDown();
    }

    /**
     * Create a temporary file with the given content.
     *
     * @param string $content   File content
     * @param string $extension File extension (default: 'php')
     *
     * @return string Full path to the created file
     */
    protected function createTempFile(string $content, string $extension = 'php'): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'mci_test_') . '.' . $extension;
        file_put_contents($tempFile, $content);
        $this->tempFiles[] = $tempFile;

        return $tempFile;
    }

    /**
     * Create a temporary directory.
     *
     * @param string $prefix Directory name prefix
     *
     * @return string Full path to the created directory
     */
    protected function createTempDir(string $prefix = 'mci_test_'): string
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . uniqid();
        mkdir($tempDir, 0755, true);
        $this->tempDirs[] = $tempDir;

        return $tempDir;
    }

    /**
     * Create a test plugin structure.
     *
     * @param string $pluginType Plugin type (e.g., 'mod', 'local', 'theme')
     * @param string $pluginName Plugin name
     * @param array  $files      Array of files to create [relativePath => content]
     *
     * @return string Path to the plugin directory
     */
    protected function createTestPlugin(string $pluginType, string $pluginName, array $files = []): string
    {
        $pluginDir = $this->createTempDir("plugin_{$pluginType}_{$pluginName}_");

        // Create version.php if not provided
        if (!isset($files['version.php'])) {
            $files['version.php'] = $this->createVersionFileContent($pluginType, $pluginName);
        }

        // Create lang file if not provided
        $langFile = "lang/en/{$pluginType}_{$pluginName}.php";
        if (!isset($files[$langFile])) {
            $files[$langFile] = $this->createLanguageFileContent([
                'pluginname' => ucfirst($pluginName) . ' Plugin',
            ]);
        }

        // Create all files
        foreach ($files as $relativePath => $content) {
            $fullPath = $pluginDir . DIRECTORY_SEPARATOR . $relativePath;
            $dir      = dirname($fullPath);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($fullPath, $content);
        }

        return $pluginDir;
    }

    /**
     * Create a Plugin object for testing.
     *
     * @param string $pluginType Plugin type (e.g., 'mod', 'local', 'theme')
     * @param string $pluginName Plugin name
     * @param string $pluginDir  Plugin directory path
     *
     * @return Plugin Plugin object
     */
    protected function createPlugin(string $pluginType, string $pluginName, string $pluginDir): Plugin
    {
        $component = $pluginType . '_' . $pluginName;

        return new Plugin($component, $pluginType, $pluginName, $pluginDir);
    }

    /**
     * Create version.php file content.
     *
     * @param string $pluginType Plugin type
     * @param string $pluginName Plugin name
     *
     * @return string Version file content
     */
    protected function createVersionFileContent(string $pluginType, string $pluginName): string
    {
        $component = $pluginType . '_' . $pluginName;

        return "<?php\n\n" .
               "defined('MOODLE_INTERNAL') || die();\n\n" .
               "\$plugin->version   = 2023060100;\n" .
               "\$plugin->requires  = 2020061500;\n" .
               "\$plugin->component = '{$component}';\n";
    }

    /**
     * Create language file content.
     *
     * @param array $strings Array of string key => value pairs
     *
     * @return string Language file content
     */
    protected function createLanguageFileContent(array $strings): string
    {
        $content = "<?php\n\n" .
                   "defined('MOODLE_INTERNAL') || die();\n\n";

        foreach ($strings as $key => $value) {
            $content .= "\$string['{$key}'] = '{$value}';\n";
        }

        return $content;
    }

    /**
     * Create database file content (access.php, caches.php, etc.).
     *
     * @param string $type Database file type (e.g., 'access', 'caches')
     * @param array  $data Data structure for the file
     *
     * @return string Database file content
     */
    protected function createDatabaseFileContent(string $type, array $data): string
    {
        $content = "<?php\n\n" .
                   "defined('MOODLE_INTERNAL') || die();\n\n";

        if ('access' === $type) {
            $content .= '$capabilities = ' . var_export($data, true) . ";\n";
        } elseif ('caches' === $type) {
            $content .= '$definitions = ' . var_export($data, true) . ";\n";
        } elseif ('messages' === $type) {
            $content .= '$messageproviders = ' . var_export($data, true) . ";\n";
        } elseif ('tag' === $type) {
            $content .= '$tagareas = ' . var_export($data, true) . ";\n";
        } elseif ('mobile' === $type) {
            $content .= '$addons = ' . var_export($data, true) . ";\n";
        }

        return $content;
    }

    /**
     * Assert that two StringContext objects are equal.
     *
     * @param StringContext $expected Expected context
     * @param StringContext $actual   Actual context
     * @param string        $message  Optional message
     */
    protected function assertStringContextEquals(StringContext $expected, StringContext $actual, string $message = ''): void
    {
        $this->assertSame($expected->getFile(), $actual->getFile(), $message . ' - file path mismatch');
        $this->assertSame($expected->getLine(), $actual->getLine(), $message . ' - line number mismatch');
        $this->assertSame($expected->getDescription(), $actual->getDescription(), $message . ' - description mismatch');
    }

    /**
     * Assert that a StringContext has the expected line number.
     *
     * @param StringContext $context      StringContext to check
     * @param int           $expectedLine Expected line number
     * @param string        $message      Optional message
     */
    protected function assertStringContextHasLine(StringContext $context, int $expectedLine, string $message = ''): void
    {
        $this->assertSame($expectedLine, $context->getLine(), $message ?: "Expected line {$expectedLine}, got {$context->getLine()}");
    }

    /**
     * Assert that a ValidationResult has the expected error count.
     *
     * @param ValidationResult $result        Validation result
     * @param int              $expectedCount Expected error count
     * @param string           $message       Optional message
     */
    protected function assertErrorCount(ValidationResult $result, int $expectedCount, string $message = ''): void
    {
        $actualCount = count($result->getErrors());
        $this->assertSame($expectedCount, $actualCount, $message ?: "Expected {$expectedCount} errors, got {$actualCount}");
    }

    /**
     * Assert that a ValidationResult has the expected warning count.
     *
     * @param ValidationResult $result        Validation result
     * @param int              $expectedCount Expected warning count
     * @param string           $message       Optional message
     */
    protected function assertWarningCount(ValidationResult $result, int $expectedCount, string $message = ''): void
    {
        $actualCount = count($result->getWarnings());
        $this->assertSame($expectedCount, $actualCount, $message ?: "Expected {$expectedCount} warnings, got {$actualCount}");
    }

    /**
     * Assert that a ValidationResult contains a missing string error.
     *
     * @param ValidationResult $result    Validation result
     * @param string           $stringKey Expected missing string key
     * @param string           $message   Optional message
     */
    protected function assertHasMissingString(ValidationResult $result, string $stringKey, string $message = ''): void
    {
        $errors = $result->getErrors();
        $found  = false;

        foreach ($errors as $error) {
            if (isset($error['string_key']) && $error['string_key'] === $stringKey) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, $message ?: "Missing string '{$stringKey}' not found in errors");
    }

    /**
     * Assert that a ValidationResult contains an unused string warning.
     *
     * @param ValidationResult $result    Validation result
     * @param string           $stringKey Expected unused string key
     * @param string           $message   Optional message
     */
    protected function assertHasUnusedString(ValidationResult $result, string $stringKey, string $message = ''): void
    {
        $warnings = $result->getWarnings();
        $found    = false;

        foreach ($warnings as $warning) {
            if (isset($warning['string_key']) && $warning['string_key'] === $stringKey) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, $message ?: "Unused string '{$stringKey}' not found in warnings");
    }

    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $dir Directory path
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
