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

namespace MoodlePluginCI\PluginValidate\Version;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;

/**
 * Static checks for a plugin's version.php, following the rules at
 * https://moodledev.io/docs/apis/commonfiles/version.php.
 */
class VersionValidator
{
    /** Stable branch: the version must increase, either by moving to a new date or by incrementing the last 2 digits on the same date. */
    public const POLICY_LATEST = 'latest';

    /** Frozen (older) branch: the version may only increment the last 2 digits on the same date. */
    public const POLICY_FROZEN = 'frozen';

    private const MATURITIES = ['MATURITY_ALPHA', 'MATURITY_BETA', 'MATURITY_RC', 'MATURITY_STABLE'];

    private const COMPONENT_PATTERN = '/^[a-z][a-z0-9]*_[a-z][a-z0-9_]*\z/';

    private MoodleBranches $branches;

    public function __construct(MoodleBranches $branches)
    {
        $this->branches = $branches;
    }

    /**
     * Verify a version.php file.
     *
     * @param string      $path     Path to version.php
     * @param int|null    $baseline Version from the base branch, or null to skip the increase check
     * @param string|null $policy   POLICY_LATEST or POLICY_FROZEN, required when $baseline is set
     */
    public function verify(string $path, ?int $baseline = null, ?string $policy = null): VersionResult
    {
        $messages = $this->runChecks($path, $baseline, $policy);

        return new VersionResult($this->format($messages), !$this->hasErrors($messages));
    }

    /**
     * @return array<array{isError: bool, text: string}> messages in the order they were found
     */
    private function runChecks(string $path, ?int $baseline, ?string $policy): array
    {
        $messages = [];
        if ($baseline !== null && !in_array($policy, [self::POLICY_LATEST, self::POLICY_FROZEN], true)) {
            throw new \InvalidArgumentException('Policy must be "latest" or "frozen" when a baseline is given.');
        }

        if (!is_file($path)) {
            $this->addError($messages, sprintf('Failed to find required file: %s', $path));

            return $messages;
        }

        try {
            $file = (new VersionFileReader())->read($path);
        } catch (\Exception $e) {
            $this->addError($messages, sprintf('Failed to read version.php: %s', $e->getMessage()));

            return $messages;
        }

        foreach ($file['duplicates'] as $name) {
            $this->addError($messages, sprintf('$plugin->%s is assigned more than once', $name));
        }

        $props = $file['properties'];
        foreach (['component', 'version'] as $required) {
            if (!isset($props[$required])) {
                $this->addError($messages, sprintf('Missing required property $plugin->%s', $required));
            }
        }

        $version = isset($props['version'])
            ? $this->checkVersionNumber($messages, '$plugin->version', $props['version'])
            : null;

        // Grace period of two days for timezones ahead of UTC.
        if ($version !== null && intdiv($version, 100) > (int) gmdate('Ymd', time() + 2 * 86400)) {
            $this->addError($messages, '$plugin->version must not have a future date');
        }

        $requires = isset($props['requires'])
            ? $this->checkRequires($messages, $props['requires'])
            : null;

        if (isset($props['component'])) {
            $this->checkComponent($messages, '$plugin->component', $props['component']);
        }

        if (isset($props['release'])) {
            $this->checkRelease($messages, $props['release']);
        }

        if (isset($props['maturity'])) {
            $this->checkMaturity($messages, $props['maturity']);
        }

        $supported = isset($props['supported'])
            ? $this->checkSupported($messages, $props['supported'])
            : null;

        $incompatible = isset($props['incompatible'])
            ? $this->checkBranch($messages, '$plugin->incompatible', $props['incompatible'])
            : null;

        if (isset($props['dependencies'])) {
            $this->checkDependencies($messages, $props['dependencies']);
        }

        if ($supported !== null && $incompatible !== null && $incompatible <= $supported[1]) {
            $this->addError($messages, sprintf(
                '$plugin->incompatible %d is not above the upper supported branch %d',
                $incompatible,
                $supported[1]
            ));
        }

        if ($supported !== null && $requires !== null) {
            $next = $this->branches->nextStartAfter($supported[0]);
            if ($next !== null && $requires >= $next) {
                $this->addError($messages, sprintf(
                    '$plugin->requires %d is newer than the versions of Moodle branch %d, which the plugin claims to support',
                    $requires,
                    $supported[0]
                ));
            }
        }

        if ($baseline !== null && $version !== null) {
            $this->checkBaseline($messages, $baseline, (string) $policy, $version);
        }

        if (!$this->hasErrors($messages)) {
            $this->addSuccess($messages, 'version.php passes static checks');
        }

        return $messages;
    }

