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

namespace MoodlePluginCI\Tests\MissingStrings\Core;

use MoodlePluginCI\Bridge\Moodle;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use MoodlePluginCI\MissingStrings\StringValidator;
use MoodlePluginCI\MissingStrings\ValidationConfig;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for StringValidator class.
 *
 * @covers \MoodlePluginCI\MissingStrings\StringValidator
 */
class StringValidatorTest extends MissingStringsTestCase
{
    /** @var string */
    private $testPluginPath;

    /** @var Plugin */
    private $testPlugin;

    /** @var Moodle */
    private $moodle;

    /** @var ValidationConfig */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test plugin directory structure
        $this->testPluginPath = $this->createTempDir('test_plugin_');
        $this->createSimpleTestPlugin();

        // Create plugin instance
        $this->testPlugin = new Plugin('local_testplugin', 'local', 'testplugin', $this->testPluginPath);

        // Create moodle mock
        $this->moodle = $this->createMock(Moodle::class);
        $this->moodle->method('getBranch')->willReturn(401);

        // Create default config
        $this->config = new ValidationConfig();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Temp directories are cleaned up automatically by base class
    }

    /**
     * Test constructor initializes correctly.
     */
    public function testConstructorInitializesCorrectly(): void
    {
        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);

        $this->assertInstanceOf(StringValidator::class, $validator);
    }

    /**
     * Test validation with valid plugin.
     */
    public function testValidateWithValidPlugin(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->getErrorCount());
    }

    /**
     * Test validation with missing required string.
     */
    public function testValidateWithMissingRequiredString(): void
    {
        // Create language file with some strings but missing the required 'pluginname'
        $this->createLangFile(['somestring' => 'Some String']);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertFalse($result->isValid());
        $this->assertGreaterThan(0, $result->getErrorCount());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Missing required string', $errors[0]);
        $this->assertStringContainsString('pluginname', $errors[0]);
    }

    /**
     * Test validation with missing language file.
     */
    public function testValidateWithMissingLanguageFile(): void
    {
        // Don't create language file

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertFalse($result->isValid());
        $this->assertGreaterThan(0, $result->getErrorCount());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('File not found', $errors[0]);
    }

    /**
     * Test validation with unreadable language file.
     */
    public function testValidateWithUnreadableLanguageFile(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        $langFile = $this->testPluginPath . '/lang/en/local_testplugin.php';
        chmod($langFile, 0000); // Make file unreadable

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        // Restore permissions for cleanup
        chmod($langFile, 0644);

        // In Docker/some environments, file permission restrictions may not work as expected
        // So we check if the validation either succeeded or failed with appropriate error
        if (!$result->isValid()) {
            $this->assertGreaterThan(0, $result->getErrorCount());
            $errors = $result->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertStringContainsString('File not readable', $errors[0]);
        } else {
            // If permissions didn't restrict access, validation should succeed
            $this->assertTrue($result->isValid());
        }
    }

    /**
     * Test validation with corrupted language file.
     */
    public function testValidateWithCorruptedLanguageFile(): void
    {
        if (!is_dir($this->testPluginPath . '/lang/en')) {
            mkdir($this->testPluginPath . '/lang/en', 0755, true);
        }
        $langFile = $this->testPluginPath . '/lang/en/local_testplugin.php';
        file_put_contents($langFile, '<?php syntax error here');

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertFalse($result->isValid());
        $this->assertGreaterThan(0, $result->getErrorCount());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Failed to parse file', $errors[0]);
    }

    /**
     * Test validation with used strings.
     */
    public function testValidateWithUsedStrings(): void
    {
        $this->createLangFile([
            'pluginname'  => 'Test Plugin',
            'used_string' => 'Used string',
        ]);

        // Create PHP file with string usage
        file_put_contents(
            $this->testPluginPath . '/lib.php',
            "<?php\nget_string('used_string', 'local_testplugin');\n"
        );

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->getErrorCount());
    }

    /**
     * Test validation with missing used string.
     */
    public function testValidateWithMissingUsedString(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        // Create PHP file with string usage
        file_put_contents(
            $this->testPluginPath . '/lib.php',
            "<?php\nget_string('missing_string', 'local_testplugin');\n"
        );

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        $this->assertFalse($result->isValid());
        $this->assertGreaterThan(0, $result->getErrorCount());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Missing used string', $errors[0]);
        $this->assertStringContainsString('missing_string', $errors[0]);
    }

    /**
     * Test validation with unused strings.
     */
    public function testValidateWithUnusedStrings(): void
    {
        $this->createLangFile([
            'pluginname'    => 'Test Plugin',
            'unused_string' => 'Unused string',
        ]);

        // Enable unused string checking
        $config = new ValidationConfig('en', false, true);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $config);
        $result    = $validator->validate();

        $this->assertTrue($result->isValid()); // Warnings don't make it invalid in non-strict mode
        $this->assertGreaterThan(0, $result->getWarningCount());

        $warnings = $result->getWarnings();
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Unused string', $warnings[0]);
        $this->assertStringContainsString('unused_string', $warnings[0]);
    }

    /**
     * Test validation with strict mode.
     */
    public function testValidateWithStrictMode(): void
    {
        $this->createLangFile([
            'pluginname'    => 'Test Plugin',
            'unused_string' => 'Unused string',
        ]);

        // Enable strict mode and unused string checking
        $config = new ValidationConfig('en', true, true);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $config);
        $result    = $validator->validate();

        $this->assertFalse($result->isValid()); // Warnings make it invalid in strict mode
        $this->assertGreaterThan(0, $result->getWarningCount());
    }

    /**
     * Test validation with excluded strings.
     */
    public function testValidateWithExcludedStrings(): void
    {
        $this->createLangFile([
            'pluginname'      => 'Test Plugin',
            'excluded_string' => 'Excluded string',
        ]);

        // Create PHP file with excluded string usage
        file_put_contents(
            $this->testPluginPath . '/lib.php',
            "<?php\nget_string('excluded_string', 'local_testplugin');\n"
        );

        // Configure exclusion patterns
        $config = new ValidationConfig('en', false, false, ['excluded_*']);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $config);
        $result    = $validator->validate();

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->getErrorCount());
        $this->assertSame(0, $result->getWarningCount());
    }

    /**
     * Test validation with custom checkers.
     */
    public function testValidateWithCustomCheckers(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        // Create mock checker
        $mockChecker = $this->createMock(StringCheckerInterface::class);
        $mockChecker->method('getName')->willReturn('Mock Checker');
        $mockChecker->method('appliesTo')->willReturn(true);

        $mockResult = new ValidationResult();
        $mockResult->addRawError('Mock checker error');
        $mockChecker->method('check')->willReturn($mockResult);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $validator->addChecker($mockChecker);

        $result = $validator->validate();

        $this->assertFalse($result->isValid());
        $this->assertGreaterThan(0, $result->getErrorCount());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Mock checker error', $errors[0]);
    }

    /**
     * Test validation with module plugin (special language file naming).
     */
    public function testValidateWithModulePlugin(): void
    {
        // Create module plugin structure
        $modulePath = $this->createTempDir('test_module_');
        if (!is_dir($modulePath)) {
            mkdir($modulePath, 0755, true);
        }
        if (!is_dir($modulePath . '/lang/en')) {
            mkdir($modulePath . '/lang/en', 0755, true);
        }

        // Create version.php
        $versionContent = "<?php\n\$plugin->component = 'mod_testmodule';\n\$plugin->version = 2023010100;\n";
        file_put_contents($modulePath . '/version.php', $versionContent);

        // Create language file with module naming convention (testmodule.php instead of mod_testmodule.php)
        $langContent = "<?php\n\$string['pluginname'] = 'Test Module';\n\$string['modulename'] = 'Test Module';\n\$string['modulenameplural'] = 'Test Modules';\n";
        file_put_contents($modulePath . '/lang/en/testmodule.php', $langContent);

        $modulePlugin = new Plugin('mod_testmodule', 'mod', 'testmodule', $modulePath);
        $validator    = new StringValidator($modulePlugin, $this->moodle, $this->config);
        $result       = $validator->validate();

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->getErrorCount());

        // Cleanup is handled automatically by base class
    }

    /**
     * Test validation with debug mode.
     */
    public function testValidateWithDebugMode(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        $config = new ValidationConfig('en', false, false, [], [], true, true);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $config);
        $result    = $validator->validate();

        $this->assertInstanceOf(ValidationResult::class, $result);
    }

    /**
     * Test adding and setting checkers.
     */
    public function testAddAndSetCheckers(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        $mockChecker1 = $this->createMock(StringCheckerInterface::class);
        $mockChecker1->method('getName')->willReturn('Mock Checker 1');
        $mockChecker1->method('appliesTo')->willReturn(false);

        $mockChecker2 = $this->createMock(StringCheckerInterface::class);
        $mockChecker2->method('getName')->willReturn('Mock Checker 2');
        $mockChecker2->method('appliesTo')->willReturn(true);

        $mockResult = new ValidationResult();
        $mockChecker2->method('check')->willReturn($mockResult);

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);

        // Add checkers
        $validator->addChecker($mockChecker1);
        $validator->addChecker($mockChecker2);

        $result = $validator->validate();

        $this->assertTrue($result->isValid());

        // Set checkers (replace existing)
        $mockChecker3 = $this->createMock(StringCheckerInterface::class);
        $mockChecker3->method('getName')->willReturn('Mock Checker 3');
        $mockChecker3->method('appliesTo')->willReturn(true);
        $mockChecker3->method('check')->willReturn($mockResult);

        $validator->setCheckers([$mockChecker3]);

        $result2 = $validator->validate();

        $this->assertTrue($result2->isValid());
    }

    /**
     * Test validation with subplugins.
     */
    public function testValidateWithSubplugins(): void
    {
        $this->createLangFile([
            'pluginname'                       => 'Test Plugin',
            'subplugintype_testsubtype'        => 'Test Subtype',
            'subplugintype_testsubtype_plural' => 'Test Subtypes',
        ]);

        // Create subplugins.json - this test ensures the main plugin validates successfully
        // even when it has subplugin definitions (the actual subplugin discovery is tested separately)
        $subpluginsJson = [
            'plugintypes' => [
                'testsubtype' => 'local/testplugin/subplugins',
            ],
        ];

        if (!is_dir($this->testPluginPath . '/db')) {
            mkdir($this->testPluginPath . '/db', 0755, true);
        }
        file_put_contents(
            $this->testPluginPath . '/db/subplugins.json',
            json_encode($subpluginsJson, JSON_PRETTY_PRINT)
        );

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $result    = $validator->validate();

        // The main plugin should validate successfully with subplugin definitions
        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->getErrorCount());
    }

    /**
     * Test validation handles errors gracefully.
     */
    public function testValidateHandlesErrorsGracefully(): void
    {
        $this->createLangFile(['pluginname' => 'Test Plugin']);

        // Create a checker that throws an exception
        $mockChecker = $this->createMock(StringCheckerInterface::class);
        $mockChecker->method('getName')->willReturn('Failing Checker');
        $mockChecker->method('appliesTo')->willReturn(true);
        $mockChecker->method('check')->willThrowException(new \RuntimeException('Checker failed'));

        $validator = new StringValidator($this->testPlugin, $this->moodle, $this->config);
        $validator->addChecker($mockChecker);

        $result = $validator->validate();

        // Validation should continue despite checker failure
        $this->assertInstanceOf(ValidationResult::class, $result);
    }

    /**
     * Create a test plugin directory structure.
     */
    private function createSimpleTestPlugin(): void
    {
        // Create version.php
        $versionContent = "<?php\n\$plugin->component = 'local_testplugin';\n\$plugin->version = 2023010100;\n";
        file_put_contents($this->testPluginPath . '/version.php', $versionContent);
    }

    /**
     * Create a language file for the test plugin.
     *
     * @param array $strings Array of string key => value pairs
     */
    private function createLangFile(array $strings): void
    {
        $langDir = $this->testPluginPath . '/lang/en';
        if (!is_dir($langDir)) {
            mkdir($langDir, 0755, true);
        }

        $langContent = "<?php\n";
        foreach ($strings as $key => $value) {
            $langContent .= "\$string['{$key}'] = '{$value}';\n";
        }

        file_put_contents($langDir . '/local_testplugin.php', $langContent);
    }
}
