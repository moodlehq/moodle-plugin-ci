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

use MoodlePluginCI\MissingStrings\Checker\ClassMethodChecker\GradeItemChecker;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Test the GradeItemChecker class.
 *
 * Tests grade item mapping detection and required gradeitem:{name} strings
 * from get_itemname_mapping_for_component() implementations.
 */
class GradeItemCheckerTest extends MissingStringsTestCase
{
    private GradeItemChecker $checker;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new GradeItemChecker();
    }

    /**
     * Test checker name.
     */
    public function testGetName(): void
    {
        $this->assertSame('Grade Item', $this->checker->getName());
    }

    /**
     * Test that checker applies to plugins with gradeitems.php file.
     */
    public function testAppliesToWithGradeItemsFile(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "grading"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker applies to plugins with itemnumber_mapping interface.
     */
    public function testAppliesToWithItemNumberMapping(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/custom_grades.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class custom_grades implements core_grades\component_gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "quiz_attempts",
            1 => "extra_credit"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $this->assertTrue($this->checker->appliesTo($plugin));
    }

    /**
     * Test that checker doesn't apply to plugins without grade item mappings.
     */
    public function testAppliesToWithoutGradeItemMapping(): void
    {
        $pluginDir = $this->createTestPlugin('local', 'testplugin', [
            'lib.php' => '<?php
defined("MOODLE_INTERNAL") || die();

function test_function() {
    return "No grade item mappings";
}
',
        ]);

        $plugin = $this->createPlugin('local', 'testplugin', $pluginDir);
        $this->assertFalse($this->checker->appliesTo($plugin));
    }

    /**
     * Test basic grade items with standard gradeitems.php file.
     */
    public function testCheckBasicGradeItemsFile(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "grading"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('grade_submissions_name', $requiredStrings);
        $this->assertArrayHasKey('grade_grading_name', $requiredStrings);
    }

    /**
     * Test grade items with empty item names (should be skipped).
     */
    public function testCheckGradeItemsWithEmptyNames(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "",           // Empty name should be skipped
            1 => "grading",
            2 => "feedback"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should only detect non-empty item names
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('grade_grading_name', $requiredStrings);
        $this->assertArrayHasKey('grade_feedback_name', $requiredStrings);
    }

    /**
     * Test grade items in alternative file location.
     */
    public function testCheckGradeItemsInAlternativeLocation(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/custom_gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class custom_gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "quiz_attempts",
            1 => "bonus_points"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('grade_quiz_attempts_name', $requiredStrings);
        $this->assertArrayHasKey('grade_bonus_points_name', $requiredStrings);
    }

    /**
     * Test multiple grade item files in the same plugin.
     */
    public function testCheckMultipleGradeItemFiles(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "grading"
        ];
    }
}
',
            'classes/grades/additional_gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class additional_gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "peer_review",
            1 => "self_assessment"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(4, $requiredStrings);
        $this->assertArrayHasKey('grade_submissions_name', $requiredStrings);
        $this->assertArrayHasKey('grade_grading_name', $requiredStrings);
        $this->assertArrayHasKey('grade_peer_review_name', $requiredStrings);
        $this->assertArrayHasKey('grade_self_assessment_name', $requiredStrings);
    }

    /**
     * Test complex grade item mapping with various array formats.
     */
    public function testCheckComplexGradeItemMapping(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "initial_submission",
            1 => "peer_reviews",
            2 => "final_grading",
            3 => "reflection_assignment"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(4, $requiredStrings);
        $this->assertArrayHasKey('grade_initial_submission_name', $requiredStrings);
        $this->assertArrayHasKey('grade_peer_reviews_name', $requiredStrings);
        $this->assertArrayHasKey('grade_final_grading_name', $requiredStrings);
        $this->assertArrayHasKey('grade_reflection_assignment_name', $requiredStrings);
    }

    /**
     * Test grade item mapping with single quotes.
     */
    public function testCheckGradeItemMappingWithSingleQuotes(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => "<?php
defined('MOODLE_INTERNAL') || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => 'assignment_draft',
            1 => 'assignment_final'
        ];
    }
}
",
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('grade_assignment_draft_name', $requiredStrings);
        $this->assertArrayHasKey('grade_assignment_final_name', $requiredStrings);
    }

    /**
     * Test grade item mapping with mixed quote types.
     */
    public function testCheckGradeItemMappingWithMixedQuotes(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "written_work",
            1 => \'presentation\',
            2 => "group_project"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('grade_written_work_name', $requiredStrings);
        $this->assertArrayHasKey('grade_presentation_name', $requiredStrings);
        $this->assertArrayHasKey('grade_group_project_name', $requiredStrings);
    }

    /**
     * Test that non-grade-item classes are ignored.
     */
    public function testCheckNonGradeItemClassIgnored(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/helper.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class helper {
    public function process_data($data) {
        return $data;
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(0, $requiredStrings);
    }

    /**
     * Test error handling for malformed grade items file.
     */
    public function testCheckMalformedGradeItemsFile(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

// Malformed PHP syntax
class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            // Missing closing brace and return statement
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);
    }

    /**
     * Test error handling for unreadable grade items file.
     */
    public function testCheckUnreadableGradeItemsFile(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "grading"
        ];
    }
}
',
        ]);

        // Make the file unreadable
        $gradeitemsFile = $pluginDir . '/classes/grades/gradeitems.php';
        chmod($gradeitemsFile, 0000);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should handle error gracefully
        $this->assertInstanceOf(\MoodlePluginCI\MissingStrings\ValidationResult::class, $result);

        // Restore permissions for cleanup
        chmod($gradeitemsFile, 0644);
    }

    /**
     * Test context information includes correct file paths.
     */
    public function testCheckContextInformation(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "grading"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);

        $errors         = $result->getErrors();
        $gradeitemsFile = $pluginDir . '/classes/grades/gradeitems.php';

        foreach ($errors as $error) {
            $this->assertSame($gradeitemsFile, $error['file']);
            $this->assertGreaterThan(0, $error['line']);
            $this->assertNotEmpty($error['description']);
            $this->assertStringContainsString('Grade item', $error['description']);
        }
    }

    /**
     * Test duplicate item names (should only appear once).
     */
    public function testCheckDuplicateItemNames(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "submissions",
            1 => "submissions",  // Duplicate
            2 => "grading"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        // Should only detect unique item names
        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('grade_submissions_name', $requiredStrings);
        $this->assertArrayHasKey('grade_grading_name', $requiredStrings);
    }

    /**
     * Test grade item mapping with numeric keys and string values.
     */
    public function testCheckNumericKeysWithStringValues(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            10 => "advanced_submission",
            20 => "peer_evaluation",
            30 => "final_grade"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('grade_advanced_submission_name', $requiredStrings);
        $this->assertArrayHasKey('grade_peer_evaluation_name', $requiredStrings);
        $this->assertArrayHasKey('grade_final_grade_name', $requiredStrings);
    }

    /**
     * Test multi-line grade item mapping (realistic formatting).
     */
    public function testCheckMultiLineGradeItemMapping(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "initial_draft",
            1 => "peer_review_score",
            2 => "instructor_feedback",
            3 => "final_submission"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(4, $requiredStrings);
        $this->assertArrayHasKey('grade_initial_draft_name', $requiredStrings);
        $this->assertArrayHasKey('grade_peer_review_score_name', $requiredStrings);
        $this->assertArrayHasKey('grade_instructor_feedback_name', $requiredStrings);
        $this->assertArrayHasKey('grade_final_submission_name', $requiredStrings);
    }

    /**
     * Test get_advancedgrading_itemnames method support.
     */
    public function testCheckAdvancedGradingItemNames(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_advancedgrading_itemnames(): array {
        return [
            "forum",
            "discussion"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        $this->assertCount(2, $requiredStrings);
        $this->assertArrayHasKey('gradeitem:forum', $requiredStrings);
        $this->assertArrayHasKey('gradeitem:discussion', $requiredStrings);
    }

    /**
     * Test both get_itemname_mapping_for_component and get_advancedgrading_itemnames methods.
     */
    public function testCheckBothMappingMethods(): void
    {
        $pluginDir = $this->createTestPlugin('mod', 'testmod', [
            'classes/grades/gradeitems.php' => '<?php
defined("MOODLE_INTERNAL") || die();

class gradeitems {
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => "rating",
            1 => "forum"
        ];
    }

    public static function get_advancedgrading_itemnames(): array {
        return [
            "forum"
        ];
    }
}
',
        ]);

        $plugin = $this->createPlugin('mod', 'testmod', $pluginDir);
        $result = $this->checker->check($plugin);

        $requiredStrings = $result->getRequiredStrings();

        // Should have: gradeitem:forum (from get_advancedgrading_itemnames), grade_rating_name, grade_forum_name (from get_itemname_mapping_for_component)
        $this->assertCount(3, $requiredStrings);
        $this->assertArrayHasKey('gradeitem:forum', $requiredStrings);
        $this->assertArrayHasKey('grade_rating_name', $requiredStrings);
        $this->assertArrayHasKey('grade_forum_name', $requiredStrings);
    }
}