    /**
     * A version number in YYYYMMDD + last 2 digits form.
     */
    private function checkVersionNumber(array &$messages, string $label, Expr $expr): ?int
    {
        if ($expr instanceof Float_) {
            $this->addError($messages, sprintf('%s must be an integer, not a float such as 2022041900.00', $label));

            return null;
        }

        $value = $this->checkInteger($messages, $label, $expr);
        if ($value === null) {
            return null;
        }

        return $this->checkVersionDate($messages, $label, $value) ? $value : null;
    }

    /**
     * The required core version, which may be a float to match a weekly build such as 2024100700.02.
     */
    private function checkRequires(array &$messages, Expr $expr): ?float
    {
        $label = '$plugin->requires';
        if ($expr instanceof Float_) {
            return $this->checkVersionDate($messages, $label, (int) $expr->value) ? $expr->value : null;
        }

        $value = $this->checkVersionNumber($messages, $label, $expr);

        return $value === null ? null : (float) $value;
    }

    private function checkVersionDate(array &$messages, string $label, int $value): bool
    {
        $digits = (string) $value;
        if (strlen($digits) !== 10) {
            $this->addError($messages, sprintf('%s must have 10 digits in YYYYMMDD + last 2 digits form, found %s', $label, $digits));

            return false;
        }

        $year  = (int) substr($digits, 0, 4);
        $month = (int) substr($digits, 4, 2);
        $day   = (int) substr($digits, 6, 2);
        if (!checkdate($month, $day, $year)) {
            $this->addError($messages, sprintf('%s has an invalid date in YYYYMMDD + last 2 digits form: %s', $label, $digits));

            return false;
        }

        return true;
    }

    /**
     * A Moodle branch number, such as 401, that must be in the catalogue.
     */
    private function checkBranch(array &$messages, string $label, Expr $expr): ?int
    {
        $value = $this->checkInteger($messages, $label, $expr);
        if ($value === null) {
            return null;
        }

        if (!$this->branches->hasBranch($value)) {
            $this->addError($messages, sprintf(
                '%s refers to unknown Moodle branch %d (not in the moodle-plugin-ci branch catalogue)',
                $label,
                $value
            ));

            return null;
        }

        return $value;
    }

    /**
     * @return int[]|null [lower, upper] branch numbers
     */
    private function checkSupported(array &$messages, Expr $expr): ?array
    {
        if (!$expr instanceof Array_) {
            $this->addError($messages, '$plugin->supported must be an array of two branch numbers, [lower, upper]');

            return null;
        }

        $branches = [];
        foreach ($expr->items as $item) {
            if ($item === null || $item->key !== null) {
                $this->addError($messages, '$plugin->supported must be a list of two branch numbers, without keys');

                return null;
            }
            $branch = $this->checkBranch($messages, '$plugin->supported', $item->value);
            if ($branch === null) {
                return null;
            }
            $branches[] = $branch;
        }

        if (count($branches) !== 2) {
            $this->addError($messages, sprintf('$plugin->supported must contain exactly two branch numbers, found %d', count($branches)));

            return null;
        }

        [$lower, $upper] = $branches;
        if ($lower > $upper) {
            $this->addError($messages, sprintf('$plugin->supported lower bound %d is above upper bound %d', $lower, $upper));

            return null;
        }

        return [$lower, $upper];
    }

    private function checkComponent(array &$messages, string $label, Expr $expr): ?string
    {
        if (!$expr instanceof String_) {
            $this->addError($messages, sprintf('%s must be a string literal', $label));

            return null;
        }

        if (!preg_match(self::COMPONENT_PATTERN, $expr->value)) {
            $this->addError($messages, sprintf('%s "%s" is not a valid frankenstyle name (expected plugintype_pluginname)', $label, $expr->value));

            return null;
        }

        return $expr->value;
    }

