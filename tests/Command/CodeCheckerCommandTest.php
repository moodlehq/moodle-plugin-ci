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

    protected function executeCommand(?string $pluginDir = null, array $options = []): CommandTester
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command         = new CodeCheckerCommand();
        $command->plugin = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $options = array_merge(['plugin' => $pluginDir], $options);

        $commandTester = new CommandTester($application->find('codechecker'));
        $commandTester->execute($options);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();
        // Verify that the progress information is always printed, no matter there aren't warnings/errors.
        $this->assertMatchesRegularExpression('/\.{9} 9 \/ 9 \(100%\)/', $output);

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle CodeSniffer standard on local_ci/', $output);
    }

    public function testExecuteFail(): void
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
    abstract private function somefunc() { // To verify PHPCompatibility sniff.
        // Private & abstract forbidden since PHP 5.1.
    }
} // No EOL @ EOF on purpose to verify it's detected.
EOT;

        $this->fs->dumpFile($this->pluginDir . '/fixable.php', $content);

        $commandTester = $this->executeCommand($this->pluginDir);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Verify various parts of the output.
        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/E\.* 10\.* \/ 10 \(100%\)/', $output);                // Progress.
        $this->assertMatchesRegularExpression('/\/fixable.php/', $output);                            // File.
        $this->assertMatchesRegularExpression('/ 11 ERRORS AND 1 WARNING AFFECTING 8 /', $output);    // Summary.
        $this->assertMatchesRegularExpression('/BoilerplateComment\.NoBoilerplateComment/', $output); // Moodle sniff.
        $this->assertMatchesRegularExpression('/Expected MOODLE_INTERNAL check/', $output);           // Moodle sniff.
        $this->assertMatchesRegularExpression('/print_error\(\) has been deprecated/', $output);      // Moodle sniff.
        $this->assertMatchesRegularExpression('/Usage of ELSEIF not allowed; use ELSE IF/', $output); // Squiz sniff.
        $this->assertMatchesRegularExpression('/print_object\(\) is forbidden/', $output);            // Moodle sniff.
        $this->assertMatchesRegularExpression('/Missing docblock for class test/', $output);          // Moodle sniff.
        $this->assertMatchesRegularExpression('/Missing @copyright tag/', $output);                   // Moodle sniff.
        $this->assertMatchesRegularExpression('/Missing @license tag/', $output);                     // Moodle sniff.
        $this->assertMatchesRegularExpression('/Missing docblock for function somefunc/', $output);   // Moodle sniff.
        $this->assertMatchesRegularExpression('/AbstractPrivateMethods\.Found/', $output);    // PHPCompatibility sniff.
        $this->assertMatchesRegularExpression('/Opening brace must be the last content/', $output);   // Generic sniff.
        $this->assertMatchesRegularExpression('/Files\.EndFileNewline\.NotFound/', $output);          // Generic of file.
        $this->assertMatchesRegularExpression('/PHPCBF CAN FIX THE 4 MARKED SNIFF/', $output);        // PHPCBF note.
        $this->assertMatchesRegularExpression('/Time:.*Memory:/', $output);                           // Time.

        // Also verify display info is correct.
        $this->assertMatchesRegularExpression('/RUN  Moodle CodeSniffer/', $commandTester->getDisplay());
    }

    public function testExecuteWithWarningsAndThreshold(): void
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
        $commandTester = $this->executeCommand($this->pluginDir, ['--max-warnings' => 0]);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 1 warning, it fails.
        $commandTester = $this->executeCommand($this->pluginDir, ['--max-warnings' => 1]);
        $this->assertSame(1, $commandTester->getStatusCode());

        // Allowing 2 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, ['--max-warnings' => 2]);
        $this->assertSame(0, $commandTester->getStatusCode());

        // Allowing 3 warnings, it passes.
        $commandTester = $this->executeCommand($this->pluginDir, ['--max-warnings' => 3]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithTestVersion(): void
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
        $commandTester = $this->executeCommand($this->pluginDir);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 0 ERRORS AND 4 WARNINGS AFFECTING 4 LINES/', $output);

        // With test-version 7.4, reports 3 new errors and <= 7.4 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '7.4']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 3 ERRORS AND 1 WARNING AFFECTING 4 LINES/', $output);

        // With test-version 8.0, reports 2 new errors and <= 8.0 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '8.0']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 2 ERRORS AND 2 WARNINGS AFFECTING 4 LINES/', $output);

        // With test-version 8.1, reports 1 new errors and <= 8.1 specific warnings and returns 0.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '8.1']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 1 ERROR AND 3 WARNINGS AFFECTING 4 LINES/', $output);

        // With test-version 7.4-8.0, reports 3 new errors and <= 8.0 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '7.4-8.0']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 3 ERRORS AND 2 WARNINGS AFFECTING 5 LINES/', $output);

        // With test-version 7.4-8.1, reports 3 new errors and <= 8.1 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '7.4-8.1']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 3 ERRORS AND 3 WARNINGS AFFECTING 6 LINES/', $output);

        // With test-version 7.4- (open range), reports 3 new errors and <= 8.2 specific warnings and returns 1.
        $commandTester = $this->executeCommand($this->pluginDir, ['--test-version' => '7.4-']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 3 ERRORS AND 4 WARNINGS AFFECTING 7 LINES/', $output);
    }

    public function testExecuteWithExclusions(): void
    {
        // Add a file with errors and warnings, and verify that they are suppressed with the exclusions.
        $content = "<?php require(__DIR__.'/../../config.php');\n";

        $this->fs->dumpFile($this->pluginDir . '/warnings.php', $content);

        // Without exclusions.
        $commandTester = $this->executeCommand($this->pluginDir);
        $output        = $commandTester->getDisplay();
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND \d+ ERRORS? AND \d+ WARNINGS? AFFECTING 1 LINE/', $output);

        // With exclusions.
        $commandTester = $this->executeCommand(
            $this->pluginDir,
            ['--exclude' => 'moodle.Files.RequireLogin,' .
                'moodle.Files.BoilerplateComment,' .
                'moodle.Commenting.MissingDocblock,' .
                'moodle.Commenting.FileExpectedTags']
        );
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithTodoCommentRegex(): void
    {
        // Let's add a file with some comments having links and some without.
        $content = <<<'EOT'
            <?php
            // phpcs:disable moodle.Files.BoilerplateComment

            /**
             * This is a nice fixture file with some todo tags.
             *
             * @copyright 2024 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
             * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
             * @todo This is also the simplest, but within a phpdoc block
             * @todo This is also the simplest, but within a phpdoc block. CUSTOM-123
             */

            // TODO: This is the simplest TODO comment.

            // TODO: This is the simplest TODO comment. CUSTOM-123.

            EOT;

        $this->fs->dumpFile($this->pluginDir . '/test_comment_todos.php', $content);

        // Without any regex configured.
        $commandTester = $this->executeCommand($this->pluginDir);
        $output        = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/\.{10} 10 \/ 10 \(100%\)/', $output);
        $this->assertSame(0, $commandTester->getStatusCode());

        // With a "CUSTOM-[0-9]+" regex configured.
        // TODO: This will start requiring complete regexp soon, see https://github.com/moodlehq/moodle-cs/issues/141
        // (with "complete regex" meaning, with delimiters, basically).
        $commandTester = $this->executeCommand($this->pluginDir, ['--todo-comment-regex' => 'CUSTOM-[0-9]+']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 0 ERRORS AND 2 WARNINGS AFFECTING 2 LINES/', $output);
        $this->assertMatchesRegularExpression('/Missing required "CUSTOM-\[0-9\]\+"/', $output);
    }

    public function testExecuteWithLicenseRegex(): void
    {
        // Let's add a file with some comments having various licenses.
        $content = <<<'EOT'
            <?php
            // phpcs:disable moodle.Files.BoilerplateComment

            /**
             * This is a nice fixture file with some licenses.
             *
             * @copyright 2024 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
             * @license https://example.org/invented-license IL v3 or later
             */

            EOT;

        $this->fs->dumpFile($this->pluginDir . '/test_comment_licenses.php', $content);

        // Without any regex configured.
        $commandTester = $this->executeCommand($this->pluginDir);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/\.{10} 10 \/ 10 \(100%\)/', $output);

        // With a "~v[345]] or later~" regex configured.
        $commandTester = $this->executeCommand($this->pluginDir, ['--license-regex' => '~v[345] or later~']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/\.{10} 10 \/ 10 \(100%\)/', $output);

        // With a "GNU GPL" regex configured.
        $commandTester = $this->executeCommand($this->pluginDir, ['--license-regex' => '~GNU GPL~']);
        $output        = $commandTester->getDisplay();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/FOUND 0 ERRORS AND 1 WARNING AFFECTING 1 LINE/', $output);
        $this->assertMatchesRegularExpression('/Value ".+invented-license IL v3 or later" does not match/', $output);
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

    /**
     * Data provider for testCommandFailedSomethingIsWrong.
     *
     * @return array of options to use for the command, all them known to fail
     */
    public static function commandFailedSomethingIsWrongProvider(): array
    {
        return [
            'default' => [
                ['--standard' => 'chocolate', '--max-warnings' => -1],
                'ERROR: the "chocolate" coding standard is not installed',
            ],
            'zero' => [
                ['--standard' => 'chocolate', '--max-warnings' => 0],
                'ERROR: the "chocolate" coding standard is not installed',
            ],
            'positive' => [
                ['--standard' => 'chocolate', '--max-warnings' => 1],
                'ERROR: the "chocolate" coding standard is not installed',
            ],
        ];
    }

    /**
     * Verify that if anything goes wrong, the command fails, no matter the max-warnings.
     *
     * @param array  $options configuration to use for the command
     * @param string $output  Expected output (substring matching is enough)
     *
     * @dataProvider commandFailedSomethingIsWrongProvider
     */
    public function testCommandFailedSomethingIsWrong(array $options, string $output): void
    {
        // Verify that with incorrect configurations we are getting the command always failed.
        $commandTester = $this->executeCommand($this->pluginDir, $options);
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertStringContainsString($output, $commandTester->getDisplay());
    }
}
