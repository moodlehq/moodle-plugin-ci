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

namespace MoodlePluginCI\Tests\Bridge;

use MoodlePluginCI\Bridge\MoodleConfig;
use MoodlePluginCI\Installer\Database\MySQLDatabase;
use MoodlePluginCI\Tests\FilesystemTestCase;

class MoodleConfigTest extends FilesystemTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('MOODLE_BEHAT_CHROME_CAPABILITIES=');
        putenv('MOODLE_BEHAT_FIREFOX_CAPABILITIES=');
    }

    public function testCreateContents(): void
    {
        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');

        $this->assertSame(file_get_contents(__DIR__ . '/../Fixture/example-config.php'), $contents);
    }

    public function testConfigureChromeBrowserCapabilities(): void
    {
        $capabilities = <<<'END'
            MOODLE_BEHAT_CHROME_CAPABILITIES = [
                'extra_capabilities' => [
                    'chromeOptions' => [
                        'args' => [
                            '--ignore-certificate-errors',
                            '--allow-running-insecure-content'
                        ]
                    ]
                ]
            ]
            END;
        $capabilities = preg_replace('/\s+/', '', $capabilities); // Remove all spacing.
        putenv($capabilities);

        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');

        $this->assertSame(file_get_contents(__DIR__ . '/../Fixture/example-config-with-chrome-capabilities.php'), $contents);
    }

    public function testConfigureFirefoxBrowserCapabilities(): void
    {
        putenv("MOODLE_BEHAT_FIREFOX_CAPABILITIES=['extra_capabilities'=>['firefoxOptions'=>['args'=>['-headless']]]]");
        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');

        $this->assertSame(file_get_contents(__DIR__ . '/../Fixture/example-config-with-firefox-capabilities.php'), $contents);
    }

    public function testInjectLineIntoConfig(): void
    {
        $before = <<<'EOT'
// Random lines 1
// Custom configuration lines are added here:
// Extra config.
// Random lines 2
EOT;

        $expected = <<<'EOT'
// Random lines 1
// Custom configuration lines are added here:
New Line
// Extra config.
// Random lines 2
EOT;

        $config   = new MoodleConfig();
        $contents = $config->injectLine($before, 'New Line');
        $this->assertSame($expected, $contents);
    }

    public function testInjectLineIntoConfigMissingPlaceholder(): void
    {
        $this->expectException(\RuntimeException::class);
        $config = new MoodleConfig();
        $config->injectLine('Bad param', 'New Line');
    }

    public function testRead(): void
    {
        $this->dumpFile('test.txt', 'Test');

        $config   = new MoodleConfig();
        $contents = $config->read($this->tempDir . '/test.txt');

        $this->assertSame('Test', $contents);
    }

    public function testReadFileNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new MoodleConfig();
        $config->read($this->tempDir . '/test.txt');
    }

    public function testReadFail(): void
    {
        $this->expectException(\RuntimeException::class);

        $tempFile = $this->dumpFile('test.txt', 'Test');
        $this->fs->chmod($tempFile, 0222);

        $config = new MoodleConfig();
        $config->read($tempFile);
    }

    public function testDump(): void
    {
        $config   = new MoodleConfig();
        $contents = $config->createContents(new MySQLDatabase(), '/path/to/moodledata');
        $config->dump($this->tempDir . '/config.php', $contents);

        $this->assertFileEquals(__DIR__ . '/../Fixture/example-config.php', $this->tempDir . '/config.php');
    }
}