    private function checkRelease(array &$messages, Expr $expr): void
    {
        if (!$expr instanceof String_ || trim($expr->value) === '') {
            $this->addError($messages, '$plugin->release must be a non-empty string literal');
        }
    }

    private function checkMaturity(array &$messages, Expr $expr): void
    {
        if (!$expr instanceof ConstFetch || !in_array($expr->name->toString(), self::MATURITIES, true)) {
            $this->addError($messages, '$plugin->maturity must be one of MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC or MATURITY_STABLE');
        }
    }

    private function checkDependencies(array &$messages, Expr $expr): void
    {
        if (!$expr instanceof Array_) {
            $this->addError($messages, '$plugin->dependencies must be an array of component => version');

            return;
        }

        foreach ($expr->items as $item) {
            if ($item === null || !$item->key instanceof String_) {
                $this->addError($messages, '$plugin->dependencies keys must be component names as string literals');
                continue;
            }

            $component = $item->key->value;
            $label     = sprintf('$plugin->dependencies[\'%s\']', $component);
            if ($this->checkComponent($messages, $label, $item->key) === null) {
                continue;
            }

            if ($item->value instanceof ConstFetch && $item->value->name->toString() === 'ANY_VERSION') {
                continue;
            }

            $this->checkVersionNumber($messages, $label, $item->value);
        }
    }

    /**
     * Check the proposed version against the baseline from the base branch.
     */
    private function checkBaseline(array &$messages, int $baseline, string $policy, int $version): void
    {
        $prefix = sprintf('policy %s: found version %d, baseline %d -', $policy, $version, $baseline);

        if ($version <= $baseline) {
            $this->addError($messages, sprintf(
                '%s $plugin->version is not higher than the baseline, so it must increase',
                $prefix
            ));

            return;
        }

        $baseDate          = intdiv($baseline, 100);
        $baseLastTwoDigits = $baseline % 100;
        $newDate           = intdiv($version, 100);
        $newLastTwoDigits  = $version % 100;

        if ($newDate === $baseDate) {
            $this->addSuccess($messages, sprintf(
                '%s date is unchanged (%d), so the last 2 digits increase from %02d to %02d',
                $prefix,
                $baseDate,
                $baseLastTwoDigits,
                $newLastTwoDigits
            ));

            return;
        }

        // The date has advanced. Frozen branches must keep their date.
        if ($policy === self::POLICY_FROZEN) {
            $this->addError($messages, sprintf(
                '%s date changed from %d to %d, which frozen branches do not allow, so the date must stay %d and only the last 2 digits may increase',
                $prefix,
                $baseDate,
                $newDate,
                $baseDate
            ));

            return;
        }

        $this->addSuccess($messages, sprintf(
            '%s date advanced from %d to %d',
            $prefix,
            $baseDate,
            $newDate
        ));
    }

    /**
     * An integer literal, not a float or string.
     */
    private function checkInteger(array &$messages, string $label, Expr $expr): ?int
    {
        if ($expr instanceof Int_) {
            return $expr->value;
        }

        $this->addError($messages, sprintf('%s must be a plain integer literal', $label));

        return null;
    }

    /**
     * @param array<array{isError: bool, text: string}> $messages
     */
    private function addError(array &$messages, string $message): void
    {
        $messages[] = ['isError' => true, 'text' => $message];
    }

    /**
     * @param array<array{isError: bool, text: string}> $messages
     */
    private function addSuccess(array &$messages, string $message): void
    {
        $messages[] = ['isError' => false, 'text' => $message];
    }

    /**
     * @param array<array{isError: bool, text: string}> $messages
     *
     * @return string[]
     */
    private function format(array $messages): array
    {
        return array_map(
            static fn (array $message): string => $message['isError']
                ? sprintf('<fg=red>X %s</>', $message['text'])
                : sprintf('<info>></info> %s', $message['text']),
            $messages
        );
    }

    /**
     * @param array<array{isError: bool, text: string}> $messages
     */
    private function hasErrors(array $messages): bool
    {
        foreach ($messages as $message) {
            if ($message['isError']) {
                return true;
            }
        }

        return false;
    }
}
