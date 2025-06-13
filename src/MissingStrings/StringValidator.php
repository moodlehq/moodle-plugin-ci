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

namespace MoodlePluginCI\MissingStrings;

use MoodlePluginCI\MissingStrings\Checker\CheckersRegistry;
use MoodlePluginCI\MissingStrings\Checker\FileDiscoveryAwareInterface;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\Discovery\SubpluginDiscovery;
use MoodlePluginCI\MissingStrings\Exception\FileException;
use MoodlePluginCI\MissingStrings\Extractor\StringExtractor;
use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;
use MoodlePluginCI\MissingStrings\Requirements\StringRequirementsResolver;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Main string validation orchestrator.
 *
 * Coordinates all aspects of string validation including:
 * - Required string validation based on plugin type
 * - Database file analysis for required strings
 * - Code analysis for used strings
 * - Unused string detection
 * - Custom checker execution
 */
class StringValidator
{
    /**
     * String checkers to run.
     *
     * @var StringCheckerInterface[]
     */
    private $checkers;

    /**
     * Requirements resolver.
     *
     * @var StringRequirementsResolver
     */
    private $requirementsResolver;

    /**
     * String extraction service.
     *
     * @var StringExtractor
     */
    private $extractor;

    /**
     * Plugin to be validated.
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * Moodle instance.
     *
     * @var mixed
     */
    private $moodle;

    /**
     * Validation configuration.
     *
     * @var ValidationConfig
     */
    private $config;

    /**
     * File discovery service.
     *
     * @var FileDiscovery
     */
    private $fileDiscovery;

    /**
     * Error handler for consistent error processing.
     *
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * Subplugin discovery service.
     *
     * @var SubpluginDiscovery
     */
    private $subpluginDiscovery;

    /**
     * Constructor.
     *
     * @param Plugin           $plugin the plugin to validate
     * @param mixed            $moodle the Moodle instance
     * @param ValidationConfig $config validation configuration
     */
    public function __construct(
        Plugin $plugin,
        $moodle,
        ValidationConfig $config
    ) {
        $this->plugin = $plugin;
        $this->moodle = $moodle;
        $this->config = $config;

        // Initialize checkers from registry and config
        $this->checkers = $this->config->shouldUseDefaultCheckers()
            ? CheckersRegistry::getCheckers()
            : [];

        // Add custom checkers from config
        $this->checkers = array_merge($this->checkers, $this->config->getCustomCheckers());

        // Initialize requirements resolver
        $this->requirementsResolver = new StringRequirementsResolver();

        // Initialize extraction service
        $this->extractor = new StringExtractor();

        // Initialize file discovery service
        $this->fileDiscovery = new FileDiscovery($plugin);

        // Inject file discovery into extraction service
        $this->extractor->setFileDiscovery($this->fileDiscovery);

        // Initialize subplugin discovery service
        /* @psalm-suppress UndefinedClass */
        $this->subpluginDiscovery = new SubpluginDiscovery();
    }

    /**
     * Validate all strings in the plugin and its subplugins.
     *
     * @return ValidationResult the result of the validation
     */
    public function validate(): ValidationResult
    {
        $result             = new ValidationResult($this->config->isStrict());
        $this->errorHandler = new ErrorHandler($result, $this->config->isDebugEnabled());

        // Validate the main plugin
        $this->validateSinglePlugin($this->plugin);

        // Discover and validate subplugins
        $this->errorHandler->safeExecute(
            fn () => $this->validateSubplugins(),
            'Validating subplugins'
        );

        return $result;
    }

