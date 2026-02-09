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

use MoodlePluginCI\MissingStrings\StringContext;
use MoodlePluginCI\MissingStrings\ValidationResult;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for ValidationResult class.
 *
 * @covers \MoodlePluginCI\MissingStrings\ValidationResult
 */
class ValidationResultTest extends MissingStringsTestCase
{
    /**
     * Test default constructor values.
     */
    public function testConstructorWithDefaultsInitializesCorrectly(): void
    {
        $result = new ValidationResult();

        $this->assertEmpty($result->getRequiredStrings(), 'Required strings should be empty initially');
        $this->assertEmpty($result->getErrors(), 'Errors should be empty initially');
        $this->assertEmpty($result->getWarnings(), 'Warnings should be empty initially');
        $this->assertEmpty($result->getMessages(), 'Messages should be empty initially');
        $this->assertSame(0, $result->getSuccessCount(), 'Success count should be zero initially');
        $this->assertSame(0, $result->getErrorCount(), 'Error count should be zero initially');
        $this->assertSame(0, $result->getWarningCount(), 'Warning count should be zero initially');
        $this->assertSame(0, $result->getTotalIssues(), 'Total issues should be zero initially');
        $this->assertTrue($result->isValid(), 'Result should be valid initially (non-strict mode)');
    }

    /**
     * Test constructor with strict mode.
     */
    public function testConstructorWithStrictModeInitializesCorrectly(): void
    {
        $result = new ValidationResult(true);

        $this->assertTrue($result->isValid(), 'Result should be valid initially even in strict mode');
    }

    /**
     * Test adding required strings.
     */
    public function testAddRequiredStringAddsCorrectly(): void
    {
        $result   = new ValidationResult();
        $context1 = new StringContext('file1.php', 10, 'Test context 1');
        $context2 = new StringContext('file2.php', 20, 'Test context 2');

        $result->addRequiredString('string1', $context1);
        $result->addRequiredString('string2', $context2);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(2, $requiredStrings, 'Should have 2 required strings');
        $this->assertArrayHasKey('string1', $requiredStrings, 'Should contain string1');
        $this->assertArrayHasKey('string2', $requiredStrings, 'Should contain string2');
        $this->assertSame($context1, $requiredStrings['string1'], 'Should store correct context for string1');
        $this->assertSame($context2, $requiredStrings['string2'], 'Should store correct context for string2');
    }

    /**
     * Test adding required strings with duplicate keys.
     */
    public function testAddRequiredStringWithDuplicateKeysOverwritesPrevious(): void
    {
        $result   = new ValidationResult();
        $context1 = new StringContext('file1.php', 10);
        $context2 = new StringContext('file2.php', 20);

        $result->addRequiredString('duplicate_key', $context1);
        $result->addRequiredString('duplicate_key', $context2);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertCount(1, $requiredStrings, 'Should have only 1 required string');
        $this->assertSame($context2, $requiredStrings['duplicate_key'], 'Should store the last context');
    }

    /**
     * Test adding raw errors.
     */
    public function testAddRawErrorAddsCorrectly(): void
    {
        $result = new ValidationResult();

        $result->addRawError('Error message 1');
        $result->addRawError('Error message 2');

        $errors = $result->getErrors();
        $this->assertCount(2, $errors, 'Should have 2 errors');
        $this->assertSame('Error message 1', $errors[0], 'Should store first error message');
        $this->assertSame('Error message 2', $errors[1], 'Should store second error message');
        $this->assertSame(2, $result->getErrorCount(), 'Error count should be 2');
    }

    /**
     * Test adding raw warnings.
     */
    public function testAddRawWarningAddsCorrectly(): void
    {
        $result = new ValidationResult();

        $result->addRawWarning('Warning message 1');
        $result->addRawWarning('Warning message 2');

        $warnings = $result->getWarnings();
        $this->assertCount(2, $warnings, 'Should have 2 warnings');
        $this->assertSame('Warning message 1', $warnings[0], 'Should store first warning message');
        $this->assertSame('Warning message 2', $warnings[1], 'Should store second warning message');
        $this->assertSame(2, $result->getWarningCount(), 'Warning count should be 2');
    }

    /**
     * Test adding formatted messages via addError and addWarning.
     */
    public function testAddFormattedMessagesAddsCorrectly(): void
    {
        $result = new ValidationResult();

        $result->addError('Error message 1');
        $result->addWarning('Warning message 1');

        $messages = $result->getMessages();
        $this->assertCount(2, $messages, 'Should have 2 formatted messages');
        $this->assertStringContainsString('Error message 1', $messages[0], 'Should contain error message');
        $this->assertStringContainsString('Warning message 1', $messages[1], 'Should contain warning message');

        // Check that formatted messages have proper formatting
        $this->assertStringContainsString('✗', $messages[0], 'Error message should have error symbol');
        $this->assertStringContainsString('⚠', $messages[1], 'Warning message should have warning symbol');
    }

