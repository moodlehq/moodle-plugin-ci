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

use MoodlePluginCI\Command\CodeCheckerCommand;
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
class CodeCheckerCommandTest extends MoodleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));
    }

    protected function executeCommand($pluginDir = null)
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command         = new CodeCheckerCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('codechecker'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        // Verify that the progress information is always printed, no matter there aren't warnings/errors.
        $this->expectOutputRegex('/\.{7} 7 \/ 7 \(100%\)/');
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteFail()
    {
        // Add a file known to have moodle errors.
        $content = <<<'EOT'
<?php

if (true) {
    $var = ldap_sort();  // To verify both PHPCompatibility and own moodle deprecations sniff.
} elseif (false) {
    $var = print_object(); // To verify moodle own sniff.

} // No EOL @ EOF on purpose to verify it's detected.
EOT;
        $this->fs->dumpFile($this->pluginDir.'/fixable.php', $content);

        $this->expectOutputRegex('/\.+/'); // Trick to avoid output, real assertions below.
        $commandTester = $this->executeCommand($this->pluginDir);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $this->getActualOutput();
        $this->assertRegExp('/E\.* 8\.* \/ 8 \(100%\)/', $output);                  // Progress.
        $this->assertRegExp('/\/fixable.php/', $output);                            // File.
        $this->assertRegExp('/ (4|5) ERRORS AND 2 WARNINGS AFFECTING 6 /', $output); // Summary (php70 shows one less)
        $this->assertRegexp('/moodle\.Files\.BoilerplateComment\.Wrong/', $output); // Moodle sniff.
        $this->assertRegexp('/print_object\(\) is forbidden/', $output);            // Moodle sniff.
        $this->assertRegexp('/FunctionUse\.RemovedFunctions\.ldap_sort/', $output); // PHPCompatibility sniff.
        $this->assertRegExp('/Time:.*Memory:/', $output);                           // Time.

        // Also verify display info is correct.
        $this->assertRegExp('/RUN  Moodle Code Checker/', $commandTester->getDisplay());
    }

    public function testExecuteNoFiles()
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir.'/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertRegExp('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('/path/to/no/plugin');
    }
}
