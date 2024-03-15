<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2023 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\SelfUpdateCommand;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests for the SelfUpdateCommand class.
 *
 * There isn't much to test here, only helper methods. Note that the utility itself
 * will be covered by some integration tests @ CIs.
 */
class SelfUpdateCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \MoodlePluginCI\Command\SelfUpdateCommand::getBackupPath
     */
    public function testGetBackupPathNotExists(): void
    {
        $command = new SelfUpdateCommand();

        // Try with a non-existing directory.
        $rollBackDir  = sys_get_temp_dir() . '/not_existing_dir';
        $rollBackFile = $rollBackDir . '/.moodle-plugin-ci/moodle-plugin-ci-old.phar';

        $this->expectException(\RuntimeException::class);

        // Use reflection to test the protected method.
        $method = new \ReflectionMethod($command, 'getBackupPath');
        $this->assertSame($rollBackFile, $method->invoke($command, $rollBackDir));
    }

    /**
     * @covers \MoodlePluginCI\Command\SelfUpdateCommand::getBackupPath
     */
    public function testGetBackupPathExists(): void
    {
        $command = new SelfUpdateCommand();

        // Try with an existing directory.
        $rollBackDir = sys_get_temp_dir() . '/existing_dir';
        (new Filesystem())->mkdir($rollBackDir); // Let's create the directory.
        $rollBackFile = $rollBackDir . '/.moodle-plugin-ci/moodle-plugin-ci-old.phar';

        // Use reflection to test the protected method.
        $method = new \ReflectionMethod($command, 'getBackupPath');
        $this->assertSame($rollBackFile, $method->invoke($command, $rollBackDir));
    }
}
