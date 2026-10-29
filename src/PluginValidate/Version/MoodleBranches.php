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

/**
 * Known Moodle branches and the version.php values.
 */
final class MoodleBranches
{
    /** Lists every branch from 3.0 onwards, because the baseline version is the 3.0.0 release. */
    private const UPDATES_URL = 'https://download.moodle.org/api/1.3/updates.php?format=json&version=2015111600&branch=3.0';

    /** @var array<int, float> Branch number => version of the x.0 release */
    private array $starts;

    /**
     * @param array<int, float> $starts
     */
    private function __construct(array $starts)
    {
        $this->starts = $starts;
    }

    /**
     * Catalogue from the live Moodle updates API.
     */
    public static function fromApi(): self
    {
        return new self(self::fetchAndParseStarts());
    }

    /**
     * Catalogue from the given branch => start values.
     *
     * @param array<int, float> $starts
     */
    public static function fromCustom(array $starts): self
    {
        return new self($starts);
    }

    /**
     * Whether the branch number is in the catalogue.
     */
    public function hasBranch(int $branch): bool
    {
        return array_key_exists($branch, $this->starts);
    }

    /**
     * Start value of the next catalogued branch after the given one, or null if none or unknown.
     */
    public function nextStartAfter(int $branch): ?float
    {
        $found = false;
        foreach ($this->starts as $candidate => $start) {
            if ($found) {
                return $start;
            }
            $found = $candidate === $branch;
        }

        return null;
    }

    private static function fetchUpdates(): string
    {
        $context = stream_context_create(['http' => ['timeout' => 10, 'header' => "User-Agent: moodle-plugin-ci\r\n"]]);
        $json    = @file_get_contents(self::UPDATES_URL, false, $context);
        if ($json === false) {
            throw new \RuntimeException('Unable to fetch Moodle branches from ' . self::UPDATES_URL);
        }

        return $json;
    }

    /**
     * Branch number => start value, from the updates API, or from the given response JSON.
     *
     * @return array<int, float>
     */
    public static function fetchAndParseStarts(?string $json = null): array
    {
        $data = json_decode($json ?? self::fetchUpdates(), true);
        if (!is_array($data) || !isset($data['updates']['core']) || !is_array($data['updates']['core'])) {
            throw new \RuntimeException('Unexpected response from the Moodle updates API');
        }

        $starts = [];
        foreach ($data['updates']['core'] as $release) {
            // Development builds such as main have maturity below 200.
            if (($release['maturity'] ?? 0) < 200) {
                continue;
            }

            // The release_index_info branch uses MOODLE_310_STABLE style names, unlike the 3.10 or 4.04 style of the branch key.
            $branch = $release['release_index_info']['branch'] ?? '';
            if (preg_match('/^MOODLE_(\d+)_STABLE$/', $branch, $matches) !== 1) {
                continue;
            }

            // Stable builds keep the x.0 date and only increment the last two digits.
            $version = (int) floor((float) $release['version']);
            $starts[(int) $matches[1]] = (float) (intdiv($version, 100) * 100);
        }

        if ($starts === []) {
            throw new \RuntimeException('No stable Moodle branches found in the updates API response');
        }

        ksort($starts);

        return $starts;
    }
}
