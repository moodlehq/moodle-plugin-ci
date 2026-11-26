<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2026 Catalyst IT Australia
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\PluginValidate\Version;

use MoodlePluginCI\PluginValidate\Version\MoodleBranches;
use MoodlePluginCI\PluginValidate\Version\VersionResult;
use MoodlePluginCI\PluginValidate\Version\VersionValidator;

class VersionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** Fixed branch catalogue (branch => x.0 start), so the tests do not depend on the live updates API. */
    private const CATALOGUE = [
        30  => 2015111600.0,
        37  => 2019052000.0,
        38  => 2019111800.0,
        39  => 2020061500.0,
        310 => 2020110900.0,
        311 => 2021051700.0,
        400 => 2022041900.0,
        401 => 2022112800.0,
        402 => 2023100900.0,
        403 => 2024100700.0,
    ];

    /** @var string[] */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        $this->files = [];
    }

    private function verifyBody(string $body, ?int $baseline = null, ?string $policy = null): VersionResult
    {
        // The parser only accepts ".php" files, so the temp file needs that extension.
        $path          = sys_get_temp_dir() . '/' . uniqid('version', true) . '.php';
        $this->files[] = $path;
        file_put_contents($path, "<?php\ndefined('MOODLE_INTERNAL') || die();\n" . $body);

        return (new VersionValidator(MoodleBranches::fromCustom(self::CATALOGUE)))->verify($path, $baseline, $policy);
    }

    private function minimal(string $extra = ''): string
    {
        return "\$plugin->version = 2022060100;\n\$plugin->component = 'mod_example';\n" . $extra;
    }

    private function assertPasses(VersionResult $result): void
    {
        $this->assertTrue($result->isValid, implode("\n", $result->messages));
    }

    private function assertFails(VersionResult $result): void
    {
        $this->assertFalse($result->isValid);
    }

    // A minimal valid version.php passes.
    public function testValidMinimal(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal()));
    }

    // A release given as a string or an integer literal passes.
    public function testStringAndIntegerReleasePass(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->release = '1.0';\n")));
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->release = 2022060100;\n")));
    }

    // An empty or non-literal release fails.
    public function testEmptyOrNonLiteralReleaseFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->release = '';\n")));
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->release = 1.5;\n")));
    }

    // A missing $plugin->component fails.
    public function testMissingComponentFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2022060100;\n"));
    }

    // A missing $plugin->version fails.
    public function testMissingVersionFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->component = 'mod_example';\n"));
    }

    // A float $plugin->requires matching a core weekly build passes.
    public function testFloatRequiresPasses(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2024100700.02;\n")));
    }

    // A float $plugin->requires with an invalid date fails.
    public function testFloatRequiresBadDateFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->requires = 2024023000.02;\n")));
    }

    // An integer $plugin->requires passes.
    public function testIntegerRequiresPasses(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2022041900;\n")));
    }

    // A float $plugin->version fails.
    public function testFloatVersionFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2022060100.00;\n\$plugin->component = 'mod_example';\n"));
    }

    // A version with an invalid date (30 February) fails.
    public function testBadDateFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2022023000;\n\$plugin->component = 'mod_example';\n"));
    }

    // A version shorter than YYYYMMDD + last 2 digits fails.
    public function testShortVersionFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 20220601;\n\$plugin->component = 'mod_example';\n"));
    }

    // A component name with spaces or capitals fails.
    public function testBadComponentFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2022060100;\n\$plugin->component = 'Mod Example';\n"));
    }

    // A component name with a trailing newline fails.
    public function testComponentTrailingNewlineFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2022060100;\n\$plugin->component = 'mod_example\n';\n"));
    }

    // A version dated in the future fails.
    public function testFutureVersionFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = 2099123100;\n\$plugin->component = 'mod_example';\n"));
    }

    // A version dated today passes.
    public function testTodayVersionPasses(): void
    {
        $this->assertPasses($this->verifyBody("\$plugin->version = " . gmdate('Ymd') . "00;\n\$plugin->component = 'mod_example';\n"));
    }

    // A version dated three days ahead, beyond the two-day grace period, fails.
    public function testVersionBeyondGracePeriodFails(): void
    {
        $this->assertFails($this->verifyBody("\$plugin->version = " . gmdate('Ymd', time() + 3 * 86400) . "00;\n\$plugin->component = 'mod_example';\n"));
    }

    // A property assigned twice fails.
    public function testDuplicatePropertyFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->version = 2022060101;\n")));
    }

    // A supported branch number that is not catalogued fails.
    public function testUnknownBranchFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->supported = [999, 999];\n")));
    }

    // A supported range including older branches passes.
    public function testOlderSupportedBranchesPass(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2015111600;\n\$plugin->supported = [30, 39];\n")));
    }

    // A requires value newer than an older supported lower bound fails.
    public function testRequiresNewerThanOlderSupportedLowerFails(): void
    {
        // Requires 3.8.0 but claims to support 3.7.
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->requires = 2019111800;\n\$plugin->supported = [37, 38];\n")));
    }

    // A supported lower bound above the upper bound fails.
    public function testSupportedLowerAboveUpperFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->supported = [402, 401];\n")));
    }

    // An incompatible value at or below the supported upper bound fails.
    public function testIncompatibleAtOrBelowUpperFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->supported = [401, 402];\n\$plugin->incompatible = 402;\n")));
    }

    // An incompatible value above the supported upper bound passes.
    public function testIncompatibleAboveUpperPasses(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->supported = [401, 402];\n\$plugin->incompatible = 403;\n")));
    }

    // A requires value newer than the supported lower bound fails.
    public function testRequiresNewerThanSupportedLowerFails(): void
    {
        // Requires 3.11.1 but claims to support 3.10.
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->requires = 2021051701;\n\$plugin->supported = [310, 311];\n")));
    }

    // A requires value equal to the next branch's start fails.
    public function testRequiresEqualToNextBranchStartFails(): void
    {
        // Moodle 3.11.0 is the first release of the branch after 310.
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->requires = 2021051700;\n\$plugin->supported = [310, 311];\n")));
    }

    // A 3.10.x release requirement with supported 310 passes.
    public function testRequires310ReleaseWithSupported310Passes(): void
    {
        // 3.10.11 has version 2020110911, which is below the 3.11.0 value 2021051700.
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2020110911;\n\$plugin->supported = [310, 311];\n")));
    }

    // A 3.9.x release requirement with supported 39 and 310 passes.
    public function testRequires39PointReleaseWithSupported39And310Passes(): void
    {
        // 3.9.1 has version 2020061501, which is below the 3.10.0 value 2020110900.
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2020061501;\n\$plugin->supported = [39, 310];\n")));
    }

    // A 4.0 requirement with supported 400 passes.
    public function testRequires40WithSupported400Passes(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->requires = 2022041900;\n\$plugin->supported = [400, 402];\n")));
    }

    // A MATURITY_* constant passes.
    public function testMaturityValid(): void
    {
        $this->assertPasses($this->verifyBody($this->minimal("\$plugin->maturity = MATURITY_STABLE;\n")));
    }

    // A string maturity value fails.
    public function testMaturityInvalidFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->maturity = 'stable';\n")));
    }

    // Dependencies using ANY_VERSION and integer versions pass.
    public function testDependenciesWithAnyVersionPasses(): void
    {
        $body = $this->minimal("\$plugin->dependencies = [\n    'mod_forum' => 2022041900,\n    'block_foo' => ANY_VERSION,\n];\n");
        $this->assertPasses($this->verifyBody($body));
    }

    // A dependency key without a component prefix fails.
    public function testDependenciesBadKeyFails(): void
    {
        $this->assertFails($this->verifyBody($this->minimal("\$plugin->dependencies = ['forum' => 2022041900];\n")));
    }

    // Latest policy: a same-date increment over the baseline passes.
    public function testBaselineLatestSameDateIncrementPasses(): void
    {
        $body = "\$plugin->version = 2026042001;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042000, VersionValidator::POLICY_LATEST));
    }

    // Latest policy: a cumulative same-date increase over the baseline passes.
    public function testBaselineLatestSameDateCumulativePasses(): void
    {
        $body = "\$plugin->version = 2026042002;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042000, VersionValidator::POLICY_LATEST));
    }

    // Latest policy: a date advance with the last 2 digits reset to 00 passes.
    public function testBaselineLatestNewDateWithLast2DigitsZeroPasses(): void
    {
        $body = "\$plugin->version = 2026042100;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042001, VersionValidator::POLICY_LATEST));
    }

    // Latest policy: a date advance with non-zero last 2 digits passes.
    public function testBaselineLatestNewDateWithLast2DigitsNonZeroPasses(): void
    {
        $body = "\$plugin->version = 2026042101;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042000, VersionValidator::POLICY_LATEST));
    }

    // A version equal to the baseline fails.
    public function testBaselineBackwardsFails(): void
    {
        $body = "\$plugin->version = 2026042000;\n\$plugin->component = 'mod_example';\n";
        $this->assertFails($this->verifyBody($body, 2026042000, VersionValidator::POLICY_LATEST));
    }

    // Frozen policy: a same-date increment over the baseline passes.
    public function testBaselineFrozenSameDateIncrementPasses(): void
    {
        $body = "\$plugin->version = 2026042001;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042000, VersionValidator::POLICY_FROZEN));
    }

    // Frozen policy: a cumulative same-date increase over the baseline passes.
    public function testBaselineFrozenSameDateCumulativePasses(): void
    {
        $body = "\$plugin->version = 2026042005;\n\$plugin->component = 'mod_example';\n";
        $this->assertPasses($this->verifyBody($body, 2026042000, VersionValidator::POLICY_FROZEN));
    }

    // Frozen policy: a date change fails.
    public function testBaselineFrozenNewDateFails(): void
    {
        $body = "\$plugin->version = 2026042100;\n\$plugin->component = 'mod_example';\n";
        $this->assertFails($this->verifyBody($body, 2026042000, VersionValidator::POLICY_FROZEN));
    }

    // The updates API response keeps only stable branches, sorted by branch number.
    public function testParseStartsKeepsStableBranches(): void
    {
        $json = json_encode([
            'updates' => ['core' => [
                ['version' => 2021051711, 'maturity' => 200, 'release_index_info' => ['branch' => 'MOODLE_311_STABLE']],
                ['version' => 2020110911, 'maturity' => 200, 'release_index_info' => ['branch' => 'MOODLE_310_STABLE']],
                ['version' => 2021051711, 'maturity' => 100, 'release_index_info' => ['branch' => 'MOODLE_312_STABLE']],
                ['version' => 2099010100, 'maturity' => 0, 'release_index_info' => ['branch' => 'MOODLE_500_DEV']],
            ]],
        ]);

        $this->assertSame([310 => 2020110900.0, 311 => 2021051700.0], MoodleBranches::fetchAndParseStarts($json));
    }

    // A missing version.php file fails.
    public function testMissingFileFails(): void
    {
        $result = (new VersionValidator(MoodleBranches::fromCustom(self::CATALOGUE)))->verify('/nonexistent/version.php');
        $this->assertFails($result);
    }
}
