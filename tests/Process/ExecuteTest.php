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

namespace MoodlePluginCI\Tests\Process;

use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Process\MoodleProcess;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ExecuteTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        // Define RUNTIME_NVM_BIN, so we check its value is added to PATH within
        // process.
        putenv('RUNTIME_NVM_BIN=/test/bin');
    }

    public function testSetNodeEnv(): void
    {
        $execute = new Execute();
        $pathenv = getenv('PATH') ?: '';

        // RUNTIME_NVM_BIN in undefined.
        putenv('RUNTIME_NVM_BIN');
        $process = new Process(['env']);
        $process = $execute->setNodeEnv($process);
        // We do not expect env set for process.
        $this->assertEmpty($process->getEnv());
        $process->run();
        $this->assertTrue($process->isSuccessful());
        // Expect path to match system one.
        $this->assertStringContainsString('PATH=' . $pathenv, $process->getOutput());
        // Expect HOME is defined (system env vars present)
        $this->assertMatchesRegularExpression('/^HOME=/m', $process->getOutput());

        // RUNTIME_NVM_BIN is defined.
        putenv('RUNTIME_NVM_BIN=/test/bin');
        $process = $execute->setNodeEnv($process);
        // Expect env is set for process.
        $this->assertArrayHasKey('PATH', $process->getEnv());
        $this->assertSame('/test/bin:' . $pathenv, $process->getEnv()['PATH']);
        $process->run();
        $this->assertTrue($process->isSuccessful());
        // RUNTIME_NVM_BIN is defined, expect it to be first item in the PATH .
        $this->assertStringContainsString('PATH=/test/bin:' . $pathenv, $process->getOutput());
        // Expect HOME is defined too (system env vars present)
        $this->assertMatchesRegularExpression('/^HOME=/m', $process->getOutput());
    }

    public function testRun(): void
    {
        $execute = new Execute();
        $process = $execute->run(['env']);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertTrue($process->isSuccessful());
        // RUNTIME_NVM_BIN is defined, expect it in the PATH.
        $this->assertMatchesRegularExpression('/^PATH=\/test\/bin:/m', $process->getOutput());
    }

    public function testMustRun(): void
    {
        $execute = new Execute();
        $process = $execute->mustRun(['env']);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertTrue($process->isSuccessful());
        // RUNTIME_NVM_BIN is defined, expect it in the PATH.
        $this->assertMatchesRegularExpression('/^PATH=\/test\/bin:/m', $process->getOutput());
    }

    public function testRunAllVerbose(): void
    {
        $processes = [
            new Process(['env']),
            new Process(['env']),
        ];

        $output  = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $execute = new Execute($output);
        $execute->runAll($processes);

        foreach ($processes as $process) {
            $this->assertTrue($process->isSuccessful());
            // RUNTIME_NVM_BIN is defined, expect it in the PATH.
            $this->assertMatchesRegularExpression('/^PATH=\/test\/bin:/m', $process->getOutput());
        }
        $this->assertNotEmpty($output->fetch());
    }

    public function testMustRunAll(): void
    {
        /** @var Process[] $processes */
        $processes = [
            new Process(['env']),
            new MoodleProcess([
                '-r',
                'echo \'PATH=\'.getenv(\'PATH\');',
            ]),
            new Process(['env']),
        ];

        $execute = new Execute();

        $execute->parallelWaitTime = 1;
        $execute->mustRunAll($processes);

        foreach ($processes as $process) {
            $this->assertTrue($process->isSuccessful());
            // RUNTIME_NVM_BIN is defined, expect it in the PATH.
            $this->assertMatchesRegularExpression('/^PATH=\/test\/bin:/m', $process->getOutput());
        }
    }

    public function testMustRunAllFail(): void
    {
        $this->expectException(ProcessFailedException::class);

        $processes = [
            new Process(['php', '-r', 'echo 42;']),
            new Process(['php', '-r', 'syntax wrong_code_error_ignore_me']), // This may appear in logs, ignore it!
            new Process(['php', '-r', 'echo 42;']),
        ];

        $execute                   = new Execute();
        $execute->parallelWaitTime = 1;
        $execute->mustRunAll($processes);
    }

    public function testPassThrough(): void
    {
        $output  = new BufferedOutput(BufferedOutput::VERBOSITY_VERY_VERBOSE);
        $execute = new Execute($output);
        $process = $execute->passThrough(['php', '-r', 'echo 42;']);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame(" RUN  'php' '-r' 'echo 42;'" . PHP_EOL . '42', $output->fetch());

        $process = $execute->passThrough(['env']);
        $this->assertInstanceOf(Process::class, $process);
        // RUNTIME_NVM_BIN is defined, expect it in the PATH.
        $this->assertMatchesRegularExpression('/^PATH=\/test\/bin:/m', $output->fetch());
    }
}
