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
use MoodlePluginCI\MissingStrings\Checker\StringContextTrait;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for cache definitions in db/caches.php.
 *
 * Analyzes cache definitions and determines required language strings.
 * Each cache definition requires a string with the pattern 'cachedef_{cachename}'.
 */
class CachesChecker implements StringCheckerInterface
{
    use StringContextTrait;

    /**
     * The pattern for cache definition strings.
     *
     * @var string
     */
    private const CACHE_DEFINITION_STRING_PATTERN = 'cachedef_';

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
        return 'Caches';
    }

    /**
     * Check if this checker applies to the given plugin.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return bool true if this checker should run for the plugin
     */
    public function appliesTo(Plugin $plugin): bool
    {
        return CheckerUtils::hasDatabaseFile($plugin, 'caches.php');
    }

    /**
     * Check the plugin for required strings.
     *
     * @param Plugin $plugin the plugin to check
     *
     * @return ValidationResult the result of the check
     *
     * @psalm-suppress TypeDoesNotContainType
     */
    public function check(Plugin $plugin): ValidationResult
    {
        $result   = new ValidationResult();
        $filePath = CheckerUtils::getDatabaseFilePath($plugin, 'caches.php');

        try {
            $definitions = CheckerUtils::loadPhpFile($filePath, 'definitions');

            if (null === $definitions) {
                $result->addRawWarning('Could not load db/caches.php file');

                return $result;
            }

            // @psalm-suppress TypeDoesNotContainType
            if (!is_array($definitions)) {
                $result->addRawWarning('$definitions is not an array in db/caches.php');

                return $result;
            }

            foreach ($definitions as $cacheName => $cacheDefinition) {
                $this->processCacheDefinition($cacheName, $cacheDefinition, $plugin, $result, $filePath);
            }
        } catch (\Exception $e) {
            $result->addRawWarning('Error parsing db/caches.php: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Process a single cache definition.
     *
     * @param string           $cacheName       the cache name
     * @param array            $cacheDefinition the cache definition
     * @param Plugin           $plugin          the plugin being checked
     * @param ValidationResult $result          the result to add strings to
     * @param string           $filePath        The path to the caches.php file.
     *
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress TypeDoesNotContainType
     */
    private function processCacheDefinition(string $cacheName, $cacheDefinition, Plugin $plugin, ValidationResult $result, string $filePath): void
    {
        // @psalm-suppress DocblockTypeContradiction
        if (!is_array($cacheDefinition)) {
            $result->addRawWarning("Cache definition '{$cacheName}' is not an array");

            return;
        }

        // Generate the required string key for this cache definition
        $stringKey   = self::CACHE_DEFINITION_STRING_PATTERN . $cacheName;
        $description = "Cache definition: {$cacheName}";

        // Use the trait helper method for array key detection
        $this->addRequiredStringWithArrayKey($result, $stringKey, $filePath, $cacheName, $description);
    }
}