    /**
     * Validate a single plugin (main plugin or subplugin).
     *
     * @param Plugin $plugin the plugin to validate
     */
    private function validateSinglePlugin(Plugin $plugin): void
    {
        // Store original plugin and file discovery
        $originalPlugin        = $this->plugin;
        $originalFileDiscovery = $this->fileDiscovery;
        $originalExtractor     = $this->extractor;

        try {
            // Temporarily switch to the plugin being validated
            $this->plugin        = $plugin;
            $this->fileDiscovery = new FileDiscovery($plugin);
            $this->extractor     = new StringExtractor();
            $this->extractor->setFileDiscovery($this->fileDiscovery);

            // Get defined strings from language file
            $definedStrings = $this->errorHandler->safeExecute(
                fn () => $this->getDefinedStrings(),
                "Loading language file for {$plugin->component}"
            );

            // Basic validation - check if language file exists
            if (empty($definedStrings)) {
                $langFileName = $this->getLangFileName();
                $langFile     = "lang/{$this->config->getLanguage()}/{$langFileName}.php";
                $this->errorHandler->addError(
                    'Language file not found or empty',
                    [
                        'file'      => $langFile,
                        'component' => $plugin->component,
                    ]
                );

                return;
            }

            // Get plugin-specific requirements
            $requirements = $this->errorHandler->safeExecute(
                fn () => $this->requirementsResolver->resolve($plugin, $this->moodle->getBranch()),
                "Resolving plugin requirements for {$plugin->component}"
            );

            if ($requirements) {
                // Validate required strings from requirements based on the plugin type.
                $this->errorHandler->safeExecute(
                    fn () => $this->validateRequiredStrings($requirements->getRequiredStrings(), $definedStrings),
                    "Validating required strings for {$plugin->component}"
                );
            }

            // Run string checkers for database files and other sources.
            $this->errorHandler->safeExecute(
                fn () => $this->runStringCheckers($definedStrings),
                "Running string checkers for {$plugin->component}"
            );

            // Find and validate used strings in the plugin code.
            $this->errorHandler->safeExecute(
                fn () => $this->validateUsedStrings($definedStrings),
                "Validating used strings for {$plugin->component}"
            );

            // Check for unused strings if requested.
            if ($this->config->shouldCheckUnused()) {
                $this->errorHandler->safeExecute(
                    fn () => $this->validateUnusedStrings($definedStrings, $requirements),
                    "Checking for unused strings in {$plugin->component}"
                );
            }
        } finally {
            // Restore original plugin and file discovery
            $this->plugin        = $originalPlugin;
            $this->fileDiscovery = $originalFileDiscovery;
            $this->extractor     = $originalExtractor;
        }
    }

    /**
     * Discover and validate all subplugins.
     */
    private function validateSubplugins(): void
    {
        $subplugins = $this->subpluginDiscovery->discoverSubplugins($this->plugin);

        if (empty($subplugins)) {
            return;
        }

        foreach ($subplugins as $subplugin) {
            $this->errorHandler->safeExecute(
                fn () => $this->validateSinglePlugin($subplugin),
                "Validating subplugin {$subplugin->component}",
                true // Allow continuing on errors for subplugins
            );
        }
    }

    /**
     * Get defined strings from language file.
     *
     * @return array the defined strings
     */
    private function getDefinedStrings(): array
    {
        // Get the correct language file name based on plugin type
        $langFileName = $this->getLangFileName();
        $langFile     = $this->plugin->directory . "/lang/{$this->config->getLanguage()}/{$langFileName}.php";

        if (!file_exists($langFile)) {
            throw FileException::fileNotFound($langFile, ['component' => $this->plugin->component]);
        }

        if (!is_readable($langFile)) {
            throw FileException::fileNotReadable($langFile, ['component' => $this->plugin->component]);
        }

        $string = [];

        try {
            include $langFile;
        } catch (\Throwable $e) {
            throw FileException::parsingError($langFile, $e->getMessage(), [], $e);
        }

        return $string;
    }

    /**
     * Get the correct language file name based on plugin type.
     * For modules (mod), use just the plugin name. For others, use the full component name.
     *
     * @return string The language file name without .php extension
     */
    private function getLangFileName(): string
    {
        return 'mod' === $this->plugin->type
            ? $this->plugin->name
            : $this->plugin->component;
    }

