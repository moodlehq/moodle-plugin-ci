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

use MoodlePluginCI\Command\ValidateCommand;
use MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ValidateCommandTest extends MoodleTestCase
{
    protected function executeCommand(): CommandTester
    {
        $command         = new ValidateCommand();
        $command->moodle = new DummyMoodle($this->moodleDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('validate'));
        $commandTester->execute([
            'plugin' => $this->pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute(): void
    {
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
