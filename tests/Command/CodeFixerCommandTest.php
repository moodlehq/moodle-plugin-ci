<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\CodeFixerCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

// Needed to allow caller CLI arguments (phpunit) to be accepted.
if (defined('PHP_CODESNIFFER_IN_TESTS') === false) {
    define('PHP_CODESNIFFER_IN_TESTS', true);
}

/**
 * @runTestsInSeparateProcesses There are some statics around (Timing...), so separate process.
 */
class CodeFixerCommandTest extends MoodleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $content = <<<'EOT'
<?php

if (true) {

} elseif (false) {

}

EOT;

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($config));
        $this->fs->dumpFile($this->pluginDir . '/fixable.php', $content);
    }

    protected function executeCommand(?string $pluginDir = null): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command          = new CodeFixerCommand();
        $command->plugin  = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phpcbf'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/F\.* 10\.* \/ 10 \(100%\)/', $output);                // Progress.
        $this->assertMatchesRegularExpression('/\/fixable.php/', $output);                            // File.
        $this->assertMatchesRegularExpression('/A TOTAL OF 1 ERROR WERE FIXED IN 1 FILE/', $output);  // Summary.
        $this->assertMatchesRegularExpression('/Time:.*Memory:/', $output);                           // Time.

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Code Beautifier and Fixer/', $commandTester->getDisplay());

        $expected = <<<'EOT'
<?php

if (true) {

} else if (false) {

}

EOT;
        $this->assertSame($expected, file_get_contents($this->pluginDir . '/fixable.php'));
    }

    public function testExecuteNoFiles(): void
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());

        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }
}
