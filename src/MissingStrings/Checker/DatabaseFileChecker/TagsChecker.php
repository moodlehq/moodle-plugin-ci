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
 * Checker for tag area strings in db/tag.php.
 */
class TagsChecker extends AbstractDatabaseChecker
{
    use StringContextTrait;

    /**
     * The pattern for tag area strings.
     *
     * @var string
     */
    private const TAG_AREA_STRING_PATTERN = 'tagarea_';

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
        return 'db/tag.php';
    }

    /**
     * Get the name of this checker.
     */
    public function getName(): string
    {
        return 'Tags';
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

            if (!isset($vars['tagareas']) || !is_array($vars['tagareas'])) {
                $result->addRawError('No valid $tagareas array found in db/tag.php');

                return $result;
            }

            foreach ($vars['tagareas'] as $index => $tagarea) {
                if (!is_array($tagarea) || !isset($tagarea['itemtype'])) {
                    continue;
                }

                // Generate the required string key for this tag area
                $stringKey   = self::TAG_AREA_STRING_PATTERN . $tagarea['itemtype'];
                $description = "Tag area: {$tagarea['itemtype']}";

                // Use the trait helper method for string literal detection
                $this->addRequiredStringWithStringLiteral($result, $stringKey, $filePath, $tagarea['itemtype'], $description);
            }
        } catch (\Exception $e) {
            $result->addRawError('Error parsing db/tag.php: ' . $e->getMessage());
        }

        return $result;
    }
}
