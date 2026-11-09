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

namespace MoodlePluginCI\Command;

use MoodlePluginCI\PluginValidate\Version\MoodleBranches;
use MoodlePluginCI\PluginValidate\Version\VersionFileReader;
use MoodlePluginCI\PluginValidate\Version\VersionValidator;
use PhpParser\Node\Scalar\Int_;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check version.php values without needing a Moodle install.
 */
class VersionCommand extends AbstractPluginCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('version')
            ->setDescription('Check version.php values without a Moodle install')
            ->setHelp('Checks the documented properties of <comment>version.php</comment> statically. When <comment>--baseline-dir</comment> is given, '
                . 'the plugin version must also increase relative to the <comment>version.php</comment> in that directory, according to <comment>--version-policy</comment>.')
            ->addOption('baseline-dir', null, InputOption::VALUE_REQUIRED, 'Copy of the previous plugin; its version.php is the baseline the plugin version must increase from. Omit for a new plugin.')
            ->addOption('version-policy', null, InputOption::VALUE_REQUIRED, 'Either "latest" or "frozen"; required with --baseline-dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<bg=green;fg=white;> RUN </> <fg=blue>Checking version.php</>');

        $baselineDir = $input->getOption('baseline-dir');
        $policy      = $input->getOption('version-policy');

        if (($baselineDir === null) !== ($policy === null)) {
            throw new \InvalidArgumentException('--baseline-dir and --version-policy must be given together.');
        }

        if ($policy !== null && !in_array($policy, [VersionValidator::POLICY_LATEST, VersionValidator::POLICY_FROZEN], true)) {
            throw new \InvalidArgumentException('--version-policy must be "latest" or "frozen".');
        }

        $baselineVersion = $baselineDir === null ? null : $this->readBaselineVersion($baselineDir);

        $result = (new VersionValidator(MoodleBranches::fromApi()))->verify($this->plugin->directory . '/version.php', $baselineVersion, $policy);

        $output->writeln($result->messages);

        return $result->isValid ? 0 : 1;
    }

    private function readBaselineVersion(string $dir): int
    {
        $path = rtrim($dir, '/') . '/version.php';
        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('--baseline-dir has no version.php: %s', $path));
        }

        $version = (new VersionFileReader())->read($path)['properties']['version'] ?? null;
        if (!$version instanceof Int_) {
            throw new \InvalidArgumentException(sprintf('Baseline $plugin->version must be an integer literal: %s', $path));
        }

        return $version->value;
    }
}
