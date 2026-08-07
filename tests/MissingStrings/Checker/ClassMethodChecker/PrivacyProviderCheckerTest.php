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

namespace MoodlePluginCI\Tests\MissingStrings\Checker\ClassMethodChecker;

use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\PrivacyProviderChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test the PrivacyProviderChecker class.
 *
 * Tests privacy provider interface detection and required privacy strings
 * for null providers, metadata providers, and request providers.
 */
class PrivacyProviderCheckerTest extends MissingStringsTestCase
{
    private PrivacyProviderChecker $checker;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new PrivacyProviderChecker();
    }

    /**
     * Test checker name.
     */
    public function testGetName(): void
    {
        $this->assertSame('Privacy Provider', $this->checker->getName());
    }

    /**
     * Test that checker applies to plugins with privacy provider file.
     */
    public function testAppliesToWithPrivacyProvider(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return "privacy:metadata";
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker doesn't apply to plugins without privacy provider file.
     */
    public function testAppliesToWithoutPrivacyProvider(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    return "No privacy provider";
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test null provider with explicit get_reason string.
     */
    public function testCheckNullProviderWithExplicitReason(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return "privacy:metadata";
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata', $requiredStrings);
    }

    /**
     * Test null provider without explicit get_reason string (fallback).
     */
    public function testCheckNullProviderWithoutExplicitReason(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return get_string("no_data_stored", "local_testplugin");
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should fall back to default privacy:metadata string
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata', $requiredStrings);
    }

    /**
     * Test metadata provider with database table fields.
     */
    public function testCheckMetadataProviderWithDatabaseTable(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("local_testplugin_data", [
            "userid" => "privacy:metadata:userid",
            "data" => "privacy:metadata:data",
            "timecreated" => "privacy:metadata:timecreated"
        ]);
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:userid', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:data', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:timecreated', $requiredStrings);
    }

    /**
     * Test metadata provider with external location.
     */
    public function testCheckMetadataProviderWithExternalLocation(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_external_location_link("external_service", "privacy:metadata:external_service", "https://example.com");
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:external_service', $requiredStrings);
    }

    /**
     * Test metadata provider with subsystem link.
     */
    public function testCheckMetadataProviderWithSubsystemLink(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_subsystem_link("core_files", [], "privacy:metadata:core_files");
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:core_files', $requiredStrings);
    }

    /**
     * Test metadata provider with user preference.
     */
    public function testCheckMetadataProviderWithUserPreference(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_user_preference("testplugin_preference", "privacy:metadata:preference:testplugin_preference");
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:preference:testplugin_preference', $requiredStrings);
    }

    /**
     * Test metadata provider with multiple types of metadata.
     */
    public function testCheckMetadataProviderWithMultipleTypes(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("local_testplugin_data", [
            "userid" => "privacy:metadata:userid",
            "content" => "privacy:metadata:content"
        ]);
        $collection->add_external_location_link("api_service", "privacy:metadata:api_service", "https://api.example.com");
        $collection->add_user_preference("testplugin_setting", "privacy:metadata:preference:setting");
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(4, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:userid', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:content', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:api_service', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:preference:setting', $requiredStrings);
    }

    /**
     * Test that non-privacy strings are ignored.
     */
    public function testCheckIgnoresNonPrivacyStrings(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("local_testplugin_data", [
            "userid" => "privacy:metadata:userid",
            "content" => "regular_string_key",  // Not a privacy string
            "other" => "some:other:format"      // Not a privacy string
        ]);
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should only detect the privacy string
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:userid', $requiredStrings);
    }

    /**
     * Test provider implementing multiple interfaces.
     */
    public function testCheckProviderWithMultipleInterfaces(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("local_testplugin_data", [
            "userid" => "privacy:metadata:userid"
        ]);
        return $collection;
    }

    // Request provider methods would be here...
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should detect metadata strings (request provider strings depend on implementation)
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:userid', $requiredStrings);
    }

    /**
     * Test handling of malformed provider file.
     */
    public function testCheckMalformedProviderFile(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

// Malformed PHP syntax
class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): {
        // Missing return type, etc.
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);
    }

    /**
     * Test handling of unreadable provider file.
     */
    public function testCheckUnreadableProviderFile(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return "privacy:metadata";
    }
}
',
        ]);

        // Make the file unreadable by replacing it with a directory
        $providerFile = $pluginDir . '/classes/privacy/provider.php';
        unlink($providerFile);
        mkdir($providerFile); // Create a directory with the same name as the file

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);

        // Check that an error was recorded (file_get_contents should fail on a directory)
        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);

        // Verify error message content (file_get_contents on directory triggers exception)
        $this->assertStringContainsString('Error analyzing privacy provider', $errors[0]);

        // Clean up
        rmdir($providerFile);
    }

    /**
     * Test context information includes correct file paths.
     */
    public function testCheckContextInformation(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("local_testplugin_data", [
            "userid" => "privacy:metadata:userid"
        ]);
        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);

        $errors       = $result->getErrors();
        $providerFile = $pluginDir . '/classes/privacy/provider.php';

        foreach ($errors as $error) {
            $this->assertSame($providerFile, $error['file']);
            $this->assertNotEmpty($error['description']);
            $this->assertStringContainsString('Privacy metadata', $error['description']);
        }
    }

    /**
     * Test mixed case interface names (partial matching).
     */
    public function testCheckMixedCaseInterfaceNames(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return "privacy:no_data";
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('privacy:no_data', $requiredStrings);
    }

    /**
     * Test complex metadata string patterns.
     */
    public function testCheckComplexMetadataPatterns(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        // Multi-line add_database_table call
        $collection->add_database_table("local_testplugin_complex", [
            "id" => "privacy:metadata:table:id",
            "userid" => "privacy:metadata:table:userid",
            "data" => "privacy:metadata:table:data",
            "timemodified" => "privacy:metadata:table:timemodified"
        ]);

        // Multiple external services
        $collection->add_external_location_link("service1", "privacy:metadata:external:service1", "https://service1.com");
        $collection->add_external_location_link("service2", "privacy:metadata:external:service2", "https://service2.com");

        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(6, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:table:id', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:table:userid', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:table:data', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:table:timemodified', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:external:service1', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:external:service2', $requiredStrings);
    }

    /**
     * Test metadata provider with link_subsystem calls.
     */
    public function testCheckMetadataProviderWithLinkSubsystem(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/privacy/provider.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class provider implements \core_privacy\local\metadata\provider {
    use \core_privacy\local\legacy_polyfill;

    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table("test_table", [
            "userid" => "privacy:metadata:test_table:userid",
            "data" => "privacy:metadata:test_table:data",
        ], "privacy:metadata:test_table");

        $collection->link_subsystem("core_rating", "privacy:metadata:core_rating");
        $collection->link_subsystem("core_tag", "privacy:metadata:core_tag");

        return $collection;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(5, $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:test_table:userid', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:test_table:data', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:test_table', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:core_rating', $requiredStrings);
        $this->assertArrayHasKey('privacy:metadata:core_tag', $requiredStrings);
    }
}