    /**
     * Test adding successes.
     */
    public function testAddSuccessIncrementsCount(): void
    {
        $result = new ValidationResult();

        $this->assertSame(0, $result->getSuccessCount(), 'Initial success count should be 0');

        $result->addSuccess('Success 1');
        $this->assertSame(1, $result->getSuccessCount(), 'Success count should be 1');

        $result->addSuccess('Success 2');
        $this->assertSame(2, $result->getSuccessCount(), 'Success count should be 2');
    }

    /**
     * Test total issues calculation.
     */
    public function testGetTotalIssuesCalculatesCorrectly(): void
    {
        $result = new ValidationResult();

        $this->assertSame(0, $result->getTotalIssues(), 'Initial total issues should be 0');

        $result->addRawError('Error 1');
        $this->assertSame(1, $result->getTotalIssues(), 'Total issues should be 1 with 1 error');

        $result->addRawWarning('Warning 1');
        $this->assertSame(2, $result->getTotalIssues(), 'Total issues should be 2 with 1 error + 1 warning');

        $result->addRawError('Error 2');
        $result->addRawWarning('Warning 2');
        $this->assertSame(4, $result->getTotalIssues(), 'Total issues should be 4 with 2 errors + 2 warnings');
    }

    /**
     * Test validation status in non-strict mode.
     */
    public function testIsValidNonStrictModeOnlyConsidersErrors(): void
    {
        $result = new ValidationResult(false);

        // Initially valid
        $this->assertTrue($result->isValid(), 'Should be valid with no errors or warnings');

        // Still valid with only warnings
        $result->addRawWarning('Warning message');
        $this->assertTrue($result->isValid(), 'Should be valid with only warnings in non-strict mode');

        // Invalid with errors
        $result->addRawError('Error message');
        $this->assertFalse($result->isValid(), 'Should be invalid with errors');
    }

    /**
     * Test validation status in strict mode.
     */
    public function testIsValidStrictModeConsidersErrorsAndWarnings(): void
    {
        $result = new ValidationResult(true);

        // Initially valid
        $this->assertTrue($result->isValid(), 'Should be valid with no errors or warnings');

        // Invalid with warnings in strict mode
        $result->addRawWarning('Warning message');
        $this->assertFalse($result->isValid(), 'Should be invalid with warnings in strict mode');

        // Create new strict result for error test
        $result2 = new ValidationResult(true);
        $result2->addRawError('Error message');
        $this->assertFalse($result2->isValid(), 'Should be invalid with errors in strict mode');
    }

    /**
     * Test summary generation.
     */
    public function testGetSummaryGeneratesCorrectSummary(): void
    {
        $result = new ValidationResult();

        $result->addRawError('Error 1');
        $result->addRawError('Error 2');
        $result->addRawWarning('Warning 1');
        $result->addSuccess('Success 1');
        $result->addSuccess('Success 2');
        $result->addSuccess('Success 3');

        $summary = $result->getSummary();

        $expectedSummary = [
            'errors'       => 2,
            'warnings'     => 1,
            'successes'    => 3,
            'total_issues' => 3,
            'is_valid'     => false,
        ];

        $this->assertSame($expectedSummary, $summary, 'Summary should match expected values');
    }

    /**
     * Test summary generation in strict mode.
     */
    public function testGetSummaryStrictModeReflectsStrictValidation(): void
    {
        $result = new ValidationResult(true);
        $result->addRawWarning('Warning 1');

        $summary = $result->getSummary();

        $this->assertFalse($summary['is_valid'], 'Summary should show invalid in strict mode with warnings');
    }

    /**
     * Test merging two results.
     */
    public function testMergeCombinesResultsCorrectly(): void
    {
        $result1 = new ValidationResult();
        $result1->addRequiredString('string1', new StringContext('file1.php', 10));
        $result1->addRawError('Error from result1');
        $result1->addRawWarning('Warning from result1');
        $result1->addSuccess('Success from result1');

        $result2 = new ValidationResult();
        $result2->addRequiredString('string2', new StringContext('file2.php', 20));
        $result2->addRawError('Error from result2');
        $result2->addRawWarning('Warning from result2');
        $result2->addSuccess('Success from result2');
        $result2->addSuccess('Another success from result2');

        $result1->merge($result2);

        // Check required strings
        $requiredStrings = $result1->getRequiredStrings();
        $this->assertCount(2, $requiredStrings, 'Should have 2 required strings after merge');
        $this->assertArrayHasKey('string1', $requiredStrings, 'Should contain string1');
        $this->assertArrayHasKey('string2', $requiredStrings, 'Should contain string2');

        // Check errors
        $errors = $result1->getErrors();
        $this->assertCount(2, $errors, 'Should have 2 errors after merge');
        $this->assertContains('Error from result1', $errors, 'Should contain error from result1');
        $this->assertContains('Error from result2', $errors, 'Should contain error from result2');

        // Check warnings
        $warnings = $result1->getWarnings();
        $this->assertCount(2, $warnings, 'Should have 2 warnings after merge');
        $this->assertContains('Warning from result1', $warnings, 'Should contain warning from result1');
        $this->assertContains('Warning from result2', $warnings, 'Should contain warning from result2');

        // Check success count
        $this->assertSame(3, $result1->getSuccessCount(), 'Should have combined success count');
    }

