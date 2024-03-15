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

use MoodlePluginCI\Command\GruntCommand;
use MoodlePluginCI\Model\GruntTaskModel;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

class GruntCommandTest extends MoodleTestCase
{
    protected function executeCommand(): CommandTester
    {
        $command            = new GruntCommand();
        $command->moodle    = new DummyMoodle($this->moodleDir);
        $command->execute   = new DummyExecute();
        $command->backupDir = $this->tempDir . '/backup';

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('grunt'));
        $commandTester->execute([
            'plugin'   => $this->pluginDir,
            '--moodle' => $this->moodleDir,
            '--tasks'  => ['css'],
        ]);

        return $commandTester;
    }

    protected function newCommand(): GruntCommand
    {
        $command            = new GruntCommand();
        $command->moodle    = new DummyMoodle($this->moodleDir);
        $command->plugin    = new DummyMoodlePlugin($this->pluginDir);
        $command->backupDir = $this->tempDir . '/backup';

        return $command;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testToGruntTaskWithAMD(): void
    {
        $command = $this->newCommand();

        $task = $command->toGruntTask('amd');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('amd', $task->taskName);
        $this->assertSame('amd/build', $task->buildDirectory);
        $this->assertSame($this->pluginDir . '/amd', $task->workingDirectory);

        $this->fs->remove($this->pluginDir . '/amd');

        $this->assertNull($command->toGruntTask('amd'));
    }

    public function testToGruntTaskWithYUI(): void
    {
        $command = $this->newCommand();

        $task = $command->toGruntTask('yui');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('yui', $task->taskName);
        $this->assertSame('yui/build', $task->buildDirectory);
        $this->assertSame($this->pluginDir . '/yui/src', $task->workingDirectory);

        $task = $command->toGruntTask('shifter');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('shifter', $task->taskName);
        $this->assertSame('yui/build', $task->buildDirectory);
        $this->assertSame($this->pluginDir . '/yui/src', $task->workingDirectory);

        $this->fs->remove($this->pluginDir . '/yui');

        $this->assertNull($command->toGruntTask('yui'));
        $this->assertNull($command->toGruntTask('shifter'));
    }

    public function testToGruntTaskWithLegacyYUI(): void
    {
        $command = $this->newCommand();
        $this->fs->remove($this->pluginDir . '/yui/src');
        $this->fs->touch($this->pluginDir . '/yui/examplejs');

        $this->assertNull($command->toGruntTask('yui'));
        $this->assertNull($command->toGruntTask('shifter'));
    }

    public function testToGruntTaskWithGherkin(): void
    {
        $command = $this->newCommand();

        $task = $command->toGruntTask('gherkinlint');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('gherkinlint', $task->taskName);
        $this->assertSame('', $task->buildDirectory);
        $this->assertSame($this->moodleDir, $task->workingDirectory);

        /** @var DummyMoodle $moodle */
        $moodle         = $command->moodle;
        $moodle->branch = 30;

        $this->assertNull($command->toGruntTask('gherkinlint'));

        $moodle->branch = 33;

        $this->assertInstanceOf(GruntTaskModel::class, $command->toGruntTask('gherkinlint'), 'Should work again');

        $this->fs->remove($this->pluginDir . '/tests/behat');

        $this->assertNull($command->toGruntTask('gherkinlint'));
    }

    public function testToGruntTaskWithStyles(): void
    {
        $command = $this->newCommand();

        $task = $command->toGruntTask('stylelint:css');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('stylelint:css', $task->taskName);
        $this->assertSame('', $task->buildDirectory);
        $this->assertSame($this->moodleDir, $task->workingDirectory);

        $this->fs->remove($this->pluginDir . '/styles.css');

        $this->assertNull($command->toGruntTask('stylelint:css'));
        $this->assertNull($command->toGruntTask('stylelint:less'));
        $this->assertNull($command->toGruntTask('stylelint:scss'));
    }

    public function testToGruntTaskDefaultTask(): void
    {
        $command = $this->newCommand();

        $task = $command->toGruntTask('foo');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('foo', $task->taskName);
        $this->assertSame('', $task->buildDirectory);
        $this->assertSame($this->moodleDir, $task->workingDirectory);

        $this->fs->touch($this->pluginDir . '/Gruntfile.js');

        $task = $command->toGruntTask('foo');
        $this->assertInstanceOf(GruntTaskModel::class, $task);
        $this->assertSame('foo', $task->taskName);
        $this->assertSame('', $task->buildDirectory);
        $this->assertSame($this->pluginDir, $task->workingDirectory);
    }

    public function testValidatePluginFiles(): void
    {
        $command = $this->newCommand();
        $command->backupPlugin();

        $emptyOutput = new BufferedOutput();

        $this->assertSame(0, $command->validatePluginFiles($emptyOutput));
        $this->assertSame('', $emptyOutput->fetch());

        $this->fs->dumpFile($this->pluginDir . '/styles.css', 'changed');

        $output = new BufferedOutput();
        $this->assertSame(1, $command->validatePluginFiles($output));
        $this->assertSame("File is stale and needs to be rebuilt: styles.css\n", $output->fetch());

        $command->restorePlugin();
        $this->assertSame(0, $command->validatePluginFiles($emptyOutput));
        $this->assertSame('', $emptyOutput->fetch());

        $this->fs->remove($this->pluginDir . '/amd/build/keys.min.js');

        $output = new BufferedOutput();
        $this->assertSame(1, $command->validatePluginFiles($output));
        $this->assertSame("File no longer generated and likely should be deleted: amd/build/keys.min.js\n", $output->fetch());

        $command->restorePlugin();
        $this->assertSame(0, $command->validatePluginFiles($emptyOutput));
        $this->assertSame('', $emptyOutput->fetch());

        $this->fs->touch($this->pluginDir . '/amd/build/new.min.js');

        $output = new BufferedOutput();
        $this->assertSame(1, $command->validatePluginFiles($output));
        $this->assertSame("File is newly generated and needs to be added: amd/build/new.min.js\n", $output->fetch());

        $command->restorePlugin();
        $this->assertSame(0, $command->validatePluginFiles($emptyOutput));
        $this->assertSame('', $emptyOutput->fetch());

        $this->fs->remove($this->pluginDir . '/amd/build/keys.min.js.map');

        $output = new BufferedOutput();
        $this->assertSame(1, $command->validatePluginFiles($output));
        $this->assertSame("File no longer generated and likely should be deleted: amd/build/keys.min.js.map\n", $output->fetch());

        $command->restorePlugin();
        $this->assertSame(0, $command->validatePluginFiles($emptyOutput));
        $this->assertSame('', $emptyOutput->fetch());

        $this->fs->touch($this->pluginDir . '/amd/build/new.min.js.map');

        $output = new BufferedOutput();
        $this->assertSame(1, $command->validatePluginFiles($output));
        $this->assertSame("File is newly generated and needs to be added: amd/build/new.min.js.map\n", $output->fetch());
    }
}
