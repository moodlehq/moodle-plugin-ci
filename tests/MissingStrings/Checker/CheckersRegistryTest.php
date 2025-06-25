<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\MissingStrings\Checker;

use MoodlePluginCI\MissingStrings\Checker\CheckersRegistry;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\ExceptionChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\GradeItemChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\PrivacyProviderChecker;
use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\SearchAreaChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CachesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\CapabilitiesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MessagesChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\MobileChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\SubpluginsChecker;
use MoodlePluginCI\MissingStrings\Checker\DatabaseFileChecker\TagsChecker;
use MoodlePluginCI\MissingStrings\Checker\StringCheckerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CheckersRegistry class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Checker\CheckersRegistry
 */
class CheckersRegistryTest extends TestCase
{
    /**
     * Test getCheckers method returns all checkers.
     */
    public function testGetCheckers(): void
    {
        $checkers = CheckersRegistry::getCheckers();

        $this->assertIsArray($checkers);
        $this->assertNotEmpty($checkers);
        $this->assertContainsOnlyInstancesOf(StringCheckerInterface::class, $checkers);

        // Should contain both database file and class method checkers
        $this->assertCount(10, $checkers); // 6 database + 4 class method checkers
    }

    /**
     * Test databaseFileCheckers method returns correct checkers.
     */
    public function testDatabaseFileCheckers(): void
    {
        $checkers = CheckersRegistry::databaseFileCheckers();

        $this->assertIsArray($checkers);
        $this->assertCount(6, $checkers);
        $this->assertContainsOnlyInstancesOf(StringCheckerInterface::class, $checkers);

        // Check specific checker types
        $checkerClasses = array_map('get_class', $checkers);
        $this->assertContains(CapabilitiesChecker::class, $checkerClasses);
        $this->assertContains(CachesChecker::class, $checkerClasses);
        $this->assertContains(MessagesChecker::class, $checkerClasses);
        $this->assertContains(MobileChecker::class, $checkerClasses);
        $this->assertContains(SubpluginsChecker::class, $checkerClasses);
        $this->assertContains(TagsChecker::class, $checkerClasses);
    }

    /**
     * Test classMethodCheckers method returns correct checkers.
     */
    public function testClassMethodCheckers(): void
    {
        $checkers = CheckersRegistry::classMethodCheckers();

        $this->assertIsArray($checkers);
        $this->assertCount(4, $checkers);
        $this->assertContainsOnlyInstancesOf(StringCheckerInterface::class, $checkers);

        // Check specific checker types
        $checkerClasses = array_map('get_class', $checkers);
        $this->assertContains(ExceptionChecker::class, $checkerClasses);
        $this->assertContains(GradeItemChecker::class, $checkerClasses);
        $this->assertContains(PrivacyProviderChecker::class, $checkerClasses);
        $this->assertContains(SearchAreaChecker::class, $checkerClasses);
    }

    /**
     * Test that getCheckers returns combination of all checker types.
     */
    public function testGetCheckersReturnsCombinedCheckers(): void
    {
        $allCheckers         = CheckersRegistry::getCheckers();
        $databaseCheckers    = CheckersRegistry::databaseFileCheckers();
        $classMethodCheckers = CheckersRegistry::classMethodCheckers();

        $this->assertCount(
            count($databaseCheckers) + count($classMethodCheckers),
            $allCheckers
        );

        $allCheckerClasses         = array_map('get_class', $allCheckers);
        $databaseCheckerClasses    = array_map('get_class', $databaseCheckers);
        $classMethodCheckerClasses = array_map('get_class', $classMethodCheckers);

        // Verify all database checkers are included
        foreach ($databaseCheckerClasses as $checkerClass) {
            $this->assertContains($checkerClass, $allCheckerClasses);
        }

        // Verify all class method checkers are included
        foreach ($classMethodCheckerClasses as $checkerClass) {
            $this->assertContains($checkerClass, $allCheckerClasses);
        }
    }

    /**
     * Test that each checker method returns new instances.
     */
    public function testCheckersReturnNewInstances(): void
    {
        $checkers1 = CheckersRegistry::getCheckers();
        $checkers2 = CheckersRegistry::getCheckers();

        $this->assertCount(count($checkers1), $checkers2);

        // Each call should return new instances
        for ($i = 0; $i < count($checkers1); ++$i) {
            $this->assertSame(get_class($checkers1[$i]), get_class($checkers2[$i]));
            $this->assertNotSame($checkers1[$i], $checkers2[$i]);
        }
    }

    /**
     * Test that databaseFileCheckers returns new instances.
     */
    public function testDatabaseFileCheckersReturnNewInstances(): void
    {
        $checkers1 = CheckersRegistry::databaseFileCheckers();
        $checkers2 = CheckersRegistry::databaseFileCheckers();

        $this->assertCount(count($checkers1), $checkers2);

        // Each call should return new instances
        for ($i = 0; $i < count($checkers1); ++$i) {
            $this->assertSame(get_class($checkers1[$i]), get_class($checkers2[$i]));
            $this->assertNotSame($checkers1[$i], $checkers2[$i]);
        }
    }

    /**
     * Test that classMethodCheckers returns new instances.
     */
    public function testClassMethodCheckersReturnNewInstances(): void
    {
        $checkers1 = CheckersRegistry::classMethodCheckers();
        $checkers2 = CheckersRegistry::classMethodCheckers();

        $this->assertCount(count($checkers1), $checkers2);

        // Each call should return new instances
        for ($i = 0; $i < count($checkers1); ++$i) {
            $this->assertSame(get_class($checkers1[$i]), get_class($checkers2[$i]));
            $this->assertNotSame($checkers1[$i], $checkers2[$i]);
        }
    }

    /**
     * Test that all checkers implement the required interface.
     */
    public function testAllCheckersImplementInterface(): void
    {
        $allCheckers = CheckersRegistry::getCheckers();

        foreach ($allCheckers as $checker) {
            $this->assertInstanceOf(StringCheckerInterface::class, $checker);
        }
    }

    /**
     * Test that registry contains expected number of each checker type.
     */
    public function testCheckerTypeDistribution(): void
    {
        $allCheckers         = CheckersRegistry::getCheckers();
        $databaseCheckers    = CheckersRegistry::databaseFileCheckers();
        $classMethodCheckers = CheckersRegistry::classMethodCheckers();

        // Verify counts match expected numbers
        $this->assertSame(6, count($databaseCheckers), 'Should have 6 database file checkers');
        $this->assertSame(4, count($classMethodCheckers), 'Should have 4 class method checkers');
        $this->assertSame(10, count($allCheckers), 'Should have 10 total checkers');
    }
}