    /**
     * Validate required strings against defined strings.
     *
     * @param array $requiredStrings the required strings to validate (string key => context pairs)
     * @param array $definedStrings  the defined strings
     */
    private function validateRequiredStrings(array $requiredStrings, array $definedStrings): void
    {
        foreach ($requiredStrings as $stringKey => $context) {
            // Handle both array formats: ['key1', 'key2'] and ['key1' => 'context1', 'key2' => 'context2']
            if (is_numeric($stringKey)) {
                // Array of string keys without context
                $stringKey = $context;
                $context   = '';
            }

            if ($this->config->shouldExcludeString($stringKey)) {
                continue;
            }

            if (!array_key_exists($stringKey, $definedStrings)) {
                $errorContext = [
                    'string_key' => $stringKey,
                    'component'  => $this->plugin->component,
                ];

                // Add context information if available
                if ($context instanceof StringContext) {
                    // Convert StringContext to array format
                    $contextArray = $context->toArray();
                    $errorContext = array_merge($errorContext, $contextArray);
                } elseif (!empty($context)) {
                    $errorContext['context'] = $context;
                }

                $this->errorHandler->addError(
                    'Missing required string',
                    $errorContext
                );
            } else {
                // Count successful validations without displaying them
                $this->errorHandler->getResult()->addSuccess('');
            }
        }
    }

    /**
     * Validate used strings against defined strings.
     *
     * @param array $definedStrings the defined strings
     */
    private function validateUsedStrings(array $definedStrings): void
    {
        $usedStrings = $this->extractor->extractFromPlugin($this->plugin);

        foreach ($usedStrings as $stringKey => $usages) {
            if ($this->config->shouldExcludeString($stringKey)) {
                continue;
            }

            if (!array_key_exists($stringKey, $definedStrings)) {
                // Get the first usage for context
                $firstUsage = $usages[0];
                $this->errorHandler->addError(
                    'Missing used string',
                    [
                        'string_key' => $stringKey,
                        'file'       => $firstUsage['file'],
                        'line'       => $firstUsage['line'],
                        'component'  => $this->plugin->component,
                    ]
                );
            } else {
                // Count successful validations without displaying them
                $this->errorHandler->getResult()->addSuccess('');
            }
        }
    }

    /**
     * Validate unused strings (defined but not used).
     *
     * @param array $definedStrings the defined strings
     * @param mixed $requirements   plugin requirements (may be null if resolution failed)
     */
    private function validateUnusedStrings(array $definedStrings, $requirements): void
    {
        $usedStrings = $this->extractor->extractFromPlugin($this->plugin);

        // Get required strings to avoid marking them as unused
        $requiredStrings = [];
        if ($requirements) {
            $requiredStrings = $requirements->getRequiredStrings();
        }

        foreach ($definedStrings as $stringKey => $stringValue) {
            if ($this->config->shouldExcludeString($stringKey)) {
                continue;
            }

            // Don't report required strings as unused
            if (in_array($stringKey, $requiredStrings, true)) {
                continue;
            }

            if (!isset($usedStrings[$stringKey])) {
                $this->errorHandler->addWarning(
                    'Unused string (defined but not used)',
                    [
                        'string_key' => $stringKey,
                        'component'  => $this->plugin->component,
                    ]
                );
            }
        }
    }

    /**
     * Set custom checkers.
     *
     * @param StringCheckerInterface[] $checkers array of checker instances
     */
    public function setCheckers(array $checkers): void
    {
        $this->checkers = $checkers;
    }

    /**
     * Add a string checker.
     *
     * @param StringCheckerInterface $checker the checker to add
     */
    public function addChecker(StringCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    /**
     * Run all applicable string checkers.
     *
     * @param array $definedStrings the defined strings
     */
    private function runStringCheckers(array $definedStrings): void
    {
        foreach ($this->checkers as $checker) {
            // Inject file discovery service if the checker supports it
            if ($checker instanceof FileDiscoveryAwareInterface) {
                $checker->setFileDiscovery($this->fileDiscovery);
            }

            if (!$checker->appliesTo($this->plugin)) {
                continue;
            }

            $this->errorHandler->safeExecute(function () use ($checker, $definedStrings) {
                $checkResult = $checker->check($this->plugin);

                // Add any errors or warnings from the checker
                foreach ($checkResult->getErrors() as $error) {
                    $this->errorHandler->addError(
                        $error,
                        ['checker' => $checker->getName()]
                    );
                }

                foreach ($checkResult->getWarnings() as $warning) {
                    $this->errorHandler->addWarning(
                        $warning,
                        ['checker' => $checker->getName()]
                    );
                }

                // Validate required strings found by the checker
                $this->validateRequiredStrings(
                    $checkResult->getRequiredStrings(),
                    $definedStrings
                );
            }, "Running checker {$checker->getName()}", true);
        }
    }
}
