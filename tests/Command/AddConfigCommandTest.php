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

use MoodlePluginCI\Command\AddConfigCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddConfigCommandTest extends FilesystemTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs->copy(__DIR__ . '/../Fixture/example-config.php', $this->tempDir . '/config.php');
    }

    protected function executeCommand(string $line = '$CFG->foo = "bar";'): CommandTester
    {
        $command         = new AddConfigCommand();
        $command->moodle = new DummyMoodle($this->tempDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('add-config'));
        $commandTester->execute([
            '--moodle' => $this->tempDir . '/moodle',
            'line'     => $line,
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/\$CFG->foo = "bar";\n/', file_get_contents($this->tempDir . '/config.php'));
    }

    public function testExecuteSyntaxError(): void
    {
        $commandTester = $this->executeCommand('$CFG->foo = "bar"');
        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertMatchesRegularExpression('/Syntax error found in 1 file/', $commandTester->getDisplay());
    }
}
