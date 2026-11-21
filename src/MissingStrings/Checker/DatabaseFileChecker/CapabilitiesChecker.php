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
use MoodlePluginCI\MissingStrings\Checker\FileDiscoveryAwareInterface;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;
use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\MissingStrings\StringUsageFinder;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for capabilities defined in db/access.php.
 *
 * Analyzes capability definitions and determines required language strings.
 * Each capability requires a string with the same name as the capability.
 */
class CapabilitiesChecker implements StringCheckerInterface, FileDiscoveryAwareInterface
{
    /**
     * File discovery service.
     *
     * @var FileDiscovery|null
     */
    private $fileDiscovery;

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
     * Set the file discovery service.
     *
     * @param FileDiscovery $fileDiscovery the file discovery service
     */
    public function setFileDiscovery(FileDiscovery $fileDiscovery): void
    {
        $this->fileDiscovery = $fileDiscovery;
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Capabilities';
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
        if ($this->fileDiscovery) {
            return $this->fileDiscovery->hasDatabaseFile('access.php');
        }

        // Fallback to CheckerUtils
        return CheckerUtils::hasDatabaseFile($plugin, 'access.php');
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
        $result = new ValidationResult();

        // Use FileDiscovery if available, otherwise fall back to CheckerUtils
        $filePath = $this->fileDiscovery
            ? $this->fileDiscovery->getDatabaseFile('access.php')
            : CheckerUtils::getDatabaseFilePath($plugin, 'access.php');

        try {
            if (null === $filePath) {
                $result->addRawWarning('Could not find db/access.php file');

                return $result;
            }

            $capabilities = CheckerUtils::loadPhpFile($filePath, 'capabilities');

            if (null === $capabilities) {
                $result->addRawWarning('Could not load db/access.php file');

                return $result;
            }

            // @psalm-suppress TypeDoesNotContainType
            if (!is_array($capabilities)) {
                $result->addRawWarning('$capabilities is not an array in db/access.php');

                return $result;
            }

            foreach ($capabilities as $capabilityName => $capabilityDef) {
                $this->processCapability($capabilityName, $capabilityDef, $plugin, $result);
            }
        } catch (\Exception $e) {
            $result->addRawWarning('Error parsing db/access.php: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Process a single capability definition.
     *
     * @param string           $capabilityName the capability name
     * @param array            $capabilityDef  the capability definition
     * @param Plugin           $plugin         the plugin being checked
     * @param ValidationResult $result         the result to add strings to
     *
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress TypeDoesNotContainType
     */
    private function processCapability(string $capabilityName, $capabilityDef, Plugin $plugin, ValidationResult $result): void
    {
        // @psalm-suppress DocblockTypeContradiction
        if (!is_array($capabilityDef)) {
            $result->addRawWarning("Capability '{$capabilityName}' definition is not an array");

            return;
        }

        // Get the file path
        $filePath = $this->fileDiscovery
            ? $this->fileDiscovery->getDatabaseFile('access.php')
            : CheckerUtils::getDatabaseFilePath($plugin, 'access.php');

        // Extract the plugin name and capability from the full capability name
        // Capability format: "plugintype/pluginname:capability" -> we want "pluginname:capability"
        if (false !== strpos($capabilityName, '/') && false !== strpos($capabilityName, ':')) {
            // Split by '/' to get plugintype and pluginname:capability
            $parts = explode('/', $capabilityName, 2);
            if (2 === count($parts)) {
                $stringKey = $parts[1]; // This gives us "pluginname:capability"
            } else {
                $stringKey = $capabilityName;
            }
        } else {
            $stringKey = $capabilityName;
        }

        // Create context with file and description
        $context = new StringContext($filePath, null, "Capability: {$capabilityName}");

        // Find the line number where this capability is defined
        if (null !== $filePath) {
            $lineNumber = $this->usageFinder->findArrayKeyLine($filePath, $capabilityName);
            if (null !== $lineNumber) {
                $context->setLine($lineNumber);
            }
        }

        $result->addRequiredString($stringKey, $context);
    }
}
