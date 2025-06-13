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
use MoodlePluginCI\PluginValidate\Plugin;

/**
 * Checker for mobile app language strings in db/mobile.php.
 */
class MobileChecker extends AbstractDatabaseChecker
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
     * Get the database file path.
     */
    protected function getDatabaseFile(): string
    {
        return 'db/mobile.php';
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Mobile';
    }

    /**
     * Parse the database file and extract required strings.
     *
     * @param string $filePath the full path to the database file
     * @param Plugin $plugin   the plugin being checked
     */
    protected function parseFile(string $filePath, Plugin $plugin): \MoodlePluginCI\MissingStrings\ValidationResult
    {
        $result = new \MoodlePluginCI\MissingStrings\ValidationResult();

        try {
            $vars = $this->loadPhpFile($filePath);

            if (!isset($vars['addons']) || !is_array($vars['addons'])) {
                $result->addRawError('No valid $addons array found in db/mobile.php');

                return $result;
            }

            foreach ($vars['addons'] as $addonName => $addon) {
                if (!is_array($addon)) {
                    continue;
                }

                // Check if this addon has language strings defined
                if (!isset($addon['lang']) || !is_array($addon['lang'])) {
                    continue;
                }

                // Process each language string requirement
                foreach ($addon['lang'] as $langEntry) {
                    if (!is_array($langEntry) || count($langEntry) < 2) {
                        continue;
                    }

                    $stringKey = $langEntry[0];
                    $component = $langEntry[1];

                    // Only check strings for the current plugin component
                    if ($component === $plugin->component) {
                        $description = "Mobile addon '{$addonName}' language string";

                        // Use the trait helper method for string literal detection
                        $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $stringKey, $description);
                    }
                }
            }
        } catch (\Exception $e) {
            $result->addRawError('Error parsing db/mobile.php: ' . $e->getMessage());
        }

        return $result;
    }
}
