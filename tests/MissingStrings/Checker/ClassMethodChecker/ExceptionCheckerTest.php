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

use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\ExceptionChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test the ExceptionChecker class.
 *
 * Tests exception message string detection for moodle_exception throws,
 * custom exception classes, print_error calls, and other exception types.
 */
class ExceptionCheckerTest extends MissingStringsTestCase
{
    private ExceptionChecker $checker;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new ExceptionChecker();
    }

    /**
     * Test checker name.
     */
    public function testGetName(): void
    {
        $this->assertSame('Exception', $this->checker->getName());
    }

    /**
     * Test that checker applies to plugins with moodle_exception usage.
     */
    public function testAppliesToWithMoodleException(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("error_code", "local_testplugin");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker applies to plugins with custom exception classes.
     */
    public function testAppliesToWithCustomException(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/exception/custom_exception.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class custom_exception extends \Exception {
    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker applies to plugins with coding_exception usage.
     */
    public function testAppliesToWithCodingException(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new coding_exception("invalid_parameter");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker doesn't apply to plugins without exception usage.
     */
    public function testAppliesToWithoutExceptions(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    return "No exceptions here";
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test detection of moodle_exception with explicit component.
     */
    public function testCheckMoodleExceptionWithExplicitComponent(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("error_invalid_data", "local_testplugin");
    throw new moodle_exception("error_access_denied", "local_testplugin");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('error_invalid_data', $requiredStrings);
        $this->assertArrayHasKey('error_access_denied', $requiredStrings);
    }

    /**
     * Test that moodle_exception with different component is ignored.
     */
    public function testCheckMoodleExceptionWithDifferentComponent(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("error_invalid_data", "mod_forum");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test that moodle_exception with only error code is ignored (defaults to 'error').
     */
    public function testCheckMoodleExceptionWithOnlyErrorCode(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("generalexceptionmessage");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test detection of coding_exception with string key.
     */
    public function testCheckCodingExceptionWithStringKey(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new coding_exception("invalid_parameter");
    throw new coding_exception("missing_required_field");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('invalid_parameter', $requiredStrings);
        $this->assertArrayHasKey('missing_required_field', $requiredStrings);
    }

    /**
     * Test that coding_exception with plain message is ignored.
     */
    public function testCheckCodingExceptionWithPlainMessage(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new coding_exception("This is a plain error message");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test detection of print_error with explicit component.
     */
    public function testCheckPrintErrorWithExplicitComponent(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    print_error("error_access_denied", "local_testplugin");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('error_access_denied', $requiredStrings);
    }

    /**
     * Test detection of print_error with only error code (defaults to current component).
     */
    public function testCheckPrintErrorWithOnlyErrorCode(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    print_error("error_invalid_request");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('error_invalid_request', $requiredStrings);
    }

    /**
     * Test that print_error with different component is ignored.
     */
    public function testCheckPrintErrorWithDifferentComponent(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    print_error("error_access_denied", "mod_forum");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test detection of various exception types.
     */
    public function testCheckVariousExceptionTypes(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new invalid_parameter_exception("invalid_param_type");
    throw new file_exception("file_not_found");
    throw new dml_exception("database_error");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('invalid_param_type', $requiredStrings);
        $this->assertArrayHasKey('file_not_found', $requiredStrings);
        $this->assertArrayHasKey('database_error', $requiredStrings);
    }

    /**
     * Test detection of custom exception classes.
     */
    public function testCheckCustomExceptionClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/exception/validation_exception.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class validation_exception extends \Exception {
    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should detect potential string keys for the custom exception class
        $requiredStrings = $result->getRequiredStrings();
        $this->assertGreaterThan(0, count($requiredStrings));

        // Check that some expected string keys are present
        $this->assertArrayHasKey('validation_exception', $requiredStrings);
    }

    /**
     * Test that non-exception classes are ignored.
     */
    public function testCheckNonExceptionClass(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'classes/helper/data_helper.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class data_helper {
    public function process_data($data) {
        return $data;
    }
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test error handling for unreadable files.
     */
    public function testCheckUnreadableFile(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("error_test", "local_testplugin");
}
',
        ]);

        // Make the file unreadable
        $libFile = $pluginDir . '/lib.php';
        chmod($libFile, 0000);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);

        // Restore permissions for cleanup
        chmod($libFile, 0644);
    }

    /**
     * Test string key validation logic.
     */
    public function testLooksLikeStringKey(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    // Valid string keys
    throw new coding_exception("error_invalid_parameter");
    throw new coding_exception("missing_required_field");
    throw new coding_exception("user:access_denied");

    // Invalid string keys (should be ignored)
    throw new coding_exception("This is a plain message");
    throw new coding_exception("Error with spaces");
    throw new coding_exception("X");  // Too short
    throw new coding_exception("ERROR_UPPERCASE");  // Uppercase
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should only detect the valid string keys
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('error_invalid_parameter', $requiredStrings);
        $this->assertArrayHasKey('missing_required_field', $requiredStrings);
        $this->assertArrayHasKey('user:access_denied', $requiredStrings);
    }

    /**
     * Test that context information includes correct file and line numbers.
     */
    public function testCheckContextInformation(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    throw new moodle_exception("error_line_5", "local_testplugin");
    throw new coding_exception("error_line_6");
    print_error("error_line_7", "local_testplugin");
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);

        $errors  = $result->getErrors();
        $libFile = $pluginDir . '/lib.php';

        foreach ($errors as $error) {
            $this->assertSame($libFile, $error['file']);
            $this->assertGreaterThan(0, $error['line']);
            $this->assertNotEmpty($error['description']);
        }
    }
}
