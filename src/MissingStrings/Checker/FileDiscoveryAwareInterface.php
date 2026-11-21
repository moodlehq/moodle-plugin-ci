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

use MoodlePluginCI\MissingStrings\FileDiscovery\FileDiscovery;

/**
 * Interface for checkers that can benefit from centralized file discovery.
 *
 * Checkers implementing this interface will receive a FileDiscovery instance
 * before validation, allowing them to use pre-discovered files instead of
 * scanning the file system themselves.
 */
interface FileDiscoveryAwareInterface
{
    /**
     * Set the file discovery service.
     *
     * @param FileDiscovery $fileDiscovery the file discovery service
     */
    public function setFileDiscovery(FileDiscovery $fileDiscovery): void;
}
