<?php

// This file is part of the Moodle Plugin CI package.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\PHPDocCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePHPDoc;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit tests for PHPDocCommand.
 *
 * @copyright  2023 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PHPDocCommandTest extends MoodleTestCase
{
    protected function executeCommand(?string $pluginDir = null, int $maxWarnings = -1, string $mockOutput = ''): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command                        = new PHPDocCommand();
        $command->moodle                = new DummyMoodlePHPDoc($this->moodleDir);
        $command->plugin                = new DummyMoodlePlugin($pluginDir);
        $command->execute               = new DummyExecute(); // Mocked execution.
        $command->execute->returnOutput = $mockOutput;        // Mocked output.

        // Note: we leave this here as reference for any other command test needing a config.php file,
        // but it's not needed for this unit tess that is using a mocked execute() method.
        // Create a basic config.php file. This command requires it.
        // $config         = new MoodleConfig();
        // $configContents = $config->createContents(new MySQLDatabase(), '/path/to/moodle-data');
        // $config->dump($this->moodleDir . DIRECTORY_SEPARATOR . 'config.php', $configContents);

        $application = new Application();
        $application->add($command);

        $options = ['plugin' => $pluginDir];
        if ($maxWarnings >= 0) {
            $options['--max-warnings'] = $maxWarnings;
        }

        $commandTester = new CommandTester($application->find('phpdoc'));
        $commandTester->execute($options);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle PHPDoc Checker on local_ci/', $output);
    }

    public function testExecuteFail(): void
    {
        $mockOutput    = '  Line 12: Some error happened';
        $commandTester = $this->executeCommand($this->pluginDir, -1, $mockOutput);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Verify various parts of the output.
        // Note: Because this is a mocked process, with mocked output, we cannot inspect the command output
        // as we do in other tests. Only the title is available.
        // Some day all the mocks should be refactored to allow this.
        // $output = $commandTester->getDisplay();
        // $this->assertMatchesRegularExpression('/something/', $output);                  // Progress.

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle PHPDoc Checker/', $commandTester->getDisplay());
    }

    public function testExecuteWithWarningsAndThreshold(): void
    {
        // Let's add a file with 2 warnings, and verify how the max-warnings affects the outcome.
        $mockOutput = <<<'EOT'
    Line 2: Empty line found after PHP open tag (warning)
    Line 12: Function someclass::somefunc is not documented (warning)

EOT;
        // By default it passes.
        $commandTester = $this->executeCommand($this->pluginDir, -1, $mockOutput);
        $this->assertSame(0, $commandTester->getStatusCode());

        // Allowing 0 warning, it fails.
        $commandTester = $this->executeCommand($this->pluginDir, 0, $mockOutput);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 1 warning, it fails.
        $commandTester = $this->executeCommand($this->pluginDir, 1, $mockOutput);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 2 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, 2, $mockOutput);
        $this->assertSame(0, $commandTester->getStatusCode());

        // Allowing 3 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, 3, $mockOutput);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteNoFiles(): void
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('/path/to/no/plugin');
    }
}