    /**
     * Test merging with overlapping required strings.
     */
    public function testMergeWithOverlappingRequiredStringsOverwritesDuplicates(): void
    {
        $result1  = new ValidationResult();
        $context1 = new StringContext('file1.php', 10);
        $result1->addRequiredString('duplicate_key', $context1);

        $result2  = new ValidationResult();
        $context2 = new StringContext('file2.php', 20);
        $result2->addRequiredString('duplicate_key', $context2);

        $result1->merge($result2);

        $requiredStrings = $result1->getRequiredStrings();
        $this->assertCount(1, $requiredStrings, 'Should have only 1 required string');
        $this->assertSame($context2, $requiredStrings['duplicate_key'], 'Should use context from result2');
    }

    /**
     * Test merging empty results.
     */
    public function testMergeWithEmptyResultDoesNotChangeOriginal(): void
    {
        $result1 = new ValidationResult();
        $result1->addRawError('Original error');
        $result1->addSuccess('Original success');

        $result2 = new ValidationResult();

        $originalErrorCount   = $result1->getErrorCount();
        $originalSuccessCount = $result1->getSuccessCount();

        $result1->merge($result2);

        $this->assertSame($originalErrorCount, $result1->getErrorCount(), 'Error count should not change');
        $this->assertSame($originalSuccessCount, $result1->getSuccessCount(), 'Success count should not change');
    }

    /**
     * Test that result maintains immutability of returned arrays.
     */
    public function testArrayGettersReturnImmutableArrays(): void
    {
        $result = new ValidationResult();
        $result->addRawError('Test error');
        $result->addRawWarning('Test warning');
        $result->addError('Formatted error'); // This adds to both errors and messages

        // Get arrays and modify them
        $errors   = $result->getErrors();
        $warnings = $result->getWarnings();
        $messages = $result->getMessages();

        $errors[]   = 'Modified error';
        $warnings[] = 'Modified warning';
        $messages[] = 'Modified message';

        // Original result should be unchanged
        $this->assertCount(2, $result->getErrors(), 'Errors should not be modified (1 raw + 1 formatted)');
        $this->assertCount(1, $result->getWarnings(), 'Warnings should not be modified');
        $this->assertCount(1, $result->getMessages(), 'Messages should not be modified (1 formatted error)');
    }

    /**
     * Test large-scale operations for performance.
     */
    public function testLargeScaleOperationsPerformReasonably(): void
    {
        $result = new ValidationResult();

        $startTime = microtime(true);

        // Add many items
        for ($i = 0; $i < 1000; ++$i) {
            $result->addRequiredString("string_{$i}", new StringContext("file_{$i}.php", $i));
            $result->addRawError("Error {$i}");
            $result->addRawWarning("Warning {$i}");
            $result->addSuccess("Success {$i}");
        }

        // Check counts
        $this->assertSame(1000, count($result->getRequiredStrings()));
        $this->assertSame(1000, $result->getErrorCount());
        $this->assertSame(1000, $result->getWarningCount());
        $this->assertSame(1000, $result->getSuccessCount());

        $endTime  = microtime(true);
        $duration = $endTime - $startTime;

        // Should complete in reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $duration, 'Large-scale operations should be reasonably fast');
    }

    /**
     * Test edge cases with empty strings and null values.
     */
    public function testEdgeCasesHandlesGracefully(): void
    {
        $result = new ValidationResult();

        // Test empty strings
        $result->addRawError('');
        $result->addRawWarning('');
        $result->addError(''); // This will add to both errors and messages
        $result->addSuccess('');

        $this->assertSame(2, $result->getErrorCount(), 'Should count both raw and formatted empty errors');
        $this->assertSame(1, $result->getWarningCount(), 'Should count empty warning');
        $this->assertCount(1, $result->getMessages(), 'Should store formatted empty error message');
        $this->assertSame(1, $result->getSuccessCount(), 'Should count empty success');

        // Test with StringContext having null values
        $context = new StringContext(null, null, null);
        $result->addRequiredString('test_key', $context);

        $requiredStrings = $result->getRequiredStrings();
        $this->assertArrayHasKey('test_key', $requiredStrings, 'Should handle StringContext with null values');
    }
}
