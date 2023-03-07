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

class CodeCheckerCommandTest extends MoodleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir . '/.moodle-plugin-ci.yml', Yaml::dump($config));
    }

    protected function executeCommand($pluginDir = null, $maxWarnings = -1, $testVersion = null): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command         = new CodeCheckerCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $options = ['plugin' => $pluginDir];
        if ($maxWarnings >= 0) {
            $options['--max-warnings'] = $maxWarnings;
        }

        if (null !== $testVersion) {
            $options['--test-version'] = $testVersion;
        }

        $commandTester = new CommandTester($application->find('codechecker'));
        $commandTester->execute($options);

        return $commandTester;
    }

    public function testExecute()
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();
        // Verify that the progress information is always printed, no matter there aren't warnings/errors.
        $this->assertMatchesRegularExpression('/\.{7} 7 \/ 7 \(100%\)/', $output);

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle CodeSniffer standard on local_ci/', $output);
    }

    public function testExecuteFail()
    {
        // Add a file known to have moodle errors.
        $content = <<<'EOT'
<?php

if (true) {
    $var = print_error();  // To verify various Moodle sniffs.
} elseif (false) {
    $var = print_object();
}

class test {
    public function test() { // To verify PHPCompatibility sniff.
        // Old PHP 4.x constructor.
    }
} // No EOL @ EOF on purpose to verify it's detected.
EOT;

        $this->fs->dumpFile($this->pluginDir . '/fixable.php', $content);

        $commandTester = $this->executeCommand($this->pluginDir);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/E\.* 8\.* \/ 8 \(100%\)/', $output);                  // Progress.
        $this->assertMatchesRegularExpression('/\/fixable.php/', $output);                            // File.
        $this->assertMatchesRegularExpression('/ 8 ERRORS AND 1 WARNING AFFECTING 7 /', $output);     // Summary.
        $this->assertMatchesRegularExpression('/moodle\.Files\.BoilerplateComment\.Wrong/', $output); // Moodle sniff.
        $this->assertMatchesRegularExpression('/print_error\(\) has been deprecated/', $output);      // Moodle sniff.
        $this->assertMatchesRegularExpression('/print_object\(\) is forbidden/', $output);            // Moodle sniff.
        $this->assertMatchesRegularExpression('/RemovedPHP4StyleConstructors\.Removed/', $output);    // PHPCompatibility sniff.
        $this->assertMatchesRegularExpression('/Files\.EndFileNewline\.NotFound/', $output);          // End of file.
        $this->assertMatchesRegularExpression('/PHPCBF CAN FIX THE 3 MARKED SNIFF/', $output);        // PHPCBF note.
        $this->assertMatchesRegularExpression('/Time:.*Memory:/', $output);                           // Time.

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle CodeSniffer/', $commandTester->getDisplay());
    }

    public function testExecuteWithWarningsAndThreshold()
    {
        // Let's add a file with 2 warnings, and verify how the max-warnings affects the outcome.
        $content = <<<'EOT'
<?php // phpcs:disable moodle.Files
print_error();
print_error();

EOT;

        $this->fs->dumpFile($this->pluginDir . '/warnings.php', $content);

        // By default it passes.
        $commandTester = $this->executeCommand($this->pluginDir);
        $this->assertSame(0, $commandTester->getStatusCode());

        // Allowing 0 warning, it fails.
        $commandTester = $this->executeCommand($this->pluginDir, 0);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 1 warning, it fails.
        $commandTester = $this->executeCommand($this->pluginDir, 1);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 2 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, 2);
        $this->assertSame(0, $commandTester->getStatusCode());

        // Allowing 3 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, 3);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithTestVersion()
    {
        // Let's add a file with some new and deprecated stuff, and verify that the test-version option affects to the outcome.
        $content = <<<'EOT'
<?php // phpcs:disable moodle
mb_str_split();                       // New in PHP 7.4.
ini_get('allow_url_include');         // Deprecated in PHP 7.4.
ldap_count_references();              // New in PHP 8.0.
pg_errormessage();                    // Deprecated in PHP 8.0.
$fb = new ReflectionFiber();          // New in PHP 8.1.
ini_get('auto_detect_line_endings');  // Deprecated in PHP 8.1.
openssl_cipher_key_length();          // New in PHP 8.2.
utf8_encode();                        // Deprecated in PHP 8.2.

EOT;
        $this->fs->dumpFile($this->pluginDir . '/test_versions.php', $content);

        // By default, without specify test-version, only reports deprecation warnings and returns 0.
        $commandTester = $this->executeCommand($this->pluginDir, -1, null);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 0 ERRORS AND 3 WARNINGS AFFECTING 3 LINES/', $output);

        // With test-version 7.4, reports 2 new errors and <= 7.4 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, -1, '7.4');
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 2 ERRORS AND 1 WARNING AFFECTING 3 LINES/', $output);

        // With test-version 8.0, reports 1 new errors and <= 8.0 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, -1, '8.0');
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 1 ERROR AND 2 WARNINGS AFFECTING 3 LINES/', $output);

        // With test-version 8.1, reports 0 new errors and <= 8.1 specific warnings and returns 0.
        $commandTester = $this->executeCommand($this->pluginDir, -1, '8.1');
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 0 ERRORS AND 3 WARNINGS AFFECTING 3 LINES/', $output);

        // With test-version 7.4-8.0, reports 2 new errors and <= 8.0 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, -1, '7.4-8.0');
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 2 ERRORS AND 2 WARNINGS AFFECTING 4 LINES/', $output);

        // With test-version 7.4-8.1, reports 2 new errors and <= 8.1 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, -1, '7.4-8.1');
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 2 ERRORS AND 3 WARNINGS AFFECTING 5 LINES/', $output);

        // With test-version 7.4- (open range), reports 2 new errors and <= 8.2 specific warnings and returns 1.
        // (note that it should be 1 more warning and 1 more error, but the PHPCompatibility sniffs are not
        // still ready for many of the PHP 8.2 changes. We'll amend this test when they are ready).
        $commandTester = $this->executeCommand($this->pluginDir, -1, '7.4-');
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 2 ERRORS AND 3 WARNINGS AFFECTING 5 LINES/', $output);
    }

    public function testExecuteNoFiles()
    {
        // Just random directory with no PHP files.
        $commandTester = $this->executeCommand($this->pluginDir . '/tests/behat');
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/No relevant files found to process, free pass!/', $commandTester->getDisplay());
    }

    public function testExecuteNoPlugin()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->executeCommand('/path/to/no/plugin');
    }
}
