<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Process;

use Moodlerooms\MoodlePluginCI\Process\Execute;
use Moodlerooms\MoodlePluginCI\Process\MoodleProcess;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ExecuteTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));

        $execute = new Execute(new NullOutput(), $helper);
        $process = $execute->run('php -r "echo 42;"');

        $this->assertInstanceOf('Symfony\Component\Process\Process', $process);
        $this->assertTrue($process->isSuccessful());
    }

    public function testMustRun()
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));

        $execute = new Execute(new NullOutput(), $helper);
        $process = $execute->mustRun('php -r "echo 42;"');

        $this->assertInstanceOf('Symfony\Component\Process\Process', $process);
        $this->assertTrue($process->isSuccessful());
    }

    public function testRunAllVerbose()
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));

        /** @var Process[] $processes */
        $processes = [
            new Process('php -r "echo 42;"'),
            new Process('php -r "echo 42;"'),
        ];

        $output  = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $execute = new Execute($output, $helper);
        $execute->runAll($processes);

        foreach ($processes as $process) {
            $this->assertTrue($process->isSuccessful());
        }
        $this->assertNotEmpty($output->fetch());
    }

    public function testMustRunAll()
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));

        /** @var Process[] $processes */
        $processes = [
            new Process('php -r "echo 42;"'),
            new MoodleProcess('-r "echo 42;"'),
            new Process('php -r "echo 42;"'),
        ];

        $execute = new Execute(new NullOutput(), $helper);
        $execute->mustRunAll($processes);

        foreach ($processes as $process) {
            $this->assertTrue($process->isSuccessful());
        }
    }

    /**
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function testMustRunAllFail()
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([new DebugFormatterHelper()]));

        /** @var Process[] $processes */
        $processes = [
            new Process('php -r "echo 42;"'),
            new Process('php -r "syntax error"'),
            new Process('php -r "echo 42;"'),
        ];

        $execute = new Execute(new NullOutput(), $helper);
        $execute->mustRunAll($processes);
    }

    public function testPassThrough()
    {
        $output  = new BufferedOutput(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $execute = new Execute($output, new ProcessHelper());
        $process = $execute->passThrough('php -r "echo 42;"');

        $this->assertInstanceOf('Symfony\Component\Process\Process', $process);
        $this->assertEquals(' RUN  php -r "echo 42;"'.PHP_EOL.'42', $output->fetch());
    }
}
