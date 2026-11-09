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

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\VersionCommand;
use MoodlePluginCI\Tests\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class VersionCommandTest extends FilesystemTestCase
{
    private function versionFile(string $dir, string $body): string
    {
        return $this->dumpFile($dir . '/version.php', "<?php\n" . $body);
    }

    private function plugin(int $version): string
    {
        $this->versionFile('plugin', "\$plugin->version = {$version};\n\$plugin->component = 'mod_example';\n");

        return $this->tempDir . '/plugin';
    }

    private function execute(array $input): CommandTester
    {
        $application = new Application();
        $application->add(new VersionCommand());

        $commandTester = new CommandTester($application->find('version'));
        $commandTester->execute($input);

        return $commandTester;
    }

    // A new plugin with no baseline dir passes.
    public function testNewPluginPasses(): void
    {
        $commandTester = $this->execute(['plugin' => $this->plugin(2022060100)]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    // A version higher than the baseline passes.
    public function testIncreaseFromBaselineDirPasses(): void
    {
        $this->versionFile('base', "\$plugin->version = 2022060100;\n");

        $commandTester = $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base',
            '--version-policy' => 'latest',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    // A version equal to the baseline fails with the baseline message.
    public function testNoIncreaseFromBaselineDirFails(): void
    {
        $this->versionFile('base', "\$plugin->version = 2022060101;\n");

        $commandTester = $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base',
            '--version-policy' => 'latest',
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('baseline 2022060101 - $plugin->version is not higher than the baseline', $commandTester->getDisplay());
    }

    // A trailing slash on --baseline-dir is accepted.
    public function testTrailingSlashOnBaselineDirIsAccepted(): void
    {
        $this->versionFile('base', "\$plugin->version = 2022060100;\n");

        $commandTester = $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base/',
            '--version-policy' => 'latest',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    // A baseline dir without a version.php throws.
    public function testBaselineDirWithoutVersionFileThrows(): void
    {
        $this->fs->mkdir($this->tempDir . '/base');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('--baseline-dir has no version.php');

        $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base',
            '--version-policy' => 'latest',
        ]);
    }

    // A baseline version that is not an integer literal throws.
    public function testBaselineVersionMustBeIntegerLiteralThrows(): void
    {
        $this->versionFile('base', "\$plugin->version = '2022060100';\n");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an integer literal');

        $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base',
            '--version-policy' => 'latest',
        ]);
    }

    // --baseline-dir without --version-policy throws.
    public function testBaselineDirWithoutPolicyThrows(): void
    {
        $this->versionFile('base', "\$plugin->version = 2022060100;\n");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be given together');

        $this->execute([
            'plugin'         => $this->plugin(2022060101),
            '--baseline-dir' => $this->tempDir . '/base',
        ]);
    }

    // --version-policy without --baseline-dir throws.
    public function testPolicyWithoutBaselineDirThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be given together');

        $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--version-policy' => 'latest',
        ]);
    }

    // An unknown --version-policy value throws.
    public function testInvalidPolicyThrows(): void
    {
        $this->versionFile('base', "\$plugin->version = 2022060100;\n");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be "latest" or "frozen"');

        $this->execute([
            'plugin'           => $this->plugin(2022060101),
            '--baseline-dir'   => $this->tempDir . '/base',
            '--version-policy' => 'bogus',
        ]);
    }
}
