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

namespace MoodlePluginCI\Tests\MissingStrings\Checker\DatabaseFileChecker;

use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MessagesChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for MessagesChecker class.
 *
 * Tests the message provider string detection in db/messages.php files including
 * line detection, context information, and various message provider formats.
 */
class MessagesCheckerTest extends MissingStringsTestCase
{
    private MessagesChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new MessagesChecker();
    }

    /**
     * Test that the checker has the correct name.
     */
    public function testGetNameReturnsCorrectName(): void
    {
        $this->assertSame('Messages', $this->checker->getName());
    }

    /**
     * Test that the checker applies when messages.php exists.
     */
    public function testAppliesToWithMessagesFileReturnsTrue(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that the checker doesn't apply when messages.php doesn't exist.
     */
    public function testAppliesToWithoutMessagesFileReturnsFalse(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod');
        $plugin    = $this->createPlugin('mod', 'testmod', $pluginDir);

        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test processing single message provider.
     */
    public function testCheckWithSingleMessageProviderAddsRequiredString(): void
    {
        $messageproviders = [
            'assignment_submission' => [
                'capability' => 'mod/assign:receivegradernotifications',
                'defaults'   => [
                    'popup' => 'MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF',
                    'email' => 'MESSAGE_PERMITTED',
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', $messageproviders),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings);
        $this->assertArrayHasKey('messageprovider:assignment_submission', $requiredStrings);

        $context = $requiredStrings['messageprovider:assignment_submission'];
        $this->assertStringContainsString('db/messages.php', $context->getFile());
        $this->assertSame('Message provider: assignment_submission', $context->getDescription());
        $this->assertNotNull($context->getLine());
    }

    /**
     * Test processing multiple message providers.
     */
    public function testCheckWithMultipleMessageProvidersAddsAllRequiredStrings(): void
    {
        $messageproviders = [
            'assignment_submission' => [
                'capability' => 'mod/assign:receivegradernotifications',
                'defaults'   => ['popup' => 'MESSAGE_PERMITTED'],
            ],
            'assignment_graded' => [
                'capability' => 'mod/assign:receivegradernotifications',
                'defaults'   => ['email' => 'MESSAGE_PERMITTED'],
            ],
            'course_update' => [
                'defaults' => [
                    'popup' => 'MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN',
                    'email' => 'MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF',
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', $messageproviders),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('messageprovider:assignment_submission', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:assignment_graded', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:course_update', $requiredStrings);

        // Check that each has correct context
        foreach (['messageprovider:assignment_submission', 'messageprovider:assignment_graded', 'messageprovider:course_update'] as $expectedKey) {
            $context = $requiredStrings[$expectedKey];
            $this->assertStringContainsString('db/messages.php', $context->getFile());
            $this->assertStringContainsString('Message provider:', $context->getDescription());
            $this->assertNotNull($context->getLine());
        }
    }

    /**
     * Test message provider string pattern generation.
     */
    public function testCheckWithVariousProviderNamesGeneratesCorrectStringKeys(): void
    {
        $messageproviders = [
            'simple_notification'   => ['defaults' => ['popup' => 'MESSAGE_PERMITTED']],
            'user_action_required'  => ['defaults' => ['email' => 'MESSAGE_PERMITTED']],
            'system123_alert'       => ['defaults' => ['popup' => 'MESSAGE_PERMITTED']],
            'special-chars_message' => ['defaults' => ['email' => 'MESSAGE_PERMITTED']],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', $messageproviders),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(4, $requiredStrings);

        // Check expected string keys with messageprovider: prefix
        $this->assertArrayHasKey('messageprovider:simple_notification', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:user_action_required', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:system123_alert', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:special-chars_message', $requiredStrings);
    }

    /**
     * Test line detection accuracy.
     */
    public function testCheckWithMessageProvidersDetectsCorrectLineNumbers(): void
    {
        $messagesContent = "<?php\n\n" .
                          "defined('MOODLE_INTERNAL') || die();\n\n" .
                          "\$messageproviders = [\n" .
                          "    'assignment_submission' => [\n" .            // Line 6
                          "        'capability' => 'mod/assign:receivegradernotifications',\n" .
                          "        'defaults' => ['popup' => 'MESSAGE_PERMITTED']\n" .
                          "    ],\n" .
                          "    'assignment_graded' => [\n" .                // Line 10
                          "        'capability' => 'mod/assign:receivegradernotifications',\n" .
                          "        'defaults' => ['email' => 'MESSAGE_PERMITTED']\n" .
                          "    ]\n" .
                          "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $messagesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertStringContextHasLine($requiredStrings['messageprovider:assignment_submission'], 6);
        $this->assertStringContextHasLine($requiredStrings['messageprovider:assignment_graded'], 10);
    }

    /**
     * Test handling of empty messages file.
     */
    public function testCheckWithEmptyMessagesFileReturnsEmptyResult(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', []),
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertCount(0, $result->getErrors());
        $this->assertCount(0, $result->getWarnings());
    }

    /**
     * Test handling of malformed message provider.
     */
    public function testCheckWithMalformedMessageProviderAddsWarning(): void
    {
        $messagesContent = "<?php\n\n" .
                          "defined('MOODLE_INTERNAL') || die();\n\n" .
                          "\$messageproviders = [\n" .
                          "    'assignment_submission' => 'invalid_string_instead_of_array',\n" .
                          "];\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $messagesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('not an array', $warnings[0]);
    }

    /**
     * Test handling of invalid messages file.
     */
    public function testCheckWithInvalidMessagesFileAddsWarning(): void
    {
        $messagesContent = "<?php\n\n" .
                          "defined('MOODLE_INTERNAL') || die();\n\n" .
                          "\$messageproviders = 'not_an_array';\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $messagesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('not an array', $warnings[0]);
    }

    /**
     * Test missing messageproviders array.
     */
    public function testCheckWithMissingMessageProvidersArrayAddsWarning(): void
    {
        $messagesContent = "<?php\n\n" .
                          "defined('MOODLE_INTERNAL') || die();\n\n" .
                          "// No \$messageproviders defined\n";

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $messagesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('No $messageproviders array found', $warnings[0]);
    }

    /**
     * Test complex message providers with various configurations.
     */
    public function testCheckWithComplexMessageProvidersProcessesCorrectly(): void
    {
        $messageproviders = [
            'complex_notification' => [
                'capability' => 'local/testlocal:receivenotifications',
                'defaults'   => [
                    'popup' => 'MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF',
                    'email' => 'MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF',
                    'sms'   => 'MESSAGE_DISALLOWED',
                ],
            ],
            'minimal_alert' => [
                'defaults' => ['popup' => 'MESSAGE_PERMITTED'],
            ],
            'conditional_message' => [
                'capability' => 'moodle/site:config',
                'defaults'   => [
                    'popup' => 'MESSAGE_FORCED + MESSAGE_DEFAULT_LOGGEDIN',
                    'email' => 'MESSAGE_FORCED + MESSAGE_DEFAULT_LOGGEDOFF',
                ],
            ],
        ];

        $pluginDir = $this->createTestPlugin('local', 'testlocal', [
            'db/messages.php' => $this->createDatabaseFileContent('messages', $messageproviders),
        ]);

        $plugin = $this->createPlugin('local', 'testlocal', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(3, $requiredStrings);

        $this->assertArrayHasKey('messageprovider:complex_notification', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:minimal_alert', $requiredStrings);
        $this->assertArrayHasKey('messageprovider:conditional_message', $requiredStrings);

        // Verify context information
        $complexContext = $requiredStrings['messageprovider:complex_notification'];
        $this->assertSame('Message provider: complex_notification', $complexContext->getDescription());

        $minimalContext = $requiredStrings['messageprovider:minimal_alert'];
        $this->assertSame('Message provider: minimal_alert', $minimalContext->getDescription());
    }

    /**
     * Test error handling for corrupted messages file.
     */
    public function testCheckWithCorruptedMessagesFileHandlesGracefully(): void
    {
        $messagesContent = "<?php\n\n" .
                          "syntax error - invalid PHP\n" .
                          '$messageproviders = [';

        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'db/messages.php' => $messagesContent,
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $this->assertCount(0, $result->getRequiredStrings());
        $this->assertWarningCount($result, 1);

        $warnings = $result->getWarnings();
        $this->assertStringContainsString('Error parsing db/messages.php', $warnings[0]);
    }
}
