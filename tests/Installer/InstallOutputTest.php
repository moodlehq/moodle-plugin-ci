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

namespace MoodlePluginCI\Tests\Installer;

use MoodlePluginCI\Installer\InstallOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class InstallOutputTest extends \PHPUnit\Framework\TestCase
{
    public function testProgressBar(): void
    {
        $progressBar = new ProgressBar(new NullOutput());
        $output      = new InstallOutput(null, $progressBar);

        $output->start('Start', 5);
        $this->assertSame(5, $progressBar->getMaxSteps());
        $this->assertSame('Start', $progressBar->getMessage('message'));

        $output->step('Step 1');
        $this->assertSame(1, $progressBar->getProgress());
        $this->assertSame('Step 1', $progressBar->getMessage('message'));

        $output->end('End');
        /**
         * This seems to be a real bug in Psalm: https://github.com/vimeo/psalm/issues/7669#issuecomment-1986896033.
         *
         * @psalm-suppress DocblockTypeContradiction
         */
        $this->assertSame(5, $progressBar->getProgress());
        $this->assertSame('End', $progressBar->getMessage('message'));
    }

    public function testLogInfo(): void
    {
        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $installOutput  = new InstallOutput(new ConsoleLogger($bufferedOutput));

        $installOutput->info('Testing log');
        $this->assertSame('[info] Testing log' . PHP_EOL, $bufferedOutput->fetch());
    }

    public function testQuietLogInfo(): void
    {
        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL);
        $installOutput  = new InstallOutput(new ConsoleLogger($bufferedOutput));

        $installOutput->info('Testing log');
        $this->assertEmpty($bufferedOutput->fetch());
    }

    public function testLogDebug(): void
    {
        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_DEBUG);
        $installOutput  = new InstallOutput(new ConsoleLogger($bufferedOutput));

        $installOutput->debug('Testing log');
        $this->assertSame('[debug] Testing log' . PHP_EOL, $bufferedOutput->fetch());
    }

    public function testQuietLogDebug(): void
    {
        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $installOutput  = new InstallOutput(new ConsoleLogger($bufferedOutput));

        $installOutput->debug('Testing log');
        $this->assertEmpty($bufferedOutput->fetch());
    }
}
