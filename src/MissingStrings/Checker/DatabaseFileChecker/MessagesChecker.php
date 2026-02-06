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

use MoodlePluginCI\MissingStrings\Checker\StringContextTrait;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for message providers defined in db/messages.php.
 *
 * Analyzes message provider definitions and determines required language strings.
 * Each message provider requires a string with the pattern 'messageprovider:{providername}'.
 */
class MessagesChecker extends AbstractDatabaseChecker
{
    use StringContextTrait;

    /**
     * The pattern for message provider strings.
     *
     * @var string
     */
    private const MESSAGE_PROVIDER_STRING_PATTERN = 'messageprovider:';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeStringUsageFinder();
    }

    /**
     * Get the database file path.
     */
    protected function getDatabaseFile(): string
    {
        return 'db/messages.php';
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Messages';
    }

    /**
     * Parse the messages.php file and extract required strings.
     *
     * @param string $filePath The full path to the messages.php file.
     * @param Plugin $plugin   the plugin being checked
     *
     * @return ValidationResult the result containing required strings
     */
    protected function parseFile(string $filePath, Plugin $plugin): ValidationResult
    {
        $result = new ValidationResult();

        try {
            $fileVars = $this->loadPhpFile($filePath);

            if (!isset($fileVars['messageproviders'])) {
                $result->addRawWarning('No $messageproviders array found in db/messages.php');

                return $result;
            }

            $messageproviders = $fileVars['messageproviders'];

            if (!is_array($messageproviders)) {
                $result->addRawWarning('$messageproviders is not an array in db/messages.php');

                return $result;
            }

            foreach ($messageproviders as $providerName => $providerDefinition) {
                $this->processMessageProvider($providerName, $providerDefinition, $plugin, $result, $filePath);
            }
        } catch (\Exception $e) {
            $result->addRawWarning('Error parsing db/messages.php: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Process a single message provider definition.
     *
     * @param string           $providerName       the provider name
     * @param array            $providerDefinition the provider definition
     * @param Plugin           $plugin             the plugin being checked
     * @param ValidationResult $result             the result to add strings to
     * @param string           $filePath           The path to the messages.php file.
     *
     * @psalm-suppress DocblockTypeContradiction
     */
    private function processMessageProvider(string $providerName, $providerDefinition, Plugin $plugin, ValidationResult $result, string $filePath): void
    {
        // @psalm-suppress DocblockTypeContradiction
        if (!is_array($providerDefinition)) {
            $result->addRawWarning("Message provider '{$providerName}' definition is not an array");

            return;
        }

        // Generate the required string key for this message provider
        $stringKey   = self::MESSAGE_PROVIDER_STRING_PATTERN . $providerName;
        $description = "Message provider: {$providerName}";

        // Use the trait helper method for array key detection
        $this->addRequiredStringWithArrayKey($result, $stringKey, $filePath, $providerName, $description);
    }
}
