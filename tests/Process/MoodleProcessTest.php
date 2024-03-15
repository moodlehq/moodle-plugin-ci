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

use MoodlePluginCI\Process\MoodleDebugException;
use MoodlePluginCI\Process\MoodlePhpException;
use MoodlePluginCI\Process\MoodleProcess;

class MoodleProcessTest extends \PHPUnit\Framework\TestCase
{
    private string $outputWithDebugging;
    private string $outputWithoutDebugging;

    protected function setUp(): void
    {
        // Example output from installing Moodle with debugging message.
        $this->outputWithDebugging = '-->System\n' .
            '++ Success ++\n' .
            '-->availability_completion\n' .
            '++ Success ++\n' .
            '-->availability_date\n' .
            '++ Success ++\n' .
            '++ Test debugging messages ++\n' .
            '* line 27 of /local/travis/version.php: call to debugging()\n' .
            '* line 448 of /lib/upgradelib.php: call to require()\n' .
            '* line 1630 of /lib/upgradelib.php: call to upgrade_plugins()\n' .
            '* line 486 of /lib/installlib.php: call to upgrade_noncore()\n' .
            '* line 443 of /lib/phpunit/classes/util.php: call to install_cli_database()\n' .
            '* line 150 of /admin/tool/phpunit/cli/util.php: call to phpunit_util::install_site()\n' .
            '-->availability_grade\n' .
            '++ Success ++\n' .
            '-->availability_group\n';

        // Example output from installing Moodle.
        $this->outputWithoutDebugging = '-->System\n' .
            '++ Success ++\n' .
            '-->availability_completion\n' .
            '++ Success ++\n' .
            '-->availability_date\n' .
            '++ Success ++\n' .
            '-->availability_grade\n' .
            '++ Success ++\n' .
            '-->availability_group\n';
    }

    public function testDetectDebuggingMessages(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo "' . $this->outputWithoutDebugging . '";',
        ]);
        $process->run();
        $this->assertFalse($process->hasDebuggingMessages($process->getOutput()));

        $process = new MoodleProcess([
            '-r',
            'echo "' . $this->outputWithDebugging . '";',
        ]);
        $process->run();
        $this->assertTrue($process->hasDebuggingMessages($process->getOutput()));
    }

    public function testHasPhpErrorMessages(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo $foo[\'bar\'];',
        ]);
        $process->run();
        $this->assertTrue($process->hasPhpErrorMessages($process->getErrorOutput()));

        $process = new MoodleProcess([
            '-r',
            'echo 42;',
        ]);
        $process->run();
        $this->assertFalse($process->hasPhpErrorMessages($process->getErrorOutput()));
    }

    public function testIsSuccessful(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo 42;',
        ]);
        $process->run();
        $this->assertTrue($process->isSuccessful());

        $process = new MoodleProcess([
            '-r',
            'echo $foo[\'bar\'];',
        ]);
        $process->run();
        $this->assertFalse($process->isSuccessful());
    }

    public function testMustRun(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo 42;',
        ]);
        $process->mustRun();
        $this->assertTrue($process->isSuccessful());
    }

    public function testMustRunError(): void
    {
        $this->expectException(MoodlePhpException::class);
        $process = new MoodleProcess([
            '-r',
            'echo $foo[\'bar\'];',
        ]);
        $process->mustRun();
    }

    public function testCheckOutputForProblemsNotStarted(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo 42;',
        ]);

        try {
            $process->checkOutputForProblems();
            $this->fail('The checkOutputForProblems should have thrown a LogicException');
        } catch (\LogicException $e) {
            $this->assertMatchesRegularExpression('/started/', $e->getMessage());
        }
    }

    public function testCheckOutputForProblemsOutputDisabled(): void
    {
        $process = new MoodleProcess([
            '-r',
            'echo 42;',
        ]);
        $process->run();
        $process->disableOutput();

        try {
            $process->checkOutputForProblems();
            $this->fail('The checkOutputForProblems should have thrown a LogicException');
        } catch (\LogicException $e) {
            $this->assertMatchesRegularExpression('/disabled/', $e->getMessage());
        }
    }

    public function testCheckOutputForProblemsPhpError(): void
    {
        $this->expectException(MoodlePhpException::class);
        $process = new MoodleProcess([
            '-r',
            'echo $foo[\'bar\'];',
        ]);
        $process->run();
        $process->checkOutputForProblems();
    }

    public function testCheckOutputForProblemsDebuggingMessage(): void
    {
        $this->expectException(MoodleDebugException::class);
        $process = new MoodleProcess([
            '-r',
            'echo "' . $this->outputWithDebugging . '";',
        ]);
        $process->run();
        $process->checkOutputForProblems();
    }
}
